<?php

include '../core/core.php';

if (isset($_POST['task'])) {
    $task = $_POST['task'];
}

switch ($task) {
    case 'ADD_ANSWER':
        add_answer();
        break;
    case 'DELETE_ANSWER':
        echo delete_answer();
        break;
}

function add_answer() {

    if (is_isset_post('text', 'id_question') && User::get()->isAuth()) {

        PluginModule::load('Questions');
        $output = 0;

        $answer = new Answer();
        $answer->set_id_question($_POST['id_question']);
        $answer->set_user_id(User::get()->id());
        $answer->set_user_login(User::get()->nickname());
        $answer->set_text($_POST['text']);

        if ($answer->add_answer() == 1) {

            $id_answer = mysql_insert_id();

            $output = '
            <div  class="answer-text">
                <p>'
                    . $_POST['text'] .
                    '</p>
            </div>
            <div class="answer-footer">                        
                <a href="/?page=home&user=' . User::get()->id() . '">' . User::get()->nickname() . '</a>
                , ' . date("j") . '&nbsp;'
                    . FormatTime::ru_month(date("n")) . '&nbsp;'
                    . date("Y , G:i");
            if (User::get()->check_rights('CQ'))
                $output .= ' <a id="deleteanswer_' . $id_answer . '" class="delete-answer" href="/?page=questions&id_answer=' . $id_answer . '">Удалить</a>';
            $output .= '</div>';
        }
    }

    echo $output;
}

function delete_answer() {

    if (is_isset_post('id_answer') && User::get()->check_rights('CQ')) {

         $idArray = explode('_', $_POST['id_answer']);
         if ($idArray[1]) {
            $id = $idArray[1];
        }
        PluginModule::load('Questions');
        $answer = new Answer();
        return $answer->deactivate_answer($id);
    } 
    return 0;
}