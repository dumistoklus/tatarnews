<?php

include_once '../../../../../core/core.php';
DB_Provider::Instance()->loadProvider('Administration.Users');

if(!User::get()->check_rights('P', 'NA')) {
	echo "Access Denied";
	Logger::append_error('Illegal access: '.User::ip(), __FILE__.':'.__FUNCTION__);
	die;
}

$task = '';

if(isset($_REQUEST['task'])) $task = $_REQUEST['task'];

switch($task) {
    case 'CREATE_VOTE':
        echo create_vote();
        break;
}

function create_vote()
{
    if(is_isset_post('name', 'answers', 'date_start','date_end')) {
        PluginModule::load('VotesPlugin');
        DB_Provider::Instance()->loadProvider('Plugins.VotesPlugin');

        $name = filter_string($_POST['name']);
        $date_start = filter_var($_POST['date_start'], FILTER_SANITIZE_NUMBER_INT);
        $date_end = filter_var($_POST['date_end'], FILTER_SANITIZE_NUMBER_INT);

        $answers = json_decode($_POST['answers']);
        if(!is_array($answers)) return false;

        foreach($answers as $key => $val)
        {
            $answers[$key] = filter_string($val);
        }

        $vote = new NewVote();

        $vote->set_active(1);
        $vote->set_date_end($date_end);
        $vote->set_date_start($date_start);
        $vote->set_answers($answers);
        $vote->set_name($name);

        return (bool)$vote->create_vote();
    }
}
