<?php
// include Database Connection
require "resources/apollo.class.php";
require "resources/messages.class.php";

/**
 * Instantiate A New Database
 */
$apollo = new Apollo;

/**
 * Instantiate A New Message
 */
$message = new Messages;

/**
 * Fetch Active Users from Database
 */
$users = $apollo->getActiveUsers();

if (is_array($users)){
	/**
	 * Send a New CatFact to Each Active User
	 */
	foreach($users as $user){
		$uid = $user['uid'];
		$phoneNumber = "+1".$user['phoneNumber'];
		$msg = $apollo->getCatFact((int)$uid);
		
		/**
		 * Attempt to SEND CatFact
		 */
		if( $message->sendMessage($phoneNumber, $msg['fact']) ){
			/**
			 * Insert Sent CatFact into Log
			 */
			$apollo->insertToLog((int)$uid, 'sent', 'fact-'.$msg['id']);
		}
	}
}