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
    case 'GET_FOOTER':
        echo footer();
        break;
    case 'EDIT_FOOTER':
        edit_footer();
        break;
    default:
        echo '{failure: true}';
}

function footer() {

    PluginModule::load('Footer');
    $footer = new FooterList();
    $data = $footer->dataArray();
    $result = to_ext_datastore_json($data);

    return json_encode($result);

    return '{failure: true}';
}

function edit_footer() {

    if (is_isset_post('address', 
            'address_for_mail', 
            'chief_editor', 
            'deputy',
            'email',
            'first_deputy', 
            'phone_commercial', 
            'phone_correspondent',
            'phone_reception', 
            'secretary', 
            'text'
            )) {

        PluginModule::load('Footer');
        $footerEdit = new EditFooter();

        $footerEdit->set_address($_POST['address']);
        $footerEdit->set_address_for_mail($_POST['address_for_mail']);
        $footerEdit->set_chief_editor($_POST['chief_editor']);
        $footerEdit->set_deputy($_POST['deputy']);
        $footerEdit->set_email($_POST['email']);
        $footerEdit->set_first_deputy($_POST['first_deputy']);
        $footerEdit->set_phone_commercial($_POST['phone_commercial']);
        $footerEdit->set_phone_correspondent($_POST['phone_correspondent']);
        $footerEdit->set_phone_reception($_POST['phone_reception']);
        $footerEdit->set_secretary($_POST['secretary']);
        $footerEdit->set_text($_POST['text']);

        echo $footerEdit->update_footer();
    }
}