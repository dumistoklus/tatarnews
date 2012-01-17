<?php

 include '../core/core.php';

if (isset($_POST['voteId']))
    $voteId = $_POST['voteId'];

if (isset($_POST['answerId']))
    $answerId = $_POST['answerId'];

$voteIdFromString = explode('_', $voteId);
if ($voteIdFromString) {
    $id_vote = $voteIdFromString[1];
}
$voice = 0;
$userId =  User::get()->id();

if(isset($userId) && isset($id_vote) && isset($answerId)) {
    PluginModule::load('VotesPlugin');
    $voteAjax = new VotesAjax($userId, $id_vote, $answerId);
    $voice = $voteAjax->makeVoice();
}
if($voice != 0)
    echo $voteAjax->viewList();
else
    echo 0;

