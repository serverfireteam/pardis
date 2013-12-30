<?php

class homeController Extends baseController {

	public function index() {

		$this->registry->template->show('home');
	}
};

?>
