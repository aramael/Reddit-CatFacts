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

/**#@+
 * @access public
 * @var string 
 */
$from = filter_input(INPUT_POST, 'From', FILTER_SANITIZE_NUMBER_INT);
$from = str_replace ("+1", "", $from);
$usr_body = strtolower(filter_input(INPUT_POST, 'Body', FILTER_SANITIZE_STRING));
/**#@-*/

if (!is_null($from,$usr_body)){
	/**
	 * Look Up USER
	 */	
	if( $uid = $apollo->getUser($from) ){
		/**
		 * Look Up Last Send Text
		 */
		 $last_msg = $apollo->getLastMessage((int)$uid);
		
		/**
		 * Insert Recieved Message into Log
		 */
		$apollo->insertToLog((int)$uid, 'recieved', $usr_body);

		/**
		 * Define User Return Options
		 */
		$unsub = array(
			'cancel-1' => 'You gotta be kitten me! Are you sure you want to unsubscribe? Send YES or NO',
			'cancel-2' => 'Thank You! To finish unsubscribing from CatFacts, reply the following code: '.uniqid(),
			'cancel-' => 'Invalid Unsubscribe Code. You will continue recieving updates from CatFacts. Your unsubscribe code is: '.uniqid(),
			'cancel-end' => 'We are sorry to see you go. You will no longer recieve updates from CatFacts. You were pranked automatically by Aramael Pena-Alcantara.',
			'invalid' => 'Invalid Command! You will continue recieving updates from CatFacts on an hourly basis.',
			'about' => 'This is a project by Aramael Pena-Alcantara. You have recieved these CatFacts as a joke! We are sorry you have had to deal with it.',
			'end' => 'Thank You! We appreciate your patronage. You will continue recieving updates from CatFacts.'
		);
		
		/**
		 * Check if USER has wanted to Unsubscribe
		 */
		if(preg_match("^cancel|unsubscribe|stop^", $usr_body)){
				$return_msg = array('msg' => $unsub['cancel-1'], 'msg-id' => 'cancel-1');
		}elseif(preg_match("^info|information|about^", $usr_body)){
				$return_msg = array('msg' => $unsub['about'], 'msg-id' => 'about');
		}else{
			/**
			 * Has the User Initiated Cancel Dialouge?
			 */
			if (preg_match("^cancel-^", $last_msg)){
				/**
				 * Get Previouse Sent Message Step
				 */
				$cancel_msg = preg_replace("^cancel-^", "", $last_msg);
				switch($cancel_msg){
					case 1:
						/**
						 * Check Last Response YES or NO or ELSE
						 */
						switch(true){
							case (preg_match("^yes^", $usr_body)):
								$return_msg = array('msg' => $unsub['cancel-2'], 'msg-id' => 'cancel-2');
								break;
							case (preg_match("^no^", $usr_body)):
								$return_msg = array('msg' => $unsub['end'], 'msg-id' => 'end');
								break;
							default:
								$return_msg = array('msg' => $unsub['invalid'], 'msg-id' => 'invalid');
								break;
						}
						break;
					default:
						if ($cancel_msg > 5){

							$return_msg = array('msg' => $unsub['end'], 'msg-id' => 'end');
							
						}else{
							$cancel_msg++;
							$return_msg = array('msg' => $unsub['cancel-'], 'msg-id' => 'cancel-'.$cancel_msg);							
						}
						break;
				}
			}else{
				$return_msg = array('msg' => $unsub['invalid'], 'msg-id' => 'invalid');
			}
		}
		
		/**
		 * Attempt to SEND Response
		 */
		if( $message->sendMessage("+1".$from, $return_msg['msg']) ){
			/**
			 * Insert Sent Message into Log
			 */
			$apollo->insertToLog((int) $uid, 'sent', $return_msg['msg-id']);
		}
	}
}