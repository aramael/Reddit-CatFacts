<?php
class Apollo {
	public static $dbh;
	private static $_host = "";
	private static $_db = "";
	private static $_user = "";
	private static $_pass = "";

    public function __construct () {
    	self::$dbh = new PDO("mysql:host=".self::$_host.";dbname=".self::$_db, self::$_user, self::$_pass);
    }

	/**
	 * Fetches All Active Users
	 */	
	public function getActiveUsers(){
		$stmt = self::$dbh->prepare("SELECT * FROM users WHERE active = 1");
		$stmt->execute();
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$users = $stmt->fetchAll();	
		return $users;
	}
		
	/**
	 * Fetch CatFact from MySQL Database
	 *
	 * Fetches one random fact from the database that a particular user has not seen before
	 * 
	 * @param int $uid
	 *
	 */		
	public function getCatFact($uid){
		if (isset($uid) && is_int($uid)){
			$stmt = self::$dbh->prepare("SELECT * FROM cat_facts WHERE id NOT IN(SELECT msg FROM `log` WHERE `action` = 'sent' AND uid = :uid) ORDER BY RAND() LIMIT 1");
			$stmt->bindParam(":uid", $uid, PDO::PARAM_INT, 13);
			$stmt->execute();
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$msg = $stmt->fetch();
			return $msg;
		}
		return false;		
	}

	/**
	 * Fetch a users last message sent by the server
	 *
	 * @param int $uid
	 */		
	public function getLastMessage($uid){
		if (isset($uid) && is_int($uid)){
			$stmt = self::$dbh->prepare("SELECT msg FROM `log` WHERE uid = :uid AND action = 'sent' ORDER BY timestamp DESC LIMIT 1");
			$stmt->bindParam(":uid", $uid, PDO::PARAM_INT, 11);
			$stmt->execute();
			$stmt->setFetchMode(PDO::FETCH_OBJ);
			$old_msg = $stmt->fetch()->msg;
			return $old_msg;
		}
		return false;
	}

	/**
	 * Look up a User by their Phone Number
	 *
	 * @param string $phoneNumber
	 */		
	public function getUser($phoneNumber){
		if (isset($phoneNumber) && is_string($phoneNumber)){
			$stmt = self::$dbh->prepare("SELECT uid FROM users WHERE phoneNumber = :phoneNumber LIMIT 1");
			$stmt->bindParam(":phoneNumber", $phoneNumber, PDO::PARAM_STR, 13);
			$stmt->execute();
			$stmt->setFetchMode(PDO::FETCH_OBJ);
			$user = $stmt->fetch()->uid;
			return $user;
		}
		return false;
	}
	
	/**
	 * Insert Communication into MySQL Log
	 *
	 * @param int $uid
	 * @param string $action
	 * @param string $msg
	 */	
	public function insertToLog($uid, $action, $msg){
		if (isset($uid, $action, $msg) && is_string($msg) && is_int($uid)&&($action == "sent" || $action == "recieved")){
			$stmt = self::$dbh->prepare("INSERT INTO `log` (`id`, `uid`, `timestamp`, `action`, `msg`) VALUES (NULL, :uid, CURRENT_TIMESTAMP, :action, :msg);");
			$stmt->bindParam(":uid", $uid, PDO::PARAM_INT, 11);
			$stmt->bindParam(":action", $action, PDO::PARAM_STR, 13);
			$stmt->bindParam(":msg", $msg, PDO::PARAM_STR, 13);
			$stmt->execute();
			return true;
		}
		return false;		
	}
	
	public function unregisterUser($uid){
		if(isset($uid) && is_int($uid)){
			$stmt = self::$dbh->prepare("UPDATE `users` SET `active` =  '0' WHERE `uid` =:uid;");
			$stmt->bindParam(":uid", $uid, PDO::PARAM_INT, 11);
			$stmt->execute();
		}
		return false;
	}
	
	public function __destruct(){
		self::$dbh = null;
	}
}