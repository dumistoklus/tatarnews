<?php
include '../core/core.php';
DB_Provider::Instance()->loadProvider('Core');

$page = new Page('Administration Index');

$user = User::get();

if(isset($_GET['logout'])) {
	$user->logout();
}

if(!$user->isAuth() && isset($_POST['email']) && isset($_POST['password'])) {
	$user->Authentication($_POST['email'], $_POST['password']);
}

if($user->isAdmin()) {
	define('success', true);
	add_file(env::vars()->ROOT_PATH.'/administration/includes/index/authorized.php');
}
else {
	add_file(env::vars()->ROOT_PATH.'/administration/includes/index/login.php');
}