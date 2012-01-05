<?php
error_reporting(E_ALL);
if(function_exists('ini_set')) {
	ini_set('arg_separator.output', '&amp;');
	ini_set('display_errors', 'Off');
	ini_set('magic_quotes_gpc', 'Off');
	ini_set('memory_limit', '16M');
	ini_set('register_globals', 'Off');
	ini_set('session.gc_maxlifetime', 3600);
}
if(function_exists('date_default_timezone_set')) {
	date_default_timezone_set('Etc/UCT');
}
if(function_exists('mb_internal_encoding')) {
	mb_internal_encoding('UTF-8');
}
if(isset($_SERVER['HTTPS']) == 1 && strtolower($_SERVER['HTTPS']) == 'on') {
	$secure = 1;
} else {
	$secure = 0;
}
session_set_cookie_params(0, '/', '', $secure, 1);
session_start();
if(file_exists('wall369.php')) {
	include_once('wall369.php');
} else {
	include_once('wall369.dist.php');
}
$wall369 = new wall369();
if(GZHANDLER == 1 && isset($_SERVER['HTTP_ACCEPT_ENCODING']) == 1 && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') && extension_loaded('zlib')) {
	ob_start('ob_gzhandler');
	echo $wall369->render();
	ob_end_flush();
} else {
	echo $wall369->render();
}
unset($wall369);
exit(0);
?>