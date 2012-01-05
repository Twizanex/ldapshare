<?php
class wall369 {
	function __construct() {
		set_error_handler(array($this, 'error_handler'));
		register_shutdown_function(array($this, 'shutdown_function'));
		if(file_exists('configuration.php')) {
			include_once('configuration.php');
		} else {
			include_once('configuration.dist.php');
		}
		$this->set_get('a', 'index', 'alphabetic');
		$this->set_get('post', '', 'numeric');
		$this->set_get('comment', '', 'numeric');

		$this->pdo = new PDO(DATABASE_TYPE.':dbname='.DATABASE_NAME.';host='.DATABASE_HOST.';port='.DATABASE_PORT, DATABASE_USER, DATABASE_PASSWORD);//, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'')
	}
	function error_handler($error_type, $error_message, $error_file, $error_line) {
		header('content-type: text/html; charset=UTF-8');
		$error_type_values = array(1=>'E_ERROR', 2=>'E_WARNING', 4=>'E_PARSE', 8=>'E_NOTICE', 16=>'E_CORE_ERROR', 32=>'E_CORE_WARNING', 64=>'E_COMPILE_ERROR', 128=>'E_COMPILE_WARNING', 256=>'E_USER_ERROR', 512=>'E_USER_WARNING', 1024=>'E_USER_NOTICE', 2048=>'E_STRICT', 4096=>'E_RECOVERABLE_ERROR', 8192=>'E_DEPRECATED', 16384=>'E_USER_DEPRECATED', 30719=>'E_ALL');
		if(isset($error_type_values[$error_type]) == 1) {
			echo $error_type_values[$error_type].' | '.$error_message.' | '.$error_file.' | '.$error_line;
		} else {
			echo $error_type.' | '.$error_message.' | '.$error_file.' | '.$error_line;
		}
		exit(0);
	}
	function shutdown_function() {
		if(function_exists('error_get_last')) {
			$error = error_get_last();
			if($error['type'] == 1) {
				header('content-type: text/html; charset=UTF-8');
				echo 'E_ERROR | '.$error['message'].' | '.$error['file'].' | '.$error['line'];
				exit(0);
			}
		}
	}
	function set_get($key, $default, $type) {
		$this->get[$key] = $default;
		if(isset($_GET[$key]) == 1 && $_GET[$key] != '') {
			$set_get = 0;
			if($type == 'alphabetic') {
				if(preg_match('/^[a-z]+$/i', $_GET[$key])) {
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
			if($this->get['a'] == 'test') {
				$sql = 'SELECT * FROM wall369_post ORDER BY post_id DESC';
				$stm = $this->pdo->prepare($sql);
				$result = false;
				if($stm && $stm->execute()) {
					$result = $stm->rowCount();
					if($result > 0) {
						$render .= '<posts>';
						while($r = $stm->fetch(PDO::FETCH_OBJ)) {
							$render .= '<post_id>'.$r->post_id.'</post_id>';
							$render .= '<user_id>'.$r->user_id.'</user_id>';
							$render .= '<post_content>'.$r->post_content.'</post_content>';
							$render .= '<post_datecreated>'.$r->post_datecreated.'</post_datecreated>';
						}
						$render .= '</posts>';
					}
				} 
			}
			if(method_exists($this, 'action_'.$this->get['a'])) {
				$render .= $this->{'action_'.$this->get['a']}();
			}
			$render .= '</wall369>'."\r\n";
		}
		return $render;
	}
	function action_timezone() {
		$this->set_get('t', 0, 'numeric');
		$_SESSION['wall369']['timezone'] = $this->get['t'];
		$render = '<timezone>'.$this->get['t'].'</timezone>';
		return $render;
	}
	function __destruct() {
	}
}
?>