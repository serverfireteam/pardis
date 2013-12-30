<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

	<head>		
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

		<title>Title</title>

		<!-- CSS -->

		<!-- Reset Stylesheet -->
		<link rel="stylesheet" href="css/reset.css" type="text/css" media="screen" />
		<!-- Main Stylesheet -->
		<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
		<!-- Invalid Stylesheet. This makes stuff look pretty. Remove it if you want the CSS completely valid -->
		<link rel="stylesheet" href="css/invalid.css" type="text/css" media="screen" />

        <link rel="stylesheet" href="css/jquery-ui-1.8.20.custom.css" type="text/css" media="screen" />
		<!-- Internet Explorer Fixes Stylesheet -->

		<!--[if lte IE 7]>
			<link rel="stylesheet" href="css/ie.css" type="text/css" media="screen" />
		<![endif]-->

		<!-- Javascripts -->

		<!-- jQuery -->
		<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script>
		<!-- jQuery Configuration -->
		<script type="text/javascript" src="js/fn1.1.js"></script>
		<script type="text/javascript" src="js/simpla.jquery.configuration.js"></script>

        <!--[if IE]><script type="text/javascript" src="js/jquery.bgiframe.js"></script><![endif]-->

		<!-- Internet Explorer .png-fix -->

		<!--[if IE 6]>
			<script type="text/javascript" src="js/DD_belatedPNG_0.0.7a.js"></script>
			<script type="text/javascript">
				DD_belatedPNG.fix('.png_bg, img, li');
			</script>
		<![endif]-->

	</head>

	<body>
		<div id="body-wrapper"> <!-- Wrapper for the radial gradient background -->
		<!--  ................................................................  sidebar.........................................................	-->

		<div id="sidebar">
		<div id="sidebar-wrapper"> <!-- Sidebar with logo and menu -->

			<h1 id="sidebar-title"><a href="#">Title</a></h1>

			<!-- Logo (221px wide) -->
			<div class="logo-wrp">
	            <a href="#"><img id="logo" src="images/logo.png" alt="Pardis" /></a>
            </div>

			<!-- Sidebar Profile links -->
			<div id="profile-links">
				<?php echo show_lang("Hi , welcome back"); ?> <br /><br />
				<a href="?page=log_out" title="Sign Out"><?php echo show_lang("Sign Out"); ?></a>
			</div>

			<ul id="main-nav">  <!-- Accordion Menu -->
			<?php
				$dir = "controller/";
				// Open a known directory, and proceed to read its contents

				if (is_dir($dir)) {
					if ($dh = opendir($dir)) {
						while (($file = readdir($dh)) !== false) {
							if ($file != '.' and $file != '..') {
								$info      = pathinfo( $file);
								$page_name = $info['filename'];
								if ($file != '') {
									$contents = file($dir.$file, FILE_IGNORE_NEW_LINES);
								}
								if ($contents[2] == 'use for link_list') {
									$item 					   = array(0 => $page_name, 1 => $contents[4]);
									$link_list[$contents[3]][] = $item;
								}
							}
						}
						closedir($dh);
					}
				}

				$name    = $_GET['name'];
				$subname = $_GET['subname'];
				if (!empty($link_list)) {
					foreach ($link_list as $key => $value) { ?>
		        	<li>
	                    <? if ($key == $name) { ?>
	                        <a href="#" class="nav-top-item current"><?=$key;?></a>
						<? } else { ?>
                       		<a href="#" class="nav-top-item "><?=$key;?></a>
                        <? } ?>
                        <ul>
	                        <? foreach ($value as $sub_key => $sub_value) { ?>
                            	<? if ($sub_value[1] == $subname) { ?>
                                	<li>
										<a class="current" href="?page=<?=$sub_value[0];?>&action=pardis&name=<?=$key?>&subname=<?=$sub_value[1]?>">
											<?=$sub_value[1];?>
										</a>
                                    </li>
								<? } else { ?>
                                	<li><a href="?page=<?=$sub_value[0];?>&action=pardis&name=<?=$key?>&subname=<?=$sub_value[1]?>"><?=$sub_value[1];?></a></li>
	                         	<? }
							} ?>
						</ul>
					</li>
				<? } } ?>
			</ul> <!-- End #main-nav -->			

		</div>
		</div> <!-- End #sidebar -->

		<!--.................................................................main-content.............................................................-->
		<div id="main-content"> <!-- Main Content Section with everything -->

			<noscript> <!-- Show a notification if the user has disabled javascript -->
				<div class="notification error png_bg">
					<div>
						Javascript is disabled or is not supported by your browser. Please <a href="http://browsehappy.com/" title="Upgrade to a better browser">upgrade</a> your browser or <a href="http://www.google.com/support/bin/answer.py?answer=23852" title="Enable Javascript in your browser">enable</a> Javascript to navigate the interface properly.
					</div>
				</div>
			</noscript>

		<!--................................ Page Head........................................ -->
