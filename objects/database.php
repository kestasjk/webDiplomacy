<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas

	This file is part of webDiplomacy.

    webDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('IN_CODE') or die('This script can not be run by itself.');

// TODO: Use MySQLi prepared statements rather than the old MySQL style functions

/**
 * A MySQL DB interaction object.
 *
 * @package Base
 */
class Database {

	public function affected() {
		return mysqli_affected_rows($this->link);
	}

	/**
	 * The MySQL resource object used to identify the database connection
	 */
	private $link;

	/**
	 * @var int The number of times sql_put() has been called
	 */
	public $putqueries=0;

	/**
	 * @var int The number of times sql_tabl() has been called
	 */
	public $getqueries=0;

	/**
	 * This is like implode with a wrapper around it, because using implode for this function again
	 * and again got messy
	 *
	 * @param string $before Text before packed array
	 * @param array $array The array to pack
	 * @param string $after Text after packed array
	 * @param string $joiner Text to join the array pieces
	 *
	 * @return string Packed array
	 */
	public static function packArray($before, array $array, $after, $joiner)
	{
		if ( ! count($array) )
		{
			return '';
		}
		else
		{
			return $before . implode($after.$joiner.$before, $array) . $after;
		}
	}

	/**
	 * Initialize the database connection
	 */
	public function __construct()
    {
      $this->link = mysqli_connect(Config::$database_socket,
				Config::$database_username, Config::$database_password);

		if( ! $this->link )
			trigger_error(l_t("Couldn't connect to the MySQL server, if this problem persists please inform the admin."));

		if( ! mysqli_select_db($this->link,Config::$database_name) )
			trigger_error(l_t("Connected to the MySQL server, but couldn't access the specified database. ".
						"If this problem persists please inform the admin."));

		$this->enableTransactions();

		// Turn off error reporting to prevent exceptions being thrown; webDiplomacy checks for errors
		// within this wrapper class.
		mysqli_report(MYSQLI_REPORT_OFF);
	}

	public function enableTransactions()
	{
		/*
		 * Using InnoDB's default transaction isolation level (REPEATABLE-READ) a snapshot is taken when you
		 * first read.
		 * Any other changes made by other transactions aren't read, except for LOCK IN SHARE MODE, which will
		 * always get the latest data from the database.
		 *
		 * The amazing thing is, locking FOR UPDATE /does not/ get the latest data from the database, despite
		 * it being a "tougher" lock than LOCK IN SHARE MODE (read/write-lock instead of just read-lock).
		 *
		 * So what used to happen is when joining a game and members were locked FOR UPDATE, and a player would
		 * join based on the false assumption that the game and member rows must be the latest rows.
		 *
		 * SELECT whatever			|
		 * 							| SELECT whatever
		 * LOCK game etc FOR UPDATE	|
		 * CHECK player can join	|
		 * INSERT player into game	| LOCK game etc FOR UPDATE [waiting...]
		 * COMMIT					|
		 * 							| CHECK player can join (using same info as when SELECT whatever happened!!)
		 * 							| INSERT player into game
		 * 							| COMMIT
		 *
		 * A player has joined the game /twice/ despite read/write locking..
		 *
		 *
		 * Because of this we use READ COMMITTED, which ensures that not only LOCK IN SHARE MODE gets the latest
		 * committed data, but /all selects/ get the latest data (having the latest data is a pretty useful
		 * transaction-mode)
		 */
		$this->sql_put("SET AUTOCOMMIT=0, NAMES utf8, time_zone = '+0:00'");
		$this->sql_put("SET SQL_MODE='NO_ENGINE_SUBSTITUTION'"); // This statement is just intended to make sure the server isn't in strict mode
		$this->sql_put("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED"); // 20220515 added SESSION to ensure all transactions run under READ COMMITTED, including after first commit// Changed from READ COMMITTED which was causing too many deadlocks; use LOCK IN SHARE MODE when the latest query is needed instead
		
		// This seems odd since SET AUTOCOMMIT=0 is enough to set it for the whole session, but SET TRANSACTION ISOLATION LEVEL doesn't set it for the whole session.. https://dev.mysql.com/doc/refman/5.6/en/innodb-autocommit-commit-rollback.html
	}

