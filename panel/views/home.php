<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Title | <?php echo show_lang("login"); ?></title>
		<!-- CSS -->
        <!--link rel="stylesheet" href="css/login.css" type="text/css" media="screen" /-->
		<!-- Reset Stylesheet -->
		<link rel="stylesheet" href="css/reset.css" type="text/css" media="screen" />

		<!-- Main Stylesheet -->
		<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />

		<!-- Invalid Stylesheet. This makes stuff look pretty. Remove it if you want the CSS completely valid -->
		<link rel="stylesheet" href="css/invalid.css" type="text/css" media="screen" />
		<!-- Internet Explorer Fixes Stylesheet -->
		<!--[if lte IE 7]>
			<link rel="stylesheet" href="css/ie.css" type="text/css" media="screen" />
		<![endif]-->

		<!-- Javascripts -->
		<!-- jQuery -->
		<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
		<!-- jQuery Configuration -->
		<script type="text/javascript" src="js/simpla.jquery.configuration.js"></script>
		<!-- Internet Explorer .png-fix -->
		<!--[if IE 6]>
			<script type="text/javascript" src="js/DD_belatedPNG_0.0.7a.js"></script>
			<script type="text/javascript">
				DD_belatedPNG.fix('.png_bg, img, li');
			</script>
		<![endif]-->
	</head>

    <body id="login">

		<div id="login-wrapper" class="png_bg">
			<div id="login-top">
				<h1>Title</h1>
				<!-- Logo (221px width) -->
				<img id="logo" src="images/logo.png" alt="Pardis" />
			</div> <!-- End #logn-top -->

			<div id="login-content">
	           	<?php
					if (!empty($error)) {
					   echo '<div class="notification attention png_bg">' .
							'<a href="#" class="close"><img src="images/icons/cross_grey_small.png" title="Close this notification" alt="close" /></a>' .
							'<div>'.$error.'</div>' .
							'</div>';
					}
				?>
				<form  method="post" action="?page=login">
					<div class="notification information png_bg">
						<div><?php echo show_lang("Please enter the Username and Password."); ?></div>
					</div>
					<p>
						<label><?php echo show_lang("Username"); ?></label>
						<input class="text-input" type="text" name="username" />
					</p>
					<div class="clear"></div>
					<p>
						<label><?php echo show_lang("Password"); ?></label>
						<input class="text-input" type="password" name="password" />
					</p>
					<div class="clear"></div>
					<div class="clear"></div>
					<p>
                 		<input type="hidden" id="user" value="admin" name="user" />
						<input class="button" type="submit" value="<?php echo show_lang("Sign In"); ?>" id="login-submit" />
					</p>
				</form>
			</div> <!-- End #login-content -->
		</div> <!-- End #login-wrapper -->
	</body>
</html>
