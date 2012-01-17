<?php

include_once '../../../../../core/core.php';
DB_Provider::Instance()->loadProvider('Administration.Users');

if (!User::get()->check_rights('A', 'P')) {
    echo "Access Denied";
    Logger::append_error('Illegal access: ' . User::ip(), __FILE__ . ':' . __FUNCTION__);
    die;
}

$task = '';

if (isset($_POST['task'])) {
    $task = $_POST['task'];
}

switch ($task) {
    case 'GET_PAGES':
        echo pages();
        break;
    case 'DELETE_PAGES':
        delete_pages();
        break;
    case 'ADD_PAGE':
        echo add_page();
        break;
    case 'EDIT_PAGE':
        echo edit_page();
        break;
    default:
        echo '{failure: true}';
}

function pages() {
    if (is_isset_post('start', 'limit')) {
        PluginModule::load('CustomPages');
        $pages = new CustomPageList($_POST['start'], $_POST['limit']);
        $result = to_ext_datastore_json($pages->pages());
        $result['total'] = $pages->count();
        return json_encode($result);
    }
    return '{failure: true}';
}

function delete_pages() {
    if (is_isset_post('page_ids')) {
        PluginModule::load('CustomPages');
        if (is_isset_post('page_ids')) {
            $customPageEditor = new CustomPageEditor();
            echo $customPageEditor->delete_pages(json_decode($_POST['page_ids']));
        }
    }
}

function  add_page() {
    if (is_isset_post('title','content','description','keywords')) {
        PluginModule::load('CustomPages');
        $newPage = new  CustomPageCreator();
        return $newPage->create_page();
    }
    return 0;
}

function  edit_page() {
    if (is_isset_post('page_id','title','content','description','keywords')) {
        PluginModule::load('CustomPages');
        $editPage = new  CustomPageEditor();
        return $editPage->edit_page();
    }
    return 0;
}