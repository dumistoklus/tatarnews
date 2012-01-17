<?php

include_once '../../../../../core/core.php';
DB_Provider::Instance()->loadProvider('Administration.Users');

if (!User::get()->check_rights('A', 'DC')) {
    echo "Access Denied";
    Logger::append_error('Illegal access: ' . User::ip(), __FILE__ . ':' . __FUNCTION__);
    die;
}

$task = '';

if (isset($_POST['task'])) {
    $task = $_POST['task'];
}

switch ($task) {
    case 'GET_COMMENTS':
        echo comments();
        break;
    case 'CHAHGE_ACTIVE':
        echo change_active();
        break;
    default:
        echo '{failure: true}';
}

function comments() {
    if (is_isset_post('start', 'limit')) {
        DB_Provider::Instance()->loadProvider('Administration.Comments');
        $comments = new CommentsAdminProvider($_POST['start'], $_POST['limit']);
        $formatComments = $comments->comments();
        for ($i = 0; $i < count($formatComments); $i++) {
            $formatComments[$i]['created'] = date("j", $formatComments[$i]['created']) . ' ' . FormatTime::ru_month(date("n", $formatComments[$i]['created'])) . ' ' . date("Y , G:i", $formatComments[$i]['created']);
        }
        $result = to_ext_datastore_json($formatComments);
        $result['total'] = $comments->count();
        return json_encode($result);
    }
    return '{failure: true}';
}

function change_active() {

    if (is_isset_post('id', 'active')) {

        PluginModule::load('CommentsPlugin');
        $commentDelete = new CommentDeleteAjax($_POST['id']);
        return $commentDelete->change_active($_POST['active']);
    }
    return 0;
}