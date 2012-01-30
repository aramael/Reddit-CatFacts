<?php
require "twilioLibrary/Services/Twilio.php";

/**
 * CatFacts Message Constructor
 *
 * @category Services
 * @author   Aramael Pena-Alcantara <aramael@pena-alcantara.com>
 * @license  http://creativecommons.org/licenses/MIT/ MIT
 */
class Messages extends Apollo{
	const ACCOUNT_SID = "ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";
	const AUTH_TOKEN = "YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY";
	const SERVICE_NUMBER = "NNNNNNNNNN";
	private $twillio_client;
	
	public function __construct(){
		$this->twillio_client = new Services_Twilio(self::ACCOUNT_SID, self::AUTH_TOKEN);
	}
	
	/**
	 * Prepare Messages
	 *
	 * Split long messages into 160 character strings and number each message at the beginning,
	 * returns an array of messages or false if a string is not provided.
	 *
	 * @param string $msg
	 */
	
	private function prepareMessage($msg){
		if (is_string($msg)){
			$msg_len = strlen($msg);
			if ($msg_len>160){
				$num_msgs = ceil($msg_len/152);
				for($i = 0; $i < $num_msgs; $i++){
					$start_char = 152*$i;
					$temp_msg = substr($msg, $start_char, 152);
					$msg_num = $i+1;
					$return_msg[] = "(".$msg_num." of ".$num_msgs.")".$temp_msg;
				}
			}else{
				$return_msg[] = $msg;
			}
			return $return_msg;
		}
		return false;
	}
	
	public function sendMessage($to, $message){
		if (isset($to,$message)){
			$messages = $this->prepareMessage($message);
			foreach($messages as $message){
				$this->twillio_client->account->sms_messages->create(self::SERVICE_NUMBER, $to, $message);
			}
			return true;
		}
		return false;
	}
}


?>