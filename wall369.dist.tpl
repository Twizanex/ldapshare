<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>wall369</title>
<link href="wall369.dist.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="loading"><img alt="" src="medias/loading.gif"></div>
<div id="mask"></div>
<div id="popin"><div id="popin_display"></div></div>
<div id="wall369">
<div id="wall369_display">

	<div id="share_actions">
	<p><a href="#post_form_status" class="share_action share_action_active" id="share_action_status">Status</a> <a href="#post_form_photo" class="share_action" id="share_action_photo">Photo</a> <a href="#post_form_link" class="share_action" id="share_action_link">Link</a></p>
	</div>

	<div id="post_form_status" class="post_form">
	<form action="" method="post">
	<p><textarea class="textarea" name="status_textarea"></textarea></p>
	<p class="submit_btn"><input class="inputsubmit" name="status_inputsubmit" type="submit" value=" Share "></p>
	</form>
	</div>

	<div id="post_form_photo" class="post_form post_form_inactive">
	<form target="iframe_upload" action="wall-ajax.html?a=upload_photo" method="post" id="upload_form_photo" enctype="multipart/form-data">
	<p><textarea class="textarea" name="photo_textarea"></textarea></p>
	<p><input class="inputfile" type="file" name="photo_inputfile"><br>
	2M max. / jpeg, gif or png</p>
	<p class="submit_btn"><input class="inputsubmit" name="photo_inputsubmit" type="submit" value=" Share "></p>
	<div id="box-formpost_photo_preview"></div>
	</form>
	</div>

	<div id="post_form_link" class="post_form post_form_inactive">
	<form action="" method="post">
	<p><textarea class="textarea" name="link_textarea"></textarea></p>
	<p><input class="inputtext" type="text" name="link_inputtext" value="http://"></p>
	<p class="submit_btn"><input class="inputsubmit" name="link_inputsubmit" type="submit" value=" Share "></p>
	</form>
	</div>

	<div class="posts">
		<div class="post" id="post_1088">
			<div class="post_display">
				<div class="post_thumb"><img alt="" src="storage/test.png"></div>
				<div class="post_text">

					<p><span class="username">Vitae Massa</span></p>
					<p>Praesent viverra aliquet consectetur. Vivamus et malesuada nisl. Vivamus volutpat tempor auctor. Quisque eros magna, interdum at sagittis sed, porttitor sit amet mauris. Mauris purus risus, venenatis nec feugiat at, dignissim a magna. Aenean dignissim placerat rhoncus. Aliquam tempus, est sit amet consectetur tempus, velit diam pharetra risus, id sollicitudin nisi felis at nisi. Quisque sem sem, ullamcorper ac lobortis id, porta porttitor lacus.</p>

					<div class="links">
						<div class="link" id="link_10881">
							<div class="link_display">
								<div class="link_thumb"><a target="_blank" href="http://www.economist.com/node/21541008"><img alt="" src="storage/test.png"></a></div>
								<div class="link_text">
									<p><a target="_blank" href="http://www.economist.com/node/21541008">The sun shines bright</a> <span class="share_social"><a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fwww.economist.com%2Fnode%2F21541008"><img title="Facebook" alt="Facebook" src="medias/icon_facebook.png"></a> <a target="_blank" href="http://twitter.com/home?status=http%3A%2F%2Fwww.economist.com%2Fnode%2F21541008"><img title="Twitter" alt="Twitter" src="medias/icon_twitter.png"></a></span><br>
									<span class="icon"><img alt="" src="medias/favicon.ico"></span> <span class="hostname">www.economist.com</span></p>
									<p>HER $3 billion fortune makes Oprah Winfrey the wealthiest black person in America, a <a class="link_more_action" data-post="1088" href="#link_more_10881">Read more</a> <span class="link_more" id="link_more_10881">position she has held for years. But she is no longer the richest black person in the world.</span></p>
								</div>
							</div>
						</div>
					</div>

					<p class="post_detail post_detail_link"><span id="datecreated2011-12-1214:36:40" class="datecreated">December 12, 16:36</span> | <span class="like"><a class="like_action" data-post="1088" href="#post_like_1088">Like</a> |</span> <span class="unlike unlike_inactive"><a class="unlike_action" data-post="1088" href="#post_like_1088">Unlike</a> |</span> <a class="comment_action" data-post="1088" href="#comment_form_1088">Comment</a> | <a class="post_delete_action" data-post="1088" href="?a=post_delete&amp;post=1088">Delete</a></p>
		
					<div class="comments" id="comments_1088">
						<div class="comment post_like" id="post_like_1088">
							<div class="comment_display post_like_display">
								<p><span class="username">Sagittis Sed</span> like this</p>
							</div>
						</div>
						<div class="comment" id="comment_1066">
							<div class="comment_display">
								<div class="comment_thumb"><img alt="" src="storage/test.png"></div>
								<div class="comment_text">
									<p><span class="username">Sagittis Sed</span> Pellentesque ullamcorper augue vitae massa auctor dictum. Vestibulum quis eros ante. Maecenas sodales risus in lorem imperdiet pretium eget quis leo. Phasellus ut nunc libero. Nullam pretium nibh quis augue aliquam commodo. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed varius sapien nec felis ultricies rutrum.</p>
									<p class="comment_detail"><span id="datecreated2011-12-1215:36:03" class="datecreated">December 12, 17:36</span> | <a class="comment_delete_action" data-comment="1066" href="?a=comment_delete&amp;comment=1066">Delete</a></p>
								</div>
							</div>
						</div>
						<div class="comment" id="comment_1067">
							<div class="comment_display">
								<div class="comment_thumb"><img alt="" src="storage/test.png"></div>
								<div class="comment_text">
									<p><span class="username">Vitae Massa</span> Pellentesque ullamcorper augue vitae massa auctor dictum. Vestibulum quis eros ante. Maecenas sodales risus in lorem imperdiet pretium eget quis leo. Phasellus ut nunc libero. Nullam pretium nibh quis augue aliquam commodo. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed varius sapien nec felis ultricies rutrum.</p>
									<p class="comment_detail"><span id="datecreated2011-12-1216:42:32" class="datecreated">December 12, 18:42</span></p>
								</div>
							</div>
						</div>
						<div class="comment comment_form" id="comment_form_1088">
							<div class="comment_display comment_form_display">
								<div class="comment_thumb"><img alt="" src="storage/test.png"></div>
								<div class="comment_text">
									<form action="" method="post">
									<p><textarea class="textarea" name="comment"></textarea></p>
									<p class="submit_btn"><input class="inputsubmit" type="submit" value=" Comment " data-post="1088"></p>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="post" id="post_1089">
			<div class="post_display">
				<div class="post_thumb"><img alt="" src="storage/test.png"></div>
				<div class="post_text">

					<p><span class="username">Vitae Massa</span></p>
					<p>Praesent viverra aliquet consectetur. Vivamus et malesuada nisl. Vivamus volutpat tempor auctor. Quisque eros magna, interdum at sagittis sed, porttitor sit amet mauris. Mauris purus risus, venenatis nec feugiat at, dignissim a magna. Aenean dignissim placerat rhoncus. Aliquam tempus, est sit amet consectetur tempus, velit diam pharetra risus, id sollicitudin nisi felis at nisi. Quisque sem sem, ullamcorper ac lobortis id, porta porttitor lacus.</p>

					<p class="post_detail post_detail_photo"><span id="datecreated2011-12-1214:36:40" class="datecreated">December 12, 16:36</span> | <span class="like"><a class="like_action" data-post="1089" href="#post_like_1089">Like</a> |</span> <span class="unlike unlike_inactive"><a class="unlike_action" data-post="1089" href="#post_like_1089">Unlike</a> |</span> <a class="comment_action" data-post="1089" href="#comment_form_1089">Comment</a> | <a class="post_delete_action" data-post="1089" href="?a=post_delete&amp;post=1089">Delete</a></p>

					<div class="comments" id="comments_1088">
						<div class="comment comment_form" id="comment_form_1089">
							<div class="comment_display comment_form_display">
								<div class="comment_thumb"><img alt="" src="storage/test.png"></div>
								<div class="comment_text">
									<form action="" method="post">
									<p><textarea class="textarea" name="comment"></textarea></p>
									<p class="submit_btn"><input class="inputsubmit" type="submit" value=" Comment " data-post="1089"></p>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="post" id="post_1090">
			<div class="post_display">
				<div class="post_thumb"><img alt="" src="storage/test.png"></div>
				<div class="post_text">

					<p><span class="username">Vitae Massa</span></p>
					<p>Praesent viverra aliquet consectetur. Vivamus et malesuada nisl. Vivamus volutpat tempor auctor. Quisque eros magna, interdum at sagittis sed, porttitor sit amet mauris. Mauris purus risus, venenatis nec feugiat at, dignissim a magna. Aenean dignissim placerat rhoncus. Aliquam tempus, est sit amet consectetur tempus, velit diam pharetra risus, id sollicitudin nisi felis at nisi. Quisque sem sem, ullamcorper ac lobortis id, porta porttitor lacus.</p>

					<p class="post_detail post_detail_status"><span id="datecreated2011-12-1214:36:40" class="datecreated">December 12, 16:36</span> | <span class="like"><a class="like_action" data-post="1090" href="#post_like_1090">Like</a> |</span> <span class="unlike unlike_inactive"><a class="unlike_action" data-post="1090" href="#post_like_1090">Unlike</a> |</span> <a class="comment_action" data-post="1090" href="#comment_form_1090">Comment</a> | <a class="post_delete_action" data-post="1090" href="?a=post_delete&amp;post=1090">Delete</a></p>
		
					<div class="comments" id="comments_1090">
						<div class="comment" id="comment_1068">
							<div class="comment_display">
								<div class="comment_thumb"><img alt="" src="storage/test.png"></div>
								<div class="comment_text">
									<p><span class="username">Sagittis Sed</span> Pellentesque ullamcorper augue vitae massa auctor dictum. Vestibulum quis eros ante. Maecenas sodales risus in lorem imperdiet pretium eget quis leo. Phasellus ut nunc libero. Nullam pretium nibh quis augue aliquam commodo. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed varius sapien nec felis ultricies rutrum.</p>
									<p class="comment_detail"><span id="datecreated2011-12-1215:36:03" class="datecreated">December 12, 17:36</span> | <a class="comment_delete_action" data-comment="1068" href="?a=comment_delete&amp;comment=1068">Delete</a></p>
								</div>
							</div>
						</div>
						<div class="comment" id="comment_1069">
							<div class="comment_display">
								<div class="comment_thumb"><img alt="" src="storage/test.png"></div>
								<div class="comment_text">
									<p><span class="username">Vitae Massa</span> Pellentesque ullamcorper augue vitae massa auctor dictum. Vestibulum quis eros ante. Maecenas sodales risus in lorem imperdiet pretium eget quis leo. Phasellus ut nunc libero. Nullam pretium nibh quis augue aliquam commodo. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed varius sapien nec felis ultricies rutrum.</p>
									<p class="comment_detail"><span id="datecreated2011-12-1216:42:32" class="datecreated">December 12, 18:42</span></p>
								</div>
							</div>
						</div>
						<div class="comment comment_form" id="comment_form_1090">
							<div class="comment_display comment_form_display">
								<div class="comment_thumb"><img alt="" src="storage/test.png"></div>
								<div class="comment_text">
									<form action="" method="post">
									<p><textarea class="textarea" name="comment"></textarea></p>
									<p class="submit_btn"><input class="inputsubmit" type="submit" value=" Comment " data-post="1090"></p>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>
</div>
<script type="text/javascript" src="thirdparty/jquery.min.js" charset="UTF-8"></script>
<script type="text/javascript" src="wall369.dist.js" charset="UTF-8"></script>
</body>
</html>