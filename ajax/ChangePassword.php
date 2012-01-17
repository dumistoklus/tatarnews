<?php
include '../core/core.php';

if (isset($_POST['password']))
    $password = $_POST['password'];

$data = 0;
$idUser = User::get()->id();

    if ($idUser) {
        PluginModule::load('Home');
        $userEdit = new UserEditManager($idUser);
        $userEdit->set_password($password);

        $data = $userEdit->update_user();
    }

echo $data;
