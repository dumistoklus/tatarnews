<?php
include_once '../../../../../core/core.php';
DB_Provider::Instance()->loadProvider('Administration.Users');

if(!User::get()->check_rights('P', 'CM')) {
	echo "Access Denied";
	Logger::append_error('Illegal access: '.User::ip(), __FILE__.':'.__FUNCTION__);
	die;
}

$task = '';

if(isset($_REQUEST['task'])) $task = $_REQUEST['task'];

switch($task) {
    case 'GET_CATS':
        echo cats();
        break;
    case 'CREATE_CAT':
        echo create();
        break;
    case 'DELETE_CAT':
        echo delete();
        break;
    default:
        echo '{failure: true}';
}
 
function cats() {

    if(is_isset_post('start', 'limit'))
    {
        DB_Provider::Instance()->loadProvider('Plugins.HeaderMenuLinks');

        $p = new CatsAdministrationProvider($_POST['start'], $_POST['limit']);

        $cats = to_ext_datastore_json($p->get_with_limit());
        $cats['total'] = $p->count();

        return json_encode($cats);
    }

    return '{failure: true}';
}

function create() {
    if(is_isset_post('name')) {

        DB_Provider::Instance()->loadProvider('Administration.Cats');

        if(CatsManager::create($_POST['name'])) return 1;
    }

    return 0;
}

function delete() {
    if(is_isset_post('id')) {
        DB_Provider::Instance()->loadProvider('Administration.Cats');

        if(CatsManager::delete($_POST['id']) > 0) return 1;
    }

    return 0;
}