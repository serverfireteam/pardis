<?php

	if ($db) {
		$db = db::getInstance();
	}

	if ($utf) {
		$utf = $db->prepare('SET NAMES utf8');
	    $utf->execute();
	}

	function() {
	}
?>
