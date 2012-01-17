<?php

include_once '../../../../../core/core.php';
DB_Provider::Instance()->loadProvider('Administration.Users');

if (!User::get()->check_rights('A', 'CP')) {
    echo "Access Denied";
    Logger::append_error('Illegal access: ' . User::ip(), __FILE__ . ':' . __FUNCTION__);
    die;
}

$task = '';

define('PERSON_IMAGE_MAIN', '/images/persons/');

if (isset($_POST['task'])) {
    $task = $_POST['task'];
}

switch ($task) {
    case 'GET_PERSON':
        echo persons();
        break;
    case 'DELETE_PERSON':
        delete_person();
        break;
    case 'EDIT_PERSON':
        edit_person();
        break;
    case 'ADD_PERSON':
        add_person();
        break;
    case 'UPLOAD_PERSON_IMAGE':
        if(!User::get()->check_rights('FU')) {
            break;
        }
        echo upload_person_image();
        break;
    case 'GET_ARTICLES':
        echo get_articles();
        break;
    case 'DELETE_ARTICLE2PERSON':
        delete_article2person();
        break;
    case 'GET_ALL_ARTICLES':
        echo all_articles();
        break;
    case 'ADD_ARTICLE2PERSON':
        add_article2person();
        break;
    default:
        echo '{failure: true}';
}

function persons() {
    if (is_isset_post('start', 'limit')) {
        DB_Provider::Instance()->loadProvider('Administration.PersonPlugin');
        $provider = new PersonPluginAdministrationProvider($_POST['start'], $_POST['limit']);

        $persons = $provider->persons();
        $result = to_ext_datastore_json($persons);
        $result['total'] = $provider->count();

        return json_encode($result);
    }

    return '{failure: true}';
}

function delete_person() {

    if (is_isset_post('archives_ids')) {
        
        PluginModule::load('PersonPlugin');

        $deletePerson = new DeletePerson(json_decode($_POST['archives_ids']));

        echo $deletePerson->delete_person();
    }
}

function edit_person() {

    if (is_isset_post('id', 'img','career', 'coordinates', 'dob', 'education', 'email', 'fax', 'job', 'lastname', 'marital', 'name', 'phone', 'pob', 'post', 'scope', 'sirname', 'unknown_contact')) {

        PluginModule::load('PersonPlugin');

        $editPerson = new EditPerson($_POST['id']);

        $editPerson->set_img($_POST['img']);
        $editPerson->set_career($_POST['career']);
        $editPerson->set_coordinates($_POST['coordinates']);
        $editPerson->set_dob($_POST['dob']);
        $editPerson->set_education($_POST['education']);
        $editPerson->set_email($_POST['email']);
        $editPerson->set_fax($_POST['fax']);
        $editPerson->set_job($_POST['job']);
        $editPerson->set_lastname($_POST['lastname']);
        $editPerson->set_marital($_POST['marital']);
        $editPerson->set_name($_POST['name']);
        $editPerson->set_phone($_POST['phone']);
        $editPerson->set_pob($_POST['pob']);
        $editPerson->set_post($_POST['post']);
        $editPerson->set_scope($_POST['scope']);
        $editPerson->set_sirname($_POST['sirname']);
        $editPerson->set_unknown_contact($_POST['unknown_contact']);

        echo $editPerson->update_person();
    }
}

function add_person() {

    if (is_isset_post('img','career', 'coordinates', 'dob', 'education', 'email', 'fax', 'job', 'lastname', 'marital', 'name', 'phone', 'pob', 'post', 'scope', 'sirname', 'unknown_contact')) {

        PluginModule::load('PersonPlugin');

        $newPerson = new NewPerson();

        $newPerson->set_img($_POST['img']);
        $newPerson->set_career($_POST['career']);
        $newPerson->set_coordinates($_POST['coordinates']);
        $newPerson->set_dob($_POST['dob']);
        $newPerson->set_education($_POST['education']);
        $newPerson->set_email($_POST['email']);
        $newPerson->set_fax($_POST['fax']);
        $newPerson->set_job($_POST['job']);
        $newPerson->set_lastname($_POST['lastname']);
        $newPerson->set_marital($_POST['marital']);
        $newPerson->set_name($_POST['name']);
        $newPerson->set_phone($_POST['phone']);
        $newPerson->set_pob($_POST['pob']);
        $newPerson->set_post($_POST['post']);
        $newPerson->set_scope($_POST['scope']);
        $newPerson->set_sirname($_POST['sirname']);
        $newPerson->set_unknown_contact($_POST['unknown_contact']);

        echo $newPerson->create_person();
    }
}

function upload_person_image() {

    $dir = env::vars()->ROOT_PATH . PERSON_IMAGE_MAIN;

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

function get_articles() {
    
    if (is_isset_post('id_user')) {
        
        DB_Provider::Instance()->loadProvider('Plugins.PersonPlugin');
        
        $provider = new PersonPluginProvider();

        $articles = $provider->get_articles($_POST['id_user']);
        $result = to_ext_datastore_json($articles);

        return json_encode($result);
    }
}

function delete_article2person() {
    
    if (is_isset_post('person2A', 'article2P')) {
        
        PluginModule::load('PersonPlugin');

        $deletePerson = new DeletePerson(array( 0 => $_POST['person2A']));

        echo $deletePerson->delete_article2person($_POST['article2P']);
    }
}

function all_articles() {

    PluginModule::load('PersonPlugin');

    $person = new Person2Articles();

    $articles = $person->all_articles();
    $result = to_ext_datastore_json($articles);

    return json_encode($result);
}

function add_article2person() {
    
    if (is_isset_post('id_article', 'id_person')) {
        
        PluginModule::load('PersonPlugin');

        $person = new Person2Articles();
        
        echo $person->add_article2person($_POST['id_person'], $_POST['id_article']);
    }

}