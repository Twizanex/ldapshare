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
	document_height = $(document).height();
	window_width = $(window).width();
	window_height = $(window).height();
	$('#mask').css({'height': document_height, 'width': window_width});
	_width = $('#loading').width();
	_height = $('#loading').height();
	_top = document_top + (window_height / 2) - (_height / 2);
	_margin_left = (window_width - _width) / 2;
	$('#loading').css({'margin-left': _margin_left, 'top': _top});
	_width = $('#popin').width();
	_height = $('#popin').height();
	_top = document_top + 10;
	_margin_left = (window_width - _width) / 2 - 10;// ?
	$('#popin').css({'margin-left': _margin_left, 'top': _top});
}
function loading_hide() {
	$('#loading').hide();
}
function loading_show() {
	$('#loading').show();
}
function mask_hide() {
	$('#mask').fadeOut('slow');
}
function mask_show() {
	set_positions();
	$('#mask').css({'opacity': 0.7});
	$('#mask').fadeIn(800);
}
function popin_hide() {
	mask_hide();
	$('#popin').fadeOut('slow', function() {
		$('#popin_display').html('');
	});
}
function popin_show(href) {
	if($('#mask').is(':visible')) {
	} else {
		mask_show();
	}
	loading_show();
	data = {};
	xml = ajax(href, data);
	content = $(xml).find('content').text();
	$('#popin_display').html(content);
	loading_hide();
	if($('#popin').is(':visible')) {
	} else {
		$('#popin').fadeIn(1200);
	}
}
function refresh_datecreated() {
	data = {};
	xml = ajax('index.php?a=refreshdatecreated', data);
	$(xml).find('post').each(function(){
		var post_id = $(this).attr('post_id');
		var post_datecreated = $(this).text();
		$('#post_datecreated_' + post_id).fadeOut(function() {
			$('#post_datecreated_' + post_id).html(post_datecreated);
			$('#post_datecreated_' + post_id).fadeIn();
		});
	});
	$(xml).find('comment').each(function(){
		var comment_id = $(this).attr('comment_id');
		var comment_datecreated = $(this).text();
		$('#comment_datecreated_' + comment_id).fadeOut(function() {
			$('#comment_datecreated_' + comment_id).html(comment_datecreated);
			$('#comment_datecreated_' + comment_id).fadeIn();
		});
	});
}
function refresh_new() {
	data = {};
	xml = ajax('index.php?a=refreshnew', data);
	$(xml).find('post').each(function(){
		post_id = $(this).attr('post_id');
		content = $(this).text();
		$('.postlist').prepend(content);
	});
}
function geolocation_success(position) {
	data = {};
	xml = ajax('index.php?a=geolocation&latitude=' + position.coords.latitude + '&longitude=' + position.coords.longitude, data);
}
function geolocation_error(msg) {
}
function islogged_ok() {
	$('#login_form').fadeOut(function() {
		$('#login_form').html('');
		data = {};
		xml = ajax('index.php?a=postform', data);
		content = $(xml).find('content').text();
		$('#post_form').html(content);
		$('#status_textarea').focus();
		data = {};
		xml = ajax('index.php?a=postlist', data);
		$(xml).find('post').each(function(){
			post_id = $(this).attr('post_id');
			content = $(this).text();
			$('.postlist').append(content);
		});
		more = $(xml).find('more').text();
		if(more != '') {
			$('.postlist').append(more);
		}
		$('#post_form').fadeIn();
		$('.postlist').fadeIn();
	});
}
function islogged_ko() {
	$('.postlist').fadeOut(function() {
		$('.postlist').html('');
		$('#post_form').fadeOut(function() {
			$('#post_form').html('');
			data = {};
			xml = ajax('index.php?a=loginform', data);
			content = $(xml).find('content').text();
			$('#login_form').html(content);
			$('#email').focus();
			$('#login_form').fadeIn();
		});
	});
}
$(document).ready(function() {
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
	$('#mask').live('click', function(event) {
		event.preventDefault();
		popin_hide();
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
	d = new Date();
	t = -d.getTimezoneOffset() / 60;
	data = {};
	xml = ajax('index.php?a=timezone&t=' + t, data);
	if(navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(geolocation_success, geolocation_error);
	}
	$('.logout_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		data = {};
		xml = ajax(href, data);
		islogged_ko();
	});
	$('.postlist_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		$(this).parent().hide();
		data = {};
		xml = ajax(href, data);
		$(xml).find('post').each(function(){
			post_id = $(this).attr('post_id');
			content = $(this).text();
			$('.postlist').append(content);
		});
		more = $(xml).find('more').text();
		if(more != '') {
			$('.postlist').append(more);
		}
	});
	$('.commentall_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		post_id = $(this).data('post_id');
		data = {};
		xml = ajax(href, data);
		$('#comment_all_' + post_id).hide();
		content = $(xml).find('content').text();
		$('#post_' + post_id).find('.commentlist_display').prepend(content);
	});
	$('.comment_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		post_id = $(this).data('post_id');
		if($(href).is(':visible')) {
			$(href).slideUp();
		} else {
			$(href).slideDown();
			$(href).find('.textarea').focus();
		}
	});
	$('.post_delete_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		post_id = $(this).data('post_id');
		popin_show(href);
	});
	$('.post_delete_confirm_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		post_id = $(this).data('post_id');
		data = {};
		xml = ajax(href, data);
		content = $(xml).find('content').text();
		status = $(xml).find('status').text();
		if(status == 'delete_post') {
			popin_hide();
			$('#post_' + post_id).fadeOut();
		} else if(status == 'not_your_post') {
			$('#popin_display').html(content);
		}
	});
	$('.comment_delete_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		comment_id = $(this).data('comment_id');
		popin_show(href);
	});
	$('.comment_delete_confirm_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		comment_id = $(this).data('comment_id');
		data = {};
		xml = ajax(href, data);
		content = $(xml).find('content').text();
		status = $(xml).find('status').text();
		if(status == 'delete_comment') {
			popin_hide();
			$('#comment_' + comment_id).fadeOut();
		} else if(status == 'not_your_comment') {
			$('#popin_display').html(content);
		}
	});
	$('.post_like_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		post_id = $(this).data('post_id');
		data = {};
		xml = ajax(href, data);
		content = $(xml).find('content').text();
		status = $(xml).find('status').text();
		if(status == 'like_insert') {
			$('#post_' + post_id).find('.post_detail .like').hide();
			$('#post_' + post_id).find('.post_detail .unlike').show();
			$('#post_like_render_' + post_id).html(content);
		} else if(status == 'post_deleted') {
			$('#post_' + post_id).html(content);
		}
	});
	$('.post_unlike_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		post_id = $(this).data('post_id');
		data = {};
		xml = ajax(href, data);
		content = $(xml).find('content').text();
		status = $(xml).find('status').text();
		if(status == 'like_delete') {
			$('#post_' + post_id).find('.post_detail .unlike').hide();
			$('#post_' + post_id).find('.post_detail .like').show();
			$('#post_like_render_' + post_id).html(content);
		} else if(status == 'post_deleted') {
			$('#post_' + post_id).html(content);
		}
	});
	$('#login_form form').live('submit', function(e) {
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
	$('#post_form form').live('submit', function(e) {
		e.preventDefault();
		action = $(this).attr('action');
		status_textarea = $('#status_textarea').val();
		link_inputtext = $('#link_inputtext').val();
		address_inputtext = $('#address_inputtext').val();
		if(window.FormData) {
			formdata = new FormData();
			enable_submit = 0;
			if(status_textarea != '' || link_inputtext != '' || address_inputtext != '') {
				enable_submit = 1;
			}
			var photo = document.getElementById('photo_inputfile');
			if(photo.files.length != 0 && window.FileReader) {
				var file = photo.files[0];
				if((file.type == 'image/gif' || file.type == 'image/jpeg' || file.type == 'image/png') && file.size <= 2097152) {
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
						$(xml).find('post').each(function(){
							post_id = $(this).attr('post_id');
							content = $(this).text();
							$('.postlist').prepend(content);
						});
						$('#status_textarea').attr('value', '');
						$('#link_inputtext').attr('value', 'http://');
						$('#address_inputtext').attr('value', '');
						$('#photo_inputfile').attr('value', '');
						$('#post_form_photo_preview').html('');
						$('#post_form_address_preview').html('');
					},
					type: 'POST',
					url: action
				});
			}
		}
	});
	$('.comment_form_form').live('submit', function(e) {
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
				$('#commentlist_' + post_id).find('.commentlist_display').append(content);
				$(this).find('.textarea').attr('value', '');
			} else if(status == 'post_deleted') {
				$('#post_' + post_id).html(content);
			}
		}
	});
    $('#photo_inputfile').live('change', function() {
		$('#loading').show();
        var photo = document.getElementById('photo_inputfile');
        if(photo.files.length != 0 && window.FileReader) {
            var file = photo.files[0];
            if((file.type == 'image/gif' || file.type == 'image/jpeg' || file.type == 'image/png') && file.size <= 2097152) {
                $('#post_form_photo_preview').html('');
                reader = new FileReader();
                reader.onload = function(event) {
					$('#post_form_photo_preview').html('<img alt="" id="photo_inputfile_preview" src="' + event.target.result + '">');
                    $('#post_form_photo_preview').fadeIn();
                };
                reader.readAsDataURL(file);
            }
        }
		$('#loading').hide();
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
        }
    });
    $('#address_inputtext').live('blur', function() {
		address_inputtext = $(this).val();
		if(address_inputtext != '') {
			address_inputtext = encodeURIComponent(address_inputtext);
			$('#post_form_address_preview').html('<img src="http://maps.googleapis.com/maps/api/staticmap?center=' + address_inputtext + '&markers=color:red|' + address_inputtext + '&zoom=15&size=540x200&sensor=false" alt="">');
        }
		$('#loading').hide();
    });
	$('.playvideo_link a').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		$(this).parent().hide();
		$(href).show();
	});
    $('.photo_display a').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		photo_id = $(this).data('photo_id');
		popin_show(href);
    });
	data = {};
	xml = ajax('index.php?a=islogged', data);
	status = $(xml).find('status').text();
	if(status == 'ok') {
		islogged_ok();
	}
	if(status == 'ko') {
		islogged_ko();
	}
	setInterval('refresh_datecreated()', 60000 * 1);
	setInterval('refresh_new()', 60000 * 1);
});
