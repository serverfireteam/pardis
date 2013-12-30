<?php

class db {

	/*** Declare instance ***/
	private static $instance = NULL;
	private static $instance_remot = NULL;
	/**
	*
	* the constructor is set to private so
	* so nobody can create a new instance using new
	*
	*/
	private function __construct() {
		/*** maybe set the db name here later ***/
	}

	/**
	*
	* Return DB instance or create intitial connection
	*
	* @return object (PDO)
	*
	* @access public
	*
	*/
	public static function getInstance() {

		if (!self::$instance) {
			self::$instance = new PDO("mysql:host=localhost;dbname=db_name", 'root', '', array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));
	    	self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	}

		return self::$instance;
	}

	/**
	*
	* Like the constructor, we make __clone private
	* so nobody can clone the instance
	*
	*/
	private function __clone() {
	}

	public function jointb($table1, $table2, $field1, $field2) {

        $db  = db::getInstance();
		$utf = $db->prepare('SET NAMES utf8');
		$utf->execute();

        $sql  = "select $field1 from $table1 ";
	    $stmt = $db->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetchAll();
		if (count($result) != 0) {
			foreach ($result as $row) {
				$sql2 = "select * from $table2 where $field2='$row[$field1]' ";
		 		$sth  = $db->prepare($sql2);
	  	 		$sth->execute();
		 		$result2[$row[$field1]] = $sth->fetchAll();
        	} // end foreach
	    	return $result2;
		} // end if
		else return;		
	}

	// db::jointbM('message_category','message','name','category','student_num');
	public function jointbM($table1, $table2, $field1, $field2) {

	    $db  = db::getInstance();
		$utf = $db->prepare('SET NAMES utf8');
		$utf->execute();

        $sql = "select * from $table1, $table2 where  $table1.$field2 = :st_n and
				$table2.$field1 = $table1.id  GROUP BY $table1.name";
	    $stmt = $db->prepare($sql);
		$stmt->bindParam(':st_n', $_SESSION['student_num'] , PDO::PARAM_STR, 256);
		$stmt->execute();
		$result = $stmt->fetchAll();
		if (count($result) != 0) {

			foreach ($result as $row) {
				 $sql2 = "select * from $table2 where $field1 = '$row[0]' order by $table2.id ";
				 $sth  = $db->prepare($sql2);
	  			 $sth->execute();
				 $result2[$row['name']] = $sth->fetchAll();
      		} // end foreach
	
	     	return $result2;
		 } // end if
		 else return;		 
	}

	public function fetch($table) {

	    $db  = db::getInstance();
		$utf = $db->prepare('SET NAMES utf8');
		$utf->execute();

        $sql  = "select * from `$table` ";
	    $stmt = $db->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetchAll();
		return $result;
	}

	public static  function runQuery($query, $fetch = 'fetchAll') {
	    $db  = db::getInstance();
		$utf = $db->prepare('SET NAMES utf8');
		$utf->execute();

	    $stmt = $db->prepare($query);
		$stmt->execute();
		if (preg_match("/(SELECT|select)(.*){0,50}/", $query)) {
			return $stmt->$fetch();
		}			
	}

	public function content($table, $field1, $field2) {

    	$db  = db::getInstance();
		$utf = $db->prepare('SET NAMES utf8');
		$utf->execute();

		$sql  = "select * from content where name = '$field1'";
		$stmt = $db->prepare($sql);
		//$stmt->bindParam(':co_name', $name , PDO::PARAM_STR, 256);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if (count($result) != 0) {
			return $result[$field2];
		} else {
			return;
		}
	}

	public function main_title() {
		// Edit Main Title
		return "";
	}

}; /*** end of class ***/

?>
