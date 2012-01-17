<?php

include_once '../../../../../core/core.php';
DB_Provider::Instance()->loadProvider('Administration.Users');

if(!User::get()->check_rights('P', 'A')) {
	echo "Access Denied";
	Logger::append_error('Illegal access: '.User::ip(), __FILE__.':'.__FUNCTION__);
	die;	
}

DB_Provider::Instance()->loadProvider('Administration.PageSchema');

$task = '';

if(isset($_POST['task'])) $task = $_POST['task'];

switch($task) {
	
	case 'GET_PAGE_SCHEMA':
		$scheme = new PageSchemeModel();
		
		echo json_encode(array('sides' => $scheme->get()));
		break;
	case 'SAVE_PAGE': 
		
		echo savePage();
				
		break;
		
	default: echo '{failure: true}';
	
}

function savePage() {
	if(is_isset_post('pageSchema', 'pageName', 'pageTitle', 'pageKeywords', 'pageDescription')) {
		
                DB_Provider::Instance()->loadProvider('Core');
		DB_Provider::Instance()->loadProvider('Administration.Plugins');

		$name = $_POST['pageName'];

		$page = PageController::create_page($name);
		
                if($page->id() == 0) return 'Страница уже существует';

                if(trim($_POST['pageDescription']) != '') 
                    $page->set_description($_POST['pageDescription']);
                if(trim($_POST['pageKeywords']) != '')
                    $page->set_keywords($_POST['pageKeywords']);
                if(trim($_POST['pageTitle']))
                    $page->set_title($_POST['pageTitle']);

		$pl_manager = new PagePluginsManager($page);
		
		$pl_manager->removeSchema();

                $pageSchema = json_decode($_POST['pageSchema'], true);
		$success = $pl_manager->addPluginsSchema(convertSchema($pageSchema));

		
		if($success) return 1;
		
	}
	
	return 'Некорректные данные';
}

function convertSchema($schema) {
	$newSchema = array();
	
	foreach($schema as $ext_side => $plugins) {
		switch($ext_side) {
			case 'NorthPanel': $newSchema[Side::HEADER] = $plugins; break;
			case 'SouthPanel': $newSchema[Side::BOTTOM] = $plugins; break;
			case 'WestPanel': $newSchema[Side::LEFT] = $plugins; break;
			case 'EastPanel': $newSchema[Side::RIGHT] = $plugins; break;
			case 'CenterPanel': $newSchema[Side::CENTER] = $plugins; break;
		}
	}
	
	return $newSchema;
}