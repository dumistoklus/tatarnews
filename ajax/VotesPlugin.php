<?php

include '../core/core.php';

if (isset($_POST['voteId']))
    $voteId = $_POST['voteId'];

if (isset($_POST['answerId']))
    $answerId = $_POST['answerId'];

$userId =  User::get()->id();
$voice = 0;
if(isset($userId) && isset($voteId) && isset($answerId)) {
    PluginModule::load('VotesPlugin');
    $voteAjax = new VotesAjax($userId, $voteId, $answerId);
    $voice = $voteAjax->makeVoice();
}

if($voice != 0 )
    echo $voteAjax->view();
else
    echo 0;