	public function disableTransactions()
	{
		// For queries that update a lot of records but aren't transactionally important e.g. reliability ratings 
		// transactions should be disabled temporarily to avoid deadlocks due to high frequency bot requests
		$this->sql_put("SET AUTOCOMMIT=1, NAMES utf8, time_zone = '+0:00'");
		$this->sql_put("SET SQL_MODE='NO_ENGINE_SUBSTITUTION'");
		$this->sql_put("SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
	}

	/**
	 * Close the database connection
	 */
	public function __destruct()
	{
		if ( ! mysqli_close($this->link) )
		{
			// This function may be called after/before the other objects are around.
			die(l_t("Could not successfully close connection to database."));
		}
	}

	/**
	 * Sanitize incoming strings, leaving newlines. Suitable for messages.
	 * Replace newlines with <br />, and allow <br /> through the filter.
	 *
	 * @param string $text The string to be escaped
	 *
	 * @return string The sanitized string
	 */
	public function msg_escape($text, $htmlAllowed=false)
	{
		// str_replace is binary safe, nl2br isn't
		$text = str_replace("\r\n",'<br />',$text);
		$text = str_replace("\n",'<br />',$text);
		$text = str_replace("\r",'<br />',$text);

		$text = $this->escape($text, $htmlAllowed);

		$text = str_replace('&lt;br /&gt;', '<br />', $text);

		return $text;
	}

	/**
	 * Un-escape something escaped
	 *
	 * @param string $text The text to un-escape
	 *
	 * @return string Dangerous text
	 */
	public function un_msg_escape($text)
	{
		// str_replace is binary safe, nl2br isn't
		$text = str_replace("\r\n",'<br />',$text);
		$text = str_replace("\n",'<br />',$text);
		$text = str_replace("\r",'<br />',$text);

		$text = $this->escape($text);

		$text = str_replace('&lt;br /&gt;', '<br />', $text);

		return $text;
	}

	/**
	 * Sanitize incoming strings, filtering out all HTML. Suitable for all
	 * data.
	 *
	 * @param string $text The string to be escaped
	 *
	 * @return string The sanitized string
	 */
	public function escape($text, $htmlAllowed=false)
	{
		$text = (string) $text;
		$text = mysqli_real_escape_string($this->link,$text);
		if ( !$htmlAllowed )
			$text = htmlentities( $text , ENT_NOQUOTES, 'UTF-8');
		return $text;
	}

	/**
	 * Query the database and return a MySQL table resource.
	 *
	 * @param string $sql A safe, pre-sanitized SQL query
	 *
	 * @return resource A MySQL table resource
	 */
	public function sql_tabl($sql)
	{
		global $User;

		$this->getqueries++;

		if( defined('RUNNINGFROMCLI') ) print $sql."\n";
		
		if( Config::$debug )
			$timeStart=microtime(true);

		if ( ! ( $resource = mysqli_query($this->link, $sql ) ) )
		{
			trigger_error(mysqli_error($this->link));
		}

		if( Config::$debug )
			$this->profiler($timeStart, $sql);

		return $resource;
	}

	private $totalQueryTime=0.0;
	private $badQueries=array();
	private $slowestQuery=0.0;
	private function profiler($timeStart, $sql)
	{
		$timeTaken=microtime(true)-$timeStart;
		if($timeTaken>$this->slowestQuery)
			$this->slowestQuery = $timeTaken;

		$this->totalQueryTime += $timeTaken;

		$currentAv = ($this->totalQueryTime/($this->getqueries+$this->putqueries));

		if ( $timeTaken > 2*$currentAv )
		{
			$this->badQueries[] = array($timeTaken, $sql);
		}
	}

	public function profilerPrint()
	{
		$buf = '';

		$stats = array(
			'Total query time'=>$this->totalQueryTime,
			'Average query time'=>($this->totalQueryTime/($this->getqueries+$this->putqueries)),
			'Slowest query'=>$this->slowestQuery
		);

		$buf .= '<table class = "footerMax">';
		foreach($stats as $name=>$val)
			$buf .= '<tr><td>'.l_t($name).':</td><td>'.$val.' sec</td></tr>';
		$buf .= '</table>';

		$buf .= '<p class = "footerMax"><strong>'.l_t('Bad queries:').'</strong></p>';
		$buf .= '<table class = "footerMax">';
		foreach($this->badQueries as $pair)
			$buf .= '<tr><td style="width:5%">'.$pair[0].' sec</td><td>'.$pair[1].'</td></tr>';
		$buf .= '</table>';

		return $buf;
	}

	/**
	 * Take the next numbered MySQL row from a table resource, or return false
	 * if no rows remain $row[0]
	 *
	 * @param resource $tabl A MySQL table resource
	 *
	 * @return array|bool A numbered row containing one row from the table, or false if there are no rows remaining.
	 */
	public function tabl_row($tabl)
	{
		if ( $tabl == false )
		{
			return false;
		}
		else
		{
			$row = mysqli_fetch_row($tabl);

			if ( ! $row )
			{
				mysqli_free_result($tabl);
				return false;
			}
			else
			{
				return $row;
			}
		}
	}

	/**
	 * Take the next named MySQL row from a table resource, or return false
	 * if no rows remain $row['foo']
	 *
	 * @param resource $tabl A MySQL table resource
	 *
	 * @return array|bool A named row containing one row from the table, or false if there are no rows remaining.
	 */
	public function tabl_hash($tabl)
	{
		if ( $tabl == false )
		{
			return false;
		}
		else
		{
			$row = mysqli_fetch_assoc($tabl);
			if ( ! $row )
			{
				mysqli_free_result($tabl);
				return false;
			}
			else
			{
				return $row;
			}
		}
	}

	/**
	 * Run a SQL query and return a single numbered row $row[0]
	 *
	 * @param string $sql The SQL query
	 *
	 * @return array|bool A numbered row, or false if no rows were returned
	 */
	public function sql_row($sql)
	{
		if( defined('RUNNINGFROMCLI') ) print $sql."\n";

		$tabl = $this->sql_tabl($sql);
		$row = $this->tabl_row($tabl);

		// Free the table resource from memory, if it hasn't already been freed by tabl_row
		if ( $row ) mysqli_free_result($tabl);

		return $row;
	}

	/**
	 * Run a SQL query and return a single named row $row['foo']
	 *
	 * @param string $sql The SQL query
	 *
	 * @return array|bool A named row, or false if no rows were returned
	 */
	public function sql_hash($sql)
	{
		$tabl = $this->sql_tabl($sql);
		$row = $this->tabl_hash($tabl);

		if( defined('RUNNINGFROMCLI') ) print $sql."\n";

		// Free the table resource from memory, if it hasn't already been freed by tabl_row
		if ( $row ) mysqli_free_result($tabl);

		return $row;
	}

	/**
	 * Run a data insertion SQL query, halting webDip if there is an error
	 *
	 * @param string $sql The data insertion SQL query
	 *
	 * @returns int The ID of the last inserted row, which may be irrelevant if an INSERT/UPDATE query weren't performed
	 */
	public function sql_put($sql)
	{
		global $User;

		$this->putqueries++;

		if( Config::$debug )
			$timeStart=microtime(true);

		if( defined('RUNNINGFROMCLI') ) print $sql."\n";

		if(! mysqli_query($this->link,$sql) )
		{
			trigger_error(mysqli_error($this->link));
		}

		if( Config::$debug )
			$this->profiler($timeStart, $sql);
	}

	/**
	 * Runs a multi-statement SQL script
	 *
	 * @param string $sql The SQL script
	 *
	 * @returns int The ID of the last inserted row, which may be irrelevant if an INSERT/UPDATE query weren't performed
	 */
	public function sql_script($sql)
	{
		if( Config::$debug )
			$timeStart=microtime(true);
		
		if( defined('RUNNINGFROMCLI') ) print $sql."\n";

		if(mysqli_multi_query($this->link,$sql)){
			do{
				if($result=mysqli_store_result($this->link)){ 
					mysqli_free_result($result);
				}
			} while(mysqli_more_results($this->link) && mysqli_next_result($this->link));
		}
		if($error_mess=mysqli_error($this->link)){
			trigger_error($error_mess);
		}

		if( Config::$debug )
			$this->profiler($timeStart, $sql);
	}
	public function last_affected()
	{
		return mysqli_affected_rows($this->link);
	}

	public function last_inserted()
	{
		return mysqli_insert_id($this->link);
	}

	/**
	 * Get a MySQL named lock, will stop the script if the lock cannot be obtained
	 *
	 * @param string $name The name of the lock
	 * @param int[optional] $wait The time to wait before giving up, default is 8 seconds
	 */
	public function get_lock($name, $wait=8)
	{
		list($success) = $this->sql_row("SELECT GET_LOCK('".$name."', ".$wait.")");

		if ( $success != 1 )
		{
			libHTML::error(l_t("A database lock (%s) is required to complete this page safely, but it could not be ".
				"acquired (it's being used by someone else). This usually means the server is running slowly, and ".
				"taking unusually long to complete tasks.",$name)."<br /><br />".
				l_t("Please wait a few moments and try again. Sorry for the inconvenience."));
		}
	}

}
?>
