<?php
class wall369 {
	function __construct() {
		$this->microtime_start = microtime(1);
		session_set_cookie_params(0, '/', '', $this->is_https(), 1);
		session_start();
		set_error_handler(array($this, 'error_handler'));
		register_shutdown_function(array($this, 'shutdown_function'));
		if(isset($_SESSION['wall369']) == 0) {
			$_SESSION['wall369'] = array();
		}
		if(isset($_SESSION['wall369']['timezone']) == 0) {
			$_SESSION['wall369']['timezone'] = 0;
		}
		$this->language = 'en';
		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) == 1) {
			$lng_array = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			if(count($lng_array) > 0) {
				foreach($lng_array as $k => $v) {
					$lng = explode(';', $v);
					$lng = explode('-', $lng[0]);
					$lng = $lng[0];
					if(preg_match('/^[a-z]{2}$/', $lng)) {
						if(file_exists('languages/'.$lng.'.dist.php')) {
							$this->language = $lng;
							break;
						}
					}
				}
			}
		}
		include_once('languages/'.$this->language.'.dist.php');
		$this->date_day = gmdate('Y-m-d', date('U') + 3600 * $_SESSION['wall369']['timezone']);
		$this->date_time = gmdate('H:i:s', date('U') + 3600 * $_SESSION['wall369']['timezone']);
		$this->set_get('a', 'index', 'alphabetic');
		$this->set_get('post_id', '', 'numeric');
		$this->set_get('comment_id', '', 'numeric');
		$this->set_get('photo_id', '', 'numeric');
		$this->queries = array();
		try {
			$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', PDO::ATTR_PERSISTENT => 1);
			$this->pdo = new PDO(DATABASE_TYPE.':dbname='.DATABASE_NAME.';host='.DATABASE_HOST.';port='.DATABASE_PORT, DATABASE_USER, DATABASE_PASSWORD, $options);
		} catch(PDOException $e) {
			trigger_error($e->getMessage());
		}
		if(DEMO == 1 && isset($_SESSION['wall369']['user_id']) == 0) {
			$_SESSION['wall369']['user_id'] = rand(1, 100);
		}
		if($this->get['a'] == 'index') {
			$_SESSION['wall369']['post_id_oldest'] = 0;
			$_SESSION['wall369']['post_id_newest'] = 0;
			$_SESSION['wall369']['comment_id_oldest'] = 0;
			$_SESSION['wall369']['comment_id_newest'] = 0;
		}
		if(isset($_SESSION['wall369']['user_id']) == 0 && isset($_COOKIE['user_token']) == 1) {
			$user = $this->get_user_by_token($_COOKIE['user_token']);
			if($user) {
				$_SESSION['wall369']['user_id'] = $user->user_id;
			}
		} else if(isset($_SESSION['wall369']['user_id']) == 1) {
			$this->user = $this->get_user_by_id($_SESSION['wall369']['user_id']);
		}
		$this->post_query = 'SELECT post.*, user.*, DATE_ADD(post.post_datecreated, INTERVAL '.$_SESSION['wall369']['timezone'].' HOUR) AS post_datecreated, COUNT(DISTINCT(comment.comment_id)) AS count_comment, COUNT(DISTINCT(link.link_id)) AS post_countlink, COUNT(DISTINCT(photo.photo_id)) AS post_countphoto, COUNT(DISTINCT(address.address_id)) AS post_countaddress, COUNT(DISTINCT(l.like_id)) AS post_countlike, IF(l_you.like_id IS NOT NULL, 1, 0) AS you_like
		FROM '.TABLE_POST.' post
		LEFT JOIN '.TABLE_USER.' user ON user.user_id = post.user_id
		LEFT JOIN '.TABLE_COMMENT.' comment ON comment.post_id = post.post_id
		LEFT JOIN '.TABLE_LINK.' link ON link.post_id = post.post_id
		LEFT JOIN '.TABLE_PHOTO.' photo ON photo.post_id = post.post_id
		LEFT JOIN '.TABLE_ADDRESS.' address ON address.post_id = post.post_id
		LEFT JOIN '.TABLE_LIKE.' l ON l.post_id = post.post_id
		LEFT JOIN '.TABLE_LIKE.' l_you ON l_you.post_id = post.post_id AND l_you.user_id = :user_id';
	}
	function is_https() {
		if(isset($_SERVER['HTTPS']) == 1 && strtolower($_SERVER['HTTPS']) == 'on') {
			return 1;
		} else {
			return 0;
		}
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
		header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');
		header('Content-Type: text/xml; charset=UTF-8');
		$render = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
		$render .= '<wall369>'."\r\n";
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
		$render .= '</wall369>'."\r\n";
		echo $render;
		exit(0);
	}
	function render_debug() {
		$render = '';
		$microtime_end = microtime(1);
		$microtime_total = $microtime_end - $this->microtime_start;
		$render .= '<post_id_oldest>'.$_SESSION['wall369']['post_id_oldest'].'</post_id_oldest>'."\r\n";
		$render .= '<post_id_newest>'.$_SESSION['wall369']['post_id_newest'].'</post_id_newest>'."\r\n";
		$render .= '<comment_id_oldest>'.$_SESSION['wall369']['comment_id_oldest'].'</comment_id_oldest>'."\r\n";
		$render .= '<comment_id_newest>'.$_SESSION['wall369']['comment_id_newest'].'</comment_id_newest>'."\r\n";
		$render .= '<microtime_total>'.round($microtime_total, 5).'</microtime_total>'."\r\n";
		if(function_exists('memory_get_peak_usage')) {
			$render .= '<memory_get_peak_usage>'.number_format(memory_get_peak_usage(), 0, '.', ' ').'</memory_get_peak_usage>'."\r\n";
		}
		if(function_exists('memory_get_usage')) {
			$render .= '<memory_get_usage>'.number_format(memory_get_usage(), 0, '.', ' ').'</memory_get_usage>'."\r\n";
		}
		foreach($this->queries as $query) {
			$render .= '<query><![CDATA['.$query.']]></query>'."\r\n";
		}
		return $render;
	}
	function render() {
		if($this->get['a'] == 'index') {
			header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
			header('Content-Type: text/html; charset=UTF-8');
			if(file_exists('wall369.tpl')) {
				$render = file_get_contents('wall369.tpl')."\r\n";
			} else {
				$render = file_get_contents('wall369.dist.tpl')."\r\n";
			}
		} else {
			header('Content-Type: text/xml; charset=UTF-8');
			$render = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
			$render .= '<wall369>'."\r\n";
			if(method_exists($this, 'action_'.$this->get['a'])) {
				$actions_guest = array('islogged', 'loginform', 'login', 'timezone');
				if(isset($_SESSION['wall369']['user_id']) == 0 && !in_array($this->get['a'], $actions_guest)) {
					header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
				} else {
					header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
					$render .= $this->{'action_'.$this->get['a']}()."\r\n";
				}
			} else {
				header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
			}
			if(DEBUG == 1) {
				$render .= $this->render_debug();
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
	function pdo_execute($query, $parameters) {
		if(DEBUG == 1) {
			$this->queries[] = $query;
		}
		$prepare = $this->pdo->prepare($query);
		$execute = $prepare->execute($parameters);
		if($execute) {
			return $prepare;
		} else {
			$this->pdo_error($prepare);
		}
	}
	function pdo_error($prepare) {
		if($prepare->errorCode() != 0) {
			$errorinfo = $prepare->errorinfo();
			trigger_error($errorinfo[2]);
		}
	}
	function action_islogged() {
		$render = '';
		if(DEMO == 1) {
			$render .= '<status>ok</status>';
		} else if(isset($_SESSION['wall369']['user_id']) == 1 && isset($_COOKIE['user_token']) == 1 && $this->get_user_by_token($_COOKIE['user_token'])) {
			$render .= '<status>ok</status>';
		} else {
			$render .= '<status>ko</status>';
		}
		return $render;
	}
	function action_timezone() {
		$render = '';
		$this->set_get('t', 0, 'numeric');
		$_SESSION['wall369']['timezone'] = $this->get['t'];
		$render .= '<timezone>'.$this->get['t'].'</timezone>';
		return $render;
	}
	function action_loginform() {
		$render = '';
		$render .= '<content><![CDATA[';
		$render .= $this->render_loginform();
		$render .= ']]></content>';
		return $render;
	}
	function action_login() {
		$render = '';
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
							$user_lastname = $ldap_get_entries[0][LDAP_LASTNAME][0];
							$user_firstname = $ldap_get_entries[0][LDAP_FIRSTNAME][0];
							$status = 'ok';
							if(isset($ldap_get_entries[0]['jpegphoto'][0]) == 1) {
								$user_file = base64_encode($ldap_get_entries[0]['jpegphoto'][0]);
							} else {
								$user_file = '';
							}
							$user = $this->get_user_by_email($_POST['email']);
							if($user) {
								$query = 'UPDATE '.TABLE_USER.' SET user_lastname = :user_lastname, user_firstname = :user_firstname, user_file = NULLIF(:user_file, \'\') WHERE user_email = :user_email';
								$prepare = $this->pdo_execute($query, array(':user_email'=>$_POST['email'], ':user_lastname'=>$user_lastname, ':user_firstname'=>$user_firstname, ':user_file'=>$user_file));
								$user_id = $user->user_id;
							} else {
								$query = 'INSERT INTO '.TABLE_USER.' (user_email, user_lastname, user_firstname, user_file, user_datecreated) VALUES (:user_email, :user_lastname, :user_firstname, NULLIF(:user_file, \'\'), :user_datecreated)';
								$prepare = $this->pdo_execute($query, array(':user_email'=>$_POST['email'], ':user_lastname'=>$user_lastname, ':user_firstname'=>$user_firstname, ':user_file'=>$user_file, ':user_datecreated'=>date('Y-m-d H:i:s')));
								if($prepare) {
									$user_id = $this->pdo->lastinsertid();
								}
							}
							$_SESSION['wall369']['user_id'] = $user_id;
							$user_token = $this->string_generate(40, 1, 1, 1);
							$query = 'UPDATE '.TABLE_USER.' SET user_token = :user_token WHERE user_id = :user_id';
							$prepare = $this->pdo_execute($query, array(':user_id'=>$user_id, ':user_token'=>$user_token));
							setcookie('user_token', $user_token, time() + 3600 * 24 * 30, '/', '', $this->is_https(), 1);
						}
					}
				}
			}
			ldap_unbind($ldap_connect);
		}
		$render .= '<status>'.$status.'</status>';
		return $render;
	}
	function action_logout() {
		$render = '';
		$query = 'UPDATE '.TABLE_USER.' SET user_token = NULL WHERE user_id = :user_id';
		$prepare = $this->pdo_execute($query, array(':user_id'=>$_SESSION['wall369']['user_id']));
		unset($_SESSION['wall369']['user_id']);
		setcookie('user_token', NULL, NULL, '/');
		$_SESSION['wall369']['post_id_oldest'] = 0;
		$_SESSION['wall369']['post_id_newest'] = 0;
		$_SESSION['wall369']['comment_id_oldest'] = 0;
		$_SESSION['wall369']['comment_id_newest'] = 0;
		return $render;
	}
	function action_postform() {
		$render = '';
		$render .= '<content><![CDATA[';
		$render .= $this->render_postform();
		$render .= ']]></content>';
		return $render;
	}
	function action_postlist() {
		$render = '';
		$render .= $this->render_postlist();
		return $render;
	}
	function action_post() {
		$render = '';
		$query = 'INSERT INTO '.TABLE_POST.' (user_id, post_content, post_httpuseragent, post_remoteaddr, post_datecreated) VALUES (:user_id, :post_content, NULLIF(:post_httpuseragent, \'\'), NULLIF(:post_remoteaddr, \'\'), :post_datecreated)';
		$prepare = $this->pdo_execute($query, array(':user_id'=>$this->user->user_id, ':post_content'=>strip_tags($_POST['status_textarea']), ':post_httpuseragent'=>$_SERVER['HTTP_USER_AGENT'], ':post_remoteaddr'=>filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP), ':post_datecreated'=>date('Y-m-d H:i:s')));
		if($prepare) {
			$post_id = $this->pdo->lastinsertid();
			$render .= '<status>post_insert</status>';
			$photo_types = array('image/gif', 'image/jpeg', 'image/png');
			if(isset($_FILES['photo_inputfile']) == 1 && $_FILES['photo_inputfile']['error'] == 0 && in_array($_FILES['photo_inputfile']['type'], $photo_types)) {
				$photo_inputfile = $this->photo_add();
				$data = array('photo_inputfile'=>$photo_inputfile);
				$this->insert_photo($post_id, $data);
			}
			if(isset($_POST['link_inputtext']) == 1 && $_POST['link_inputtext'] != '' && $_POST['link_inputtext'] != 'http://') {
				$data = $this->analyze_link($_POST['link_inputtext']);
				$this->insert_link($post_id, $data);
			}
			if(isset($_POST['address_inputtext']) == 1 && $_POST['address_inputtext'] != '') {
				$data = array('address_title'=>$_POST['address_inputtext']);
				$this->insert_address($post_id, $data);
			}
			preg_match_all('(((ftp|http|https){1}://)[-a-zA-Z0-9@:%_\+.~#!\(\)?&//=]+)', $_POST['status_textarea'], $matches);
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
		return $render;
	}
	function action_postdelete() {
		$render = '';
		$render .= '<content><![CDATA[';
		$render .= '<div class="popin_content">';
		$render .= '<h2>'.$this->str[$this->language]['post_delete'].'</h2>';
		$render .= '<p><a class="post_delete_confirm_action" href="?a=postdeleteconfirm&amp;post_id='.$this->get['post_id'].'">'.$this->str[$this->language]['confirm'].'</a> · <a class="popin_hide" href="#">'.$this->str[$this->language]['cancel'].'</a></p>';
		$render .= '</div>';
		$render .= ']]></content>';
		return $render;
	}
	function action_postdeleteconfirm() {
		$render = '';
		$post = $this->get_post_by_id($this->get['post_id']);
		$render .= '<post_id>'.$this->get['post_id'].'</post_id>';
		if($post) {
			if($post->user_id == $this->user->user_id) {
				$query = 'DELETE FROM '.TABLE_POST.' WHERE user_id = :user_id AND post_id = :post_id';
				$prepare = $this->pdo_execute($query, array(':post_id'=>$this->get['post_id'], ':user_id'=>$this->user->user_id));
				if($prepare) {
					$query = 'DELETE FROM '.TABLE_ADDRESS.' WHERE post_id = :post_id';
					$prepare = $this->pdo_execute($query, array(':post_id'=>$this->get['post_id']));
					$query = 'DELETE FROM '.TABLE_COMMENT.' WHERE post_id = :post_id';
					$prepare = $this->pdo_execute($query, array(':post_id'=>$this->get['post_id']));
					$query = 'DELETE FROM '.TABLE_LIKE.' WHERE post_id = :post_id';
					$prepare = $this->pdo_execute($query, array(':post_id'=>$this->get['post_id']));
					$query = 'DELETE FROM '.TABLE_LINK.' WHERE post_id = :post_id';
					$prepare = $this->pdo_execute($query, array(':post_id'=>$this->get['post_id']));
					if($post->post_countphoto > 0) {
						$query = 'SELECT photo.* FROM '.TABLE_PHOTO.' photo WHERE photo.post_id = :post_id GROUP BY photo.photo_id';
						$prepare = $this->pdo_execute($query, array(':post_id'=>$post->post_id));
						if($prepare) {
							$rowCount = $prepare->rowCount();
							if($rowCount > 0) {
								while($photo = $prepare->fetch(PDO::FETCH_OBJ)) {
									unlink('storage/'.$photo->photo_file);
								}
							}
						}
						$query = 'DELETE FROM '.TABLE_PHOTO.' WHERE post_id = :post_id';
						$prepare = $this->pdo_execute($query, array(':post_id'=>$this->get['post_id']));
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
	function action_commentlist() {
		$render = '';
		$post = $this->get_post_by_id($this->get['post_id']);
		$render .= '<post_id>'.$this->get['post_id'].'</post_id>';
		$render .= '<content><![CDATA[';
		$render .= $this->render_commentlist($post, 1);
		$render .= ']]></content>';
		return $render;
	}
	function action_comment() {
		$render = '';
		$post = $this->get_post_by_id($this->get['post_id']);
		if($post) {
			$query = 'INSERT INTO '.TABLE_COMMENT.' (user_id, post_id, comment_content, comment_httpuseragent, comment_remoteaddr, comment_datecreated) VALUES (:user_id, :post_id, :comment_content, NULLIF(:comment_httpuseragent, \'\'), NULLIF(:comment_remoteaddr, \'\'), :comment_datecreated)';
			$prepare = $this->pdo_execute($query, array(':user_id'=>$this->user->user_id, ':post_id'=>$this->get['post_id'], ':comment_content'=>strip_tags($_POST['comment_textarea']), ':comment_httpuseragent'=>$_SERVER['HTTP_USER_AGENT'], ':comment_remoteaddr'=>filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP), ':comment_datecreated'=>date('Y-m-d H:i:s')));
			if($prepare) {
				$comment_id = $this->pdo->lastinsertid();
				$render .= '<status>comment_insert</status>';
			}
		} else {
			$render .= '<status>post_deleted</status>';
			$render .= '<post_id>'.$this->get['post_id'].'</post_id>';
			$render .= '<content><![CDATA[';
			$render .= '<p>'.$this->str[$this->language]['post_deleted'].'</p>';
			$render .= ']]></content>';
		}
		return $render;
	}
	function action_commentdelete() {
		$render = '';
		$render .= '<content><![CDATA[';
		$render .= '<div class="popin_content">';
		$render .= '<h2>'.$this->str[$this->language]['comment_delete'].'</h2>';
		$render .= '<p><a class="comment_delete_confirm_action" href="?a=commentdeleteconfirm&amp;comment_id='.$this->get['comment_id'].'">'.$this->str[$this->language]['confirm'].'</a> · <a class="popin_hide" href="#">'.$this->str[$this->language]['cancel'].'</a></p>';
		$render .= '</div>';
		$render .= ']]></content>';
		return $render;
	}
	function action_commentdeleteconfirm() {
		$render = '';
		$comment = $this->get_comment_by_id($this->get['comment_id']);
		$render .= '<comment_id>'.$this->get['comment_id'].'</comment_id>';
		if($comment) {
			if($comment->user_id == $this->user->user_id) {
				$query = 'DELETE FROM '.TABLE_COMMENT.' WHERE user_id = :user_id AND comment_id = :comment_id';
				$prepare = $this->pdo_execute($query, array(':comment_id'=>$this->get['comment_id'], ':user_id'=>$this->user->user_id));
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
	function action_postlike() {
		$render = '';
		$post = $this->get_post_by_id($this->get['post_id']);
		$render .= '<post_id>'.$this->get['post_id'].'</post_id>';
		if($post) {
			$query = 'INSERT INTO '.TABLE_LIKE.' (user_id, post_id, like_datecreated) VALUES (:user_id, :post_id, :like_datecreated)';
			$prepare = $this->pdo_execute($query, array(':user_id'=>$this->user->user_id, ':post_id'=>$this->get['post_id'], ':like_datecreated'=>date('Y-m-d H:i:s')));
			if($prepare) {
				$render .= '<status>like_insert</status>';
				$render .= '<content><![CDATA[';
				$post = $this->get_post_by_id($this->get['post_id']);
				$render .= $this->render_like($post, 0);
				$render .= ']]></content>';
			}
		} else {
			$render .= '<status>post_deleted</status>';
			$render .= '<content><![CDATA[';
			$render .= '<p>'.$this->str[$this->language]['post_deleted'].'</p>';
			$render .= ']]></content>';
		}
		return $render;
	}
	function action_postunlike() {
		$render = '';
		$post = $this->get_post_by_id($this->get['post_id']);
		$render .= '<post_id>'.$this->get['post_id'].'</post_id>';
		if($post) {
			$query = 'DELETE FROM '.TABLE_LIKE.' WHERE user_id = :user_id AND post_id = :post_id';
			$prepare = $this->pdo_execute($query, array(':user_id'=>$this->user->user_id, ':post_id'=>$this->get['post_id']));
			if($prepare) {
				$render .= '<status>like_delete</status>';
				$render .= '<content><![CDATA[';
				$post = $this->get_post_by_id($this->get['post_id']);
				$render .= $this->render_like($post, 0);
				$render .= ']]></content>';
			}
		} else {
			$render .= '<status>post_deleted</status>';
			$render .= '<content><![CDATA[';
			$render .= '<p>'.$this->str[$this->language]['post_deleted'].'</p>';
			$render .= ']]></content>';
		}
		return $render;
	}
	function action_likelist() {
		$render = '';
		$render .= '<post_id>'.$this->get['post_id'].'</post_id>';
		$render .= '<content><![CDATA[';
		$post = $this->get_post_by_id($this->get['post_id']);
		$render .= $this->render_like($post, 1);
		$render .= ']]></content>';
		return $render;
	}
	function action_linkpreview() {
		$render = '';
		if(isset($_POST['link_inputtext']) == 1 && $_POST['link_inputtext'] != '' && $_POST['link_inputtext'] != 'http://') {
			$link = $this->analyze_link($_POST['link_inputtext']);
			$link->link_id = 0;
			$render .= '<content><![CDATA[';
			$render .= '<div class="linklist">';
			$render .= $this->render_link($link);
			$render .= '</div>';
			$render .= ']]></content>';
		}
		return $render;
	}
	function action_addresspreview() {
		$render = '';
		if(isset($_POST['address_inputtext']) == 1 && $_POST['address_inputtext'] != '') {
			$address = new stdClass();
			$address->address_id = 0;
			$address->address_title = strip_tags($_POST['address_inputtext']);
			$render .= '<content><![CDATA[';
			$render .= '<div class="addresslist">';
			$render .= $this->render_address($address);
			$render .= '</div>';
			$render .= ']]></content>';
		}
		return $render;
	}
	function action_photozoom() {
		$render = '';
		$render = '';
		$photo = $this->get_photo_by_id($this->get['photo_id']);
		if($photo) {
			$render .= '<content><![CDATA[';
			$render .= '<a class="popin_hide" href="#"><img src="storage/'.$photo->photo_file.'"></a>';
			$render .= ']]></content>';
		}
		return $render;
	}
	function action_refreshdatecreated() {
		$render = '';
		$date_day_utc = date('Y-m-d');
		$flt = array();
		$parameters = array();
		$flt[] = '1';
		if(isset($_SESSION['wall369']['post_id_oldest']) == 1 && $_SESSION['wall369']['post_id_oldest'] != 0) {
			$flt[] = 'post.post_id >= :post_id_oldest';
			$parameters[':post_id_oldest'] = $_SESSION['wall369']['post_id_oldest'];
		}
		$flt[] = 'post.post_datecreated LIKE :today_limit';
		$parameters[':today_limit'] = $date_day_utc.'%';
		$query = 'SELECT post.post_id, DATE_ADD(post.post_datecreated, INTERVAL '.$_SESSION['wall369']['timezone'].' HOUR) AS post_datecreated FROM '.TABLE_POST.' post WHERE '.implode(' AND ', $flt).' GROUP BY post.post_id ORDER BY post.post_id';
		$prepare = $this->pdo_execute($query, $parameters);
		if($prepare) {
			$rowCount = $prepare->rowCount();
			if($rowCount > 0) {
				$render .= '<posts>';
				while($post = $prepare->fetch(PDO::FETCH_OBJ)) {
					$render .= '<post post_id="'.$post->post_id.'"><![CDATA['.$this->render_datecreated($post->post_datecreated).']]></post>';
				}
				$render .= '</posts>';
			}
		} else {
			$this->pdo_error($prepare);
		}
		$flt = array();
		$parameters = array();
		$flt[] = '1';
		if(isset($_SESSION['wall369']['comment_id_oldest']) == 1 && $_SESSION['wall369']['comment_id_oldest'] != 0) {
			$flt[] = 'comment.comment_id >= :comment_id_oldest';
			$parameters[':comment_id_oldest'] = $_SESSION['wall369']['comment_id_oldest'];
		}
		$flt[] = 'comment.comment_datecreated LIKE :today_limit';
		$parameters[':today_limit'] = $date_day_utc.'%';
		$query = 'SELECT comment.comment_id, DATE_ADD(comment.comment_datecreated, INTERVAL '.$_SESSION['wall369']['timezone'].' HOUR) AS comment_datecreated FROM '.TABLE_COMMENT.' comment WHERE '.implode(' AND ', $flt).' GROUP BY comment.comment_id ORDER BY comment.comment_id';
		$prepare = $this->pdo_execute($query, $parameters);
		if($prepare) {
			$rowCount = $prepare->rowCount();
			if($rowCount > 0) {
				$render .= '<comments>';
				while($comment = $prepare->fetch(PDO::FETCH_OBJ)) {
					$render .= '<comment comment_id="'.$comment->comment_id.'"><![CDATA['.$this->render_datecreated($comment->comment_datecreated).']]></comment>';
				}
				$render .= '</comments>';
			}
		} else {
			$this->pdo_error($prepare);
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
		$parameters[':user_id'] = $this->user->user_id;
		$query = $this->post_query.' WHERE '.implode(' AND ', $flt).' GROUP BY post.post_id ORDER BY post.post_id ASC';
		$prepare = $this->pdo_execute($query, $parameters);
		if($prepare) {
			$rowCount = $prepare->rowCount();
			if($rowCount > 0) {
				$render .= '<posts>';
				$u = 0;
				while($post = $prepare->fetch(PDO::FETCH_OBJ)) {
					if($u == 0 && ($_SESSION['wall369']['post_id_oldest'] > $post->post_id || $_SESSION['wall369']['post_id_oldest'] == 0)) {
						$_SESSION['wall369']['post_id_oldest'] = $post->post_id;
					}
					$render .= '<post post_id="'.$post->post_id.'"><![CDATA['.$this->render_post($post).']]></post>';
					$_SESSION['wall369']['post_id_newest'] = $post->post_id;
					$u++;
				}
				$render .= '</posts>';
			}
		} else {
			$this->pdo_error($prepare);
		}
		$query = 'SELECT comment.*, user.*, DATE_ADD(comment.comment_datecreated, INTERVAL '.$_SESSION['wall369']['timezone'].' HOUR) AS comment_datecreated FROM '.TABLE_COMMENT.' comment LEFT JOIN '.TABLE_USER.' user ON user.user_id = comment.user_id WHERE comment.comment_id > :comment_id_newest AND comment.post_id >= :post_id_oldest GROUP BY comment.comment_id';
		$prepare = $this->pdo_execute($query, array(':comment_id_newest'=>$_SESSION['wall369']['comment_id_newest'], ':post_id_oldest'=>$_SESSION['wall369']['post_id_oldest']));
		if($prepare) {
			$rowCount = $prepare->rowCount();
			if($rowCount > 0) {
				$render .= '<comments>';
				$u = 0;
				while($comment = $prepare->fetch(PDO::FETCH_OBJ)) {
					if($u == 0 && ($_SESSION['wall369']['comment_id_oldest'] > $comment->comment_id || $_SESSION['wall369']['comment_id_oldest'] == 0)) {
						$_SESSION['wall369']['comment_id_oldest'] = $comment->comment_id;
					}
					$render .= '<comment post_id="'.$comment->post_id.'" comment_id="'.$comment->comment_id.'"><![CDATA['.$this->render_comment($comment).']]></comment>';
					if($_SESSION['wall369']['comment_id_newest'] < $comment->comment_id || $_SESSION['wall369']['comment_id_newest'] == 0) {
						$_SESSION['wall369']['comment_id_newest'] = $comment->comment_id;
					}
					$u++;
				}
				$render .= '</comments>';
			}
		}
		return $render;
	}
	function get_user_by_id($user_id) {
		$query = 'SELECT user.* FROM '.TABLE_USER.' user WHERE user.user_id = :user_id GROUP BY user.user_id';
		$prepare = $this->pdo_execute($query, array(':user_id'=>$user_id));
		if($prepare) {
			$rowCount = $prepare->rowCount();
			if($rowCount > 0) {
				return $prepare->fetch(PDO::FETCH_OBJ);
			}
		}
	}
	function get_user_by_email($user_email) {
		$query = 'SELECT user.* FROM '.TABLE_USER.' user WHERE user.user_email = :user_email GROUP BY user.user_id';
		$prepare = $this->pdo_execute($query, array(':user_email'=>filter_var($user_email, FILTER_VALIDATE_EMAIL)));
		if($prepare) {
			$rowCount = $prepare->rowCount();
			if($rowCount > 0) {
				return $prepare->fetch(PDO::FETCH_OBJ);
			}
		}
	}
	function get_user_by_token($user_token) {
		$query = 'SELECT user.* FROM '.TABLE_USER.' user WHERE user.user_token = :user_token GROUP BY user.user_id';
		$prepare = $this->pdo_execute($query, array(':user_token'=>$user_token));
		if($prepare) {
			$rowCount = $prepare->rowCount();
			if($rowCount > 0) {
				return $prepare->fetch(PDO::FETCH_OBJ);
			}
		}
	}
	function get_post_by_id($post_id) {
		$query = $this->post_query.' WHERE post.post_id = :post_id GROUP BY post.post_id';
		$prepare = $this->pdo_execute($query, array(':post_id'=>$post_id, ':user_id'=>$this->user->user_id));
		if($prepare) {
			$rowCount = $prepare->rowCount();
			if($rowCount > 0) {
				return $prepare->fetch(PDO::FETCH_OBJ);
			}
		}
	}
	function get_comment_by_id($comment_id) {
		$query = 'SELECT comment.*, user.*, DATE_ADD(comment.comment_datecreated, INTERVAL '.$_SESSION['wall369']['timezone'].' HOUR) AS comment_datecreated FROM '.TABLE_COMMENT.' comment LEFT JOIN '.TABLE_USER.' user ON user.user_id = comment.user_id WHERE comment.comment_id = :comment_id GROUP BY comment.comment_id';
		$prepare = $this->pdo_execute($query, array(':comment_id'=> $comment_id));
		if($prepare) {
			$rowCount = $prepare->rowCount();
			if($rowCount > 0) {
				return $prepare->fetch(PDO::FETCH_OBJ);
			}
		}
	}
	function get_photo_by_id($photo_id) {
		$query = 'SELECT photo.*, DATE_ADD(photo.photo_datecreated, INTERVAL '.$_SESSION['wall369']['timezone'].' HOUR) AS photo_datecreated FROM '.TABLE_PHOTO.' photo WHERE photo.photo_id = :photo_id GROUP BY photo.photo_id';
		$prepare = $this->pdo_execute($query, array(':photo_id'=> $photo_id));
		if($prepare) {
			$rowCount = $prepare->rowCount();
			if($rowCount > 0) {
				return $prepare->fetch(PDO::FETCH_OBJ);
			}
		}
	}
	function insert_photo($post_id, $data) {
		$query = 'INSERT INTO '.TABLE_PHOTO.' (post_id, photo_file, photo_datecreated) VALUES (:post_id, :photo_file, :photo_datecreated)';
		$prepare = $this->pdo_execute($query, array(':post_id'=>$post_id, ':photo_file'=>$data['photo_inputfile'], ':photo_datecreated'=>date('Y-m-d H:i:s')));
	}
	function insert_link($post_id, $data) {
		$query = 'INSERT INTO '.TABLE_LINK.' (post_id, link_url, link_title, link_image, link_video, link_videotype, link_videowidth, link_videoheight, link_icon, link_description, link_datecreated) VALUES (:post_id, :link_url, :link_title, NULLIF(:link_image, \'\'), NULLIF(:link_video, \'\'), NULLIF(:link_videotype, \'\'), NULLIF(:link_videowidth, \'\'), NULLIF(:link_videoheight, \'\'), NULLIF(:link_icon, \'\'), NULLIF(:link_description, \'\'), :link_datecreated)';
		$prepare = $this->pdo_execute($query, array(':post_id'=>$post_id, ':link_url'=>$data->link_url, ':link_title'=>$data->link_title, ':link_image'=>$data->link_image, ':link_video'=>$data->link_video, ':link_videotype'=>$data->link_videotype, ':link_videowidth'=>$data->link_videowidth, ':link_videoheight'=>$data->link_videoheight, ':link_icon'=>$data->link_icon, ':link_description'=>$data->link_description, ':link_datecreated'=>date('Y-m-d H:i:s')));
	}
	function insert_address($post_id, $data) {
		$query = 'INSERT INTO '.TABLE_ADDRESS.' (post_id, address_title, address_datecreated) VALUES (:post_id, :address_title, :address_datecreated)';
		$prepare = $this->pdo_execute($query, array(':post_id'=>$post_id, ':address_title'=>$data['address_title'], ':address_datecreated'=>date('Y-m-d H:i:s')));
	}
	function render_loginform() {
		$render = '';
		$render .= '<form action="?a=login" enctype="application/x-www-form-urlencoded" method="post">';
		$render .= '<p class="form_email"><label for="email">'.$this->str[$this->language]['email'].'</label><input class="inputtext" id="email" name="email" type="text" value=""></p>';
		$render .= '<p class="form_password"><label for="password">'.$this->str[$this->language]['password'].'</label><input class="inputpassword" id="password" name="password" type="password" value=""></p>';
		$render .= '<p class="submit_btn"><input class="inputsubmit" type="submit" value="'.$this->str[$this->language]['login'].'"></p>';
		$render .= '</form>';
		return $render;
	}
	function render_postform() {
		$render = '';
		if(DEMO == 0) {
			$render .= '<p id="postform_detail"><a class="logout_action" href="?a=logout">'.$this->str[$this->language]['logout'].'</a></p>';
		}
		$render .= '<form action="?a=post" enctype="multipart/form-data" method="post">';
		$render .= '<p class="form_status"><textarea class="textarea" id="status_textarea" name="status_textarea"></textarea></p>';
		$render .= '<p class="form_link"><input class="inputtext" id="link_inputtext" type="text" value="http://"><a href="?a=linkpreview"><img src="medias/icon_preview.png" alt="" width="16" height="16"></a></p>';
		$render .= '<p class="form_address"><input class="inputtext" id="address_inputtext" type="text" value=""><a href="?a=addresspreview"><img src="medias/icon_preview.png" alt="" width="16" height="16"></a></p>';
		$render .= '<p class="form_photo"><input class="inputfile" id="photo_inputfile" name="photo_inputfile" type="file"></p>';
		$render .= '<p class="submit_btn"><input class="inputsubmit" type="submit" value="'.$this->str[$this->language]['share'].'"></p>';
		$render .= '</form>';
		$render .= '<div class="postform_preview" id="postform_link_preview"></div>';
		$render .= '<div class="postform_preview" id="postform_address_preview"></div>';
		$render .= '<div class="postform_preview" id="postform_photo_preview"></div>';
		return $render;
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
		$parameters[':user_id'] = $this->user->user_id;
		$query = $this->post_query.' WHERE '.implode(' AND ', $flt).' GROUP BY post.post_id ORDER BY post.post_id DESC LIMIT 0,'.LIMIT_POSTS;
		$prepare = $this->pdo_execute($query, $parameters);
		if($prepare) {
			$rowCount = $prepare->rowCount();
			if($rowCount > 0) {
				$render .= '<posts>';
				$u = 0;
				while($post = $prepare->fetch(PDO::FETCH_OBJ)) {
					if($u == 0 && ($_SESSION['wall369']['post_id_newest'] < $post->post_id || $_SESSION['wall369']['post_id_newest'] == 0)) {
						$_SESSION['wall369']['post_id_newest'] = $post->post_id;
					}
					$render .= '<post post_id="'.$post->post_id.'"><![CDATA['.$this->render_post($post).']]></post>';
					$_SESSION['wall369']['post_id_oldest'] = $post->post_id;
					$u++;
				}
				$render .= '</posts>';
				$flt = array();
				$parameters = array();
				$flt[] = '1';
				$flt[] = 'post.post_id < :post_id_oldest';
				$parameters[':post_id_oldest'] = $_SESSION['wall369']['post_id_oldest'];
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
			}
		} else {
			$this->pdo_error($prepare);
		}
		return $render;
	}
	function render_post($post) {
		$render = '';
		$render .= '<div class="post" id="post_'.$post->post_id.'">';
		$render .= '<div class="post_display">';
		$render .= '<div class="post_thumb">';
		if($post->user_file != '') {
			$render .= '<img alt="" src="data:image/jpeg;base64,'.$post->user_file.'">';
		} else if(GRAVATAR == 1) {
			$render .= '<img alt="" src="http://www.gravatar.com/avatar/'.md5(strtolower($post->user_email)).'?rating='.GRAVATAR_RATING.'&size=50&default='.GRAVATAR_DEFAULT.'">';
		} else {
			$render .= '<img alt="" src="medias/avatar.png">';
		}
		$render .= '</div>';
		$render .= '<div class="post_text">';
		if($post->user_id == $this->user->user_id) {
			$render .= '<a class="delete_action post_delete_action" href="?a=postdelete&amp;post_id='.$post->post_id.'"></a>';
			$username = $this->str[$this->language]['you'];
		} else {
			$username = $post->user_firstname.' '.$post->user_lastname;
		}
		$render .= '<p><span class="username">'.$username.'</span>  · <span class="datecreated" id="post_datecreated_'.$post->post_id.'">'.$this->render_datecreated($post->post_datecreated).'</span></p>';
		$render .= '<p>'.$this->render_content($post->post_content).'</p>';
		$render .= '</div>';
		$render .= $this->render_linklist($post);
		$render .= $this->render_addresslist($post);
		$render .= $this->render_photolist($post);
		$render .= '<p class="post_detail">';
		if($post->you_like == 1) {
			$render .= '<span class="like like_inactive">';
		} else {
			$render .= '<span class="like">';
		}
		$render .= '<a class="post_like_action" href="?a=postlike&amp;post_id='.$post->post_id.'">'.$this->str[$this->language]['like'].'</a> ·</span> ';
		if($post->you_like == 1) {
			$render .= '<span class="unlike">';
		} else {
			$render .= '<span class="unlike unlike_inactive">';
		}
		$render .= '<a class="post_unlike_action" href="?a=postunlike&amp;post_id='.$post->post_id.'">'.$this->str[$this->language]['unlike'].'</a> ·</span> ';
		$render .= '<a class="comment_action" href="#commentform_'.$post->post_id.'">'.$this->str[$this->language]['comment'].'</a>';
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
			$render .= '<img alt="" src="data:image/jpeg;base64,'.$this->user->user_file.'">';
		} else if(GRAVATAR == 1) {
			$render .= '<img alt="" src="http://www.gravatar.com/avatar/'.md5(strtolower($this->user->user_email)).'?rating='.GRAVATAR_RATING.'&size=30&default='.GRAVATAR_DEFAULT.'">';
		} else {
			$render .= '<img alt="" src="medias/avatar.png">';
		}
		$render .= '</div>';
		$render .= '<div class="comment_text">';
		$render .= '<form action="?a=comment&amp;post_id='.$post->post_id.'" method="post">';
		$render .= '<p><textarea class="textarea" name="comment"></textarea></p>';
		$render .= '<p class="submit_btn"><input class="inputsubmit" type="submit" value="'.$this->str[$this->language]['comment'].'"></p>';
		$render .= '</form>';
		$render .= '</div>';
		$render .= '</div>';
		$render .= '</div>';
		$render .= '</div>';
		$render .= '</div>';
		$render .= '</div>';
		return $render;
	}
	function render_commentlist($post, $all) {
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
					$render .= '<p><a class="commentall_action" href="?a=commentlist&amp;post_id='.$post->post_id.'">'.sprintf($this->str[$this->language]['view_all_comments'], $post->count_comment).'</a></p>';
					$render .= '</div>';
					$render .= '</div>';
					$min = $post->count_comment - LIMIT_COMMENTS;
					$limit = ' LIMIT '.$min.', '.LIMIT_COMMENTS;
				}
			}
			$query = 'SELECT comment.*, user.*, DATE_ADD(comment.comment_datecreated, INTERVAL '.$_SESSION['wall369']['timezone'].' HOUR) AS comment_datecreated FROM '.TABLE_COMMENT.' comment LEFT JOIN '.TABLE_USER.' user ON user.user_id = comment.user_id WHERE comment.post_id = :post_id GROUP BY comment.comment_id'.$limit;
			$prepare = $this->pdo_execute($query, array(':post_id'=>$post->post_id));
			if($prepare) {
				$rowCount = $prepare->rowCount();
				if($rowCount > 0) {
					$u = 0;
					while($comment = $prepare->fetch(PDO::FETCH_OBJ)) {
						if($u == 0 && ($_SESSION['wall369']['comment_id_oldest'] > $comment->comment_id || $_SESSION['wall369']['comment_id_oldest'] == 0)) {
							$_SESSION['wall369']['comment_id_oldest'] = $comment->comment_id;
						}
						$render .= $this->render_comment($comment);
						if($_SESSION['wall369']['comment_id_newest'] < $comment->comment_id || $_SESSION['wall369']['comment_id_newest'] == 0) {
							$_SESSION['wall369']['comment_id_newest'] = $comment->comment_id;
						}
						$u++;
					}
				}
			}
		}
		return $render;
	}
	function render_comment($comment) {
		$render = '';
		$render .= '<div class="comment" id="comment_'.$comment->comment_id.'">';
		$render .= '<div class="comment_display">';
		$render .= '<div class="comment_thumb">';
		if($comment->user_file != '') {
			$render .= '<img alt="" src="data:image/jpeg;base64,'.$comment->user_file.'">';
		} else if(GRAVATAR == 1) {
			$render .= '<img alt="" src="http://www.gravatar.com/avatar/'.md5(strtolower($comment->user_email)).'?rating='.GRAVATAR_RATING.'&size=30&default='.GRAVATAR_DEFAULT.'">';
		} else {
			$render .= '<img alt="" src="medias/avatar.png">';
		}
		$render .= '</div>';
		$render .= '<div class="comment_text">';
		if($comment->user_id == $this->user->user_id) {
			$render .= '<a class="delete_action comment_delete_action" href="?a=commentdelete&amp;comment_id='.$comment->comment_id.'"></a>';
			$username = $this->str[$this->language]['you'];
		} else {
			$username = $comment->user_firstname.' '.$comment->user_lastname;
		}
		$render .= '<p><span class="username">'.$username.'</span> · <span class="datecreated" id="comment_datecreated_'.$comment->comment_id.'">'.$this->render_datecreated($comment->comment_datecreated).'</span></p>';
		$render .= '<p>'.$this->render_content($comment->comment_content).'</p>';
		$render .= '</div>';
		$render .= '</div>';
		$render .= '</div>';
		return $render;
	}
	function render_like($post, $all) {
		$render = '';
		if($post->post_countlike != 0) {
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
			$query = 'SELECT l.*, user.*, DATE_ADD(l.like_datecreated, INTERVAL '.$_SESSION['wall369']['timezone'].' HOUR) AS like_datecreated, IF(l.user_id = :user_id OR post.user_id = l.user_id, 1, 0) AS ordering FROM '.TABLE_LIKE.' l LEFT JOIN '.TABLE_USER.' user ON user.user_id = l.user_id LEFT JOIN '.TABLE_POST.' post ON post.post_id = l.post_id WHERE l.post_id = :post_id GROUP BY l.like_id ORDER BY ordering ASC, l.like_id ASC'.$limit;
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
							$username = $this->str[$this->language]['you'];
						} else {
							$username = $like->user_firstname.' '.$like->user_lastname;
						}
						$render .= '<span class="username" title="'.$this->date_transform($like->like_datecreated).'">'.$username.'</span>';
						if($post->post_countlike != 1) {
							if($u == $rowCount && $rowCount < $post->post_countlike) {
								$diff = $post->post_countlike - $rowCount;
								$render .=  ' '.$this->str[$this->language]['and'].' <a class="likelist_action" href="?a=likelist&amp;post_id='.$post->post_id.'">'.sprintf($this->str[$this->language]['others'], $diff).'</a> ';
							} else if($u == $rowCount - 1 && $rowCount == $post->post_countlike) {
								$render .=  ' '.$this->str[$this->language]['and'].' ';
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
					if(isset($this->str[$this->language][$k]) == 1) {
						$render .= ' '.$this->str[$this->language][$k].'.';
					}
					$render .= '</p>';
					$render .= '</div>';
					$render .= '</div>';
				}
			}
		}
		return $render;
	}
	function render_photolist($post) {
		$render = '';
		if($post->post_countphoto > 0) {
			$query = 'SELECT photo.* FROM '.TABLE_PHOTO.' photo WHERE photo.post_id = :post_id GROUP BY photo.photo_id';
			$prepare = $this->pdo_execute($query, array(':post_id'=>$post->post_id));
			if($prepare) {
				$rowCount = $prepare->rowCount();
				if($rowCount > 0) {
					$render .= '<div class="photolist">';
					$render .= '<div class="photolist_display">';
					while($photo = $prepare->fetch(PDO::FETCH_OBJ)) {
						$render .= $this->render_photo($photo);
					}
					$render .= '</div>';
					$render .= '</div>';
				}
			}
		}
		return $render;
	}
	function render_photo($photo) {
		$render = '';
		$render .= '<div class="photo" id="photo_'.$photo->photo_id.'">';
		$render .= '<div class="photo_display">';
		$render .= '<a href="?a=photozoom&amp;photo_id='.$photo->photo_id.'"><img alt="" src="storage/'.$photo->photo_file.'"></a>';
		$render .= '</div>';
		$render .= '</div>';
		return $render;
	}
	function render_linklist($post) {
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
	function render_link($link) {
		$render = '';
		$url = parse_url($link->link_url);
		$render .= '<div class="link" id="link_'.$link->link_id.'">';
		$render .= '<div class="link_display">';
		if($link->link_image != '') {
			$render .= '<div class="link_thumb">';
			$full = '';
			$render .= '<a target="_blank" href="'.$link->link_url.'"><img alt="" src="'.$link->link_image.'"></a>';
			$render .= '</div>';
		} else {
			$full = ' link_text_full';
		}
		$render .= '<div class="link_text'.$full.'">';
		$render .= '<p><a target="_blank" href="'.$link->link_url.'">'.$link->link_title.'</a><br>';
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
			$render .= '<p class="playvideo_link"><a href="#playvideo'.$link->link_id.'"><img src="medias/play_video.png" alt=""></a></p>';
			$render .= '<iframe class="playvideo" id="playvideo'.$link->link_id.'" width="'.$link->link_videowidth.'" height="'.$link->link_videoheight.'" src="'.$link->link_video.'" frameborder="0"></iframe>';
		}
		$render .= '</div>';
		$render .= '</div>';
		return $render;
	}
	function render_addresslist($post) {
		$render = '';
		if($post->post_countaddress > 0) {
			$query = 'SELECT address.* FROM '.TABLE_ADDRESS.' address WHERE address.post_id = :post_id GROUP BY address.address_id';
			$prepare = $this->pdo_execute($query, array(':post_id'=>$post->post_id));
			if($prepare) {
				$rowCount = $prepare->rowCount();
				if($rowCount > 0) {
					$render .= '<div class="addresslist">';
					while($address = $prepare->fetch(PDO::FETCH_OBJ)) {
						$render .= $this->render_address($address);
					}
					$render .= '</div>';
				}
			}
		}
		return $render;
	}
	function render_address($address) {
		$render = '';
		$render .= '<div class="address" id="address_'.$address->address_id.'">';
		$render .= '<div class="address_display">';
		$render .= '<p>'.$address->address_title.'</p>';
		$render .= '<p><a href="http://maps.google.com/maps?q='.urlencode($address->address_title).'&oe=UTF-8&ie=UTF-8" target="_blank"><img src="http://maps.googleapis.com/maps/api/staticmap?center='.urlencode($address->address_title).'&markers=color:red|'.urlencode($address->address_title).'&zoom=15&size=540x200&sensor=false" alt=""></a></p>';
		$render .=' </div>';
		$render .= '</div>';
		return $render;
	}
	function render_content($text) {
		preg_match_all('(((ftp|http|https){1}://)[-a-zA-Z0-9@:%_\+.~#!\(\)?&//=]+)', $text, $matches);
		$matches = $matches[0];
		if(count($matches) != 0) {
			$matches = array_unique($matches);
			foreach($matches as $match) {
				$text = str_replace($match, '<a href="'.$match.'" target="_blank">'.$match.'</a>', $text);
			}
		}
		preg_match_all("/[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}/i", $text, $matches);
		$matches = $matches[0];
		if(count($matches) != 0) {
			$matches = array_unique($matches);
			foreach($matches as $match) {
				$text = str_replace($match, '<a href="mailto:'.$match.'">'.$match.'</a>', $text);
			}
		}
		return nl2br($text);
	}
	function render_datecreated($date) {
		return '<span title="'.$this->date_transform($date).'">'.$this->date_mention($date).'</span>';
	}
	function date_mention($date) {
		$mention = '';
		if($date != '') {
			list($datecreated, $timecreated) = explode(' ', $date);
			if(function_exists('date_create') && function_exists('date_diff')) {
				$prev = date_create($datecreated);
				$next = date_create($this->date_day);
				$interval = date_diff($prev, $next);
				$diff = $interval->format('%a');
			} else {
				$prev = strtotime($datecreated);
				$next = strtotime($this->date_day);
				$diff = ($next - $prev) / 3600 / 24;
			}
			if($diff == 0) {
				list($prev_h, $prev_m, $prev_s) = explode(':', $timecreated);
				list($next_h, $next_m, $prev_s) = explode(':', $this->date_time);
				$diff = ($next_h * 60 + $next_m) - ($prev_h * 60 + $prev_m);
				if($diff <= 1) {
					$mention = $this->str[$this->language]['now'];
				} else if($diff >= 120) {
					$mention = sprintf($this->str[$this->language]['hours_diff'], ceil($diff/60));
				} else {
					$mention = sprintf($this->str[$this->language]['minutes_diff'], $diff);
				}
			} else if($diff == 1) {
				$mention = $this->str[$this->language]['yesterday'].' '.$this->str[$this->language]['at'].' '.substr($timecreated, 0, 5);
			} else if($diff >= 730) {
				$mention = sprintf($this->str[$this->language]['years_diff'], ceil($diff/365));
			} else if($diff >= 60) {
				$mention = sprintf($this->str[$this->language]['months_diff'], ceil($diff/30));
			} else if($diff >= 14) {
				$mention = sprintf($this->str[$this->language]['weeks_diff'], ceil($diff/7));
			} else {
				$mention = sprintf($this->str[$this->language]['days_diff'], $diff);
			}
		}
		return $mention;
	}
	function date_transform($date) {
		if($date != '') {
			$format =  $this->str[$this->language]['date_format'];
			if(function_exists('date_create') && function_exists('date_format')) {
				$date = date_create($date);
				$date = date_format($date, $format);
			} else {
				$date = date($format, strtotime($date));
			}
			$formats = array('l', 'D', 'jS', 'F', 'M');
			foreach($formats as $k) {
				if(strstr($format, $k) && isset($this->str[$this->language]['date_'.$k]) == 1) {
					$ref = $this->str[$this->language]['date_'.$k];
					if($k == 'jS') {
						$ref = array_reverse($ref, 1);
					}
					$date = str_replace(array_keys($ref), array_values($ref), $date);
				}
			}
		}
		return $date;
	}
	function photo_add() {
		$newfile = '';
		if(isset($_FILES['photo_inputfile']) == 1 && $_FILES['photo_inputfile']['error'] == 0) {
			$folder = 'storage';
			if(is_dir($folder)) {
				$year = date('Y');
				if(!is_dir($folder.'/'.$year)) {
					mkdir($folder.'/'.$year);
					copy($folder.'/index.php', $folder.'/'.$year.'/index.php');
				}
				$newfile = $year.'/'.$this->string_generate(14, 1, 1, 0).'-'.$this->string_clean($_FILES['photo_inputfile']['name']);
				move_uploaded_file($_FILES['photo_inputfile']['tmp_name'], $folder.'/'.$newfile);
				if($_FILES['photo_inputfile']['type'] == 'image/jpeg') {
					$filename = $folder.'/'.$newfile;
					$width = 600;
					$height = 600;
					list($width_orig, $height_orig) = getimagesize($filename);
					$ratio_orig = $width_orig / $height_orig;
					if ($width/$height > $ratio_orig) {
						$width = $height * $ratio_orig;
					} else {
						$height = $width / $ratio_orig;
					}
					$image = imagecreatetruecolor($width, $height);
					$image_orig = imagecreatefromjpeg($filename);
					imagecopyresampled($image, $image_orig, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
					imagejpeg($image, $filename, 75);
				}
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
		$accepted = array();
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
	function string_generate($size = 8, $with_numbers = 1, $with_tiny_letters = 1, $with_capital_letters = 0) { 
		$string = '';
		$sizeof_lchar = 0;
		$letter = '';
		if($with_numbers == 1) {
			$sizeof_lchar += 10;
			$letter .= '0123456789';
		}
		if($with_tiny_letters == 1) {
			$sizeof_lchar += 26;
			$letter .= 'abcdefghijklmnopqrstuvwxyz';
		}
		if($with_capital_letters == 1) {
			$sizeof_lchar += 26;
			$letter .= 'ABCDEFGHIJKLMNOPRQSTUVWXYZ';
		}
		if($sizeof_lchar > 0) {
			for($i=0;$i<$size;$i++) {
				$char_select = rand(0, $sizeof_lchar - 1);
				$string .= $letter[$char_select];
			}
		}
		return $string;
	}
	function analyze_link($link) {
		$data = new stdClass();
		$default = array('link_url'=>$link, 'link_title'=>'', 'link_image'=>'', 'link_video'=>'', 'link_videotype'=>'', 'link_videowidth'=>'', 'link_videoheight'=>'', 'link_icon'=>'', 'link_description'=>'');
		foreach($default as $k => $v) {
			$data->{$k} = $v;
		}
		if(isset($_SESSION['wall369'][$link]) == 1) {
			return unserialize($_SESSION['wall369'][$link]);
		} else {
			$headers = get_headers($link, 1);
			if(isset($headers['Location']) == 1) {
				if(is_array($headers['Location'])) {
					$link = $headers['Location'][0];
				} else {
					$link = $headers['Location'];
				}
				$data->link_url = $link;
				$origin_status = $headers[0];
				$headers = get_headers($link, 1);
				$headers[0] = $headers[0].' ('.$origin_status.')';
				if(isset($headers['Content-Type']) == 1 && is_array($headers['Content-Type'])) {
					$headers['Content-Type'] = $headers['Content-Type'][0];
				}
			}
			$headers = array_unique($headers);
			$keys = array();
			foreach($headers as $k => $v) {
				$keys['headers-'.strtolower($k)] = $v;
			}
			$opts = array('http'=>array('header'=>'User-Agent: '.$_SERVER['HTTP_USER_AGENT']."\r\n"));
			$context = stream_context_create($opts);
			$content = file_get_contents($link, false, $context);
			$content = str_replace("\t", '', $content);
			$content_flat = str_replace("\r\n", '', $content);
			$content_flat = str_replace("\n", '', $content_flat);
			$pattern_one = array();
			$pattern_one['title'] = "|<[tT][iI][tT][lL][eE](.*)>(.*)<\/[tT][iI][tT][lL][eE]>|U";
			$pattern_one['charsetclient'] = "|<[mM][eE][tT][aA](.*)[cC][hH][aA][rR][sS][eE][tT]=[\"'](.*)[\"'](.*)>|U";
			foreach($pattern_one as $k => $pattern) {
				$matches = array();
				preg_match_all($pattern, $content_flat, $matches, PREG_SET_ORDER);
				foreach($matches as $match) {
					$keys[$k] = trim($match[2]);
				}
			}
			$pattern_multi = array();
			$pattern_multi["|<[lL][iI][nN][kK](.*)[hH][rR][eE][fF]=[\"'](.*)[\"'](.*)>|U"] = array("|(.*)[rR][eE][lL]=[\"'](.*)[\"'](.*)|U", "|(.*)[rR][eE][fF]=[\"'](.*)[\"'](.*)|U");
			$pattern_multi["|<[mM][eE][tT][aA](.*)[cC][oO][nN][tT][eE][nN][tT]=\"(.*)\"(.*)>|U"] = array("|(.*)[nN][aA][mM][eE]=[\"'](.*)[\"'](.*)|U", "|(.*)[pP][rR][oO][pP][eE][rR][tT][yY]=[\"'](.*)[\"'](.*)|U", "|(.*)[hH][tT][tT][pP]-[eE][qQ][uU][iI][vV]=[\"'](.*)[\"'](.*)|U");
			foreach($pattern_multi as $pattern => $pattern_sub) {
				$matches = array();
				preg_match_all($pattern, $content_flat, $matches, PREG_SET_ORDER);
				foreach($matches as $match) {
					$value = $match[2];
					foreach($pattern_sub as $pattern) {
						$matches_sub = array();
						preg_match_all($pattern, $match[1], $matches_sub, PREG_SET_ORDER);
						foreach($matches_sub as $match_sub) {
							$keys[strtolower($match_sub[2])] = $value;
						}
						$matches_sub = array();
						preg_match_all($pattern, $match[3], $matches_sub, PREG_SET_ORDER);
						foreach($matches_sub as $match_sub) {
							$keys[strtolower($match_sub[2])] = $value;
						}
					}
				}
			}
			foreach($keys as $key => $value) {
				if(!is_array($value)) {
					if($key == 'image_src') {
						$data->link_image = $value;
					} else if($key == 'shortcut icon') {
						$data->link_icon = $value;
					} else if($key == 'headers-content-type' && stristr($value, 'charset')) {
						$data->link_charsetserver = substr($value, strpos($value, '=') + 1);
					} else if($key == 'content-type' && stristr($value, 'charset')) {
						$data->link_charsetclient = substr($value, strpos($value, '=') + 1);
					} else if(substr($key, 0, 3) == 'og:' || substr($key, 0, 3) == 'fb:') {
						$key = substr($key, 3);
						$key = str_replace(':', '', $key);
						$key = str_replace('_', '', $key);
						$data->{'link_'.$key} = $value;
					} else {
						$data->{'link_'.$key} = $value;
					}
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
			$_SESSION['wall369'][$link] = serialize($data);
			return $data;
		}
	}
}
?>
