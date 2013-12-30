<?php
	class contentController extends baseController
	{
		function index()
		{
			$page = $_GET["action"];

			$this->registry->template->show($page);
		}
	}
?>
