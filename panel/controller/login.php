<?php

class loginController Extends baseController 
{
	public function index() 
	{
		if (@$_POST['username'] != '' and @$_POST['password'] != '') {
			$db = db::getInstance();
 			// select from controller for link_list
			$dir = "controller/";
			// Open a known directory, and proceed to read its contents
			if (is_dir($dir)) {	
				if ($dh = opendir($dir)) {
					while (($file = readdir($dh)) !== false) {
						if ($file != '.' and $file != '..') {
							$info = pathinfo( $file);
							$page_name = $info['filename'];
							if ($file != '') {
								$contents = file($dir.$file, FILE_IGNORE_NEW_LINES);
							}
							if ($contents[2] == 'use for link_list') {
								$item = array(0 => $page_name, 1 => $contents[4]);
								$link_list[$contents[3]][] = $item;
							}
						}
					}
					closedir($dh);
				}
			}

			$kind      = false;
	        $ip        = $_SERVER['REMOTE_ADDR'];
			$date      = date("Y-m-d");
			$sql_check = "SELECT * FROM `login_control` WHERE `ip` = '$ip'";
			$stmt      = $db->prepare($sql_check);
			$stmt->execute();
			$re_yet_login = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($re_yet_login != '') {
				list($year, $month, $day) = explode('-', $re_yet_login['last_try']);
				list($year1, $month1, $day1) = explode('-', date("y-m-d"));
				$dif_day = $day - $day1;
				if ($dif_day == 0) {
				        $check_day = 1;
				} else {
					$check_day = 0;
				}
				list($hour,$min)   = explode('-', $re_yet_login['last_time']);
				list($hour1,$min1) = explode('-', date("h:m"));
				$dif_hour          = $hour1 - $hour;
				$dif_min           = $min1 - $min;

				if ($dif_hour == 0) {
					$check_hour = 1;
				} else {
					$check_hour = 0;
				}
				if ($dif_min <15 and $dif_min >= 0) {
					$check_min = 1;
				} else {
					$check_min = 0;
				}
				if ($dif_hour == 1 and $dif_min < -45 and $dif_min > -59) {
					$check_hour = 1;
					$check_min  = 1;
				}

				if ($check_day == 1 and $check_hour == 1 and $check_min == 1) {
					if ($re_yet_login['count'] > 5) {
						$continue = false;
					 	$this->registry->template->error = 'your blocked for next 15 min';
				            	$this->registry->template->show('home');
					} else { //if($re_yet_login['count'] > 2)
						$id           = $re_yet_login['id'];
						$count        = $re_yet_login['count'] + 1;
						$query_update = "UPDATE `login_control` SET   `count` = '".$count."',`last_try`='".date("y-m-d").
										"',`last_time`='".date("h:m")."' WHERE `ip` = '".$_SERVER['REMOTE_ADDR']."' ";
						$stmt = $db->prepare($query_update);
					 	$stmt->execute();
						if ($_POST['user'] == 'admin') {
							$user         = $_POST['username'];
							$pass         = md5($_POST['password']);
							$select_admin = 'SELECT * FROM `admin` WHERE `username` =:log_user  and `password`=:log_pass ';
						}
						$stmt = $db->prepare($select_admin);

						$stmt->bindParam(':log_user', $user, PDO::PARAM_STR, 256);
						$stmt->bindParam(':log_pass', $pass , PDO::PARAM_STR, 256);
						$stmt->execute();
						$re_admin = $stmt->fetch(PDO::FETCH_ASSOC);
						if ($re_admin != '') {
							if ($kind == false) {
								foreach ($link_list as $key => $value) {
        	   						foreach ($value as $sub_key => $sub_value) {
            							$_SESSION['address'][] = $sub_value[0].'&action=pardis';
									}
								}
								$_SESSION['access'] = 'all';
							}
							$_SESSION['login'] = md5($_SERVER['REMOTE_ADDR'].$date.'omg-panel');
					        $_SESSION['users'] = $user;
							header('Location: ?page=controlpanel');

						} else { //if($re_admin != '')

							$this->registry->template->error        = 'username or password is wrong';
							$this->registry->template->page_content = '<a></a>';
							$this->registry->template->show('home');
						}
					  }
				} else { //if($check_day == 1 and $check_hour == 1 and $check_min == 1)

					$query_update = "UPDATE `login_control` SET   `count` = '1',`last_try`='".date("y-m-d").
									"',`last_time`='".date("h:m")."' WHERE `ip` = '".$_SERVER['REMOTE_ADDR']."' ";
					$stmt         = $db->prepare($query_update);
					$stmt->execute();
					if ($_POST['user'] == 'admin') {
						$user	      = $_POST['username'];
						$pass	      = md5($_POST['password']);
						$select_admin = 'SELECT * FROM `admin` WHERE `username` =:log_user  and `password`=:log_pass ';
					}
					$stmt = $db->prepare($select_admin);
					$stmt->bindParam(':log_user', $user, PDO::PARAM_STR, 256);
					$stmt->bindParam(':log_pass', $pass , PDO::PARAM_STR, 256);
					$stmt->execute();
					$re_admin = $stmt->fetch(PDO::FETCH_ASSOC);
					if ($re_admin != '') {
						if ($kind == false) {
							foreach ($link_list as $key => $value) {
	           					foreach ($value as $sub_key => $sub_value) {
	       							$_SESSION['address'][] = $sub_value[0].'&action=pardis';
								}
							}
							$_SESSION['access'] = 'all';
						}
						$_SESSION['login'] = md5($_SERVER['REMOTE_ADDR'].$date.'omg-panel');
						$_SESSION['users'] = $user;
						header('Location: ?page=controlpanel');

					} else { //if($re_admin != '')
						$this->registry->template->error = 'username or password is wrong';
						$this->registry->template->show('home');
					}
				}
			} else { //if($re_yet_login !='')

				$myquery = "insert into `login_control` (ip,last_try,last_time,count) values ('".
						   $_SERVER['REMOTE_ADDR']."','".date("y-m-d h:m")."','".date("h:m")."','1')";
				$stmt    = $db->prepare($myquery);
				$stmt->execute();
				if ($_POST['user'] == 'admin') {
					$user         = $_POST['username'];
					$pass         = md5($_POST['password']);
					$select_admin = 'SELECT * FROM `admin` WHERE `username` =:log_user  and `password`=:log_pass ';
				}
				$stmt = $db->prepare($select_admin);
				$stmt->bindParam(':log_user', $user,  PDO::PARAM_STR, 256);
				$stmt->bindParam(':log_pass', $pass , PDO::PARAM_STR, 256);
				$stmt->execute();
				$re_admin = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($re_admin != '') {
					if ($kind == false) {
				  		foreach ($link_list as $key => $value) {
							foreach ($value as $sub_key => $sub_value) {
								$_SESSION['address'][]=$sub_value[0].'&action=pardis';
							}
						}
						$_SESSION['access'] = 'all';
				  	}
				  	$_SESSION['login'] = md5($_SERVER['REMOTE_ADDR'].$date.'omg-panel');
				  	$_SESSION['users'] = $user;
					header('Location: ?page=controlpanel');
				} else { //if($re_admin != '')
					$this->registry->template->error = 'username or password is wrong';
					$this->registry->template->show('home');
				}
			}

		} else {
			$this->registry->template->error = 'please insert username and password';
	        $this->registry->template->show('home');
		}
	}
};
?>
