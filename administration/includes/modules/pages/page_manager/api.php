<?php

include_once '../../../../../core/core.php';
DB_Provider::Instance()->loadProvider('Administration.Users');

if(!User::get()->check_rights('P', 'A')) {
	echo "Access Denied";
	Logger::append_error('Illegal access: '.User::ip(), __FILE__.':'.__FUNCTION__);
	die;	
}

DB_Provider::Instance()->loadProvider('Administration.PageSchema');

$task = 'GET_PAGES';

if(isset($_REQUEST['task'])) $task = $_REQUEST['task'];

switch($task) {
    case 'GET_PAGES':
        echo get_pages();
        break;
	case 'GET_PLUGINS':
            echo get_plugins();
            break;
	case 'GET_PAGE_SCHEMA':
            echo page_schema();
            break;
	case 'SAVE_PAGE': 		
            echo savePage();				
            break;
    case 'DELETE_PAGE':
            echo deletePage();
            break;
		
	default: echo '{failure: true}';
}

function get_pages() {
    DB_Provider::Instance()->loadProvider('Core');
    $pages['pages'] = PageController::get_all_pages();
    return json_encode($pages);
}

function get_plugins() {
    if(is_isset_post('page')) {

        DB_Provider::Instance()->loadProvider('Core');
        Module::load('Utils.Plugins.Map');
        Module::load('Utils.Sides.Format');



        $manager = new PluginsManager($_POST['page']);
        $schema = $manager->schema();

        $extjs_panels_schema = array();

        foreach($schema as $side => $plugins) {
            $tmp_panels_shema = array();

            foreach($plugins as $plugin) {
                $tmp_panels_shema[] = $plugin['id'];
            }
            $extjs_panels_schema[FormatExtJsSide::convert($side)] = $tmp_panels_shema;
        }

        return json_encode($extjs_panels_schema);
    }

    return json_encode(array());
}

function page_schema() {
    $scheme = new PageSchemeModel();
    return json_encode(array('sides' => $scheme->get()));
}


function savePage() {
	if(is_isset_post('pageSchema', 'pageName', 'pageTitle', 'pageKeywords', 'pageDescription')) {
		
        DB_Provider::Instance()->loadProvider('Core');
		DB_Provider::Instance()->loadProvider('Administration.Plugins');

		$name = $_POST['pageName'];

		$page = PageController::edit_page(PageController::load($name));

		
        if($page->id() == 0) return 'Страница не существует';

        if(trim($_POST['pageDescription']) != '' && $page->description() != $_POST['pageDescription'])
            $page->set_description($_POST['pageDescription']);
        if(trim($_POST['pageKeywords']) != '' )
            $page->set_keywords($_POST['pageKeywords'] && $page->description() != $_POST['pageKeywords']);
        if(trim($_POST['pageTitle']) && $page->title() != $_POST['pageTitle'])
            $page->set_title($_POST['pageTitle']);

		$pl_manager = new PagePluginsManager($page);
		
		$pl_manager->removeSchema();

        $pageSchema = json_decode($_POST['pageSchema'], true);
		$success = $pl_manager->addPluginsSchema(convertSchema($pageSchema));
        
		if($success) return 1;
	}
	
	return 'Некорректные данные';
}

function deletePage() {
    if(is_isset_post('pageName')) {

        DB_Provider::Instance()->loadProvider('Core');
        $p = new EditPage(new Page($_POST['pageName']));

        if($p->delete()) return 1;
    }

    return 0;
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