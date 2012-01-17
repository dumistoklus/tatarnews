<?php

include_once '../../../../../core/core.php';
DB_Provider::Instance()->loadProvider('Administration.Users');

if (!User::get()->check_rights('A', 'CC')) {
    echo "Access Denied";
    Logger::append_error('Illegal access: ' . User::ip(), __FILE__ . ':' . __FUNCTION__);
    die;
}

$task = '';

define('COMPANY_IMAGE_MAIN', '/images/company/');

if (isset($_POST['task'])) {
    $task = $_POST['task'];
}

switch ($task) {
    case 'GET_COMPANY':
        echo company();
        break;
    case 'DELETE_COMPANY':
        delete_company();
        break;
    case 'EDIT_COMPANY':
        edit_company();
        break;
    case 'ADD_COMPANY':
        add_company();
        break;
    case 'UPLOAD_COMPANY_IMAGE':
        if(!User::get()->check_rights('FU')) {
            break;
        }
        echo upload_company_image();
        break;
    default:
        echo '{failure: true}';
}

function company() {
    if (is_isset_post('start', 'limit')) {
        DB_Provider::Instance()->loadProvider('Administration.CompanyPlugin');
        $provider = new CompanyPluginAdministrationProvider($_POST['start'], $_POST['limit']);

        $result = to_ext_datastore_json($provider->company());
        $result['total'] = $provider->count();

        return json_encode($result);
    }

    return '{failure: true}';
}

function delete_company() {

    PluginModule::load('CompanyPlugin');

    if (is_isset_post('company_ids')) {

        $deleteCompany = new DeleteCompany(json_decode($_POST['company_ids']));

        echo $deleteCompany->delete_company();
    }
}

function edit_company() {

   if (is_isset_post('id','img','name','dob','industry','products','revenue','profit','director','number_of_emplayees', 'about', 'history', 'adress','phone','email','site','guide' )) {

      PluginModule::load('CompanyPlugin');

        $editCompany = new EditCompany($_POST['id']);
        
        $editCompany->set_name($_POST['name']);
        $editCompany->set_dob($_POST['dob']);
        $editCompany->set_about($_POST['about']);
        $editCompany->set_adress($_POST['adress']);
        $editCompany->set_director($_POST['director']);
        $editCompany->set_email($_POST['email']);
        $editCompany->set_guide($_POST['guide']);
        $editCompany->set_history($_POST['history']);
        $editCompany->set_industry($_POST['industry']);
        $editCompany->set_number_of_emplayees($_POST['number_of_emplayees']);
        $editCompany->set_phone($_POST['phone']);
        $editCompany->set_products($_POST['products']);
        $editCompany->set_profit($_POST['profit']);
        $editCompany->set_revenue($_POST['revenue']);
        $editCompany->set_site($_POST['site']);
        $editCompany->set_img($_POST['img']);

        

        echo $editCompany->update_company();
    }
}

function add_company() {

    if (is_isset_post('img','name','dob','industry','products','revenue','profit','director','number_of_emplayees', 'about', 'history', 'adress','phone','email','site','guide' )) {

        PluginModule::load('CompanyPlugin');

        $newCompany = new NewCompany();
        
        $newCompany->set_name($_POST['name']);
        $newCompany->set_dob($_POST['dob']);
        $newCompany->set_about($_POST['about']);
        $newCompany->set_adress($_POST['adress']);
        $newCompany->set_director($_POST['director']);
        $newCompany->set_email($_POST['email']);
        $newCompany->set_guide($_POST['guide']);
        $newCompany->set_history($_POST['history']);
        $newCompany->set_industry($_POST['industry']);
        $newCompany->set_number_of_emplayees($_POST['number_of_emplayees']);
        $newCompany->set_phone($_POST['phone']);
        $newCompany->set_products($_POST['products']);
        $newCompany->set_profit($_POST['profit']);
        $newCompany->set_revenue($_POST['revenue']);
        $newCompany->set_site($_POST['site']);
        $newCompany->set_img($_POST['img']);

        

        echo $newCompany->create_company();
    }
}

function upload_company_image() {

    $dir = env::vars()->ROOT_PATH . COMPANY_IMAGE_MAIN;

    if (strpos($_FILES['img']['name'], '.') !== false) {
        $ext = explode('.', $_FILES['img']['name']);
        $extension = array_pop($ext);
        $filename = substr($_FILES['img']['name'], 0, strlen($_FILES['img']['name']) - strlen($extension) - 1);
    } else {
        return '{success:false, failed: 1, uploaded: 0}';
    }
    $allowed = array('jpeg', 'jpg', 'gif', 'png');

    if (!in_array(strtolower($extension), $allowed)) {
        return '{success:false, failed: 1, uploaded: 0}';
    }

    Module::load('Utils.Images.PHPExifReader');
    Module::load('Utils.Images.Image_Toolbox');

    $t = new Image_Toolbox($_FILES['img']['tmp_name']);

    $t->newOutputSize(200, 0, 0, false, '#FFFFFF');
    $t->save($_FILES['img']['tmp_name'], 'jpg', 80);

    $md5 = md5_file($_FILES['img']['tmp_name']);
    $file = $md5 . '.' . $extension;

    $files[$file]['imageinfo'] = getimagesize($_FILES['img']['tmp_name']);

    if (empty($files[$file]['imageinfo'])) {
        return '{success:false, failed: 1, uploaded: 0}';
    }


    if (!move_uploaded_file($_FILES['img']['tmp_name'], $dir . '/' . $filename . '.' . $extension)) {
        return '{success:false, failed: 1, uploaded: 0}';
    }

    $result['success'] = true;
    $result['failed'] = 0;
    $result['uploaded'] = 1;
    $result['name'] = $_FILES['img']['name'];
    return json_encode($result);
}