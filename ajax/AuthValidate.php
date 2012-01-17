<?php

include '../core/core.php';

if(!User::get()->isAuth() && isset($_POST['login']) && isset($_POST['password'])) {

	if ( User::get()->Authentication($_POST['login'], $_POST['password']) ) {
		echo 1;
	} else {
		echo 0;
	}
}
