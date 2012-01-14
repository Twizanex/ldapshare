<?php
class wall369 {
	function __construct() {
		set_error_handler(array($this, 'error_handler'));
		register_shutdown_function(array($this, 'shutdown_function'));
		if(isset($_SESSION['wall369']) == 0) {
			$_SESSION['wall369'] = array();
		}
		if(isset($_SESSION['wall369']['timezone']) == 0) {
			$_SESSION['wall369']['timezone'] = 0;
		}
		if(isset($_SESSION['wall369']['latitude']) == 0) {
			$_SESSION['wall369']['latitude'] = '';
		}
		if(isset($_SESSION['wall369']['longitude']) == 0) {
			$_SESSION['wall369']['longitude'] = '';
		}
		$this->language = 'en';
		include_once('languages/'.$this->language.'.dist.php');
		$this->date_day = gmdate('Y-m-d', date('U') + 3600 * $_SESSION['wall369']['timezone']);
		$this->date_time = gmdate('H:i:s', date('U') + 3600 * $_SESSION['wall369']['timezone']);
		$this->set_get('a', 'index', 'alphabetic');
		$this->set_get('post_id', '', 'numeric');
		$this->set_get('comment_id', '', 'numeric');
		$this->pdo = new PDO(DATABASE_TYPE.':dbname='.DATABASE_NAME.';host='.DATABASE_HOST.';port='.DATABASE_PORT, DATABASE_USER, DATABASE_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
		if(DEMO == 1) {
			if(isset($_SESSION['wall369']['user_id']) == 0) {
				$_SESSION['wall369']['user_id'] = rand(1, 100);
			}
			$this->user = $this->get_user($_SESSION['wall369']['user_id']);
		}
		if($this->get['a'] == 'index') {
			$_SESSION['wall369']['post_id_oldest'] = 0;
			$_SESSION['wall369']['post_id_newest'] = 0;
		}
		$this->post_query = 'SELECT post.*, user.*, DATE_ADD(post.post_datecreated, INTERVAL '.$_SESSION['wall369']['timezone'].' HOUR) AS post_datecreated, COUNT(DISTINCT(comment.comment_id)) AS count_comment, COUNT(DISTINCT(link.link_id)) AS count_link, COUNT(DISTINCT(photo.photo_id)) AS count_photo, COUNT(DISTINCT(address.address_id)) AS count_address, COUNT(DISTINCT(l.like_id)) AS count_like, IF(l_you.like_id IS NOT NULL, 1, 0) AS you_like
		FROM wall369_post post
		LEFT JOIN wall369_user user ON user.user_id = post.user_id
		LEFT JOIN wall369_comment comment ON comment.post_id = post.post_id
		LEFT JOIN wall369_link link ON link.post_id = post.post_id
		LEFT JOIN wall369_photo photo ON photo.post_id = post.post_id
		LEFT JOIN wall369_address address ON address.post_id = post.post_id
		LEFT JOIN wall369_like l ON l.post_id = post.post_id
		LEFT JOIN wall369_like l_you ON l_you.post_id = post.post_id AND l_you.user_id = \''.$this->user->user_id.'\'';
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
	function action_geolocation() {
		$render = '';
		$this->set_get('latitude', '', 'numeric');
		$this->set_get('longitude', '', 'numeric');
		$_SESSION['wall369']['latitude'] = $this->get['latitude'];
		$_SESSION['wall369']['longitude'] = $this->get['longitude'];
		$render .= '<latitude>'.$this->get['latitude'].'</latitude>';
		$render .= '<longitude>'.$this->get['longitude'].'</longitude>';
		return $render;
	}
	function action_postlist() {
		$render = '';
		$render .= $this->render_postlist();
		return $render;
	}
	function action_post() {
		$render = '';
		$prepare = $this->pdo->prepare('INSERT INTO wall369_post (user_id, post_content, post_latitude, post_longitude, post_httpuseragent, post_remoteaddr, post_datecreated) VALUES (:user_id, :post_content, NULLIF(:post_latitude, \'\'), NULLIF(:post_longitude, \'\'), NULLIF(:post_httpuseragent, \'\'), NULLIF(:post_remoteaddr, \'\'), :post_datecreated)');
		$execute = $prepare->execute(array(':user_id'=>$this->user->user_id, ':post_content'=>strip_tags($_POST['status_textarea']), ':post_latitude'=>$_SESSION['wall369']['latitude'], ':post_longitude'=>$_SESSION['wall369']['longitude'], ':post_httpuseragent'=>$_SERVER['HTTP_USER_AGENT'], ':post_remoteaddr'=>$_SERVER['REMOTE_ADDR'], ':post_datecreated'=>date('Y-m-d H:i:s')));
		if($execute) {
			$post_id = $this->pdo->lastinsertid();
			if(isset($_FILES['photo_inputfile']) == 1 && $_FILES['photo_inputfile']['error'] == 0) {
				$photo_inputfile = $this->file_add('storage', 'photo_inputfile', 1, 1);
				$data = array('photo_inputfile'=>$photo_inputfile);
				$this->insert_photo($post_id, $data);
			}
			if(isset($_POST['link_inputtext']) == 1 && $_POST['link_inputtext'] != '' && $_POST['link_inputtext'] != 'http://') {
				$data = $this->analyze_link($_POST['link_inputtext']);
				$this->insert_link($post_id, $data);
			}
			if(isset($_POST['address_inputtext']) == 1 && $_POST['address_inputtext'] != '') {
				$this->insert_address($post_id, $_POST['address_inputtext']);
			}
			$links = preg_match_all('(((ftp|http|https){1}://)[-a-zA-Z0-9@:%_\+.~#!\(\)?&//=]+)', $_POST['status_textarea'], $matches);
			$matches = $matches[0];
			if(count($matches) != 0) {
				$matches = array_unique($matches);
				foreach($matches as $match) {
					$analyze = 1;
					if(isset($_POST['link_inputtext']) == 1 && $_POST['link_inputtext'] != '' && $_POST['link_inputtext'] != 'http://') {
						if($match == $_POST['link_inputtext']) {
							$analyze = 0;
						}
					}
					if($analyze == 1) {
						$data = $this->analyze_link($match);
						$this->insert_link($post_id, $data);
					}
				}
			}
		}
		$render .= '<result>'.$execute.'</result>';
		$render .= $this->action_refreshnew();
		return $render;
	}
	function action_postdelete() {
		$render = '';
		$render .= '<content><![CDATA[';
		$render .= '<h2>Post delete</h2>';
		$render .= '<p><a class="post_delete_confirm_action" data-post_id="'.$this->get['post_id'].'" href="?a=postdeleteconfirm&amp;post_id='.$this->get['post_id'].'">Confirm</a> · <a class="popin_hide" href="#">Cancel</a></p>';
		$render .= ']]></content>';
		return $render;
	}
	function action_postdeleteconfirm() {
		$render = '';
		$post = $this->get_post($this->get['post_id']);
		if($post) {
			if($post->user_id == $this->user->user_id) {
				$sql = 'DELETE FROM wall369_post WHERE user_id = '.$this->user->user_id.' AND post_id = '.$this->get['post_id'];
				$execute = $this->pdo->exec($sql);
				$sql = 'DELETE FROM wall369_address WHERE post_id = '.$this->get['post_id'];
				$execute = $this->pdo->exec($sql);
				$sql = 'DELETE FROM wall369_comment WHERE post_id = '.$this->get['post_id'];
				$execute = $this->pdo->exec($sql);
				$sql = 'DELETE FROM wall369_like WHERE post_id = '.$this->get['post_id'];
				$execute = $this->pdo->exec($sql);
				$sql = 'DELETE FROM wall369_link WHERE post_id = '.$this->get['post_id'];
				$execute = $this->pdo->exec($sql);
				$sql = 'DELETE FROM wall369_photo WHERE post_id = '.$this->get['post_id'];
				$execute = $this->pdo->exec($sql);
				$render .= '<status>delete_post</status>';
			} else {
				$render .= '<status>not_your_post</status>';
				$render .= '<content><![CDATA[';
				$render .= '<h2>Post delete</h2>';
				$render .= '<p>Not your post · <a class="popin_hide" href="#">Cancel</a></p>';
				$render .= ']]></content>';
			}
		} else {
			$render .= '<status>delete_post</status>';
		}
		return $render;
	}
	function action_commentlist() {
		$render = '';
		$render .= '<content><![CDATA[';
		$render .= $this->render_commentlist($this->get['post_id'], 1);
		$render .= ']]></content>';
		return $render;
	}
	function action_comment() {
		$render = '';
		$post = $this->get_post($this->get['post_id']);
		if($post) {
			$prepare = $this->pdo->prepare('INSERT INTO wall369_comment (user_id, post_id, comment_content, comment_datecreated) VALUES (:user_id, :post_id, :comment_content, :comment_datecreated)');
			$execute = $prepare->execute(array(':user_id'=>$this->user->user_id, ':post_id'=>$this->get['post_id'], ':comment_content'=>strip_tags($_POST['comment_textarea']), ':comment_datecreated'=>date('Y-m-d H:i:s')));
			if($execute) {
				$comment_id = $this->pdo->lastinsertid();
				$render .= '<status>comment_insert</status>';
				$render .= '<post_id>'.$this->get['post_id'].'</post_id>';
				$render .= '<content><![CDATA[';
				$render .= $this->render_comment($this->get_comment($comment_id));
				$render .= ']]></content>';
			}
		} else {
			$render .= '<status>post_deleted</status>';
			$render .= '<post_id>'.$this->get['post_id'].'</post_id>';
			$render .= '<content><![CDATA[';
			$render .= '<p>Post deleted</p>';
			$render .= ']]></content>';
		}
		return $render;
	}
	function action_commentdelete() {
		$render = '';
		$render .= '<content><![CDATA[';
		$render .= '<h2>Comment delete</h2>';
		$render .= '<p><a class="comment_delete_confirm_action" data-comment_id="'.$this->get['comment_id'].'" href="?a=commentdeleteconfirm&amp;comment_id='.$this->get['comment_id'].'">Confirm</a> · <a class="popin_hide" href="#">Cancel</a></p>';
		$render .= ']]></content>';
		return $render;
	}
	function action_commentdeleteconfirm() {
		$render = '';
		$comment = $this->get_comment($this->get['comment_id']);
		if($comment) {
			if($comment->user_id == $this->user->user_id) {
				$sql = 'DELETE FROM wall369_comment WHERE user_id = '.$this->user->user_id.' AND comment_id = '.$this->get['comment_id'];
				$execute = $this->pdo->exec($sql);
				$render .= '<status>delete_comment</status>';
			} else {
				$render .= '<status>not_your_comment</status>';
				$render .= '<content><![CDATA[';
				$render .= '<h2>Comment delete</h2>';
				$render .= '<p>Not your comment · <a class="popin_hide" href="#">Cancel</a></p>';
				$render .= ']]></content>';
			}
		} else {
			$render .= '<status>delete_comment</status>';
		}
		return $render;
	}
	function action_postlike() {
		$render = '';
		$post = $this->get_post($this->get['post_id']);
		if($post) {
			$prepare = $this->pdo->prepare('INSERT INTO wall369_like (user_id, post_id, like_datecreated) VALUES (:user_id, :post_id, :like_datecreated)');
			$execute = $prepare->execute(array(':user_id'=>$this->user->user_id, ':post_id'=>$this->get['post_id'], ':like_datecreated'=>date('Y-m-d H:i:s')));
			if($execute) {
				$render .= '<status>like_insert</status>';
				$render .= '<post_id>'.$this->get['post_id'].'</post_id>';
				$render .= '<content><![CDATA[';
				$post = $this->get_post($this->get['post_id']);
				$render .= $this->render_like($post);
				$render .= ']]></content>';
			}
		} else {
			$render .= '<status>post_deleted</status>';
			$render .= '<post_id>'.$this->get['post_id'].'</post_id>';
			$render .= '<content><![CDATA[';
			$render .= '<p>Post deleted</p>';
			$render .= ']]></content>';
		}
		return $render;
	}
	function action_postunlike() {
		$render = '';
		$post = $this->get_post($this->get['post_id']);
		if($post) {
			//$prepare = $this->pdo->prepare('DELETE FROM wall369_like WHERE user_id = :user_id AND post_id = :post_id)');
			//$execute = $prepare->execute(array(':user_id'=>$this->user->user_id, ':post_id'=>$this->get['post_id']));
			$sql = 'DELETE FROM wall369_like WHERE user_id = '.$this->user->user_id.' AND post_id = '.$this->get['post_id'];
			$execute = $this->pdo->exec($sql);
			//if($execute) {
				$render .= '<status>like_delete</status>';
				$render .= '<post_id>'.$this->get['post_id'].'</post_id>';
				$render .= '<content><![CDATA[';
				$post = $this->get_post($this->get['post_id']);
				$render .= $this->render_like($post);
				$render .= ']]></content>';
			//}
		} else {
			$render .= '<status>post_deleted</status>';
			$render .= '<post_id>'.$this->get['post_id'].'</post_id>';
			$render .= '<content><![CDATA[';
			$render .= '<p>Post deleted</p>';
			$render .= ']]></content>';
		}
		return $render;
	}
	function action_refreshdatecreated() {
		$render = '';
		$flt = array();
		$parameters = array();
		$flt[] = '1';
		$flt[] = 'post.post_datecreated LIKE :today_limit';
		$parameters[':today_limit'] = $this->date_day.'%';
		$prepare = $this->pdo->prepare('SELECT post.post_id, DATE_ADD(post.post_datecreated, INTERVAL '.$_SESSION['wall369']['timezone'].' HOUR) AS post_datecreated FROM wall369_post post WHERE '.implode(' AND ', $flt).' GROUP BY post.post_id ORDER BY post.post_id');
		$execute = $prepare->execute($parameters);
		$rowCount = $prepare->rowCount();
		if($rowCount > 0) {
			$render .= '<posts>';
			while($post = $prepare->fetch(PDO::FETCH_OBJ)) {
				$render .= '<post post_id="'.$post->post_id.'"><![CDATA['.$this->render_datecreated($post->post_datecreated).']]></post>';
			}
			$render .= '</posts>';
		}
		$flt = array();
		$parameters = array();
		$flt[] = '1';
		$flt[] = 'comment.comment_datecreated LIKE :today_limit';
		$parameters[':today_limit'] = $this->date_day.'%';
		$prepare = $this->pdo->prepare('SELECT comment.comment_id, DATE_ADD(comment.comment_datecreated, INTERVAL '.$_SESSION['wall369']['timezone'].' HOUR) AS comment_datecreated FROM wall369_comment comment WHERE '.implode(' AND ', $flt).' GROUP BY comment.comment_id ORDER BY comment.comment_id');
		$execute = $prepare->execute($parameters);
		$rowCount = $prepare->rowCount();
		if($rowCount > 0) {
			$render .= '<comments>';
			while($comment = $prepare->fetch(PDO::FETCH_OBJ)) {
				$render .= '<comment comment_id="'.$comment->comment_id.'"><![CDATA['.$this->render_datecreated($comment->comment_datecreated).']]></comment>';
			}
			$render .= '</comments>';
		}
		return $render;
	}
	function action_refreshnew() {
		$render = '';
		$flt = array();
		$parameters = array();
		$flt[] = '1';
		if(isset($_SESSION['wall369']['post_id_newest']) == 1 && $_SESSION['wall369']['post_id_newest'] != 0) {
			$flt[] = 'post.post_id > :post_id_newest';
			$parameters[':post_id_newest'] = $_SESSION['wall369']['post_id_newest'];
		}
		$prepare = $this->pdo->prepare($this->post_query.' WHERE '.implode(' AND ', $flt).' GROUP BY post.post_id ORDER BY post.post_id ASC');
		$execute = $prepare->execute($parameters);
		$rowCount = $prepare->rowCount();
		if($rowCount > 0) {
			$render .= '<posts>';
			while($post = $prepare->fetch(PDO::FETCH_OBJ)) {
				$render .= '<post post_id="'.$post->post_id.'"><![CDATA['.$this->render_post($post).']]></post>';
				$_SESSION['wall369']['post_id_newest'] = $post->post_id;
			}
			$render .= '</posts>';
		}
		/*$flt = array();
		$parameters = array();
		$flt[] = '1';
		$flt[] = 'comment.comment_datecreated LIKE :today_limit';
		$parameters[':today_limit'] = $this->date_day.'%';
		$prepare = $this->pdo->prepare('SELECT comment.comment_id, DATE_ADD(comment.comment_datecreated, INTERVAL '.$_SESSION['wall369']['timezone'].' HOUR) AS comment_datecreated FROM wall369_comment comment WHERE '.implode(' AND ', $flt).' GROUP BY comment.comment_id ORDER BY comment.comment_id');
		$execute = $prepare->execute($parameters);
		$rowCount = $prepare->rowCount();
		if($rowCount > 0) {
			$render .= '<comments>';
			while($comment = $prepare->fetch(PDO::FETCH_OBJ)) {
				$render .= '<comment comment_id="'.$comment->comment_id.'"><![CDATA['.$this->render_datecreated($comment->comment_datecreated).']]></comment>';
			}
			$render .= '</comments>';
		}*/
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
		$prepare = $this->pdo->prepare($this->post_query.' WHERE post.post_id = :post_id GROUP BY post.post_id');
		$execute = $prepare->execute(array(':post_id'=>$post_id));
		$rowCount = $prepare->rowCount();
		if($rowCount > 0) {
			return $prepare->fetch(PDO::FETCH_OBJ);
		}
	}
	function get_comment($comment_id) {
		$prepare = $this->pdo->prepare('SELECT comment.*, user.*, DATE_ADD(comment.comment_datecreated, INTERVAL '.$_SESSION['wall369']['timezone'].' HOUR) AS comment_datecreated FROM wall369_comment comment LEFT JOIN wall369_user user ON user.user_id = comment.user_id WHERE comment.comment_id = :comment_id GROUP BY comment.comment_id');
		$execute = $prepare->execute(array(':comment_id'=>$comment_id));
		$rowCount = $prepare->rowCount();
		if($rowCount > 0) {
			return $prepare->fetch(PDO::FETCH_OBJ);
		}
	}
	function insert_photo($post_id, $data) {
		$prepare = $this->pdo->prepare('INSERT INTO wall369_photo (post_id, photo_file, photo_datecreated) VALUES (:post_id, :photo_file, :photo_datecreated)');
		$execute = $prepare->execute(array(':post_id'=>$post_id, ':photo_file'=>$data['photo_inputfile'], ':photo_datecreated'=>date('Y-m-d H:i:s')));
	}
	function insert_link($post_id, $data) {
		$prepare = $this->pdo->prepare('INSERT INTO wall369_link (post_id, link_url, link_title, link_image, link_video, link_videotype, link_videowidth, link_videoheight, link_icon, link_content, link_datecreated) VALUES (:post_id, :link_url, :link_title, NULLIF(:link_image, \'\'), NULLIF(:link_video, \'\'), NULLIF(:link_videotype, \'\'), NULLIF(:link_videowidth, \'\'), NULLIF(:link_videoheight, \'\'), NULLIF(:link_icon, \'\'), NULLIF(:link_content, \'\'), :link_datecreated)');
		$execute = $prepare->execute(array(':post_id'=>$post_id, ':link_url'=>$data['url'], ':link_title'=>$data['title'], ':link_image'=>$data['image'], ':link_video'=>$data['video'], ':link_videotype'=>$data['videotype'], ':link_videowidth'=>$data['videowidth'], ':link_videoheight'=>$data['videoheight'], ':link_icon'=>$data['icon'], ':link_content'=>$data['description'], ':link_datecreated'=>date('Y-m-d H:i:s')));
	}
	function insert_address($post_id, $address_title) {
		$prepare = $this->pdo->prepare('INSERT INTO wall369_address (post_id, address_title, address_datecreated) VALUES (:post_id, :address_title, :address_datecreated)');
		$execute = $prepare->execute(array(':post_id'=>$post_id, ':address_title'=>$address_title, ':address_datecreated'=>date('Y-m-d H:i:s')));
	}
	function render_postlist() {
		$render = '';
		$flt = array();
		$parameters = array();
		$flt[] = '1';
		if(isset($_SESSION['wall369']['post_id_oldest']) == 1 && $_SESSION['wall369']['post_id_oldest'] != 0) {
			$flt[] = 'post.post_id < :post_id_oldest';
			$parameters[':post_id_oldest'] = $_SESSION['wall369']['post_id_oldest'];
		}
		$prepare = $this->pdo->prepare($this->post_query.' WHERE '.implode(' AND ', $flt).' GROUP BY post.post_id ORDER BY post.post_id DESC LIMIT 0,'.LIMIT_POSTS);
		$execute = $prepare->execute($parameters);
		$rowCount = $prepare->rowCount();
		if($rowCount > 0) {
			$u = 0;
			while($post = $prepare->fetch(PDO::FETCH_OBJ)) {
				if($u == 0) {
					$_SESSION['wall369']['post_id_newest'] = $post->post_id;
				}
				$render .= '<post post_id="'.$post->post_id.'"><![CDATA['.$this->render_post($post).']]></post>';
				$_SESSION['wall369']['post_id_oldest'] = $post->post_id;
				$u++;
			}
			$flt = array();
			$flt[] = '1';
			$flt[] = 'post.post_id < :post_id_oldest';
			$parameters[':post_id_oldest'] = $_SESSION['wall369']['post_id_oldest'];
			$prepare = $this->pdo->prepare('SELECT COUNT(post.post_id) AS count_post FROM wall369_post post WHERE '.implode(' AND ', $flt));
			$execute = $prepare->execute($parameters);
			$rowCount = $prepare->rowCount();
			if($rowCount > 0) {
				$fetch = $prepare->fetch(PDO::FETCH_OBJ);
				if($fetch->count_post > 0) {
					$render .= '<more><![CDATA[<p><a class="postlist_action" href="?a=postlist">More posts</a></p>]]></more>';
				}
			}
		}
		return $render;
	}
	function render_post($post) {
		$render = '<div class="post" id="post_'.$post->post_id.'">
			<div class="post_display">
				<div class="post_thumb">';
				if(GRAVATAR == 1) {
					$render .= '<img alt="" src="http://www.gravatar.com/avatar/'.md5(strtolower($post->user_email)).'?rating='.GRAVATAR_RATING.'&size=50&default='.GRAVATAR_DEFAULT.'">';
				} else {
					$render .= '<img alt="" src="medias/default_mediasmall.gif">';
				}
				$render .= '</div>
				<div class="post_text">';
					if($post->user_id == $this->user->user_id) {
						$render .= '<a class="delete_action post_delete_action" data-post_id="'.$post->post_id.'" href="?a=postdelete&amp;post_id='.$post->post_id.'"></a>';
					}
					$render .= '<p><span class="username">'.$post->user_firstname.' '.$post->user_lastname.'</span></p>
					<p>'.nl2br($post->post_content, 0).'</p>';
					if($post->count_link > 0) {
						$render .= $this->render_linklist($post->post_id);
					}
					if($post->count_address > 0) {
						$render .= $this->render_addresslist($post->post_id);
					}
					if($post->count_photo > 0) {
						$render .= $this->render_photolist($post->post_id);
					}
					$render .= '<p class="post_detail">';
					if($post->you_like == 1) {
						$render .= '<span class="like like_inactive">';
					} else {
						$render .= '<span class="like">';
					}
					$render .= '<a class="post_like_action" data-post_id="'.$post->post_id.'" href="?a=postlike&amp;post_id='.$post->post_id.'">'.$this->str[$this->language]['like'].'</a> ·</span> ';
					if($post->you_like == 1) {
						$render .= '<span class="unlike">';
					} else {
						$render .= '<span class="unlike unlike_inactive">';
					}
					$render .= '<a class="post_unlike_action" data-post_id="'.$post->post_id.'" href="?a=postunlike&amp;post_id='.$post->post_id.'">'.$this->str[$this->language]['unlike'].'</a> ·</span> ';
					$render .= '<a class="comment_action" data-post_id="'.$post->post_id.'" href="#comment_form_'.$post->post_id.'">'.$this->str[$this->language]['comment'].'</a>';
					$render .= ' · <span class="datecreated" id="post_datecreated_'.$post->post_id.'">'.$this->render_datecreated($post->post_datecreated).'</span>';
					$render .= '</p>
					<div class="comments" id="comments_'.$post->post_id.'">';
						$render .= '<div id="post_like_render_'.$post->post_id.'">';
							$render .= $this->render_like($post);
						$render .= '</div>';
						$render .= '<div class="comments_display">';
						if($post->count_comment > 0) {
							$render .= $this->render_commentlist($post->post_id, 0);
						}
						$render .= '</div>
						<div class="comment comment_form" id="comment_form_'.$post->post_id.'">
							<div class="comment_display comment_form_display">
								<div class="comment_thumb">';
								if(GRAVATAR == 1) {
									$render .= '<img alt="" src="http://www.gravatar.com/avatar/'.md5(strtolower($this->user->user_email)).'?rating='.GRAVATAR_RATING.'&size=30&default='.GRAVATAR_DEFAULT.'">';
								} else {
									$render .= '<img alt="" src="medias/default_mediasmall.gif">';
								}
								$render .= '</div>
								<div class="comment_text">
									<form action="?a=comment&amp;post_id='.$post->post_id.'" class="comment_form_form" method="post">
									<p><textarea class="textarea" name="comment"></textarea></p>
									<p class="submit_btn"><input class="inputsubmit" type="submit" value=" '.$this->str[$this->language]['comment'].' " data-post_id="'.$post->post_id.'"></p>
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
	function render_commentlist($post_id, $all) {
		$render = '';
		$prepare = $this->pdo->prepare('SELECT COUNT(comment.comment_id) AS count_comment FROM wall369_comment comment WHERE comment.post_id = :post_id');
		$execute = $prepare->execute(array(':post_id'=>$post_id));
		$rowCount = $prepare->rowCount();
		if($rowCount > 0) {
			$comment_all = $prepare->fetch(PDO::FETCH_OBJ);
			if($comment_all->count_comment > 0) {
				$limit = '';
				if($all == 1) {
					$max = $comment_all->count_comment - LIMIT_COMMENTS;
					$limit = ' LIMIT 0, '.$max;
				}
				if($all == 0) {
					if($comment_all->count_comment > LIMIT_COMMENTS) {
						$render .= '<div class="comment comment_all" id="comment_all_'.$post_id.'">
							<div class="comment_display comment_all_display">
								<p><a class="commentall_action" data-post_id="'.$post_id.'" href="?a=commentlist&amp;post_id='.$post_id.'">View all '.$comment_all->count_comment.' comments</a></p>
							</div>
						</div>';
						$min = $comment_all->count_comment - LIMIT_COMMENTS;
						$limit = ' LIMIT '.$min.', '.LIMIT_COMMENTS;
					}
				}
				$prepare = $this->pdo->prepare('SELECT comment.*, user.*, DATE_ADD(comment.comment_datecreated, INTERVAL '.$_SESSION['wall369']['timezone'].' HOUR) AS comment_datecreated FROM wall369_comment comment LEFT JOIN wall369_user user ON user.user_id = comment.user_id WHERE comment.post_id = :post_id GROUP BY comment.comment_id'.$limit);
				$execute = $prepare->execute(array(':post_id'=>$post_id));
				$rowCount = $prepare->rowCount();
				if($rowCount > 0) {
					while($comment = $prepare->fetch(PDO::FETCH_OBJ)) {
						$render .= $this->render_comment($comment);
					}
				}
			}
		}
		return $render;
	}
	function render_comment($comment) {
		$render = '<div class="comment" id="comment_'.$comment->comment_id.'">
			<div class="comment_display">
				<div class="comment_thumb">';
				if(GRAVATAR == 1) {
					$render .= '<img alt="" src="http://www.gravatar.com/avatar/'.md5(strtolower($comment->user_email)).'?rating='.GRAVATAR_RATING.'&size=30&default='.GRAVATAR_DEFAULT.'">';
				} else {
					$render .= '<img alt="" src="medias/default_mediasmall.gif">';
				}
				$render .= '</div>
				<div class="comment_text">';
					if($comment->user_id == $this->user->user_id) {
						$render .= '<a class="delete_action comment_delete_action" data-comment_id="'.$comment->comment_id.'" href="?a=commentdelete&amp;comment_id='.$comment->comment_id.'"></a>';
					}
					$render .= '<p><span class="username">'.$comment->user_firstname.' '.$comment->user_lastname.'</span> '.nl2br($comment->comment_content, 0).'</p>
					<p class="comment_detail">';
					$render .= '<span class="datecreated" id="comment_datecreated_'.$comment->comment_id.'">'.$this->render_datecreated($comment->comment_datecreated).'</span>';
					$render .= '</p>
				</div>
			</div>
		</div>';
		return $render;
	}
	function render_like($post) {
		$render = '';
		if($post->count_like != 0) {
			if($post->count_like == 4) {
				$display_limit = 2;
			} else {
				$display_limit = 3;
			}
			if($post->count_like > $display_limit) {
				$min = $post->count_like - $display_limit;
				$limit = ' LIMIT '.$min.', '.$display_limit;
			} else {
				$limit = '';
			}
			$prepare = $this->pdo->prepare('SELECT wl.*, DATE_ADD(wl.like_datecreated, INTERVAL '.$_SESSION['wall369']['timezone'].' HOUR) AS wl_datecreated, usr.user_id AS userid, CONCAT(usr.user_firstname, \' \', usr.user_lastname) AS username, IF(wl.user_id = \''.$this->user->user_id.'\', 1, 0) AS ordering FROM wall369_like wl LEFT JOIN wall369_user usr ON usr.user_id = wl.user_id WHERE wl.post_id = :post_id GROUP BY wl.like_id ORDER BY ordering ASC, wl.like_id ASC'.$limit);
			$execute = $prepare->execute(array(':post_id'=>$post->post_id));
			$rowCount = $prepare->rowCount();
		
			if($rowCount > 0) {
				$values = array();
				$render .= '<div class="comment post_like" id="post_like_'.$post->post_id.'">
					<div class="comment_display post_like_display">';
				$render .= '<p>';
				$u = 1;
				while($like = $prepare->fetch(PDO::FETCH_OBJ)) {
					if($this->user->user_id == $like->userid) {
						$render .= '<span class="username">You</span>';
					} else {
						$render .= '<span class="username">'.$like->username.'</span>';
					}
					if($post->count_like != 1) {
						if($u == $rowCount && $rowCount < $post->count_like) {
							$diff = $post->count_like - $rowCount;
							$render .=  ' and <a id="'.$post->post_id.'" class="others_like" href="#">'.$diff.' others</a> ';
						} elseif($u == $rowCount - 1 && $rowCount == $post->count_like) {
							$render .=  ' and ';
						} elseif($u < $rowCount) {
							$render .= ', ';
						}
					}
					$u++;
				}
				$render .= ' like this';
				$render .= '</p>';
				$render .= '</div>';
				$render .= '</div>';
			}
		}
		return $render;
	}
	function render_photolist($post_id) {
		$render = '';
		$prepare = $this->pdo->prepare('SELECT photo.* FROM wall369_photo photo WHERE photo.post_id = :post_id GROUP BY photo.photo_id');
		$execute = $prepare->execute(array(':post_id'=>$post_id));
		$rowCount = $prepare->rowCount();
		if($rowCount > 0) {
			$render .= '<div class="photolist">';
			while($link = $prepare->fetch(PDO::FETCH_OBJ)) {
				$render .= $this->render_photo($link);
			}
			$render .= '</div>';
		}
		return $render;
	}
	function render_photo($photo) {
		$render = '<div class="photo" id="photo_'.$photo->photo_id.'">
			<div class="photo_display">
				<img alt="" src="storage/'.$photo->photo_file.'">
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
			$render .= '<div class="linklist">';
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
			<div class="link_display">';
				if($link->link_image != '') {
				$render .= '<div class="link_thumb">';
					$full = '';
					$render .= '<a target="_blank" href="'.$link->link_url.'"><img alt="" src="'.$link->link_image.'"></a>';
				$render .= '</div>';
				} else {
					$full = ' link_text_full';
				}
				$render .= '<div class="link_text'.$full.'">
					<p><a target="_blank" href="'.$link->link_url.'">'.$link->link_title.'</a><br>';
					if($link->link_icon != '') {
						$render .= '<span class="icon"><img alt="" src="'.$link->link_icon.'"></span> ';
					}
					$render .= '<span class="hostname">'.$url['host'].'</span></p>';
					if($link->link_content != '') {
						$render .= '<p>'.$link->link_content.'</p>';
					}
				$render .= '</div>';
				if($link->link_video != '' && $link->link_videowidth != '' && $link->link_videoheight != '') {
					$link->link_videoheight = round(($link->link_videoheight * 540) / $link->link_videowidth);
					$link->link_videowidth = 540;
					$render .= '<p class="playvideo_link"><a href="#playvideo'.$link->link_id.'"><img src="medias/play_video.png" alt=""></a></p>';
					$render .= '<iframe class="playvideo" id="playvideo'.$link->link_id.'" width="'.$link->link_videowidth.'" height="'.$link->link_videoheight.'" src="'.$link->link_video.'" frameborder="0"></iframe>';
				}
			$render .= '</div>
		</div>';
		return $render;
	}
	function render_addresslist($post_id) {
		$render = '';
		$prepare = $this->pdo->prepare('SELECT address.* FROM wall369_address address WHERE address.post_id = :post_id GROUP BY address.address_id');
		$execute = $prepare->execute(array(':post_id'=>$post_id));
		$rowCount = $prepare->rowCount();
		if($rowCount > 0) {
			$render .= '<div class="addresslist">';
			while($address = $prepare->fetch(PDO::FETCH_OBJ)) {
				$render .= $this->render_address($address);
			}
			$render .= '</div>';
		}
		return $render;
	}
	function render_address($address) {
		$render = '<div class="address" id="address_'.$address->address_id.'">
			<div class="address_display">';
				$render .= '<p>'.$address->address_title.'</p>';
				$render .= '<p><img src="http://maps.googleapis.com/maps/api/staticmap?center='.urlencode($address->address_title).'&markers=color:red|'.urlencode($address->address_title).'&zoom=15&size=540x300&sensor=false" alt=""></p>';
			$render .=' </div>
		</div>';
		return $render;
	}
	function render_datecreated($date) {
		list($datecreated, $timecreated) = explode(' ', $date);
		$diff = $this->date_diff_days($this->date_day, $datecreated);
		if($diff != 0) {
			$mention = $this->date_diff_days_mention($diff);
			if($diff == -1) {
				$mention .= ' at '.substr($timecreated, 0, 5);
			}
		} else {
			$diff = $this->date_diff_minutes($this->date_time, $timecreated);
			$mention = $this->date_diff_minutes_mention($diff);
		}
		return '<span title="'.$this->date_transform(array('format'=>DATE_FORMAT, 'date'=>$date)).'">'.$mention.'</span>';
	}
	function date_diff_days($previous, $next) {
		if($previous == '' || $next == '') {
			return '';
		} else {
			/*if(function_exists('date_create') && function_exists('date_diff')) {
				$datetime1 = date_create($previous);
				$datetime2 = date_create($next);
				$interval = date_diff($datetime1, $datetime2);
				return $interval->format('%R%d');
			} else {*/
				$datetime1 = strtotime($previous);
				$datetime2 = strtotime($next);
				$interval = ($datetime2 - $datetime1)/3600/24;
				if($interval == 0) {
					$interval = '0';
				}
				return $interval;
			//}
		}
	}
	function date_diff_days_mention($diff) {
		$mention = '';
		if($diff != '') {
			if($diff == 0) {
				$mention = '<strong>'.$this->str[$this->language]['today'].'</strong>';
			} elseif($diff == 1) {
				$mention = '<strong>'.$this->str[$this->language]['tomorrow'].'</strong>';
			} elseif($diff == -1) {
				$mention = $this->str[$this->language]['yesterday'];
			} elseif(abs($diff) >= 730) {
				$mention = sprintf($this->str[$this->language]['years-diff'], ceil(intval(abs($diff))/365));
			} elseif(abs($diff) > 56) {
				$mention = sprintf($this->str[$this->language]['months-diff'], ceil(intval(abs($diff))/28));
			} elseif(abs($diff) >= 14) {
				$mention = sprintf($this->str[$this->language]['weeks-diff'], ceil(intval(abs($diff))/7));
			} else {
				$mention = sprintf($this->str[$this->language]['days-diff'], intval(abs($diff)));
			}
		}
		return $mention;
	}
	function date_diff_minutes($previous, $next) {
		if($previous == '' || $next == '') {
			return '';
		} else {
			list($prev_h, $prev_m, $prev_s) = explode(':', $previous);
			$previous_total = $prev_h*60 + $prev_m;
			list($next_h, $next_m, $prev_s) = explode(':', $next);
			$next_total = $next_h*60 + $next_m;
			$interval = ($next_total - $previous_total);
			if($interval == 0) {
				$interval = '0';
			}
			return $interval;
		}
	}
	function date_diff_minutes_mention($diff) {
		$mention = '';
		if($diff != '') {
			if(abs($diff) <= 1) {
				$mention = '<strong>'.$this->str[$this->language]['now'].'</strong>';
			} elseif(abs($diff) >= 120) {
				$mention = sprintf($this->str[$this->language]['hours-diff'], ceil(intval(abs($diff))/60));
			} else {
				$mention = sprintf($this->str[$this->language]['minutes-diff'], intval(abs($diff)));
			}
		}
		return $mention;
	}
	function date_transform($prm) {
		$date = '';
		$days_key = 'days_values';
		$months_key = 'months_values';
		foreach($prm as $_key => $_val) {
			switch($_key) {
				case 'date':
				case 'days_key':
				case 'months_key':
				case 'format':
					$$_key = (string)$_val;
					break;
			}
		}
		if($date != '') {
			if($format != '') {
				if(function_exists('date_create') && function_exists('date_format')) {
					$date = date_create($date);
					$date = date_format($date, $format);
				} else {
					$date = date($format, strtotime($date));
				}
			}
			if(isset($this->str[$this->language][$months_key]) == 1 && count($this->str[$this->language][$months_key]) != 0) {
				$months = $this->str[$this->language][$months_key];
				$date = str_replace(array_keys($months), array_values($months), $date);
			}
			if(isset($this->str[$this->language][$days_key]) == 1 && count($this->str[$this->language][$days_key]) != 0) {
				$days = $this->str[$this->language][$days_key];
				$date = str_replace(array_keys($days), array_values($days), $date);
			}
			if(isset($this->str[$this->language]['daynumber_suffix_values']) == 1 && count($this->str[$this->language]['daynumber_suffix_values']) != 0) {
				$days = array_reverse($this->str[$this->language]['daynumber_suffix_values'], 1);
				$date = str_replace(array_keys($days), array_values($days), $date);
			}
		}
		return $date;
	}
	function file_add($folder, $key, $secure = 1, $yearfolder = 1) {
		$newfile = '';
		if(isset($_FILES[$key]) == 1 && $_FILES[$key]['error'] == 0) {
			if(is_dir($folder)) {
				if(substr($folder, -1) != '/') {
					$folder = $folder.'/';
				}
				if($yearfolder == 1) {
					$year = date('Y');
					if(!is_dir($folder.$year)) {
						mkdir($folder.$year);
						copy('storage/index.php', $folder.$year.'/index.php');
					}
					$year = $year.'/';
				} else {
					$year = '';
				}
				if($secure == 1) {
					$newfile = $year.$this->string_generate(14, 1, 1, 0).'-'.$this->string_clean($_FILES[$key]['name']);
				} else {
					$newfile = $year.$this->string_clean($_FILES[$key]['name']);
				}
				move_uploaded_file($_FILES[$key]['tmp_name'], $folder.$newfile);
			}
		}
		return $newfile;
	}
	function string_length($str) {
		$str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
		if(function_exists('mb_strlen')) {
			$strlen = mb_strlen($str, 'UTF-8');
		} else {
			$strlen = strlen($str);
		}
		return $strlen;
	}
	function string_lower($str) {
		$str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
		if(function_exists('mb_strtolower')) {
			$strtolower = mb_strtolower($str, 'UTF-8');
		} else {
			$strtolower = strtolower($str);
		}
		return $strtolower;
	}
	function string_clean($str) {
		$str = $this->string_lower($str);
		$array_convertdata = array('&#039;'=>'-', '&quot;'=>'', '&amp;'=>'-', '&lt;'=>'', '&gt;'=>'', '\''=>'-', '@'=>'-', '('=>'-', ')'=>'-', '#'=>'-', '&'=>'-', ' '=>'-', '_'=>'-', '\\'=>'', '/'=>'', '"'=>'', '?'=>'-', ':'=>'-', '*'=>'-', '|'=>'-', '<'=>'-', '>'=>'-', '°'=>'-',' ,'=>'-');
		$str = str_replace(array_keys($array_convertdata), array_values($array_convertdata), $str);
		$alphanumeric = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.-';
		$strlen = $this->string_length($alphanumeric);
		for($i=0;$i<$strlen;$i++) {
			$accepted[] = substr($alphanumeric, $i, 1);
		}
		$newstr = '';
		$strlen = $this->string_length($str);
		for($i=0;$i<$strlen;$i++) {
			$asc = substr($str, $i, 1);
			if(in_array($asc, $accepted)) {
				$newstr .= $asc;
			}
		}
		while(strstr($newstr, '--')) {
			$newstr = str_replace('--', '-', $newstr);
		}
		if(substr($newstr, -1) == '-') {
			$newstr = substr($newstr, 0, -1);
		}
		return $newstr;
	}
	function string_generate($size=8, $with_numbers=true, $with_tiny_letters=true, $with_capital_letters=false) { 
		$string = '';
		$sizeof_lchar = 0;
		$letter = '';
		$letter_tiny = 'abcdefghijklmnopqrstuvwxyz';
		$letter_capital = 'ABCDEFGHIJKLMNOPRQSTUVWXYZ';
		$letter_number = '0123456789';
		if($with_tiny_letters == true) {
			$sizeof_lchar += 26;
			if(isset($letter) == 1) {
				$letter .= $letter_tiny;
			} else {
				$letter = $letter_tiny;
			}
		}
		if($with_capital_letters == true) {
			$sizeof_lchar += 26;
			if(isset($letter) == 1) {
				$letter .= $letter_capital;
			} else {
				$letter = $letter_capital;
			}
		}
		if($with_numbers == true) {
			$sizeof_lchar += 10;
			if(isset($letter) == 1) {
				$letter .= $letter_number;
			} else {
				$letter = $letter_number;
			}
		}
		if($sizeof_lchar > 0) {
			//srand((double)microtime()*date('YmdGis'));
			for($cnt = 0; $cnt < $size; $cnt++) {
				$char_select = rand(0, $sizeof_lchar - 1);
				$string .= $letter[$char_select];
			}
		}
		return $string;
	}
	function analyze_link($link) {
		$data = array('url'=>$link, 'icon'=>'', 'image'=>'', 'video'=>'', 'videotype'=>'', 'videowidth'=>'', 'videoheight'=>'', 'title'=>'', 'description'=>'', 'charset_server'=>'', 'charset_client'=>'');

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
				$key = str_replace(':', '', $key);
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
		$data_sanityze = array();
		foreach($data as $k => $v) {
			$data_sanityze[$k] = strip_tags($v);
		}
		return $data_sanityze;
	}
}
?>
