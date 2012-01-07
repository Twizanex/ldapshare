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
		<div id="share_actions">
			<p><a href="#post_form_status" class="share_action share_action_active" id="share_action_status">Status</a> <a href="#post_form_photo" class="share_action" id="share_action_photo">Photo</a> <a href="#post_form_link" class="share_action" id="share_action_link">Link</a></p>
			<div id="post_form_status" class="post_form">
				<form action="?a=post" data-type="status" enctype="application/x-www-form-urlencoded" id="post_status" method="post">
				<p><textarea class="textarea" id="status_textarea" name="status_textarea"></textarea></p>
				<p class="submit_btn"><input class="inputsubmit" name="status_inputsubmit" type="submit" value=" Share "></p>
				</form>
			</div>
			<div id="post_form_photo" class="post_form post_form_inactive">
				<form action="?a=post" data-type="photo" enctype="multipart/form-data" id="post_photo" method="post">
				<p><textarea class="textarea" id="photo_textarea" name="photo_textarea"></textarea></p>
				<p><input class="inputfile" id="photo_inputfile" name="photo_inputfile" type="file"><br>
				2M max. / jpeg, gif or png</p>
				<p class="submit_btn"><input class="inputsubmit" name="photo_inputsubmit" type="submit" value=" Share "></p>
				<div id="post_form_photo_preview"></div>
				</form>
			</div>
			<div id="post_form_link" class="post_form post_form_inactive">
				<form action="?a=post" data-type="link" enctype="application/x-www-form-urlencoded" id="post_link" method="post">
				<p><textarea class="textarea" id="link_textarea" name="link_textarea"></textarea></p>
				<p><input class="inputtext" id="link_inputtext" type="text" value="http://"></p>
				<p class="submit_btn"><input class="inputsubmit" name="link_inputsubmit" type="submit" value=" Share "></p>
				</form>
			</div>
		</div>
		<div class="posts">
		</div>
	</div>
</div>
<script type="text/javascript" src="thirdparty/jquery.min.js" charset="UTF-8"></script>
<script type="text/javascript" src="wall369.dist.js" charset="UTF-8"></script>
</body>
</html>