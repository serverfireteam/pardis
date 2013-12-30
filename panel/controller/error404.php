<?php

class error404Controller Extends baseController {

	public function index() {
    	$this->registry->template->title         = 'error 404';
        $this->registry->template->page_title    = 'error 404';
        $this->registry->template->error_message = 'This page is not available !';

        $this->registry->template->show('error404');
	}
};
?>
