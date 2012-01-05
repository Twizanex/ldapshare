$(document).ready(function() {
	var d = new Date();
	var t = -d.getTimezoneOffset() / 60;
	var post = {};
	$.ajax({
		async: false,
		cache: true,
		data: post,
		dataType: 'xml',
		statusCode: {
			200: function() {
			}
		},
		type: 'POST',
		url: 'index.php?a=timezone&t=' + t
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
		}
	});
});