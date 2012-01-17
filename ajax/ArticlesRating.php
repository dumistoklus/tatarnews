<?php


include '../core/core.php';

if (isset($_POST['rate']))
    $rate = (int)$_POST['rate'];

if (isset($_POST['article_id']))
    $article_id = (int) $_POST['article_id'];

if (isset($_POST['rating']))
    $rating = (int) $_POST['rating'];

if (User::get()->isAuth()) {
    $user_id = User::get()->id();

    PluginModule::load('ArticlesRatingPlugin');
    $articlesRatingAjax = new ArticlesRatingAjax($user_id, $article_id);

    $makeVoice = $articlesRatingAjax->make_rating_voice($rate);

    if($makeVoice > 0)
        $rating = $rating + $rate;
}
if ($rating > 0)
    echo '+'.$rating;
else if ($rating < 0)
    echo $rating;
else if ($rating == 0)
    echo '0';