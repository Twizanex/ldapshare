<?php
class wall369 {
	function __construct() {
		set_error_handler(array($this, 'error_handler'));
		register_shutdown_function(array($this, 'shutdown_function'));
		if(isset($_SESSION['wall369']) == 0) {
			$_SESSION['wall369'] = array();
		}
		$this->set_get('a', 'index', 'alphabetic');
		$this->set_get('post', '', 'numeric');
		$this->set_get('comment', '', 'numeric');
		$this->pdo = new PDO(DATABASE_TYPE.':dbname='.DATABASE_NAME.';host='.DATABASE_HOST.';port='.DATABASE_PORT, DATABASE_USER, DATABASE_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
		$this->user = $this->get_user(1000);//TODO
	}
	function error_handler($e_type, $e_message, $e_file, $e_line) {
		$this->render_error($e_type, $e_message, $e_file, $e_line);
	}
	function shutdown_function() {
		if(function_exists('error_get_last')) {
			$e = error_get_last();
			if($e['type'] == 1) {
				$this->render_error($e['type'], $e['message'], $e['file'], $e['line']);
			}
		}
	}
	function render_error($e_type, $e_message, $e_file, $e_line) {
		$e_type_values = array(1=>'E_ERROR', 2=>'E_WARNING', 4=>'E_PARSE', 8=>'E_NOTICE', 16=>'E_CORE_ERROR', 32=>'E_CORE_WARNING', 64=>'E_COMPILE_ERROR', 128=>'E_COMPILE_WARNING', 256=>'E_USER_ERROR', 512=>'E_USER_WARNING', 1024=>'E_USER_NOTICE', 2048=>'E_STRICT', 4096=>'E_RECOVERABLE_ERROR', 8192=>'E_DEPRECATED', 16384=>'E_USER_DEPRECATED', 30719=>'E_ALL');
		if(isset($e_type_values[$e_type]) == 1) {
			$e_type = $e_type_values[$e_type];
		}
		header('content-type: text/xml; charset=UTF-8');
		$render = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
		$render .= '<wall369>'."\r\n";
		$render .= '<error>'."\r\n";
		$render .= '<type>'.$e_type.'</type>'."\r\n";
		$render .= '<message>'.$e_message.'</message>'."\r\n";
		$render .= '<file>'.$e_file.'</file>'."\r\n";
		$render .= '<line>'.$e_line.'</line>'."\r\n";
		$render .= '</error>'."\r\n";
		$render .= '</wall369>'."\r\n";
		echo $render;
		exit(0);
	}
	function render() {
		if($this->get['a'] == 'index') {
			header('content-type: text/html; charset=UTF-8');
			if(file_exists('wall369.tpl')) {
				$render = file_get_contents('wall369.tpl');
			} else {
				$render = file_get_contents('wall369.dist.tpl');
			}
		} else {
			header('content-type: text/xml; charset=UTF-8');
			$render = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
			$render .= '<wall369>'."\r\n";
			if(method_exists($this, 'action_'.$this->get['a'])) {
				$render .= $this->{'action_'.$this->get['a']}();
			}
			$render .= '</wall369>'."\r\n";
		}
		if(GZHANDLER == 1 && isset($_SERVER['HTTP_ACCEPT_ENCODING']) == 1 && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') && extension_loaded('zlib')) {
			ob_start('ob_gzhandler');
			echo $render;
			ob_end_flush();
		} else {
			echo $render;
		}
		exit(0);
	}
	function set_get($key, $default, $type) {
		$this->get[$key] = $default;
		if(isset($_GET[$key]) == 1 && $_GET[$key] != '') {
			$set_get = 0;
			if($type == 'alphabetic') {
				if(preg_match('/^[a-z_-]+$/i', $_GET[$key])) {
					$set_get = 1;
				}
			}
			if($type == 'numeric') {
				if(is_numeric($_GET[$key])) {
					$set_get = 1;
				}
			}
			if($set_get == 1) {
				$this->get[$key] = $_GET[$key];
			}
		}
	}
	function action_timezone() {
		$render = '';
		$this->set_get('t', 0, 'numeric');
		$_SESSION['wall369']['timezone'] = $this->get['t'];
		$render .= '<timezone>'.$this->get['t'].'</timezone>';
		return $render;
	}
	function action_postlist() {
		$render = '';
		$render .= '<content><![CDATA[';
		$render .= $this->render_postlist();
		$render .= ']]></content>';
		return $render;
	}
	function action_post() {
		$render = '';
		$this->set_get('type', '', 'alphabetic');
		$prepare = $this->pdo->prepare('INSERT INTO wall369_post (user_id, post_content, post_httpuseragent, post_remoteaddr, post_datecreated) VALUES (:user_id, :post_content, :post_httpuseragent, :post_remoteaddr, :post_datecreated)');
		$execute = $prepare->execute(array(':user_id'=>$this->user->user_id, ':post_content'=>$_POST['post_content'], ':post_httpuseragent'=>$_SERVER['HTTP_USER_AGENT'], ':post_remoteaddr'=>$_SERVER['REMOTE_ADDR'], ':post_datecreated'=>date('Y-m-d H:i:s')));
		if($execute) {
			$post_id = $this->pdo->lastinsertid();
			if($this->get['type'] == 'link') {
				$data = $this->analyze_link($_POST['link_inputtext']);
				$prepare = $this->pdo->prepare('INSERT INTO wall369_link (post_id, link_url, link_title, link_image, link_icon, link_content, link_datecreated) VALUES (:post_id, :link_url, :link_title, :link_image, :link_icon, :link_content, :link_datecreated)');
				$execute = $prepare->execute(array(':post_id'=>$post_id, ':link_url'=>$data['url'], ':link_title'=>$data['title'], ':link_image'=>$data['image'], ':link_icon'=>$data['icon'], ':link_content'=>$data['description'], ':link_datecreated'=>date('Y-m-d H:i:s')));
			}
			$links = preg_match_all('(((ftp|http|https){1}://)[-a-zA-Z0-9@:%_\+.~#!\(\)?&//=]+)', $_POST['post_content'], $matches);
			$matches = $matches[0];
			if(count($matches) != 0) {
				$matches = array_unique($matches);
				foreach($matches as $match) {
					$analyze = 1;
					if($this->get['type'] == 'link') {
						if($match == $_POST['link_inputtext']) {
							$analyze = 0;
						}
					}
					if($analyze == 1) {
						$data = $this->analyze_link($match);
						$prepare = $this->pdo->prepare('INSERT INTO wall369_link (post_id, link_url, link_title, link_image, link_icon, link_content, link_datecreated) VALUES (:post_id, :link_url, :link_title, :link_image, :link_icon, :link_content, :link_datecreated)');
						$execute = $prepare->execute(array(':post_id'=>$post_id, ':link_url'=>$data['url'], ':link_title'=>$data['title'], ':link_image'=>$data['image'], ':link_icon'=>$data['icon'], ':link_content'=>$data['description'], ':link_datecreated'=>date('Y-m-d H:i:s')));
					}
				}
			}
		}
		$render .= '<result>'.$execute.'</result>';
		$render .= '<content><![CDATA[';
		$render .= $this->render_post($this->get_post($post_id));
		$render .= ']]></content>';
		return $render;
	}
	function action_postdelete() {
		$render = '';
		$render .= '<content><![CDATA[';
		$render .= '<h2>Post delete</h2>';
		$render .= ']]></content>';
		return $render;
	}
	function action_comment() {
		$render = '';
		$prepare = $this->pdo->prepare('INSERT INTO wall369_comment (user_id, post_id, comment_content, comment_datecreated) VALUES (:user_id, :post_id, :comment_content, :comment_datecreated)');
		$execute = $prepare->execute(array(':user_id'=>$this->user->user_id, ':post_id'=>$this->get['post'], ':comment_content'=>$_POST['comment_textarea'], ':comment_datecreated'=>date('Y-m-d H:i:s')));
		if($execute) {
			$comment_id = $this->pdo->lastinsertid();
			$render .= '<result>'.$execute.'</result>';
			$render .= '<post>'.$this->get['post'].'</post>';
			$render .= '<content><![CDATA[';
			$render .= $this->render_comment($this->get_comment($comment_id));
			$render .= ']]></content>';
		}
		return $render;
	}
	function action_commentdelete() {
		$render = '';
		$render .= '<content><![CDATA[';
		$render .= '<h2>Comment delete</h2>';
		$render .= ']]></content>';
		return $render;
	}
	function get_user($user_id) {
		$prepare = $this->pdo->prepare('SELECT user.* FROM wall369_user user WHERE user.user_id = :user_id GROUP BY user.user_id');
		$execute = $prepare->execute(array(':user_id'=>$user_id));
		$rowCount = $prepare->rowCount();
		if($rowCount > 0) {
			return $prepare->fetch(PDO::FETCH_OBJ);
		}
	}
	function get_post($post_id) {
		$prepare = $this->pdo->prepare('SELECT post.*, user.*, COUNT(comment.comment_id) AS count_comment, COUNT(link.link_id) AS count_link FROM wall369_post post LEFT JOIN wall369_user user ON user.user_id = post.user_id LEFT JOIN wall369_comment comment ON comment.post_id = post.post_id LEFT JOIN wall369_link link ON link.post_id = post.post_id WHERE post.post_id = :post_id GROUP BY post.post_id');
		$execute = $prepare->execute(array(':post_id'=>$post_id));
		$rowCount = $prepare->rowCount();
		if($rowCount > 0) {
			return $prepare->fetch(PDO::FETCH_OBJ);
		}
	}
	function get_comment($comment_id) {
		$prepare = $this->pdo->prepare('SELECT comment.*, user.* FROM wall369_comment comment LEFT JOIN wall369_user user ON user.user_id = comment.user_id WHERE comment.comment_id = :comment_id GROUP BY comment.comment_id');
		$execute = $prepare->execute(array(':comment_id'=>$comment_id));
		$rowCount = $prepare->rowCount();
		if($rowCount > 0) {
			return $prepare->fetch(PDO::FETCH_OBJ);
		}
	}
	function render_postlist() {
		$render = '';
		$prepare = $this->pdo->prepare('SELECT post.*, user.*, COUNT(comment.comment_id) AS count_comment, COUNT(link.link_id) AS count_link FROM wall369_post post LEFT JOIN wall369_user user ON user.user_id = post.user_id LEFT JOIN wall369_comment comment ON comment.post_id = post.post_id LEFT JOIN wall369_link link ON link.post_id = post.post_id GROUP BY post.post_id ORDER BY post.post_id DESC');
		$execute = $prepare->execute();
		$rowCount = $prepare->rowCount();
		if($rowCount > 0) {
			while($post = $prepare->fetch(PDO::FETCH_OBJ)) {
				$render .= $this->render_post($post);
			}
		}
		return $render;
	}
	function render_post($post) {
		$render = '<div class="post" id="post_'.$post->post_id.'">
			<div class="post_display">
				<div class="post_thumb">';
				if(GRAVATAR == 1) {
					$render .= '<img alt="" src="http://www.gravatar.com/avatar/'.md5(strtolower($post->user_email)).'?rating='.GRAVATAR_RATING.'&size=70&default='.GRAVATAR_DEFAULT.'">';
				} else {
					$render .= '<img alt="" src="medias/default_mediasmall.gif">';
				}
				$render .= '</div>
				<div class="post_text">
					<p><span class="username">'.$post->user_firstname.' '.$post->user_lastname.'</span></p>
					<p>'.nl2br($post->post_content, 0).'</p>';
					if($post->count_link > 0) {
						$render .= $this->render_linklist($post->post_id);
					}
					$render .= '<p class="post_detail post_detail_photo"><span class="datecreated">'.$post->post_datecreated.'</span> | <span class="like"><a class="like_action" data-post="'.$post->post_id.'" href="#post_like_'.$post->post_id.'">Like</a> |</span> <span class="unlike unlike_inactive"><a class="unlike_action" data-post="'.$post->post_id.'" href="#post_like_'.$post->post_id.'">Unlike</a> |</span> <a class="comment_action" data-post="'.$post->post_id.'" href="#comment_form_'.$post->post_id.'">Comment</a>';
					if($post->user_id == $this->user->user_id) {
						$render .= ' | <a class="post_delete_action" data-post="'.$post->post_id.'" href="?a=postdelete&amp;post='.$post->post_id.'">Delete</a>';
					}
					$render .= '</p>
					<div class="comments" id="comments_'.$post->post_id.'">
						<div class="comment post_like" id="post_like_1088">
							<div class="comment_display post_like_display">
								<p><span class="username">Sagittis Sed</span> like this</p>
							</div>
						</div>
						<div class="comments_display">';
						if($post->count_comment > 0) {
							$render .= $this->render_commentlist($post->post_id);
						}
						$render .= '</div>
						<div class="comment comment_form" id="comment_form_'.$post->post_id.'">
							<div class="comment_display comment_form_display">
								<div class="comment_thumb">';
								if(GRAVATAR == 1) {
									$render .= '<img alt="" src="http://www.gravatar.com/avatar/'.md5(strtolower($this->user->user_email)).'?rating='.GRAVATAR_RATING.'&size=50&default='.GRAVATAR_DEFAULT.'">';
								} else {
									$render .= '<img alt="" src="medias/default_mediasmall.gif">';
								}
								$render .= '</div>
								<div class="comment_text">
									<form action="?a=comment&amp;post='.$post->post_id.'" class="comment_form_form" method="post">
									<p><textarea class="textarea" name="comment"></textarea></p>
									<p class="submit_btn"><input class="inputsubmit" type="submit" value=" Comment " data-post="'.$post->post_id.'"></p>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>';
		return $render;
	}
	function render_commentlist($post_id) {
		$render = '';
		$prepare = $this->pdo->prepare('SELECT comment.*, user.* FROM wall369_comment comment LEFT JOIN wall369_user user ON user.user_id = comment.user_id WHERE comment.post_id = :post_id GROUP BY comment.comment_id');
		$execute = $prepare->execute(array(':post_id'=>$post_id));
		$rowCount = $prepare->rowCount();
		if($rowCount > 0) {
			while($comment = $prepare->fetch(PDO::FETCH_OBJ)) {
				$render .= $this->render_comment($comment);
			}
		}
		return $render;
	}
	function render_comment($comment) {
		$render = '<div class="comment" id="comment_'.$comment->comment_id.'">
			<div class="comment_display">
				<div class="comment_thumb">';
				if(GRAVATAR == 1) {
					$render .= '<img alt="" src="http://www.gravatar.com/avatar/'.md5(strtolower($comment->user_email)).'?rating='.GRAVATAR_RATING.'&size=50&default='.GRAVATAR_DEFAULT.'">';
				} else {
					$render .= '<img alt="" src="medias/default_mediasmall.gif">';
				}
				$render .= '</div>
				<div class="comment_text">
					<p><span class="username">'.$comment->user_firstname.' '.$comment->user_lastname.'</span> '.nl2br($comment->comment_content, 0).'</p>
					<p class="comment_detail"><span class="datecreated">'.$comment->comment_datecreated.'</span>';
					if($comment->user_id == $this->user->user_id) {
						$render .= ' | <a class="comment_delete_action" data-comment="'.$comment->comment_id.'" href="?a=commentdelete&amp;comment='.$comment->comment_id.'">Delete</a>';
					}
					$render .= '</p>
				</div>
			</div>
		</div>';
		return $render;
	}
	function render_linklist($post_id) {
		$render = '';
		$prepare = $this->pdo->prepare('SELECT link.* FROM wall369_link link WHERE link.post_id = :post_id GROUP BY link.link_id');
		$execute = $prepare->execute(array(':post_id'=>$post_id));
		$rowCount = $prepare->rowCount();
		if($rowCount > 0) {
			$render .= '<div class="links">';
			while($link = $prepare->fetch(PDO::FETCH_OBJ)) {
				$render .= $this->render_link($link);
			}
			$render .= '</div>';
		}
		return $render;
	}
	function render_link($link) {
		$url = parse_url($link->link_url);
		$render = '<div class="link" id="link_'.$link->link_id.'">
			<div class="link_display">
				<div class="link_thumb">';
				if($link->link_image != '') {
					$render .= '<a target="_blank" href="'.$link->link_url.'"><img alt="" src="'.$link->link_image.'"></a>';
				}
				$render .= '</div>
				<div class="link_text">
					<p><a target="_blank" href="'.$link->link_url.'">'.$link->link_title.'</a> <span class="share_social"><a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u='.urlencode($link->link_url).'"><img title="Facebook" alt="Facebook" src="medias/icon_facebook.png"></a> <a target="_blank" href="http://twitter.com/home?status='.urlencode($link->link_url).'"><img title="Twitter" alt="Twitter" src="medias/icon_twitter.png"></a></span><br>';
					if($link->link_icon != '') {
						$render .= '<span class="icon"><img alt="" src="'.$link->link_icon.'"></span> ';
					}
					$render .= '<span class="hostname">'.$url['host'].'</span></p>';
					if($link->link_content != '') {
						$render .= '<p>'.$link->link_content.'</p>';
					}
				$render .= '</div>
			</div>
		</div>';
		return $render;
	}
	function analyze_link($link) {
		$data = array('url'=>$link, 'icon'=>'', 'image'=>'', 'title'=>'', 'description'=>'', 'charset_server'=>'', 'charset_client'=>'');

		$headers = get_headers($link, 1);
		if(isset($headers['Location']) == 1) {
			if(is_array($headers['Location'])) {
				$link = $headers['Location'][0];
			} else {
				$link = $headers['Location'];
			}
			$origin_status = $headers[0];
			$headers = get_headers($link, 1);
			$headers[0] = $headers[0].' ('.$origin_status.')';
			if(isset($headers['Content-Type']) == 1 && is_array($headers['Content-Type'])) {
				$headers['Content-Type'] = $headers['Content-Type'][0];
			}
		}
		$headers = array_unique($headers);

		$opts = array('http'=>array('header'=>'User-Agent: '.$_SERVER['HTTP_USER_AGENT']."\r\n"));
		$context = stream_context_create($opts);
		$content = file_get_contents($link, false, $context);
		$content = str_replace("\t", '', $content);
		$content_flat = str_replace("\r\n", '', $content);
		$content_flat = str_replace("\n", '', $content_flat);

		$pattern = "|<[tT][iI][tT][lL][eE](.*)>(.*)<\/[tT][iI][tT][lL][eE]>|U";
		$matches = array();
		preg_match_all($pattern, $content_flat, $matches, PREG_SET_ORDER);
		foreach($matches as $match) {
			$data['title'] = trim($match[2]);
		}

		$pattern = "|<[mM][eE][tT][aA](.*)[cC][hH][aA][rR][sS][eE][tT]=[\"'](.*)[\"'](.*)>|U";
		$matches = array();
		preg_match_all($pattern, $content_flat, $matches, PREG_SET_ORDER);
		foreach($matches as $match) {
			$data['charset_client'] = strtolower($match[2]);
		}

		$pattern = "|<[lL][iI][nN][kK](.*)[hH][rR][eE][fF]=[\"'](.*)[\"'](.*)>|U";
		$matches = array();
		preg_match_all($pattern, $content_flat, $matches, PREG_SET_ORDER);
		foreach($matches as $match) {
			$href = $match[2];

			$rel = '';
			$pattern = "|(.*)[rR][eE][lL]=[\"'](.*)[\"'](.*)|U";
			$matches_sub = array();
			preg_match_all($pattern, $match[1], $matches_sub, PREG_SET_ORDER);
			foreach($matches_sub as $match_sub) {
				$rel = strtolower($match_sub[2]);
			}
			$pattern = "|(.*)[rR][eE][lL]=[\"'](.*)[\"'](.*)|U";
			$matches_sub = array();
			preg_match_all($pattern, $match[3], $matches_sub, PREG_SET_ORDER);
			foreach($matches_sub as $match_sub) {
				$rel = strtolower($match_sub[2]);
			}
			if($rel == 'image_src') {
				$data['image'] = $href;
			}
			if($rel == 'icon' || $rel == 'shortcut icon') {
				$data['icon'] = $href;
			}

			$ref = '';
			$pattern = "|(.*)[rR][eE][fF]=[\"'](.*)[\"'](.*)|U";
			$matches_sub = array();
			preg_match_all($pattern, $match[1], $matches_sub, PREG_SET_ORDER);
			foreach($matches_sub as $match_sub) {
				$ref = strtolower($match_sub[2]);
			}
			$pattern = "|(.*)[rR][eE][fF]=[\"'](.*)[\"'](.*)|U";
			$matches_sub = array();
			preg_match_all($pattern, $match[3], $matches_sub, PREG_SET_ORDER);
			foreach($matches_sub as $match_sub) {
				$ref = strtolower($match_sub[2]);
			}
			if($ref == 'icon' || $ref == 'shortcut icon') {
				$data['icon'] = $href;
			}
		}

		$pattern = "|<[mM][eE][tT][aA](.*)[cC][oO][nN][tT][eE][nN][tT]=\"(.*)\"(.*)>|U";
		$matches = array();
		preg_match_all($pattern, $content_flat, $matches, PREG_SET_ORDER);
		foreach($matches as $match) {
			$value = $match[2];

			$key = '';
			$pattern = "|(.*)[nN][aA][mM][eE]=[\"'](.*)[\"'](.*)|U";
			$matches_sub = array();
			preg_match_all($pattern, $match[1], $matches_sub, PREG_SET_ORDER);
			foreach($matches_sub as $match_sub) {
				$key = $match_sub[2];
			}
			$pattern = "|(.*)[nN][aA][mM][eE]=[\"'](.*)[\"'](.*)|U";
			$matches_sub = array();
			preg_match_all($pattern, $match[3], $matches_sub, PREG_SET_ORDER);
			foreach($matches_sub as $match_sub) {
				$key = $match_sub[2];
			}

			$pattern = "|(.*)[pP][rR][oO][pP][eE][rR][tT][yY]=[\"'](.*)[\"'](.*)|U";
			$matches_sub = array();
			preg_match_all($pattern, $match[1], $matches_sub, PREG_SET_ORDER);
			foreach($matches_sub as $match_sub) {
				$key = $match_sub[2];
			}
			$pattern = "|(.*)[pP][rR][oO][pP][eE][rR][tT][yY]=[\"'](.*)[\"'](.*)|U";
			$matches_sub = array();
			preg_match_all($pattern, $match[3], $matches_sub, PREG_SET_ORDER);
			foreach($matches_sub as $match_sub) {
				$key = $match_sub[2];
			}

			$pattern = "|(.*)[hH][tT][tT][pP]-[eE][qQ][uU][iI][vV]=[\"'](.*)[\"'](.*)|U";
			$matches_sub = array();
			preg_match_all($pattern, $match[1], $matches_sub, PREG_SET_ORDER);
			foreach($matches_sub as $match_sub) {
				$key = $match_sub[2];
			}
			$pattern = "|(.*)[hH][tT][tT][pP]-[eE][qQ][uU][iI][vV]=[\"'](.*)[\"'](.*)|U";
			$matches_sub = array();
			preg_match_all($pattern, $match[3], $matches_sub, PREG_SET_ORDER);
			foreach($matches_sub as $match_sub) {
				$key = $match_sub[2];
			}
			if(strtolower($key) == 'content-type') {
				$meta_charset = $match[2];
			}

			if($key == 'description') {
				$data[$key] = $value;
			} elseif(substr($key, 0, 3) == 'og:') {
				$key = substr($key, 3);
				$data[$key] = $value;
			}
		}

		if(isset($headers['Content-Type']) == 1 && stristr($headers['Content-Type'], 'charset')) {
			$charset = strtolower(substr($headers['Content-Type'], strpos($headers['Content-Type'], '=')+1));
			$data['charset_server'] = strtolower($charset);
		}
		if(isset($meta_charset) == 1 && stristr($meta_charset, 'charset')) {
			$charset = strtolower(substr($meta_charset, strpos($meta_charset, '=')+1));
			$data['charset_client'] = strtolower($charset);
		}
		if($data['charset_server'] != 'utf-8' && $data['charset_client'] != 'utf-8') {
			$data['title'] = utf8_encode($data['title']);
			$data['description'] = utf8_encode($data['description']);
		}
		return $data;
	}
}
?>