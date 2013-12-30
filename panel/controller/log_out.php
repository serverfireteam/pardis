<?php

class log_outController Extends baseController {

	public function index() {

		$db  = db::getInstance();
		$utf = $db->prepare('SET NAMES utf8');
		$utf->execute();
		if (isset($_SESSION['login_user'])) {
	 		$id 	      = $_SESSION['login_user'];
	  		$query_update = "UPDATE `user_data` SET `last_time`='' WHERE `id` = '$id' ";
	  		$stmt         = $db->prepare($query_update);
	  		$stmt->execute();
			session_destroy();
			header('Location:../?page=home&error=exit');
		} else {
			session_destroy();
		  	$str_update = "DELETE FROM `login_control` WHERE  `ip`='".$_SERVER['REMOTE_ADDR']."'";
		  	$stmt       = $db->prepare($str_update);
			$bool	    = $stmt->execute();
			$this->registry->template->error = 'Your exit was successful';
			$this->registry->template->show('home');
		}
	}
};
?>
