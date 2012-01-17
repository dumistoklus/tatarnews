<?php
include_once '../../../../../core/core.php';
DB_Provider::Instance()->loadProvider('Administration.Users');

if(!User::get()->check_rights('P', 'NN')) {
	echo "Access Denied";
	Logger::append_error('Illegal access: '.User::ip(), __FILE__.':'.__FUNCTION__);
	die;
}

$task = '';

if(isset($_REQUEST['task'])) $task = $_REQUEST['task'];

switch($task) {
    case 'GET_NEWS':
        echo news();
        break;
    case 'EDIT_NEWS':
        echo edit_news();
        break;
    case 'DELETE_NEWS':
        delete_news();
        break;
    default:
        echo '{failure: true}';
}

function news() {
    if(is_isset_post('start', 'limit')) {
        DB_Provider::Instance()->loadProvider('Administration.News');

        $p = new NewsListAdministrationProvider($_POST['start'], $_POST['limit']);

        $news = to_ext_datastore_json($p->get_list());
        $news['total'] = $p->count();

        return json_encode($news);
    }
    return '{failure: true}';
}

function edit_news() {
    if(is_isset_post('nid', 'preview', 'text', 'date')) {

        DB_Provider::Instance()->loadProvider('Plugins.ShortNews');
        PluginModule::load('ShortNews');

        $editor = new EditShortNews($_POST['nid']);

        $editor->set_date($_POST['date']);
        $editor->set_shortNews($_POST['preview']);
        $editor->set_text($_POST['text']);

        if($editor->update_shortNews()) return 1;
    }

    return 0;
}

function delete_news() {

    PluginModule::load('ShortNews');

    if (is_isset_post('news_ids')) {

        $deleteNews = new DeleteShortNews(json_decode($_POST['news_ids']));

        echo $deleteNews->delete_news();
    }
}