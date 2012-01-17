<?php
include '../core/core.php';

if (isset($_POST['name']))
    $name = $_POST['name'];
if (isset($_POST['day']))
    $day = $_POST['day'];
if (isset($_POST['month']))
    $month = $_POST['month'];
if (isset($_POST['year']))
    $year = $_POST['year'];
if (isset($_POST['fb']))
    $fb = $_POST['fb'];
if (isset($_POST['tw']))
    $tw = $_POST['tw'];
if (isset($_POST['vk']))
    $vk = $_POST['vk'];
if (isset($_POST['site']))
    $site = $_POST['site'];
if (isset($_POST['blog']))
    $blog = $_POST['blog'];
if (isset($_POST['about']))
    $about = $_POST['about'];

$data = 0;
$idUser = User::get()->id();

    if ($idUser) {
         PluginModule::load('Home');
         $userEdit = new UserEditManager($idUser);
         $userEdit->set_name($name);
         $userEdit->set_fb($fb);
         $userEdit->set_about($about);
         $userEdit->set_vk($vk);
         $userEdit->set_blog($blog);
         $userEdit->set_site($site);
         $userEdit->set_tw($tw);
         if($day !=0 && $month !=0 && $year != 0) {

             if ($day < 10) $day = '0'.$day;
             if ($month < 10) $month = '0'.$month;
             $dob = $year.'-'.$month.'-'.$day;
            $userEdit->set_birth_date($dob);
         }
         
         $data = $userEdit->update_user();
    }

echo $data;