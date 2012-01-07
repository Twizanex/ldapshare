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
$(document).ready(function() {
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
    $('.share_action').click(function(e) {
		e.preventDefault();
        href = $(this).attr('href');
        $('.share_action').removeClass('share_action_active');
        $(this).addClass('share_action_active');
        if($(href).is(':visible')) {
        } else {
            $('.post_form').slideUp();
            $(href).slideDown();
            $(href).find('.textarea').focus();
        }
    });
	$('.link_more_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		$(this).hide();
		$(href).slideDown();
	});
	$('.like_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		post = $(this).data('post');
		$('#post_' + post).find('.post_detail .like').hide();
		$('#post_' + post).find('.post_detail .unlike').show();
	});
	$('.unlike_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		post = $(this).data('post');
		$('#post_' + post).find('.post_detail .unlike').hide();
		$('#post_' + post).find('.post_detail .like').show();
	});
	$('.comment_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		post = $(this).data('post');
		if($(href).is(':visible')) {
			$(href).slideUp();
		} else {
			$(href).slideDown();
			$(href).find('.textarea').focus();
		}
	});
	$('.post_delete_action, .comment_delete_action').live('click', function(e) {
		e.preventDefault();
		href = $(this).attr('href');
		post = $(this).data('post');
		popin_show(href);
	});
	$('#post_status').live('submit', function(e) {
		e.preventDefault();
		action = $(this).attr('action');
		type = $(this).data('type');
		post_content = $('#' + type + '_textarea').val();
		if(post_content != '') {
			data = {};
			data['post_content'] = post_content;
			xml = ajax(action + '&type=' + type, data);
			result = $(xml).find('result').text();
			if(result == '1') {
				content = $(xml).find('content').text();
				$('.posts').prepend(content);
				$('#status_textarea').attr('value', '');
			}
		}
	});
	$('#post_link').live('submit', function(e) {
		e.preventDefault();
		action = $(this).attr('action');
		type = $(this).data('type');
		post_content = $('#' + type + '_textarea').val();
		link_inputtext = $('#' + type + '_inputtext').val();
		if(post_content != '' && link_inputtext != '' && link_inputtext != 'http://') {
			data = {};
			data['post_content'] = post_content;
			data['link_inputtext'] = link_inputtext;
			xml = ajax(action + '&type=' + type, data);
			result = $(xml).find('result').text();
			if(result == '1') {
				content = $(xml).find('content').text();
				$('.posts').prepend(content);
				$('#link_textarea').attr('value', '');
				$('#link_inputtext').attr('value', 'http://');
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
			result = $(xml).find('result').text();
			if(result == '1') {
				post = $(xml).find('post').text();
				content = $(xml).find('content').text();
				$('#comments_' + post).find('.comments_display').append(content);
				$(this).find('.textarea').attr('value', '');
			}
		}
	});
	data = {};
	xml = ajax('index.php?a=postlist', data);
	content = $(xml).find('content').text();
	$('.posts').html(content);
});