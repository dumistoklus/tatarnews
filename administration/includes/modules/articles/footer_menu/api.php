<?php

include_once '../../../../../core/core.php';
DB_Provider::Instance()->loadProvider('Administration.Users');

if (!User::get()->check_rights('A', 'FM')) {
    echo "Access Denied";
    Logger::append_error('Illegal access: ' . User::ip(), __FILE__ . ':' . __FUNCTION__);
    die;
}

$task = '';

if (isset($_POST['task'])) {
    $task = $_POST['task'];
}

switch ($task) {
    case 'GET_FOOTER_MENU':
        echo footer_menu();
        break;
    case 'DELETE_PUNKT':
        delete_punkts();
        break;
    case 'EDIT_PUNKT':
        edit_punkt();
        break;
    case 'ADD_PUNKT':
        add_punkt();
        break;
    default:
        echo '{failure: true}';
}

function footer_menu() {

    PluginModule::load('Footer');
    $footer = new FooterList();

    $result = to_ext_datastore_json($footer->footermenu());

    return json_encode($result);

    return '{failure: true}';
}

function delete_punkts() {

    if (is_isset_post('punkts_ids')) {

        PluginModule::load('Footer');

        $footerMenu = new FooterMenu();

        echo $footerMenu->delete_punkt(json_decode($_POST['punkts_ids']));
    }
}

function edit_punkt() {

    if (is_isset_post('id','name', 'link')) {

        PluginModule::load('Footer');

        $newFooterMenu = new FooterMenu();

        $newFooterMenu->set_name($_POST['name']);
        $newFooterMenu->set_link($_POST['link']);

        echo $newFooterMenu->update_punkt($_POST['id']);
    }
}

function add_punkt() {

    if (is_isset_post('name', 'link')) {

        PluginModule::load('Footer');

        $newFooterMenu = new FooterMenu();

        $newFooterMenu->set_name($_POST['name']);
        $newFooterMenu->set_link($_POST['link']);

        echo $newFooterMenu->add_punkt();
    }
}
