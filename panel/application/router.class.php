<?php

class router {
	/*
	* @the registry
	*/
	private $registry;

	/*
	* @the controller path
	*/
	private $path;

	private $args = array();

	public $file;

	public $controller;

	public $action;

	function __construct($registry) {
    	$this->registry = $registry;
	}

	/**
	*
	* @set controller directory path
	*
	* @param string $path
	*
	* @return void
	*
	*/
	function setPath($path) {

		/*** check if path i sa directory ***/
		if (is_dir($path) == false) {
			throw new Exception ('Invalid controller path: `' . $path . '`');
		}
		/*** set the path ***/
 		$this->path = $path;
	}

	/**
	*
	* @load the controller
	*
	* @access public
	*
	* @return void
	*
	*/
	public function loader()
	{
		/*** check the route ***/
		$this->getController();

		/*** if the file is not there diaf ***/
		if (is_readable($this->file) == false) {
			// echo $this->file;
			// die ('404 Not Found');
			$this->file		  = $this->path.'/error404.php';
   		    $this->controller = 'error404';	
		}

		/*** include the controller ***/
		include $this->file;

		/*** a new controller class instance ***/

		$class 		= $this->controller . 'Controller';
		$controller = new $class($this->registry);

		/*** check if the action is callable ***/
		if (is_callable(array($controller, $this->action)) == false) {
			$action = 'index';
		} else {
			$action = $this->action;
		}
		/*** run the action ***/
		$controller->$action();
	}

	/**
	*
	* @get the controller
	*
	* @access private
	*
	* @return void
	*
	*/
	private function getController() {
		//.........................login check..........and.........update time.......
		if (isset($_SESSION['login_user'])) {
			$id  = $_SESSION['login_user'];
			$q 	 = new query;
			$db  = db::getInstance();
			$utf = $db->prepare('SET NAMES utf8');
			$utf->execute();
			$query_update = "UPDATE `user_data` SET `last_time`='" . (time()+120) . "' WHERE `id` = '$id' ";
			$stmt         = $db->prepare( $query_update );
			$stmt->execute();
		}
		/*** get the route from the url ***/
		$route = (empty($_GET['page'])) ? '' : $_GET['page'];

		if (empty($route)) {
			$route = 'home';
		} else {
			$access 	  = array('home', 'login');
			$admin_access = array('controlpanel', 'log_out');
			// check login
			$check = false;
			if ($_SESSION['address'] != '') {
	        	foreach($_SESSION['address'] as $i => $v) {
			        if ($_GET['action'] == '' && $v == $_GET['page']) {
			  			$check = true;
					} elseif ($_SESSION['access'] == 'all' || $_GET['action'] == 'pardis') {
						$check = true;
					} elseif ($v == $_GET['page'] . '&action=' . $_GET['action']) {
				    	$check = true;
					}
				}
			}
			if (in_array($_GET['page'], $admin_access)) {
				$check = true;
				//echo ($check)? 't':'f';
			}		

	        if (!in_array($route, $access))	{
			  	if (@$_SESSION['login'] != md5($_SERVER['REMOTE_ADDR'] . date("Y-m-d") . 'omg-panel') or $check != true) {
				    header('Location: ?page=home&error=access-denied');
				     exit;
		    	}
			}

		    /*** get the parts of the route ***/
		    $parts = explode('/', $route);
		    $this->controller = $parts[0];
		    if (isset( $parts[1])) {
				$this->action = $parts[1];
		    }
		}

		if (empty($this->controller)) {
			$this->controller = 'home';
		}

		/*** Get action ***/
		if (empty($this->action)) {
			$this->action = 'home';
		}

		/*** set the file path ***/
	    $this->file = $this->path . '/'. $this->controller . '.php';
	}
};
?>
