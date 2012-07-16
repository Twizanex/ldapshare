<?php
class ldapshare {
	public function __construct() {
		$this->microtime_start = microtime(1);
		session_set_cookie_params(0, '/', '', $this->is_https(), 1);
		session_start();
		set_error_handler(array($this, 'error_handler'));
		register_shutdown_function(array($this, 'shutdown_function'));
		$languages = array();
		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) == 1) {
			$lng_array = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			if(count($lng_array) > 0) {
				foreach($lng_array as $k => $v) {
					$lng = explode(';', $v);
					$lng = explode('-', $lng[0]);
					if(preg_match('/^[a-z]{2}$/', $lng[0])) {
						$languages[] = $lng[0];
					}
				}
			}
		}
		$languages[] = 'en';
		foreach($languages as $lng) {
			if(file_exists('languages/'.$lng.'.php')) {
				$lang_file = $lng;
				break;
			} else if(file_exists('languages/'.$lng.'.dist.php')) {
				$lang_file = $lng.'.dist';
				break;
			}
		}
		include_once('languages/'.$lang_file.'.php');
		if(isset($_GET['a']) == 0 || $_GET['a'] == 'index') {
			$_SESSION['ldapshare']['data'] = array('language'=>$lang_file, 'timezone'=>0, 'post_id_oldest'=>0, 'post_id_newest'=>0, 'comment_id_oldest'=>0, 'comment_id_newest'=>0);
		}
		$this->date_day = gmdate('Y-m-d', date('U') + 3600 * $_SESSION['ldapshare']['data']['timezone']);
		$this->date_time = gmdate('H:i:s', date('U') + 3600 * $_SESSION['ldapshare']['data']['timezone']);
		$this->allowed_images = array('image/gif', 'image/jpeg', 'image/png');
		try {
			$options = array(PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES utf8', PDO::ATTR_PERSISTENT=>1);
			$this->pdo = new PDO(DATABASE_TYPE.':dbname='.DATABASE_NAME.';host='.DATABASE_HOST.';port='.DATABASE_PORT, DATABASE_USER, DATABASE_PASSWORD, $options);
		} catch(PDOException $e) {
			trigger_error($e->getMessage());
		}
		if(isset($_SESSION['ldapshare']['user_id']) == 0 && DEMO == 1) {
			$_SESSION['ldapshare']['user_id'] = rand(1, 100);
		}
		if(isset($_SESSION['ldapshare']['user_id']) == 0 && isset($_COOKIE['user_token']) == 1) {
			$user = $this->get_user_by_type($_COOKIE['user_token'], 'user_token');
			if($user) {
				$_SESSION['ldapshare']['user_id'] = $user->user_id;
			}
		} else if(isset($_SESSION['ldapshare']['user_id']) == 1) {
			$this->user = $this->get_user_by_type($_SESSION['ldapshare']['user_id'], 'user_id');
		}
		$this->select_post = 'SELECT post.*, user.*, DATE_ADD(post.post_datecreated, INTERVAL '.$_SESSION['ldapshare']['data']['timezone'].' HOUR) AS post_datecreated, COUNT(DISTINCT(comment.comment_id)) AS count_comment, COUNT(DISTINCT(link.link_id)) AS post_countlink, COUNT(DISTINCT(l.like_id)) AS post_countlike, IF(l_you.like_id IS NOT NULL, 1, 0) AS you_like FROM '.TABLE_POST.' post LEFT JOIN '.TABLE_USER.' user ON user.user_id = post.user_id LEFT JOIN '.TABLE_COMMENT.' comment ON comment.post_id = post.post_id LEFT JOIN '.TABLE_LINK.' link ON link.post_id = post.post_id LEFT JOIN '.TABLE_LIKE.' l ON l.post_id = post.post_id LEFT JOIN '.TABLE_LIKE.' l_you ON l_you.post_id = post.post_id AND l_you.user_id = :user_id';
		$this->select_comment = 'SELECT comment.*, user.*, DATE_ADD(comment.comment_datecreated, INTERVAL '.$_SESSION['ldapshare']['data']['timezone'].' HOUR) AS comment_datecreated FROM '.TABLE_COMMENT.' comment LEFT JOIN '.TABLE_USER.' user ON user.user_id = comment.user_id';
	}
	private function is_https() {
		if(isset($_SERVER['HTTPS']) == 1 && strtolower($_SERVER['HTTPS']) == 'on') {
			return 1;
		} else {
			return 0;
		}
	}
	private function error_handler($e_type, $e_message, $e_file, $e_line) {
		$this->render_error($e_type, $e_message, $e_file, $e_line);
	}
	private function shutdown_function() {
		if(function_exists('error_get_last')) {
			$e = error_get_last();
			if($e['type'] == 1) {
				$this->render_error($e['type'], $e['message'], $e['file'], $e['line']);
			}
		}
	}
	private function render_error($e_type, $e_message, $e_file, $e_line) {
		header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');
		header('Content-Type: text/xml; charset=UTF-8');
		$render = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
		$render .= '<ldapshare>'."\r\n";
		if(DEBUG == 1) {
			$e_type_values = array(1=>'E_ERROR', 2=>'E_WARNING', 4=>'E_PARSE', 8=>'E_NOTICE', 16=>'E_CORE_ERROR', 32=>'E_CORE_WARNING', 64=>'E_COMPILE_ERROR', 128=>'E_COMPILE_WARNING', 256=>'E_USER_ERROR', 512=>'E_USER_WARNING', 1024=>'E_USER_NOTICE', 2048=>'E_STRICT', 4096=>'E_RECOVERABLE_ERROR', 8192=>'E_DEPRECATED', 16384=>'E_USER_DEPRECATED', 30719=>'E_ALL');
			if(isset($e_type_values[$e_type]) == 1) {
				$e_type = $e_type_values[$e_type];
			}
			$render .= '<type>'.$e_type.'</type>'."\r\n";
			$render .= '<message><![CDATA['.$e_message.']]></message>'."\r\n";
			$render .= '<file>'.$e_file.'</file>'."\r\n";
			$render .= '<line>'.$e_line.'</line>'."\r\n";
			$render .= $this->render_debug();
		}
		$render .= '</ldapshare>'."\r\n";
		echo $render;
		exit(0);
	}
	private function render_debug() {
		$microtime_end = microtime(1);
		$microtime_total = $microtime_end - $this->microtime_start;
		$render = '<microtime_total>'.round($microtime_total, 5).'</microtime_total>'."\r\n";
		if(function_exists('memory_get_peak_usage')) {
			$render .= '<memory_get_peak_usage>'.number_format(memory_get_peak_usage(), 0, '.', ' ').'</memory_get_peak_usage>'."\r\n";
		}
		if(function_exists('memory_get_usage')) {
			$render .= '<memory_get_usage>'.number_format(memory_get_usage(), 0, '.', ' ').'</memory_get_usage>'."\r\n";
		}
		return $render;
	}
	public function render() {
		if(isset($_GET['a']) == 0 || $_GET['a'] == 'index') {
			header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
			header('Content-Type: text/html; charset=UTF-8');
			if(file_exists('ldapshare.tpl')) {
				$render = file_get_contents('ldapshare.tpl')."\r\n";
			} else {
				$render = file_get_contents('ldapshare.dist.tpl')."\r\n";
			}
		} else {
			header('Content-Type: text/xml; charset=UTF-8');
			$render = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
			$render .= '<ldapshare>'."\r\n";
			if(method_exists($this, 'action_'.$_GET['a']) && preg_match('/^[a-z]+$/i', $_GET['a'])) {
				$refl = new ReflectionMethod($this, 'action_'.$_GET['a']);
				if(isset($_SESSION['ldapshare']['user_id']) == 0 && $refl->isPrivate()) {
					header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
				} else {
					header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
					$render .= $this->{'action_'.$_GET['a']}()."\r\n";
				}
			} else {
				header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
			}
			if(DEBUG == 1) {
				$render .= $this->render_debug();
			}
			$render .= '</ldapshare>'."\r\n";
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
	private function pdo_execute($query, $parameters) {
		$prepare = $this->pdo->prepare($query);
		$execute = $prepare->execute($parameters);
		if($execute) {
			return $prepare;
		} else {
			$this->pdo_error($prepare);
		}
	}
	private function pdo_error($prepare) {
		if($prepare->errorCode() != 0) {
			$errorinfo = $prepare->errorinfo();
			trigger_error($errorinfo[2]);
		}
	}
	protected function action_islogged() {
		$render = '';
		if(DEMO == 1) {
			$render .= '<status>ok</status>';
		} else if(isset($_SESSION['ldapshare']['user_id']) == 1 && isset($_COOKIE['user_token']) == 1 && $this->get_user_by_type($_COOKIE['user_token'], 'user_token')) {
			$render .= '<status>ok</status>';
		} else {
			$render .= '<status>ko</status>';
		}
		return $render;
	}
	protected function action_client() {
		$_SESSION['ldapshare']['data']['timezone'] = intval($_POST['timezone']);
		$render = '<upload_max_filesize>'.intval(ini_get('upload_max_filesize')).'</upload_max_filesize>';
		$render .= '<language>'.substr($_SESSION['ldapshare']['data']['language'], 0, 2).'</language>';
		return $render;
	}
	protected function action_loginform() {
		$render = '<content><![CDATA[';
		$render .= '<form action="?a=login" enctype="application/x-www-form-urlencoded" method="post">';
		$render .= '<p class="form_email"><label for="email">'.$this->str['email'].'</label><input class="inputtext" id="email" name="email" type="text" value=""></p>';
		$render .= '<p class="form_password"><label for="password">'.$this->str['password'].'</label><input class="inputpassword" id="password" name="password" type="password" value=""></p>';
		$render .= '<p class="submit_btn"><input class="inputsubmit" type="submit" value="'.$this->str['login'].'"></p>';
		$render .= '</form>';
		$render .= ']]></content>';
		return $render;
	}
	protected function action_login() {
		$status = 'ko';
		$ldap_connect = ldap_connect(LDAP_SERVER, LDAP_PORT);
		if($ldap_connect && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
			ldap_set_option($ldap_connect, LDAP_OPT_PROTOCOL_VERSION, LDAP_PROTOCOL);
			ldap_set_option($ldap_connect, LDAP_OPT_REFERRALS, 0); 
			if(ldap_bind($ldap_connect, LDAP_ROOTDN, LDAP_ROOTPW)) {
				$ldap_search = ldap_search($ldap_connect, LDAP_BASEDN, str_replace('[email]', $_POST['email'], LDAP_FILTER));
				if($ldap_search) {
					$ldap_get_entries = ldap_get_entries($ldap_connect, $ldap_search);
					if($ldap_get_entries['count'] > 0) {
						if(@ldap_bind($ldap_connect, $ldap_get_entries[0]['dn'], $_POST['password'])) {
							$user_lastname = filter_var($ldap_get_entries[0][LDAP_LASTNAME][0], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
							$user_firstname = filter_var($ldap_get_entries[0][LDAP_FIRSTNAME][0], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
							$status = 'ok';
							$user = $this->get_user_by_type($_POST['email'], 'user_email');
							if($user) {
								$query = 'UPDATE '.TABLE_USER.' SET user_lastname = :user_lastname, user_firstname = :user_firstname WHERE user_email = :user_email';
								$prepare = $this->pdo_execute($query, array(':user_email'=>$_POST['email'], ':user_lastname'=>$user_lastname, ':user_firstname'=>$user_firstname));
								$user_id = $user->user_id;
							} else {
								$query = 'INSERT INTO '.TABLE_USER.' (user_email, user_lastname, user_firstname, user_datecreated) VALUES (:user_email, :user_lastname, :user_firstname, :user_datecreated)';
								$prepare = $this->pdo_execute($query, array(':user_email'=>$_POST['email'], ':user_lastname'=>$user_lastname, ':user_firstname'=>$user_firstname, ':user_datecreated'=>date('Y-m-d H:i:s')));
								if($prepare) {
									$user_id = $this->pdo->lastinsertid();
								}
							}
							$_SESSION['ldapshare']['user_id'] = $user_id;
							$user_token = sha1(uniqid('', 1).mt_rand());
							$query = 'UPDATE '.TABLE_USER.' SET user_token = :user_token WHERE user_id = :user_id';
							$prepare = $this->pdo_execute($query, array(':user_id'=>$user_id, ':user_token'=>$user_token));
							setcookie('user_token', $user_token, time() + 3600 * 24 * 30, '/', '', $this->is_https(), 1);
						}
					}
				}
			}
			ldap_unbind($ldap_connect);
		}
		$render = '<status>'.$status.'</status>';
		return $render;
	}
	private function action_logout() {
		$query = 'UPDATE '.TABLE_USER.' SET user_token = NULL WHERE user_id = :user_id';
		$prepare = $this->pdo_execute($query, array(':user_id'=>$this->user->user_id));
		setcookie('user_token', NULL, NULL, '/');
		unset($_SESSION['ldapshare']['user_id']);
		$_SESSION['ldapshare']['data'] = array('timezone'=>0, 'post_id_oldest'=>0, 'post_id_newest'=>0, 'comment_id_oldest'=>0, 'comment_id_newest'=>0);
	}
	private function action_avatar() {
		$render = '<content><![CDATA[';
		$render .= '<div class="popin_content">';
		$render .= '<h2>'.$this->str['avatar'].'</h2>';
		$render .= '<form action="?a=avatarsubmit" enctype="multipart/form-data" method="post">';
		$render .= '<p><input class="inputfile" id="avatar_inputfile" name="avatar_inputfile" type="file"> <input class="inputsubmit" type="submit" value="'.$this->str['send'].'"> · <a class="popin_hide" href="#">'.$this->str['cancel'].'</a></p>';
		$render .= '</form>';
		$render .= '<div class="avatarform_preview" id="avatarform_photo_preview">';
		if($this->user->user_file != '') {
			$render .= '<p><img alt="" id="avatar_inputfile_preview" src="storage/'.$this->user->user_file.'"></p>';
		}
		$render .= '</div>';
		$render .= '</div>';
		$render .= ']]></content>';
		return $render;
	}
	private function action_avatarsubmit() {
		if(isset($_FILES['avatar_inputfile']) == 1 && $_FILES['avatar_inputfile']['error'] == 0 && in_array($_FILES['avatar_inputfile']['type'], $this->allowed_images)) {
			$avatar_inputfile = $this->image_upload('avatar_inputfile', 100, 100);
			if($avatar_inputfile != $this->user->user_file && $this->user->user_file != '') {
				unlink('storage/'.$this->user->user_file);
			}
			$query = 'UPDATE '.TABLE_USER.' SET user_file = NULLIF(:user_file, \'\') WHERE user_id = :user_id';
			$prepare = $this->pdo_execute($query, array(':user_id'=>$this->user->user_id, ':user_file'=>$avatar_inputfile));
		}
		$this->user = $this->get_user_by_type($this->user->user_id, 'user_id');
		$render = '<filename><![CDATA['.$this->user->user_file.']]></filename>';
		return $render;
	}
	private function action_postform() {
		$render = '<content><![CDATA[';
		$render .= '<p id="postform_detail">';
		$render .= '<a class="popin_show" href="?a=avatar">'.$this->str['avatar'].'</a>';
		if(DEMO == 0) {
			$render .= '· <a class="logout_action" href="?a=logout">'.$this->str['logout'].'</a>';
		}
		$render .= '</p>';
		$render .= '<form action="?a=post" enctype="multipart/form-data" method="post">';
		$render .= '<p class="form_status"><textarea class="textarea" id="status_textarea" name="status_textarea"></textarea></p>';
		$render .= '<p class="form_link"><input class="inputtext" id="link_inputtext" type="text" value="http://"><a href="?a=linkpreview"><img src="medias/icon_preview.png" alt="" width="16" height="16"></a></p>';
		$render .= '<p class="form_address"><input class="inputtext" id="address_inputtext" type="text" value=""><a href="?a=addresspreview"><img src="medias/icon_preview.png" alt="" width="16" height="16"></a></p>';
		$render .= '<p class="form_photo"><input class="inputfile" id="photo_inputfile" name="photo_inputfile" type="file"></p>';
		$render .= '<p class="submit_btn"><input class="inputsubmit" type="submit" value="'.$this->str['share'].'"></p>';
		$render .= '</form>';
		$render .= '<div class="postform_preview" id="postform_link_preview"></div>';
		$render .= '<div class="postform_preview" id="postform_address_preview"></div>';
		$render .= '<div class="postform_preview" id="postform_photo_preview"></div>';
		$render .= ']]></content>';
		return $render;
	}
	private function action_postlist() {
		$render = $this->get_postlist('DESC');
		$flt = array();
		$parameters = array();
		$flt[] = '1';
		$flt[] = 'post.post_id < :post_id_oldest';
		$parameters[':post_id_oldest'] = $_SESSION['ldapshare']['data']['post_id_oldest'];
		$query = 'SELECT COUNT(post.post_id) AS count_post FROM '.TABLE_POST.' post WHERE '.implode(' AND ', $flt);
		$prepare = $this->pdo_execute($query, $parameters);
		if($prepare) {
			$rowCount = $prepare->rowCount();
			if($rowCount > 0) {
				$fetch = $prepare->fetch(PDO::FETCH_OBJ);
				if($fetch->count_post > 0) {
					$render .= '<more><![CDATA[<p><a class="postlist_action" href="?a=postlist">More posts</a></p>]]></more>';
				}
			}
		} else {
			$this->pdo_error($prepare);
		}
		return $render;
	}
	private function action_post() {
		$render = '';
		if(isset($_POST['status_textarea']) == 1) {
			$query = 'INSERT INTO '.TABLE_POST.' (user_id, post_content, post_httpuseragent, post_remoteaddr, post_datecreated) VALUES (:user_id, :post_content, NULLIF(:post_httpuseragent, \'\'), NULLIF(:post_remoteaddr, \'\'), :post_datecreated)';
			$prepare = $this->pdo_execute($query, array(':user_id'=>$this->user->user_id, ':post_content'=>filter_var($_POST['status_textarea'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES), ':post_httpuseragent'=>filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES), ':post_remoteaddr'=>filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP), ':post_datecreated'=>date('Y-m-d H:i:s')));
			if($prepare) {
				$post_id = $this->pdo->lastinsertid();
				$render .= '<status>post_insert</status>';
				if(isset($_FILES['photo_inputfile']) == 1 && $_FILES['photo_inputfile']['error'] == 0 && in_array($_FILES['photo_inputfile']['type'], $this->allowed_images)) {
					$photo_inputfile = $this->image_upload('photo_inputfile', 540, 540);
					$query = 'UPDATE '.TABLE_POST.' SET post_photo = :post_photo WHERE post_id = :post_id';
					$prepare = $this->pdo_execute($query, array(':post_id'=>$post_id, ':post_photo'=>$photo_inputfile));
				}
				if(isset($_POST['link_inputtext']) == 1 && filter_var($_POST['link_inputtext'], FILTER_VALIDATE_URL)) {
					$data = $this->analyze_link($_POST['link_inputtext']);
					$this->insert_link($post_id, $data);
				}
				if(isset($_POST['address_inputtext']) == 1 && $_POST['address_inputtext'] != '') {
					$query = 'UPDATE '.TABLE_POST.' SET post_address = :post_address WHERE post_id = :post_id';
					$prepare = $this->pdo_execute($query, array(':post_id'=>$post_id, ':post_address'=>filter_var($_POST['address_inputtext'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES)));
				}
				preg_match_all('(((ftp|http|https){1}://)[-a-zA-Z0-9@:%_\+.~#!\(\)?&//=]+)', $_POST['status_textarea'], $matches);
				$matches = $matches[0];
				if(count($matches) > 0) {
					$matches = array_unique($matches);
					foreach($matches as $match) {
						$analyze = 1;
						if(isset($_POST['link_inputtext']) == 1 && filter_var($_POST['link_inputtext'], FILTER_VALIDATE_URL)) {
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
		}
		return $render;
	}
	private function action_postdelete() {
		$render = '<content><![CDATA[';
		$render .= '<div class="popin_content">';
		$render .= '<h2>'.$this->str['post_delete'].'</h2>';
		$render .= '<p><a class="post_delete_confirm_action" href="?a=postdeleteconfirm&amp;post_id='.intval($_GET['post_id']).'">'.$this->str['confirm'].'</a> · <a class="popin_hide" href="#">'.$this->str['cancel'].'</a></p>';
		$render .= '</div>';
		$render .= ']]></content>';
		return $render;
	}
	private function action_postdeleteconfirm() {
		$post = $this->get_post_by_id($_GET['post_id']);
		$render = '<post_id>'.intval($_GET['post_id']).'</post_id>';
		if($post) {
			if($post->user_id == $this->user->user_id) {
				$query = 'DELETE FROM '.TABLE_POST.' WHERE user_id = :user_id AND post_id = :post_id';
				$prepare = $this->pdo_execute($query, array(':post_id'=>intval($_GET['post_id']), ':user_id'=>$this->user->user_id));
				if($prepare) {
					if($post->post_photo) {
						unlink('storage/'.$post->post_photo);
					}
					$tables = array(TABLE_COMMENT, TABLE_LIKE, TABLE_LINK);
					foreach($tables as $table) {
						$query = 'DELETE FROM '.$table.' WHERE post_id = :post_id';
						$prepare = $this->pdo_execute($query, array(':post_id'=>intval($_GET['post_id'])));
					}
					$render .= '<status>delete_post</status>';
				}
			} else {
				header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
				$render .= '<status>not_your_post</status>';
			}
		} else {
			$render .= '<status>delete_post</status>';
		}
		return $render;
	}
	private function action_commentlist() {
		$post = $this->get_post_by_id($_GET['post_id']);
		$render = '<post_id>'.intval($_GET['post_id']).'</post_id>';
		$render .= '<content><![CDATA['.$this->render_commentlist($post, 1).']]></content>';
		return $render;
	}
	private function action_comment() {
		$render = '';
		if(isset($_POST['comment_textarea']) == 1) {
			$post = $this->get_post_by_id($_GET['post_id']);
			if($post) {
				$query = 'INSERT INTO '.TABLE_COMMENT.' (user_id, post_id, comment_content, comment_httpuseragent, comment_remoteaddr, comment_datecreated) VALUES (:user_id, :post_id, :comment_content, NULLIF(:comment_httpuseragent, \'\'), NULLIF(:comment_remoteaddr, \'\'), :comment_datecreated)';
				$prepare = $this->pdo_execute($query, array(':user_id'=>$this->user->user_id, ':post_id'=>intval($_GET['post_id']), ':comment_content'=>filter_var($_POST['comment_textarea'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES), ':comment_httpuseragent'=>filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES), ':comment_remoteaddr'=>filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP), ':comment_datecreated'=>date('Y-m-d H:i:s')));
				if($prepare) {
					$comment_id = $this->pdo->lastinsertid();
					$render .= '<status>comment_insert</status>';
				}
			} else {
				$render .= '<status>post_deleted</status>';
				$render .= '<post_id>'.intval($_GET['post_id']).'</post_id>';
				$render .= '<content><![CDATA[<p>'.$this->str['post_deleted'].'</p>]]></content>';
			}
		}
		return $render;
	}
	private function action_commentdelete() {
		$render = '<content><![CDATA[';
		$render .= '<div class="popin_content">';
		$render .= '<h2>'.$this->str['comment_delete'].'</h2>';
		$render .= '<p><a class="comment_delete_confirm_action" href="?a=commentdeleteconfirm&amp;comment_id='.intval($_GET['comment_id']).'">'.$this->str['confirm'].'</a> · <a class="popin_hide" href="#">'.$this->str['cancel'].'</a></p>';
		$render .= '</div>';
		$render .= ']]></content>';
		return $render;
	}
	private function action_commentdeleteconfirm() {
		$comment = $this->get_comment_by_id($_GET['comment_id']);
		$render = '<comment_id>'.intval($_GET['comment_id']).'</comment_id>';
		if($comment) {
			if($comment->user_id == $this->user->user_id) {
				$query = 'DELETE FROM '.TABLE_COMMENT.' WHERE user_id = :user_id AND comment_id = :comment_id';
				$prepare = $this->pdo_execute($query, array(':comment_id'=>intval($_GET['comment_id']), ':user_id'=>$this->user->user_id));
				if($prepare) {
					$render .= '<status>delete_comment</status>';
				}
			} else {
				header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
				$render .= '<status>not_your_comment</status>';
			}
		} else {
			$render .= '<status>delete_comment</status>';
		}
		return $render;
	}
	private function action_postlike() {
		$post = $this->get_post_by_id($_GET['post_id']);
		$render = '<post_id>'.intval($_GET['post_id']).'</post_id>';
		if($post) {
			$query = 'INSERT INTO '.TABLE_LIKE.' (user_id, post_id, like_datecreated) VALUES (:user_id, :post_id, :like_datecreated)';
			$prepare = $this->pdo_execute($query, array(':user_id'=>$this->user->user_id, ':post_id'=>intval($_GET['post_id']), ':like_datecreated'=>date('Y-m-d H:i:s')));
			if($prepare) {
				$post = $this->get_post_by_id($_GET['post_id']);
				$render .= '<status>like_insert</status>';
				$render .= '<content><![CDATA['.$this->render_like($post, 0).']]></content>';
			}
		} else {
			$render .= '<status>post_deleted</status>';
			$render .= '<content><![CDATA[<p>'.$this->str['post_deleted'].'</p>]]></content>';
		}
		return $render;
	}
	private function action_postunlike() {
		$post = $this->get_post_by_id($_GET['post_id']);
		$render = '<post_id>'.intval($_GET['post_id']).'</post_id>';
		if($post) {
			$query = 'DELETE FROM '.TABLE_LIKE.' WHERE user_id = :user_id AND post_id = :post_id';
			$prepare = $this->pdo_execute($query, array(':user_id'=>$this->user->user_id, ':post_id'=>intval($_GET['post_id'])));
			if($prepare) {
				$post = $this->get_post_by_id($_GET['post_id']);
				$render .= '<status>like_delete</status>';
				$render .= '<content><![CDATA['.$this->render_like($post, 0).']]></content>';
			}
		} else {
			$render .= '<status>post_deleted</status>';
			$render .= '<content><![CDATA[<p>'.$this->str['post_deleted'].'</p>]]></content>';
		}
		return $render;
	}
	private function action_likelist() {
		$post = $this->get_post_by_id($_GET['post_id']);
		$render = '<post_id>'.intval($_GET['post_id']).'</post_id>';
		$render .= '<status>like_list</status>';
		$render .= '<content><![CDATA['.$this->render_like($post, 1).']]></content>';
		return $render;
	}
	private function action_linkpreview() {
		$render = '';
		if(isset($_POST['link_inputtext']) == 1 && filter_var($_POST['link_inputtext'], FILTER_VALIDATE_URL)) {
			$link = $this->analyze_link($_POST['link_inputtext']);
			$render .= '<content><![CDATA[';
			$render .= '<div class="linklist">';
			$render .= $this->render_link($link);
			$render .= '</div>';
			$render .= ']]></content>';
		}
		return $render;
	}
	private function action_addresspreview() {
		$render = '';
		if(isset($_POST['address_inputtext']) == 1 && $_POST['address_inputtext'] != '') {
			$post = new stdClass();
			$post->post_id = 0;
			$post->post_address = filter_var($_POST['address_inputtext'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
			$render .= '<content><![CDATA[';
			$render .= $this->render_address($post);
			$render .= ']]></content>';
		}
		return $render;
	}
	private function action_refreshnew() {
		$render = $this->get_postlist('ASC');
		$query = $this->select_comment.' WHERE comment.comment_id > :comment_id_newest AND comment.post_id >= :post_id_oldest GROUP BY comment.comment_id';
		$prepare = $this->pdo_execute($query, array(':comment_id_newest'=>$_SESSION['ldapshare']['data']['comment_id_newest'], ':post_id_oldest'=>$_SESSION['ldapshare']['data']['post_id_oldest']));
		if($prepare) {
			$rowCount = $prepare->rowCount();
			if($rowCount > 0) {
				$render .= '<comments>';
				$u = 0;
				while($comment = $prepare->fetch(PDO::FETCH_OBJ)) {
					if($u == 0 && ($_SESSION['ldapshare']['data']['comment_id_oldest'] > $comment->comment_id || $_SESSION['ldapshare']['data']['comment_id_oldest'] == 0)) {
						$_SESSION['ldapshare']['data']['comment_id_oldest'] = $comment->comment_id;
					}
					$render .= '<comment post_id="'.$comment->post_id.'" comment_id="'.$comment->comment_id.'"><![CDATA['.$this->render_comment($comment).']]></comment>';
					if($_SESSION['ldapshare']['data']['comment_id_newest'] < $comment->comment_id || $_SESSION['ldapshare']['data']['comment_id_newest'] == 0) {
						$_SESSION['ldapshare']['data']['comment_id_newest'] = $comment->comment_id;
					}
					$u++;
				}
				$render .= '</comments>';
			}
		}
		return $render;
	}
	private function get_postlist($order) {
		$render = '';
		$flt = array();
		$parameters = array();
		$flt[] = '1';
		if($order == 'ASC') {
			if(isset($_SESSION['ldapshare']['data']['post_id_newest']) == 1 && $_SESSION['ldapshare']['data']['post_id_newest'] > 0) {
				$flt[] = 'post.post_id > :post_id_newest';
				$parameters[':post_id_newest'] = $_SESSION['ldapshare']['data']['post_id_newest'];
			}
		}
		if($order == 'DESC') {
			if(isset($_SESSION['ldapshare']['data']['post_id_oldest']) == 1 && $_SESSION['ldapshare']['data']['post_id_oldest'] > 0) {
				$flt[] = 'post.post_id < :post_id_oldest';
				$parameters[':post_id_oldest'] = $_SESSION['ldapshare']['data']['post_id_oldest'];
			}
		}
		$parameters[':user_id'] = $this->user->user_id;
		$query = $this->select_post.' WHERE '.implode(' AND ', $flt).' GROUP BY post.post_id ORDER BY post.post_id '.$order;
		if($order == 'DESC') {
			$query .= ' LIMIT 0,'.LIMIT_POSTS;
		}
		$prepare = $this->pdo_execute($query, $parameters);
		if($prepare) {
			$rowCount = $prepare->rowCount();
			if($rowCount > 0) {
				$render .= '<posts>';
				$u = 0;
				while($post = $prepare->fetch(PDO::FETCH_OBJ)) {
					if($order == 'ASC') {
						if($u == 0 && ($_SESSION['ldapshare']['data']['post_id_oldest'] > $post->post_id || $_SESSION['ldapshare']['data']['post_id_oldest'] == 0)) {
							$_SESSION['ldapshare']['data']['post_id_oldest'] = $post->post_id;
						}
						$_SESSION['ldapshare']['data']['post_id_newest'] = $post->post_id;
					}
					if($order == 'DESC') {
						if($u == 0 && ($_SESSION['ldapshare']['data']['post_id_newest'] < $post->post_id || $_SESSION['ldapshare']['data']['post_id_newest'] == 0)) {
							$_SESSION['ldapshare']['data']['post_id_newest'] = $post->post_id;
						}
						$_SESSION['ldapshare']['data']['post_id_oldest'] = $post->post_id;
					}
					$render .= '<post post_id="'.$post->post_id.'"><![CDATA['.$this->render_post($post).']]></post>';
					$u++;
				}
				$render .= '</posts>';
			}
		} else {
			$this->pdo_error($prepare);
		}
		return $render;
	}
	private function get_user_by_type($value, $type) {
		$query = 'SELECT user.* FROM '.TABLE_USER.' user WHERE user.'.$type.' = :'.$type.' GROUP BY user.user_id';
		$prepare = $this->pdo_execute($query, array(':'.$type=>$value));
		if($prepare) {
			$rowCount = $prepare->rowCount();
			if($rowCount > 0) {
				return $prepare->fetch(PDO::FETCH_OBJ);
			}
		}
	}
	private function get_post_by_id($post_id) {
		$query = $this->select_post.' WHERE post.post_id = :post_id GROUP BY post.post_id';
		$prepare = $this->pdo_execute($query, array(':post_id'=>$post_id, ':user_id'=>$this->user->user_id));
		if($prepare) {
			$rowCount = $prepare->rowCount();
			if($rowCount > 0) {
				return $prepare->fetch(PDO::FETCH_OBJ);
			}
		}
	}
	private function get_comment_by_id($comment_id) {
		$query = $this->select_comment.' WHERE comment.comment_id = :comment_id GROUP BY comment.comment_id';
		$prepare = $this->pdo_execute($query, array(':comment_id'=> $comment_id));
		if($prepare) {
			$rowCount = $prepare->rowCount();
			if($rowCount > 0) {
				return $prepare->fetch(PDO::FETCH_OBJ);
			}
		}
	}
	private function get_photo_by_id($photo_id) {
		$query = 'SELECT photo.*, DATE_ADD(photo.photo_datecreated, INTERVAL '.$_SESSION['ldapshare']['data']['timezone'].' HOUR) AS photo_datecreated FROM '.TABLE_PHOTO.' photo WHERE photo.photo_id = :photo_id GROUP BY photo.photo_id';
		$prepare = $this->pdo_execute($query, array(':photo_id'=> intval($photo_id)));
		if($prepare) {
			$rowCount = $prepare->rowCount();
			if($rowCount > 0) {
				return $prepare->fetch(PDO::FETCH_OBJ);
			}
		}
	}
	private function insert_link($post_id, $data) {
		$query = 'INSERT INTO '.TABLE_LINK.' (post_id, link_url, link_title, link_image, link_video, link_videotype, link_videowidth, link_videoheight, link_icon, link_description, link_datecreated) VALUES (:post_id, :link_url, :link_title, NULLIF(:link_image, \'\'), NULLIF(:link_video, \'\'), NULLIF(:link_videotype, \'\'), NULLIF(:link_videowidth, \'\'), NULLIF(:link_videoheight, \'\'), NULLIF(:link_icon, \'\'), NULLIF(:link_description, \'\'), :link_datecreated)';
		$prepare = $this->pdo_execute($query, array(':post_id'=>$post_id, ':link_url'=>$data->link_url, ':link_title'=>$data->link_title, ':link_image'=>$data->link_image, ':link_video'=>$data->link_video, ':link_videotype'=>$data->link_videotype, ':link_videowidth'=>$data->link_videowidth, ':link_videoheight'=>$data->link_videoheight, ':link_icon'=>$data->link_icon, ':link_description'=>$data->link_description, ':link_datecreated'=>date('Y-m-d H:i:s')));
	}
	private function render_post($post) {
		$render = '<div class="post" id="post_'.$post->post_id.'">';
		$render .= '<div class="post_display">';
		$render .= '<div class="post_thumb">';
		$classes = array();
		if($post->user_id == $this->user->user_id) {
			$classes[] = 'you';
		}
		if($post->user_file != '') {
			$render .= '<img class="'.implode(' ', $classes).'" alt="" src="storage/'.$post->user_file.'">';
		} else if(GRAVATAR == 1) {
			$render .= '<img class="'.implode(' ', $classes).'" alt="" src="http://www.gravatar.com/avatar/'.md5(strtolower($post->user_email)).'?rating='.GRAVATAR_RATING.'&size=50&default='.GRAVATAR_DEFAULT.'">';
		} else {
			$render .= '<img class="'.implode(' ', $classes).'" alt="" src="medias/avatar.png">';
		}
		$render .= '</div>';
		$render .= '<div class="post_text">';
		if($post->user_id == $this->user->user_id) {
			$render .= '<a class="delete_action popin_show" href="?a=postdelete&amp;post_id='.$post->post_id.'"></a>';
			$username = $this->str['you'];
		} else {
			$username = $post->user_firstname.' '.$post->user_lastname;
		}
		$render .= '<p><span class="username">'.$username.'</span>  · <span class="datecreated" id="post_datecreated_'.$post->post_id.'" title="'.$post->post_datecreated.'">'.$this->date_transform($post->post_datecreated).'</span></p>';
		$render .= '<p>'.$this->render_content($post->post_content).'</p>';
		$render .= '</div>';
		$render .= $this->render_linklist($post);
		$render .= $this->render_address($post);
		$render .= $this->render_photo($post);
		$render .= '<p class="post_detail">';
		if($post->you_like == 1) {
			$render .= '<span class="like like_inactive">';
		} else {
			$render .= '<span class="like">';
		}
		$render .= '<a class="post_like_action" href="?a=postlike&amp;post_id='.$post->post_id.'">'.$this->str['like'].'</a> ·</span> ';
		if($post->you_like == 1) {
			$render .= '<span class="unlike">';
		} else {
			$render .= '<span class="unlike unlike_inactive">';
		}
		$render .= '<a class="post_unlike_action" href="?a=postunlike&amp;post_id='.$post->post_id.'">'.$this->str['unlike'].'</a> ·</span> ';
		$render .= '<a class="comment_action" href="#commentform_'.$post->post_id.'">'.$this->str['comment'].'</a>';
		$render .= '</p>';
		$render .= '<div class="commentlist" id="commentlist_'.$post->post_id.'">';
		$render .= '<div id="post_like_render_'.$post->post_id.'">';
		$render .= $this->render_like($post, 0);
		$render .= '</div>';
		$render .= '<div class="commentlist_display">';
		if($post->count_comment > 0) {
			$render .= $this->render_commentlist($post, 0);
		}
		$render .= '</div>';
		$render .= '<div class="comment commentform" id="commentform_'.$post->post_id.'">';
		$render .= '<div class="comment_display commentform_display">';
		$render .= '<div class="comment_thumb">';
		if($this->user->user_file != '') {
			$render .= '<img class="you" alt="" src="storage/'.$this->user->user_file.'">';
		} else if(GRAVATAR == 1) {
			$render .= '<img class="you" alt="" src="http://www.gravatar.com/avatar/'.md5(strtolower($this->user->user_email)).'?rating='.GRAVATAR_RATING.'&size=30&default='.GRAVATAR_DEFAULT.'">';
		} else {
			$render .= '<img class="you" alt="" src="medias/avatar.png">';
		}
		$render .= '</div>';
		$render .= '<div class="comment_text">';
		$render .= '<form action="?a=comment&amp;post_id='.$post->post_id.'" method="post">';
		$render .= '<p><textarea class="textarea" name="comment"></textarea></p>';
		$render .= '<p class="submit_btn"><input class="inputsubmit" type="submit" value="'.$this->str['comment'].'"></p>';
		$render .= '</form>';
		$render .= '</div>';
		$render .= '</div>';
		$render .= '</div>';
		$render .= '</div>';
		$render .= '</div>';
		$render .= '</div>';
		return $render;
	}
	private function render_commentlist($post, $all) {
		$render = '';
		if($post->count_comment > 0) {
			$limit = '';
			if($all == 1) {
				$max = $post->count_comment - LIMIT_COMMENTS;
				$limit = ' LIMIT 0, '.$max;
			}
			if($all == 0) {
				if($post->count_comment > LIMIT_COMMENTS) {
					$render .= '<div class="comment comment_all" id="comment_all_'.$post->post_id.'">';
					$render .= '<div class="comment_display comment_all_display">';
					$render .= '<p><a class="commentall_action" href="?a=commentlist&amp;post_id='.$post->post_id.'">'.sprintf($this->str['view_all_comments'], $post->count_comment).'</a></p>';
					$render .= '</div>';
					$render .= '</div>';
					$min = $post->count_comment - LIMIT_COMMENTS;
					$limit = ' LIMIT '.$min.', '.LIMIT_COMMENTS;
				}
			}
			$query = $this->select_comment.' WHERE comment.post_id = :post_id GROUP BY comment.comment_id'.$limit;
			$prepare = $this->pdo_execute($query, array(':post_id'=>$post->post_id));
			if($prepare) {
				$rowCount = $prepare->rowCount();
				if($rowCount > 0) {
					$u = 0;
					while($comment = $prepare->fetch(PDO::FETCH_OBJ)) {
						if($u == 0 && ($_SESSION['ldapshare']['data']['comment_id_oldest'] > $comment->comment_id || $_SESSION['ldapshare']['data']['comment_id_oldest'] == 0)) {
							$_SESSION['ldapshare']['data']['comment_id_oldest'] = $comment->comment_id;
						}
						$render .= $this->render_comment($comment);
						if($_SESSION['ldapshare']['data']['comment_id_newest'] < $comment->comment_id || $_SESSION['ldapshare']['data']['comment_id_newest'] == 0) {
							$_SESSION['ldapshare']['data']['comment_id_newest'] = $comment->comment_id;
						}
						$u++;
					}
				}
			}
		}
		return $render;
	}
	private function render_comment($comment) {
		$render = '<div class="comment" id="comment_'.$comment->comment_id.'">';
		$render .= '<div class="comment_display">';
		$render .= '<div class="comment_thumb">';
		$classes = array();
		if($comment->user_id == $this->user->user_id) {
			$classes[] = 'you';
		}
		if($comment->user_file != '') {
			$render .= '<img class="'.implode(' ', $classes).'" alt="" src="storage/'.$comment->user_file.'">';
		} else if(GRAVATAR == 1) {
			$render .= '<img class="'.implode(' ', $classes).'" alt="" src="http://www.gravatar.com/avatar/'.md5(strtolower($comment->user_email)).'?rating='.GRAVATAR_RATING.'&size=30&default='.GRAVATAR_DEFAULT.'">';
		} else {
			$render .= '<img class="'.implode(' ', $classes).'" alt="" src="medias/avatar.png">';
		}
		$render .= '</div>';
		$render .= '<div class="comment_text">';
		if($comment->user_id == $this->user->user_id) {
			$render .= '<a class="delete_action popin_show" href="?a=commentdelete&amp;comment_id='.$comment->comment_id.'"></a>';
			$username = $this->str['you'];
		} else {
			$username = $comment->user_firstname.' '.$comment->user_lastname;
		}
		$render .= '<p><span class="username">'.$username.'</span> · <span class="datecreated" id="comment_datecreated_'.$comment->comment_id.'" title="'.$comment->comment_datecreated.'">'.$this->date_transform($comment->comment_datecreated).'</span></p>';
		$render .= '<p>'.$this->render_content($comment->comment_content).'</p>';
		$render .= '</div>';
		$render .= '</div>';
		$render .= '</div>';
		return $render;
	}
	private function render_like($post, $all) {
		$render = '';
		if($post->post_countlike > 0) {
			if($post->post_countlike == 4) {
				$display_limit = 2;
			} else {
				$display_limit = 3;
			}
			if($post->post_countlike > $display_limit && $all == 0) {
				$min = $post->post_countlike - $display_limit;
				$limit = ' LIMIT '.$min.', '.$display_limit;
			} else {
				$limit = '';
			}
			$query = 'SELECT l.*, user.*, DATE_ADD(l.like_datecreated, INTERVAL '.$_SESSION['ldapshare']['data']['timezone'].' HOUR) AS like_datecreated, IF(l.user_id = :user_id OR post.user_id = l.user_id, 1, 0) AS ordering FROM '.TABLE_LIKE.' l LEFT JOIN '.TABLE_USER.' user ON user.user_id = l.user_id LEFT JOIN '.TABLE_POST.' post ON post.post_id = l.post_id WHERE l.post_id = :post_id GROUP BY l.like_id ORDER BY ordering ASC, l.like_id ASC'.$limit;
			$prepare = $this->pdo_execute($query, array(':post_id'=>$post->post_id, ':user_id'=>$this->user->user_id));
			if($prepare) {
				$rowCount = $prepare->rowCount();
				if($rowCount > 0) {
					$render .= '<div class="comment post_like" id="post_like_'.$post->post_id.'">';
					$render .= '<div class="comment_display post_like_display">';
					$render .= '<p>';
					$u = 1;
					while($like = $prepare->fetch(PDO::FETCH_OBJ)) {
						if($this->user->user_id == $like->user_id) {
							$username = $this->str['you'];
						} else {
							$username = $like->user_firstname.' '.$like->user_lastname;
						}
						$render .= '<span class="username" title="'.$this->date_transform($like->like_datecreated).'">'.$username.'</span>';
						if($post->post_countlike > 1) {
							if($u == $rowCount && $rowCount < $post->post_countlike) {
								$diff = $post->post_countlike - $rowCount;
								$render .=  ' '.$this->str['and'].' <a class="likelist_action" href="?a=likelist&amp;post_id='.$post->post_id.'">'.sprintf($this->str['others'], $diff).'</a> ';
							} else if($u == $rowCount - 1 && $rowCount == $post->post_countlike) {
								$render .=  ' '.$this->str['and'].' ';
							} else if($u < $rowCount) {
								$render .= ', ';
							}
						}
						$u++;
					}
					$k = '';
					if($post->you_like == 1 && $post->post_countlike > 1) {
						$k = 'like_people_you';
					} else if($post->you_like == 1 && $post->post_countlike == 1) {
						$k = 'like_you';
					} else if($post->you_like == 0 && $post->post_countlike > 1) {
						$k = 'like_people_plural';
					} else if($post->you_like == 0 && $post->post_countlike == 1) {
						$k = 'like_people_singular';
					}
					if(isset($this->str[$k]) == 1) {
						$render .= ' '.$this->str[$k].'.';
					}
					$render .= '</p>';
					$render .= '</div>';
					$render .= '</div>';
				}
			}
		}
		return $render;
	}
	private function render_photo($post) {
		$render = '';
		if($post->post_photo) {
			$render .= '<div class="photo" id="photo_'.$post->post_id.'">';
			$render .= '<div class="photo_display">';
			$render .= '<img alt="" src="storage/'.$post->post_photo.'">';
			$render .= '</div>';
			$render .= '</div>';
		}
		return $render;
	}
	private function render_linklist($post) {
		$render = '';
		if($post->post_countlink > 0) {
			$query = 'SELECT link.* FROM '.TABLE_LINK.' link WHERE link.post_id = :post_id GROUP BY link.link_id';
			$prepare = $this->pdo_execute($query, array(':post_id'=>$post->post_id));
			if($prepare) {
				$rowCount = $prepare->rowCount();
				if($rowCount > 0) {
					$render .= '<div class="linklist">';
					while($link = $prepare->fetch(PDO::FETCH_OBJ)) {
						$render .= $this->render_link($link);
					}
					$render .= '</div>';
				}
			}
		}
		return $render;
	}
	private function render_link($link) {
		$url = parse_url($link->link_url);
		$render = '<div class="link" id="link_'.$link->link_id.'">';
		$render .= '<div class="link_display">';
		if($link->link_image != '') {
			$render .= '<div class="link_thumb">';
			$render .= '<a href="'.$link->link_url.'" target="_blank"><img alt="" src="'.$link->link_image.'"></a>';
			$render .= '</div>';
			$render .= '<div class="link_text">';
		} else {
			$render .= '<div class="link_text link_text_full">';
		}
		$render .= '<p><a href="'.$link->link_url.'" target="_blank">'.$link->link_title.'</a><br>';
		if($link->link_icon != '') {
			$render .= '<span class="icon"><img alt="" src="'.$link->link_icon.'"></span> ';
		}
		$render .= '<span class="hostname">'.$url['host'].'</span></p>';
		if($link->link_description != '') {
			$render .= '<p>'.$this->render_content($link->link_description).'</p>';
		}
		$render .= '</div>';
		if($link->link_video != '' && $link->link_videowidth != '' && $link->link_videoheight != '') {
			$link->link_videoheight = round(($link->link_videoheight * 540) / $link->link_videowidth);
			$link->link_videowidth = 540;
			$render .= '<p class="playvideo_link"><a href="#playvideo_'.$link->link_id.'"><img src="medias/play_video.png" alt=""></a></p>';
			$render .= '<iframe class="playvideo" id="playvideo_'.$link->link_id.'" width="'.$link->link_videowidth.'" height="'.$link->link_videoheight.'" src="'.$link->link_video.'" frameborder="0"></iframe>';
		}
		$render .= '</div>';
		$render .= '</div>';
		return $render;
	}
	private function render_address($post) {
		$render = '';
		if($post->post_address) {
			$render .= '<div class="address" id="address_'.$post->post_id.'">';
			$render .= '<div class="address_display">';
			$render .= '<p>'.$post->post_address.'</p>';
			$render .= '<p><a href="http://maps.google.com/maps?q='.urlencode($post->post_address).'&oe=UTF-8&ie=UTF-8" target="_blank"><img src="http://maps.googleapis.com/maps/api/staticmap?center='.urlencode($post->post_address).'&markers=color:red|'.urlencode($post->post_address).'&zoom=15&size=540x200&sensor=false" alt=""></a></p>';
			$render .=' </div>';
			$render .= '</div>';
		}
		return $render;
	}
	private function render_content($text) {
		preg_match_all('(((ftp|http|https){1}://)[-a-zA-Z0-9@:%_\+.~#!\(\)?&//=]+)', $text, $matches);
		$matches = $matches[0];
		if(count($matches) > 0) {
			$matches = array_unique($matches);
			foreach($matches as $match) {
				$text = str_replace($match, '<a href="'.$match.'" target="_blank">'.$match.'</a>', $text);
			}
		}
		preg_match_all("/[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}/i", $text, $matches);
		$matches = $matches[0];
		if(count($matches) > 0) {
			$matches = array_unique($matches);
			foreach($matches as $match) {
				$text = str_replace($match, '<a href="mailto:'.$match.'">'.$match.'</a>', $text);
			}
		}
		return nl2br($text);
	}
	private function date_transform($date) {
		if($date != '') {
			$format =  $this->str['date_format'];
			if(function_exists('date_create') && function_exists('date_format')) {
				$date = date_create($date);
				$date = date_format($date, $format);
			} else {
				$date = date($format, strtotime($date));
			}
			$formats = array('l', 'D', 'jS', 'F', 'M');
			foreach($formats as $k) {
				if(strstr($format, $k) && isset($this->str['date_'.$k]) == 1) {
					$ref = $this->str['date_'.$k];
					if($k == 'jS') {
						$ref = array_reverse($ref, 1);
					}
					$date = str_replace(array_keys($ref), array_values($ref), $date);
				}
			}
		}
		return $date;
	}
	private function image_upload($key, $width, $height) {
		$newfile = '';
		$folder = 'storage';
		if(is_dir($folder)) {
			if(in_array($_FILES[$key]['type'], $this->allowed_images)) {
				$year = date('Y');
				if(!is_dir($folder.'/'.$year)) {
					mkdir($folder.'/'.$year);
					copy($folder.'/index.php', $folder.'/'.$year.'/index.php');
				}
				$newfile = $year.'/'.sha1(uniqid('', 1).md5($_FILES[$key]['name'])).substr($_FILES[$key]['name'], strrpos($_FILES[$key]['name'], '.'));
				move_uploaded_file($_FILES[$key]['tmp_name'], $folder.'/'.$newfile);
				require('thirdparty/zebra.image.php');
				$image = new Zebra_Image();
				$image->source_path = $folder.'/'.$newfile;
				$image->target_path = $folder.'/'.$newfile;
				$image->jpeg_quality = 75;
				$image->preserve_aspect_ratio = true;
				$image->enlarge_smaller_images = false;
				$image->resize($width, $height, ZEBRA_IMAGE_NOT_BOXED);
			}
		}
		return $newfile;
	}
	private function analyze_link($link) {
		$data = new stdClass();
		$default = array('link_id'=>0, 'link_url'=>$link, 'link_title'=>'', 'link_image'=>'', 'link_video'=>'', 'link_videotype'=>'', 'link_videowidth'=>'', 'link_videoheight'=>'', 'link_icon'=>'', 'link_description'=>'', 'link_charsetserver'=>'', 'link_charsetclient'=>'');
		foreach($default as $k => $v) {
			$data->{$k} = $v;
		}
		if(isset($_SESSION['ldapshare'][$link]) == 1) {
			return unserialize($_SESSION['ldapshare'][$link]);
		} else {
			$keys = array();
			$headers = get_headers($link, 1);
			if(isset($headers['Location']) == 1) {
				if(is_array($headers['Location'])) {
					$data->link_url = array_pop($headers['Location']);
				} else {
					$data->link_url = $headers['Location'];
				}
			}
			if(isset($headers['Content-Type']) == 1) {
				if(is_array($headers['Content-Type'])) {
					$keys['content-type-server'] = array_pop($headers['Content-Type']);
				} else {
					$keys['content-type-server'] = $headers['Content-Type'];
				}
			}
			$opts = array('http'=>array('header'=>'User-Agent: '.filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES)."\r\n"));
			$context = stream_context_create($opts);
			$content = file_get_contents($data->link_url, false, $context);
			$content = str_replace(array("\t", "\r\n", "\n"), array('', '', ''), $content);
			$pattern_one = array();
			$pattern_one['title'] = "|<[tT][iI][tT][lL][eE](.*)>(.*)<\/[tT][iI][tT][lL][eE]>|U";
			$pattern_one['charsetclient'] = "|<[mM][eE][tT][aA](.*)[cC][hH][aA][rR][sS][eE][tT]=[\"'](.*)[\"'](.*)>|U";
			foreach($pattern_one as $k => $pattern) {
				preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
				foreach($matches as $match) {
					$keys[$k] = trim($match[2]);
				}
			}
			$pattern_multi = array();
			$pattern_multi["|<[lL][iI][nN][kK](.*)[hH][rR][eE][fF]=[\"'](.*)[\"'](.*)>|U"] = array("|(.*)[rR][eE][lL]=[\"'](.*)[\"'](.*)|U", "|(.*)[rR][eE][fF]=[\"'](.*)[\"'](.*)|U");
			$pattern_multi["|<[mM][eE][tT][aA](.*)[cC][oO][nN][tT][eE][nN][tT]=\"(.*)\"(.*)>|U"] = array("|(.*)[nN][aA][mM][eE]=[\"'](.*)[\"'](.*)|U", "|(.*)[pP][rR][oO][pP][eE][rR][tT][yY]=[\"'](.*)[\"'](.*)|U", "|(.*)[hH][tT][tT][pP]-[eE][qQ][uU][iI][vV]=[\"'](.*)[\"'](.*)|U");
			foreach($pattern_multi as $pattern => $pattern_sub) {
				preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
				foreach($matches as $match) {
					foreach($pattern_sub as $pattern) {
						preg_match_all($pattern, $match[1], $matches_sub, PREG_SET_ORDER);
						foreach($matches_sub as $match_sub) {
							$keys[strtolower($match_sub[2])] = $match[2];
						}
						preg_match_all($pattern, $match[3], $matches_sub, PREG_SET_ORDER);
						foreach($matches_sub as $match_sub) {
							$keys[strtolower($match_sub[2])] = $match[2];
						}
					}
				}
			}
			foreach($keys as $key => $value) {
				$value = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
				if($key == 'image_src') {
					$data->link_image = $value;
				} else if($key == 'shortcut icon') {
					$data->link_icon = $value;
				} else if($key == 'content-type-server' && stristr($value, 'charset')) {
					$data->link_charsetserver = substr($value, strpos($value, '=') + 1);
				} else if($key == 'content-type' && stristr($value, 'charset')) {
					$data->link_charsetclient = substr($value, strpos($value, '=') + 1);
				} else if(substr($key, 0, 3) == 'og:') {
					$key = substr($key, 3);
					$key = str_replace(':', '', $key);
					$key = str_replace('_', '', $key);
					$data->{'link_'.$key} = $value;
				} else {
					$data->{'link_'.$key} = $value;
				}
			}
			if($data->link_icon != '' && substr($data->link_icon, 0, 4) != 'http' && substr($data->link_icon, 0, 5) != 'data:') {
				$url = parse_url($data->link_url);
				$data->link_icon = $url['scheme'].'://'.$url['host'].'/'.$data->link_icon;
			}
			if(strtolower($data->link_charsetserver) != 'utf-8' && strtolower($data->link_charsetclient) != 'utf-8') {
				$data->link_title = utf8_encode($data->link_title);
				$data->link_description = utf8_encode($data->link_description);
			}
			$_SESSION['ldapshare'][$link] = serialize($data);
			return $data;
		}
	}
}
