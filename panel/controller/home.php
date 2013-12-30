<?php

class homeController Extends baseController {

	public function index() {

		if ($_GET['error'] == 'access-denied') {
			$this->registry->template->error = 'You can not access this page, Please log in. ';
		}

		$this->registry->template->show('home');
	}
};

?>
