var upload_max_filesize;
var language;
function action_client() {
	d = new Date();
	data = {};
	data['timezone'] = -d.getTimezoneOffset() / 60;
	xml = ajax('?a=client', data);
	upload_max_filesize = $(xml).find('upload_max_filesize').text() * 1048576;
	language = $(xml).find('language').text();
	$.ajax({
		async: false,
		cache: true,
		dataType: 'script',
		type: 'GET',
		url: 'languages/jquery.timeago.'+ language + '.js'
	});
}
function ajax(url, data) {
	var xml_return;
	loading_show();
	$.ajax({
		async: false,
		cache: true,
		data: data,
		dataType: 'xml',
		statusCode: {
			200: function(xml) {
				xml_return = xml;
			}
		},
		type: 'POST',
		url: url
	});
	loading_hide();
	return xml_return;
}
function set_positions() {
	document_top = $(document).scrollTop();
	window_width = $(window).width();
	window_height = $(window).height();
	_width = $('#loading').width();
	_height = $('#loading').height();
	_top = document_top + (window_height / 2) - (_height / 2);
	_margin_left = (window_width - _width) / 2;
	$('#loading').css({'margin-left': _margin_left, 'top': _top});
	_width = $('#popin').width();
	_height = $('#popin').height();
	_top = document_top + (window_height / 2) - (_height / 2);
	_margin_left = (window_width - _width) / 2;
	$('#popin').css({'margin-left': _margin_left, 'top': _top});
}
function loading_hide() {
	$('#loading').hide();
}
function loading_show() {
	$('#loading').show();
}
function popin_hide() {
	$('#popin').fadeOut('slow', function() {
		$('#popin_display').html('');
		$('#ldapshare').animate({'opacity': 1}, 400);
	});
}
function popin_show(href) {
	loading_show();
	$('#ldapshare').animate({'opacity': 0}, 400, function() {
		data = {};
		xml = ajax(href, data);
		content = $(xml).find('content').text();
		$('#popin_display').html(content);
		set_positions();
		loading_hide();
		if($('#popin').is(':visible')) {
		} else {
			$('#popin').fadeIn(1200);
		}
	});
}
function refreshnew() {
	data = {};
	xml = ajax('?a=refreshnew', data);
	count_post = $(xml).find('post').length;
	if(count_post > 0) {
		$('.post').removeClass('post_fresh');
		$(xml).find('post').each(function(i){
			post_id = $(this).attr('post_id');
			content = $(this).text();
			$('.postlist').prepend(content);
			$('#post_' + post_id).find('.datecreated').timeago();
			if(i == 0) {
				$('#post_' + post_id).addClass('post_fresh');
			}
		});
	}
	count_comment = $(xml).find('comment').length;
	if(count_comment > 0) {
		$('.comment').removeClass('comment_fresh');
		$(xml).find('comment').each(function(){
			post_id = $(this).attr('post_id');
			comment_id = $(this).attr('comment_id');
			content = $(this).text();
			$('#commentlist_' + post_id).find('.commentlist_display').append(content);
			$('#comment_' + comment_id).find('.datecreated').timeago();
			$('#comment_' + comment_id).addClass('comment_fresh');
		});
	}
}
function postlist() {
	data = {};
	xml = ajax('?a=postlist', data);
	$(xml).find('post').each(function(){
		post_id = $(this).attr('post_id');
		content = $(this).text();
		$('.postlist').append(content);
	});
	$('.postlist .datecreated').timeago();
	more = $(xml).find('more').text();
	if(more != '') {
		$('.postlist').append(more);
	}
}
function islogged_ok() {
	$('#loginform').fadeOut(function() {
		$('#loginform').html('');
		data = {};
		xml = ajax('?a=postform', data);
		content = $(xml).find('content').text();
		$('#postform').html(content);
		$('#status_textarea').focus();
		postlist();
		$('#postform').fadeIn();
		$('.postlist').fadeIn();
	});
}
function islogged_ko() {
	$('.postlist').fadeOut(function() {
		$('.postlist').html('');
		$('#postform').fadeOut(function() {
			$('#postform').html('');
			data = {};
			xml = ajax('?a=loginform', data);
			content = $(xml).find('content').text();
			$('#loginform').html(content);
			$('#email').focus();
			$('#loginform').fadeIn();
		});
	});
}
$(document).ready(function() {
	action_client();
	set_positions();
	$(window).bind('resize scroll', function(event) {
		set_positions();
	});
	$(document).bind('keydown', function(event) {
		if(event == null) { // ie
			keycode = event.keyCode;
		} else { // mozilla
			keycode = event.which;
		}
		if(keycode == 27) {
			popin_hide();
		}
	});
	$('.popin_hide').live('click', function(event) {
		event.preventDefault();
		popin_hide();
	});
	$('.popin_show').live('click', function(event) {
		event.preventDefault();
		href = $(this).attr('href');
		popin_show(href);
	});
	$('.logout_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		data = {};
		xml = ajax(href, data);
		islogged_ko();
		action_client();
	});
	$('.postlist_action').live('click', function(e) {
		e.preventDefault();
		$(this).parent().hide();
		postlist();
	});
	$('.commentall_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		data = {};
		xml = ajax(href, data);
		content = $(xml).find('content').text();
		post_id = $(xml).find('post_id').text();
		$('#comment_all_' + post_id).hide();
		$('#post_' + post_id).find('.commentlist_display').prepend(content);
	});
	$('.comment_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		if($(href).is(':visible')) {
			$(href).slideUp();
		} else {
			$(href).slideDown();
			$(href).find('.textarea').focus();
		}
	});
	$('.post_delete_confirm_action, .comment_delete_confirm_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		data = {};
		xml = ajax(href, data);
		status = $(xml).find('status').text();
		if(status == 'delete_post') {
			post_id = $(xml).find('post_id').text();
			popin_hide();
			$('#post_' + post_id).fadeOut();
		}
		if(status == 'delete_comment') {
			comment_id = $(xml).find('comment_id').text();
			popin_hide();
			$('#comment_' + comment_id).fadeOut();
		}
	});
	$('.likelist_action, .post_like_action, .post_unlike_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		data = {};
		xml = ajax(href, data);
		content = $(xml).find('content').text();
		status = $(xml).find('status').text();
		post_id = $(xml).find('post_id').text();
		if(status == 'like_list') {
			$('#post_like_render_' + post_id).html(content);
		} else if(status == 'like_insert') {
			$('#post_' + post_id).find('.post_detail .like').hide();
			$('#post_' + post_id).find('.post_detail .unlike').show();
			$('#post_like_render_' + post_id).html(content);
		} else if(status == 'like_delete') {
			$('#post_' + post_id).find('.post_detail .unlike').hide();
			$('#post_' + post_id).find('.post_detail .like').show();
			$('#post_like_render_' + post_id).html(content);
		} else if(status == 'post_deleted') {
			$('#post_' + post_id).html(content);
		}
	});
	$('#loginform form').live('submit', function(e) {
		e.preventDefault();
		action = $(this).attr('action');
		email = $('#email').val();
		password = $('#password').val();
		if(email != '' || password != '') {
			data = {};
			data['email'] = email;
			data['password'] = password;
			xml = ajax(action, data);
			status = $(xml).find('status').text();
			if(status == 'ok') {
				islogged_ok();
			}
			if(status == 'ko') {
				islogged_ko();
			}
		}
	});
	$('#postform form').live('submit', function(e) {
		e.preventDefault();
		action = $(this).attr('action');
		status_textarea = $('#status_textarea').val();
		link_inputtext = $('#link_inputtext').val();
		address_inputtext = $('#address_inputtext').val();
		if(window.FormData) {
			formdata = new FormData();
			enable_submit = 0;
			if(status_textarea != '' || (link_inputtext != '' && link_inputtext != 'http://') || address_inputtext != '') {
				enable_submit = 1;
			}
			var photo = document.getElementById('photo_inputfile');
			if(photo.files.length != 0 && window.FileReader) {
				var file = photo.files[0];
				if((file.type == 'image/gif' || file.type == 'image/jpeg' || file.type == 'image/png') && file.size <= upload_max_filesize) {
					formdata.append('photo_inputfile', file);
					enable_submit = 1;
				}
			}
			formdata.append('status_textarea', status_textarea);
			formdata.append('link_inputtext', link_inputtext);
			formdata.append('address_inputtext', address_inputtext);
			if(formdata && enable_submit == 1) {
				$.ajax({
					contentType: false,
					data: formdata,
					dataType: 'xml',
					processData: false,
					success: function(xml) {
						status = $(xml).find('status').text();
						if(status == 'post_insert') {
							refreshnew();
							$('#status_textarea').attr('value', '');
							$('#link_inputtext').attr('value', 'http://');
							$('#address_inputtext').attr('value', '');
							$('#photo_inputfile').attr('value', '');
							$('.postform_preview').html('');
						}
					},
					type: 'POST',
					url: action
				});
			}
		}
	});
	$('.commentform form').live('submit', function(e) {
		e.preventDefault();
		action = $(this).attr('action');
		comment_textarea = $(this).find('.textarea').val()
		if(comment_textarea != '') {
			data = {};
			data['comment_textarea'] = comment_textarea;
			xml = ajax(action, data);
			post_id = $(xml).find('post_id').text();
			content = $(xml).find('content').text();
			status = $(xml).find('status').text();
			if(status == 'comment_insert') {
				refreshnew();
				$(this).find('.textarea').attr('value', '');
			} else if(status == 'post_deleted') {
				$('#post_' + post_id).html(content);
			}
		}
	});
	$('#popin form').live('submit', function(e) {
		e.preventDefault();
		action = $(this).attr('action');
		status_textarea = $('#status_textarea').val();
		if(window.FormData) {
			formdata = new FormData();
			enable_submit = 0;
			var photo = document.getElementById('avatar_inputfile');
			if(photo.files.length != 0 && window.FileReader) {
				var file = photo.files[0];
				if((file.type == 'image/gif' || file.type == 'image/jpeg' || file.type == 'image/png') && file.size <= upload_max_filesize) {
					formdata.append('avatar_inputfile', file);
					enable_submit = 1;
				}
			}
			if(formdata && enable_submit == 1) {
				$.ajax({
					contentType: false,
					data: formdata,
					dataType: 'xml',
					processData: false,
					success: function(xml) {
						filename = $(xml).find('filename').text();
						$('img.you').attr('src', 'storage/' + filename);
						popin_hide();
					},
					type: 'POST',
					url: action
				});
			}
		}
	});
	$('#avatar_inputfile').live('change', function() {
		loading_show();
		var photo = document.getElementById('avatar_inputfile');
		if(photo.files.length != 0 && window.FileReader) {
			var file = photo.files[0];
			if((file.type == 'image/gif' || file.type == 'image/jpeg' || file.type == 'image/png') && file.size <= upload_max_filesize) {
				$('#avatarform_photo_preview').html('');
				reader = new FileReader();
				reader.onload = function(event) {
					$('#avatarform_photo_preview').html('<p><img alt="" id="avatar_inputfile_preview" src="' + event.target.result + '"></p>');
					$('#avatarform_photo_preview').fadeIn();
				};
				reader.readAsDataURL(file);
			}
		}
		loading_hide();
	});
	$('#photo_inputfile').live('change', function() {
		loading_show();
		var photo = document.getElementById('photo_inputfile');
		if(photo.files.length != 0 && window.FileReader) {
			var file = photo.files[0];
			if((file.type == 'image/gif' || file.type == 'image/jpeg' || file.type == 'image/png') && file.size <= upload_max_filesize) {
				$('#postform_photo_preview').html('');
				reader = new FileReader();
				reader.onload = function(event) {
					$('#postform_photo_preview').html('<div class="photolist"><div class="photolist_display"><div class="photo" id="photo_0"><div class="photo_display"><img alt="" id="photo_inputfile_preview" src="' + event.target.result + '"></div></div></div></div>');
					$('#postform_photo_preview').fadeIn();
				};
				reader.readAsDataURL(file);
			}
		}
		loading_hide();
	});
	$('#link_inputtext').live('focus', function() {
		link_inputtext = $(this).val();
		if(link_inputtext == 'http://') {
			$(this).attr('value', '');
		}
	});
	$('#link_inputtext').live('blur', function() {
		link_inputtext = $(this).val();
		if(link_inputtext == '') {
			$(this).attr('value', 'http://');
			$('#postform_link_preview').html('');
		}
	});
	$('.form_link a').live('click', function(e) {
		e.preventDefault();
		loading_show();
		link_inputtext = $('#link_inputtext').val();
		if(link_inputtext != '' && link_inputtext != 'http://') {
			href = $(this).attr('href');
			data = {};
			data['link_inputtext'] = link_inputtext;
			xml = ajax(href, data);
			content = $(xml).find('content').text();
			$('#postform_link_preview').html(content);
		}
		loading_hide();
	});
	$('#address_inputtext').live('blur', function() {
		address_inputtext = $(this).val();
		if(address_inputtext == '') {
			$('#postform_address_preview').html('');
		}
	});
	$('.form_address a').live('click', function(e) {
		e.preventDefault();
		loading_show();
		address_inputtext = $('#address_inputtext').val();
		if(address_inputtext != '') {
			href = $(this).attr('href');
			data = {};
			data['address_inputtext'] = address_inputtext;
			xml = ajax(href, data);
			content = $(xml).find('content').text();
			$('#postform_address_preview').html(content);
		}
		loading_hide();
	});
	$('.playvideo_link a').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		$(this).parent().hide();
		$(href).show();
	});
	data = {};
	xml = ajax('?a=islogged', data);
	status = $(xml).find('status').text();
	if(status == 'ok') {
		islogged_ok();
	}
	if(status == 'ko') {
		islogged_ko();
	}
	setInterval('refreshnew()', 60000 * 1);
});
