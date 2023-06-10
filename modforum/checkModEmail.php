<?php
/*
    Copyright (C) 2020 Oliver Auth

	This file is part of vDiplomacy.

    vDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    vDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with vDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */
/*
defined('IN_CODE') or die('This script can not be run by itself.');

$imap = imap_open(Config::$modEMailServerIMAP, Config::$modEMailLogin, Config::$modEMailPassword);
$headers = imap_search($imap, 'UNSEEN');
if ($headers != false)
{
	$lastDate = $Misc->vDipLastMail;
	foreach ($headers as $val)
	{
		$overview =  imap_fetch_overview ( $imap , $val );
		$subject = $DB->escape(quoted_printable_decode($overview[0]->subject));
		$from    = $DB->escape(quoted_printable_decode($overview[0]->from));
		$date    = $DB->escape(quoted_printable_decode($overview[0]->udate));
		$body    = $DB->escape(quoted_printable_decode(imap_fetchbody($imap,$val,1.1))); 
        
		if ($body == '') $body = $DB->escape(quoted_printable_decode(imap_fetchbody($imap,$val,1))); 
		$body = preg_replace(array('/<[^>]+>/i', '/<[^>]+$/i'),array(' ', ' '), $body);
		if (strlen($body) > 490) $body = substr($body,0,490).'...';
		$body = str_replace('\r\n','<br />',$body);
		
		if ($date > $Misc->vDipLastMail)
		{
			require_once('modforum/libMessage.php');

			$newmessage = ModForumMessage::send(0, 2,
						'There is a new Mail in the mod-team-inbox (<a href="'.Config::$modEMailServerHTTP.'">Webmail</a>)<br />'.$body,
						$subject.' (From: '.$from.')',
						'ThreadStart');
						
			$lastDate = max ($date, $lastDate);
			print 'There is a new Mail '.$date.' in the mod-team-inbox.<br>'.$subject.' (From: '.$from.')'."<br />\n";
		}
		
	}
	$Misc->vDipLastMail = $lastDate;
	$Misc->write();
}
imap_close($imap);

?>
*/