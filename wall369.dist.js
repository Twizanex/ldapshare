function ajax(url, data) {
	loading_show();
	$.ajax({
		async: false,
		cache: true,
		data: data,
		dataType: 'xml',
		statusCode: {
			200: function(xml) {
				return xml;
			}
		},
		type: 'POST',
		url: url
	});
	loading_hide();
}
function set_positions() {
	document_top = $(document).scrollTop();
	window_width = $(window).width();
	window_height = $(window).height();
	$('#mask').css({'height': window_height, 'width': window_width});
	_width = $('#loading').width();
	_height = $('#loading').height();
	_margin_top = (window_height - _height) / 2;
	_margin_left = (window_width - _width) / 2;
	$('#loading').css({'margin-left': _margin_left, 'margin-top': _margin_top});
	_width = $('#popin').width();
	_height = $('#popin').height();
	_margin_top = (window_height - _height) / 2;
	_margin_left = (window_width - _width) / 2;
	$('#popin').css({'margin-left': _margin_left});
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
	$.ajax({
		async: false,
		cache: true,
		dataType: 'xml',
		statusCode: {
			200: function(xml) {
				content = $(xml).find('content').text();
				$('#popin_display').html(content);
			}
		},
		type: 'POST',
		url: href
	});
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

	var d = new Date();
	var t = -d.getTimezoneOffset() / 60;
	var data = {};
	ajax('index.php?a=timezone&t=' + t, data);
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
});