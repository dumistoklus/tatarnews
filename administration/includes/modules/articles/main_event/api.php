<?php
include_once '../../../../../core/core.php';
DB_Provider::Instance()->loadProvider('Administration.Users');

if(!User::get()->check_rights('A', 'NA')) {
	echo "Access Denied";
	Logger::append_error('Illegal access: '.User::ip(), __FILE__.':'.__FUNCTION__);
	die;
}

$task = '';

if(isset($_POST['task'])) {
	$task = $_POST['task'];
}

switch ($task) {
    case 'SAVE_EVENT':
        echo save_event();
        break;
    
    default:
        echo '{failure: true}';
}

function save_event()
{
    if(is_isset_post('aid', 'preview', 'date_start', 'date_end')) {
        DB_Provider::Instance()->loadProvider('Administration.MainEvent');

        $p = new NewMainEventProvider();
        $p->aid = $_POST['aid'];
        $p->preview = $_POST['preview'];
        $p->date_start = $_POST['date_start'];
        $p->date_end = $_POST['date_end'];

        if($p->create()) return 1;
    }

    return 0;
}