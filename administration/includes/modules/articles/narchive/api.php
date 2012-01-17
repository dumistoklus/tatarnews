<?php
include_once '../../../../../core/core.php';
DB_Provider::Instance()->loadProvider('Administration.Users');

if(!User::get()->check_rights('A', 'NPA')) {
	echo "Access Denied";
	Logger::append_error('Illegal access: '.User::ip(), __FILE__.':'.__FUNCTION__);
	die;
}

$task = '';

if(isset($_POST['task'])) {
	$task = $_POST['task'];
}

switch ($task) {
    case 'GET_ARCHIVE':
        echo archive();
        break;
    case 'NEW_ARCHIVE':
            echo create_archive();
            break;
    case 'SAVE_ARCHIVE':
            echo edit_archive();
            break;
    defualt:
        echo '{failure: true}';
}

function archive()
{
    if(is_isset_post('start', 'limit')) {
        DB_Provider::Instance()->loadProvider('Administration.NArchive');
        $provider = new NewsPaperArchiveAdministrationProvider($_POST['start'], $_POST['limit']);

        $result = to_ext_datastore_json($provider->archive());
        $result['total'] = $provider->count();

        return json_encode($result);
    }

    return '{failure: true}';
}

function create_archive()
{
    if(is_isset_post('number', 'number_total', 'date_start', 'date_end')) {
        DB_Provider::Instance()->loadProvider('Administration.NArchive');
        $p = new NewArchiveProvider();
        $p->number = $_POST['number'];
        $p->number_total = $_POST['number_total'];
        $p->date_start = $_POST['date_start'];
        $p->date_end = $_POST['date_end'];
        return $p->create();
    }

    return false;
}

function edit_archive()
{
    if(is_isset_post('id', 'number', 'number_total', 'date_start', 'date_end')) {
        DB_Provider::Instance()->loadProvider('Administration.NArchive');
        $p = new EditArchiveProvider($_POST['id']);
        $p->number = $_POST['number'];
        $p->number_total = $_POST['number_total'];
        $p->date_start = $_POST['date_start'];
        $p->date_end = $_POST['date_end'];
        return $p->create();
    }

    return 0;
}