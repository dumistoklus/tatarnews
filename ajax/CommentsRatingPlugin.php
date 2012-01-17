<?php

include '../core/core.php';

if (isset($_POST['comment_id']))
    $comment_id = $_POST['comment_id'];

if (isset($_POST['votecomment']))
    $votecomment = (int) $_POST['votecomment'];

    $rating = (int)$_POST['rating'];

$commentArray = explode('_', $comment_id);

if ($commentArray)
    $id_comment = $commentArray[1];
if (User::get()->isAuth()) {
    $id_user = User::get()->id();

    PluginModule::load('CommentsPlugin');
    $commentRatingAjax = new CommentsRatingAjax($id_user, $id_comment);

    $makeVoice = $commentRatingAjax->make_rating_voice($votecomment);

    if($makeVoice > 0)
        $rating = $rating + $votecomment;
}
if ($rating>0)
    $rating = '+'.$rating;

echo $rating;