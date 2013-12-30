<?php

class controlpanelController Extends baseController {

	public function index() {

		$content = '<h2>Welcome </h2>' .
				   '<!-- End .shortcut-buttons-set -->' .
				   '<!--<div class="clear"></div> <!-- End .clear -->';

		$this->registry->template->page_content = $content;

		$this->registry->template->show('controlpanel');
	}
};

?>
