<?php namespace Listener;

/* Receive donation receipts automatically, to ensure donations are acknowledged and processed quickly and transparently */

// Watch out as this will catch any errors and return 200, which paypal will consider a successful acknowledgement
require('header.php');

require('contrib/PaypalIPN.php');

use PaypalIPN;

$ipn = new PaypalIPN();

function getPaypalVarOrDefault($var, $default)
{
	if( isset($_POST) && isset($_POST[$var]) ) return $_POST[$var];
	else return $default;
}

$verified = $ipn->verifyIPN();
if ($verified) {
	$userID = (int)(getPaypalVarOrDefault('custom','1'));
	$email = $DB->escape(getPaypalVarOrDefault('payer_email',''));
	$gross = (float)(getPaypalVarOrDefault('mc_gross','0'));
	$currency = $DB->escape(getPaypalVarOrDefault('mc_currency',''));
	$filteredStatus = 'Unknown';
	$status = strtolower(getPaypalVarOrDefault('payment_status','Unknown'));
	foreach(array('Canceled_Reversal','Completed','Created','Denied','Expired','Failed','Pending','Refunded','Reversed','Processed','Voided') as $validStatus)
	{
		if( $status === strtolower($validStatus) )
		{
			$filteredStatus = $validStatus;
		}
	}

	$DB->sql_put("INSERT INTO wD_PaypalIPN (userID, email, value, currency, status, receivedTime) VALUES (".$userID.",'".$email."',".$gross.",'".$currency."','".$filteredStatus."',".time().")");
	$DB->sql_put("COMMIT");
}

// Reply with an empty 200 response to indicate to paypal the IPN was received correctly.
header("HTTP/1.1 200 OK");
