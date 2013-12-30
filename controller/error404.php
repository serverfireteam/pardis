<?php

class error404Controller Extends baseController {

	public function index()
	{
		$this->registry->template->page_content = '';
        $this->registry->template->content 	    = 'This is the 404.';
        $this->registry->template->show('error404');
	}
};
?>
