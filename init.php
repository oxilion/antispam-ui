<?php
function autoload_class($className) {
	$file = 'classes/' . strtolower($className) . ".class.php";
	if (is_file($file)) {
		include $file;
		return true;
	} else {
		return false;
	}
}
spl_autoload_register('autoload_class');

// Don't feel free to edit
if (!LIVE) {
	error_reporting(E_ALL);
	ini_set('display_errors', 'on');
}

ini_set('soap.wsdl_cache_enabled', 0);

session_start();

if (USER == 0) {
	die('Please read the manual and edit the config.php');
}

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME) or die($db->error);
$as = new antispam($wsdl, USER, PASS);
