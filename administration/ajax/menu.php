<?php
include_once '../../core/core.php';
$user = User::get();
if(!$user->isAdmin()) {
	echo "{failure: true}";
	die;
}

$user = new AdminUserProvider();

$groups = $user->get_admininistration_pages_in_extjs_tree_format_array();

if($groups) {
	echo json_encode($groups);
}
