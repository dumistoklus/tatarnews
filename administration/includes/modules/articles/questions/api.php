<?php

include_once '../../../../../core/core.php';
DB_Provider::Instance()->loadProvider('Administration.Users');

if (!User::get()->check_rights('A', 'CQ')) {
    echo "Access Denied";
    Logger::append_error('Illegal access: ' . User::ip(), __FILE__ . ':' . __FUNCTION__);
    die;
}

$task = '';

if (isset($_POST['task'])) {
    $task = $_POST['task'];
}

switch ($task) {
    case 'GET_QUESTIONS':
        echo questions();
        break;
    case 'CHAHGE_ACTIVE':
        echo change_active();
        break;
    case 'ADD_QUESTION':
        echo add_question();
        break;
    case 'EDIT_QUESTION':
        echo edit_question();
        break;
    default:
        echo '{failure: true}';
}

function questions() {
    if (is_isset_post('start', 'limit')) {
        PluginModule::load('Questions');
        $questions = new QuestionList(true, $_POST['start'], $_POST['limit'], false);
        $data = $questions->questions();
                   
        $result = to_ext_datastore_json($data);
        $result['total'] = $questions->count();

        return json_encode($result);
    }

    return '{failure: true}';
}

function change_active() {

    if (is_isset_post('id', 'active')) {

        PluginModule::load('Questions');
        $question = new Question();

        return $question->change_active($_POST['id'], $_POST['active']);
    }
    return 0;
}

function add_question() {
    
    if (is_isset_post('text')) {

        PluginModule::load('Questions');
        $question = new Question();

        $question->set_text($_POST['text']);
        return $question->add_question();
    }
    return 0;
}

function edit_question() {
    
    if (is_isset_post('id','text')) {

        PluginModule::load('Questions');
        $question = new Question();

        $question->set_text($_POST['text']);
        return $question->edit_question($_POST['id']);
    }
    return 0;
}