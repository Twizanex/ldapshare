<?php
class wall369 {
	function __construct() {
		set_error_handler(array($this, 'error_handler'));
		register_shutdown_function(array($this, 'shutdown_function'));
		$this->set_get('a', 'index', 'alphabetic');
		$this->set_get('post', '', 'numeric');
		$this->set_get('comment', '', 'numeric');
		$this->pdo = new PDO(DATABASE_TYPE.':dbname='.DATABASE_NAME.';host='.DATABASE_HOST.';port='.DATABASE_PORT, DATABASE_USER, DATABASE_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));

		$this->get_user(1000);//TODO
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
	function action_poststatus() {
		$prepare = $this->pdo->prepare('INSERT INTO wall369_post (user_id, post_content, post_datecreated) VALUES (:user_id, :post_content, :post_datecreated)');
		$execute = $prepare->execute(array(':user_id'=>$this->user->user_id, ':post_content'=>$_POST['status_textarea'], ':post_datecreated'=>date('Y-m-d H:i:s')));
		$post_id = $this->pdo->lastinsertid();
		$render = '';
		$render .= '<result>'.$execute.'</result>';
		$render .= '<content><![CDATA[';
		$render .= $this->render_post($this->get_post($post_id));
		$render .= ']]></content>';
		return $render;
	}
	function action_test() {
		$render = '';
		$render .= '<posts>';
		$sql = 'SELECT * FROM wall369_post ORDER BY post_id DESC';
		$stm = $this->pdo->prepare($sql);
		$result = false;
		if($stm && $stm->execute()) {
			$result = $stm->rowCount();
			if($result > 0) {
				while($r = $stm->fetch(PDO::FETCH_OBJ)) {
					$render .= '<post>';
					$render .= '<post_id>'.$r->post_id.'</post_id>';
					$render .= '<user_id>'.$r->user_id.'</user_id>';
					$render .= '<post_content>'.$r->post_content.'</post_content>';
					$render .= '<post_datecreated>'.$r->post_datecreated.'</post_datecreated>';
					$render .= '</post>';
				}
			}
		}
		$render .= '</posts>';
		return $render;
	}
	function action_postdelete() {
		$render = '';
		$render .= '<content><![CDATA[';
		$render .= '<h2>Post delete</h2>';
		$render .= ']]></content>';
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
		$prepare = $this->pdo->prepare('SELECT user.* FROM wall369_user user WHERE user.user_id = :user_id');
		$execute = $prepare->execute(array(':user_id'=>$user_id));
		$rowCount = $prepare->rowCount();
		if($rowCount > 0) {
			$this->user = $prepare->fetch(PDO::FETCH_OBJ);
		}
	}
	function get_post($post_id) {
		$prepare = $this->pdo->prepare('SELECT post.*, user.* FROM wall369_post post LEFT JOIN wall369_user user ON user.user_id = post.user_id WHERE post.post_id = :post_id');
		$execute = $prepare->execute(array(':post_id'=>$post_id));
		$rowCount = $prepare->rowCount();
		if($rowCount > 0) {
			return $prepare->fetch(PDO::FETCH_OBJ);
		}
	}
	function render_post($post) {
		$render = '<div class="post" id="post_'.$post->post_id.'">
			<div class="post_display">
				<div class="post_thumb"><img alt="" src="storage/test.png"></div>
				<div class="post_text">
					<p><span class="username">'.$post->user_firstname.' '.$post->user_lastname.'</span></p>
					<p>'.nl2br($post->post_content, 0).'</p>
					<p class="post_detail post_detail_photo"><span id="datecreated2011-12-1214:36:40" class="datecreated">'.$post->post_datecreated.'</span> | <span class="like"><a class="like_action" data-post="'.$post->post_id.'" href="#post_like_'.$post->post_id.'">Like</a> |</span> <span class="unlike unlike_inactive"><a class="unlike_action" data-post="'.$post->post_id.'" href="#post_like_'.$post->post_id.'">Unlike</a> |</span> <a class="comment_action" data-post="'.$post->post_id.'" href="#comment_form_'.$post->post_id.'">Comment</a>';
					if($post->user_id == $this->user->user_id) {
						$render .= '| <a class="post_delete_action" data-post="'.$post->post_id.'" href="?a=postdelete&amp;post='.$post->post_id.'">Delete</a>';
					}
					$render .= '</p>
					<div class="comments" id="comments_'.$post->post_id.'">
						<div class="comment comment_form" id="comment_form_'.$post->post_id.'">
							<div class="comment_display comment_form_display">
								<div class="comment_thumb"><img alt="" src="storage/test.png"></div>
								<div class="comment_text">
									<form action="" method="post">
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
	function __destruct() {
	}
}
?>