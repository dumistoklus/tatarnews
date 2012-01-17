<?php
include_once '../../../../../core/core.php';
DB_Provider::Instance()->loadProvider('Administration.Rights');

if(!User::get()->check_rights('G')) {
	echo "Access Denied";
	Logger::append_error('Illegal access: '.User::ip(), __FILE__.':'.__FUNCTION__);
	die;	
}

$task = '';

if(isset($_POST['task'])) {
	$task = $_POST['task'];
}

switch ($task) {
	case 'GET_GROUP_RIGHTS': 
		get_first_group_rights();
		break;
		
	case 'GET_GROUPS':
		get_groups(); 
		break;
		
	case 'SAVE_RIGHTS':
		save_rights();
		break;
	
	case 'CREATE_GROUP':
		create_group();
		break;
		
	case 'DELETE_GROUP':
		delete_group();
		break;
		
	default:
		echo '{failure: true}';
		break;
}

function get_first_group_rights() {
	
	$provider = new RightsEditorProvider();
	
	$group = isset($_POST['group']) ? (int)$_POST['group'] : 0;
	
	$rights_array = $provider->get_group_rights($group);
		
	$rights = format_rights_array($rights_array);
	
	print_json($rights);
	
}

function format_rights_array($array) {
	
	$array_size = sizeof($array);

	$formatted_array = array();
	
	for($i = 0; $i < $array_size; $i++) {
		$formatted_array[$i]['id'] = $array[$i]['id'];
		$formatted_array[$i]['right_index'] = $array[$i]['right_index'];
		$formatted_array[$i]['name'] = $array[$i]['name'];
		$formatted_array[$i]['enable'] = $array[$i]['group_id'] == '' ? false : true;
	}
	
	return $formatted_array;
}

function get_groups() {
	
	$provider = new GroupProvider();
	
	$groups = $provider->get_groups();
	
	print_json($groups);
}

function save_rights() {
	$error_answer = '0';
	
	if(is_isset_post('right_id', 'group_id', 'enabled')) {
		
		$right_id = (int)$_POST['right_id'] != 0 ? (int)$_POST['right_id']  : die($error_answer);
		$enabled = ''; 
		
		if($_POST['enabled'] == 'true') {
			$enabled = true;
		}
		else if ($_POST['enabled'] == 'false') {
			$enabled = false;
		}
		else die('not bool');
		
		$group = (int)$_POST['group_id'] != 0 ? (int)$_POST['group_id']  : die($error_answer);
		
		$provider = new RightsEditorProvider();
		
		echo ($provider->set_rights($group, $right_id, $enabled) ? '1' : 'Изменение не удалось');
		
	}
	else echo $error_answer;
}

function create_group() {
	if(is_isset_post('name')) {
		$provider = new GroupProvider();
		$group_id = $provider->create_group($_POST['name']);

		if($group_id > 1) {
			echo 'SUCCESS';
		}
	}
	else echo '0';		
}

function delete_group() {
	
	if(is_isset_post('group_id')) {
		
		$group_id = (int)$_POST['group_id'];
		$provider = new GroupProvider();
		
		$ans = $provider->delete_group($group_id);

		if($ans) {
			echo 'SUCCESS';
		}
	}
	else echo 'not isset id';
}