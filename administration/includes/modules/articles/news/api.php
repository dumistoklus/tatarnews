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
    case 'CREATE_NEWS':
        echo create_news();
        break;
}

function create_news()
{
    if(is_isset_post('name', 'content', 'date'))
    {
        $name = $_POST['name'];
        $content = $_POST['content'];
        $date = filter_var($_POST['date'], FILTER_SANITIZE_NUMBER_INT);

        DB_Provider::Instance()->loadProvider('Plugins.ShortNews');
        PluginModule::load('ShortNews');

        $news = new NewShortNews();
        $news->set_shortNews($name);
        $news->set_text($content);
        $news->set_date($date);

        return $news->create_shortNews();
    }
}