<?php require 'head.php'; ?>
<!-- ........................................................Start Notifications ..............................-->
<div id="content">
	<?=@$page_content?>
<!-- End Notifications -->
</div>
<? if ($page_help != '') { ?>
		<div class="notification attention png_bg">
			<a href="#" class="close"><img src="images/icons/cross_grey_small.png" title="Close this notification" alt="close" /></a>
			<div><?=@$page_help?></div>
		</div>
<? } if ($page_successfull != '') { ?>
		<div class="notification success png_bg">
			<a href="#" class="close"><img src="images/icons/cross_grey_small.png" title="Close this notification" alt="close" /></a>
			<div><?=@$page_successfull?></div>
		</div>
<? } if ($page_error != '') { ?>	
		<div class="notification error png_bg">
			<a href="#" class="close"><img src="images/icons/cross_grey_small.png" title="Close this notification" alt="close" /></a>
			<div><?=@$page_error?></div>
		</div>
<? }?>
<div class="ui-widget-overlay loading" style="z-index: 1002; display:none"></div>
<div style="display: none; z-index: 1003; position: fixed; height: auto; width: 300px; top: 259px; left: 30%; padding:20px; text-align:center" class=" loading ui-dialog ui-widget ui-widget-content ui-corner-all">
	<div class="notification information png_bg">
		<a href="#" class="close"><img src="images/icons/cross_grey_small.png" title="Close this notification" alt="close" /></a>
		<div>
			Loading...
		</div>
	</div>
</div>
<?php require 'footer.php'; ?>
