<?php

include_once '../../../../../core/core.php';
DB_Provider::Instance()->loadProvider('Administration.Users');

if(!User::get()->check_rights('U', 'G')) {
	echo "Access Denied";
	Logger::append_error('Illegal access: '.User::ip(), __FILE__.':'.__FUNCTION__);
	die;	
}

$task = '';

if(isset($_POST['task'])) {
	$task = $_POST['task'];
}

switch ($task) {
	case 'LIST':
		print_list(); 
		break;
	
	case 'GET_USERS_GROUPS':
		print_users_groups();
		break;
	
	case 'UPDATE_USER_DATA':
		update_user();
		break;
	
	case 'CREATE_USER':
		create_user();
		break;
	
	case 'DELETE_USERS': 
		delete_users();
		break;
		
	default:
		echo '{failure: true}';
		break;
}

function print_list() {
	$start = 0;
	$limit = 0; 

	if(is_isset_post('start', 'limit')) {
		$start = (int)$_POST['start'];
		$limit = (int)$_POST['limit'];
	}
	
	$usersInfo = new UsersCollection();
	
	$users_array = $usersInfo->GetUsers($start, $limit);
	

	$result = to_ext_datastore_json($users_array[1]);
    $result['total'] = $users_array[0];

	echo json_encode($result);
}

function print_users_groups() {
	
	$groups = new UsersManager();
	
	$result = to_ext_datastore_json($groups->Groups());
	
	echo json_encode($result);	
	
}

function update_user() {
	if(is_isset_post('id', 'email', 'group', 'nickname', 'name')) {
		$user_id = (int)$_POST['id'];
		$email = trim($_POST['email']);
		$group = $_POST['group'];
		$nickname = trim($_POST['nickname']);
                $name = trim($_POST['name']);
		
		$update = new UsersManager();
		
		$answer = $update->Edit($user_id, $nickname, $email, $group, $name);
		
		switch($answer) {
                        case 'NAME_FAIL':
                            echo 'Введите корректное ФИО';
                            break;
			case 'EMAIL_FAIL' :
				echo 'Введите корректный email.';
				break;
				
			case 'NICKNAME_FAIL':
				echo 'Nickname может состоять из латинских букв и цифр, длиной от 2 до 40 символов';
				break;
			
			case 'GROUP_FAIL':
				echo 'Группа должна состоять только из латинских букв';
				break;
				
			case 'ID_FAIL':
				echo 'ID должен быть больше 0.';
				break;
				
			default: 
				echo '1';
				break;
		}
	}	
	else {
		echo 'Ошибка передачи данных.';
	}
}

function create_user() {
	if(is_isset_post('email', 'nickname', 'password', 'group', 'name')) {
		$email = $_POST['email'];
		$group = $_POST['group'];
		$nickname = $_POST['nickname'];
		$password = $_POST['password'];
		$name = $_POST['name'];

		$manager = new UsersManager();
		
		echo $manager->CreateUser($email, $nickname, $group, $password, $name);
	}
	
	else echo 'EMAIL';
}

function delete_users() {
	if(is_isset_post('users_ids')) {
		
		$ids = json_decode(stripslashes($_POST['users_ids']));
		
		$manager = new UsersManager();
		
		if($manager->Delete($ids)) echo '1';
	}
}
