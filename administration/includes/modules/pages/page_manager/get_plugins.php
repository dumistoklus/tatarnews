<?php

include '../../../../../core/core.php';
DB_Provider::Instance()->loadProvider('Administration.Users');

if(!User::get()->check_rights('P', 'A')) {
	echo "Access Denied";
	Logger::append_error('Illegal access: '.User::ip(), __FILE__.':'.__FUNCTION__);
	die;	
}

DB_Provider::Instance()->loadProvider('Administration.Plugins');

$provider = new PluginsAdminProvider();

$plugins = $provider->get_all_plugins();

$pl_tree = array( 'plugins' => array());

foreach ($plugins as $plugin) {
	$pl_tree['plugins'][] = array(
		'text' => $plugin['name'], 
		'id' => 'Plugin'.$plugin['id'], 
		'name' => $plugin['name'],
		'bd_id' => $plugin['id'],
		'container' => $plugin['container'],
		'settings' => $plugin['have_settings'],
		'class' => $plugin['file_name']);
}

echo json_encode($pl_tree);