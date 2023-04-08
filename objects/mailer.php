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

require_once("contrib/PHPMailer_v5.1/PHPMailerAutoload.php");

/**
 * A PHPMailer interface object, currently only used for validation e-mails but
 * in future will also be used to optionally send notifications.
 *
 * @package Base
 */
class Mailer
{
	private $PHPMailer;

	private $useDebug=false;

	public function __construct()
	{
		if ( Config::$mailerConfig["UseDebug"] )
			$this->useDebug=true;

		$this->PHPMailer = new PHPMailer();

		$this->PHPMailer->setFrom(Config::$mailerConfig['From'], Config::$mailerConfig['FromName']);
		$this->PHPMailer->addReplyTo(Config::$mailerConfig['From'], Config::$mailerConfig['FromName']);
		$this->PHPMailer->wordWrap = 50;
		$this->PHPMailer->isHTML(true);

		if ( Config::$mailerConfig["UseSendmail"] )
		{
			foreach(Config::$mailerConfig['SendmailSettings'] as $name=>$value)
				$this->PHPMailer->{$name} = $value;
			$this->PHPMailer->IsSendmail();
		}
		elseif ( Config::$mailerConfig["UseSMTP"] )
		{
			$this->PHPMailer->IsSMTP();

			$SMTPSettings = array('Host','Port','SMTPAuth','Username','Password','SMTPSecure', 'SMTPOptions');
			foreach($SMTPSettings as $SMTPSetting)
			{
				if ( isset(Config::$mailerConfig['SMTPSettings'][$SMTPSetting]) )
					$this->PHPMailer->{$SMTPSetting} = Config::$mailerConfig['SMTPSettings'][$SMTPSetting];
			}
		}
		elseif ( !Config::$mailerConfig["UseMail"] )
			trigger_error(l_t("No mailer type chosen; either sendmail, smtp, or mail need to be selected for e-mailing."));
	}

	public function SetReplyTo($address, $name)
	{
		$this->PHPMailer->clearReplyTos();
		$this->PHPMailer->addReplyTo($address, $name, false);
	}
	
	private function Clear()
	{
		if( $this->useDebug ) return;

		$this->PHPMailer->Subject = '';
		$this->PHPMailer->Body = '';
		$this->PHPMailer->AltBody = '';
		$this->PHPMailer->isHTML(false);
		$this->PHPMailer->clearAllRecipients();
		$this->PHPMailer->clearAttachments();
		$this->PHPMailer->clearReplyTos();
	}

	public function Send(array $to, $title, $message)
	{
		if( $this->useDebug )
		{
			print '<p>'.l_t('Mailed "%s": <b>%s</b> - %s</p>','<em>&lt;'.implode('&gt;, &lt;',$to).'&gt;</em>',$title,$message);
			return;
		}

		$title = l_t('webDiplomacy: %s (no reply)',$title);
		$this->PHPMailer->Subject = $title;


		foreach( $to as $email=>$name )
		{
			$wrappedmessage = '
<p>'.l_t('Hello %s,',$name).'</p>
<p style="color:#222">
'.$message."
</p>
<p style='color:#555'>".l_t('Kind regards,')."<br />
".Config::$mailerConfig['FromName']."</p>

<p style='font-size:90%; font-style:italic; color:#555'>
".l_t("This message was generated by a webDiplomacy server, sent from %s to %s (%s) at %s (GMT+0), on behalf of %s.",
	Config::$mailerConfig['From'],
	$email,
	$name,
	gmdate("Y-m-d\TH:i:s\Z"),
	// Check for REMOTE_ADDR as this might be from a console
	htmlentities(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'webdiplomacy.net')
)."<br /><br />

".l_t("If this e-mail was unexpected it can be ignored, please don't reply.")."<br />
".l_t("If you repeatedly get unexpected / unwanted e-mails from this address please contact the server admin at %s to have the matter investigated.",
	"<a href='mailto:".Config::$adminEMail."'>".Config::$adminEMail."</a>")."
</p>
";

			$this->PHPMailer->Body = $wrappedmessage;
			$this->PHPMailer->AltBody =
				preg_replace('/<[^>]*>/','', // Get rid of any other HTML
				preg_replace('/<\/?p>/',"\n\n", // Convert paragraph ends to newlines
				preg_replace('/<br ?\/?>/',"\n", // Convert breaks to newlines
				preg_replace("/\n/","", // Get rid of newlines
				$wrappedmessage))));

			$this->PHPMailer->addAddress($email, $name);

			$this->PHPMailer->send();
			$this->PHPMailer->clearAddresses();
		}

		if ( $this->PHPMailer->isError() )
		{
			throw new Exception(l_t("Mailer error: %s",$this->PHPMailer->ErrorInfo));
		}

		$this->Clear();
	}
}

?>
