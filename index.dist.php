<?php
error_reporting(E_ALL);
if(function_exists('ini_set')) {
	ini_set('display_errors', 'Off');
	ini_set('magic_quotes_gpc', 'Off');
	ini_set('memory_limit', '8M');
	ini_set('register_globals', 'Off');
	ini_set('session.gc_maxlifetime', 3600);
}
if(function_exists('date_default_timezone_set')) {
	date_default_timezone_set('Etc/UCT');
}
if(function_exists('mb_internal_encoding')) {
	mb_internal_encoding('UTF-8');
}
if(file_exists('configuration.php')) {
	include_once('configuration.php');
} else {
	include_once('configuration.dist.php');
}
if(file_exists('ldapshare.php')) {
	include_once('ldapshare.php');
} else {
	include_once('ldapshare.dist.php');
}
$ldapshare = new ldapshare();
$ldapshare->render();
unset($ldapshare);
exit(0);
?>
