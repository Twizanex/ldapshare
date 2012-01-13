<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>wall369</title>
<link href="wall369.dist.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="loading"><img alt="" height="11" src="medias/loading.gif" width="16"></div>
<div id="mask"></div>
<div id="popin">
	<div id="popin_display">
	</div>
</div>
<div id="wall369">
	<div id="wall369_display">
		<div id="post_form">
			<form action="?a=post" enctype="multipart/form-data" method="post">
			<p class="form_status"><textarea class="textarea" id="status_textarea" name="status_textarea"></textarea></p>
			<p class="form_link"><input class="inputtext" id="link_inputtext" type="text" value="http://"></p>
			<p class="form_address"><input class="inputtext" id="address_inputtext" type="text" value=""></p>
			<p class="form_photo"><input class="inputfile" id="photo_inputfile" name="photo_inputfile" type="file"></p>
			<p class="submit_btn"><input class="inputsubmit" name="inputsubmit" type="submit" value=" Share "></p>
			<div id="post_form_link_preview"></div>
			<div id="post_form_address_preview"></div>
			<div id="post_form_photo_preview"></div>
			</form>
		</div>
		<div class="postlist">
		</div>
	</div>
</div>
<!--<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false" charset="UTF-8"></script>-->
<script type="text/javascript" src="thirdparty/jquery.min.js" charset="UTF-8"></script>
<!--<script type="text/javascript" src="thirdparty/jquery.gmap3.min.js" charset="UTF-8"></script>-->
<script type="text/javascript" src="wall369.dist.js" charset="UTF-8"></script>
</body>
</html>
