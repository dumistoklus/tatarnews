<?php

include '../core/core.php';

if (isset($_POST['answerto']))
    $answerto = $_POST['answerto'];
if (isset($_POST['article_id']))
    $article_id = $_POST['article_id'];
if (isset($_POST['comment']))
    $comment = $_POST['comment'];
if (isset($_POST['name']))
    $name = $_POST['name'];
if (isset($_POST['email']))
    $email = $_POST['email'];
if ($answerto == 'comments-warp')
    $answerto = 0;
else {
    $answerArray = explode('_', $answerto);
    if ($answerArray) {
        $answerto = $answerArray[1];
    }
}

$ip = $_SERVER['REMOTE_ADDR'];
$res = get_bd_data('SELECT * FROM `fms_banip` WHERE ip = "'.$ip.'" LIMIT 1');

if ($email != ''  && count($res) == 0) {
    affectedRowsQuery('INSERT INTO `fms_banip` (`ip`) VALUES ("'.$ip.'")');
}
if ($email == '' && count($res) == 0) {
    PluginModule::load('CommentsPlugin');
    $commentajax = new CommentsAjax($article_id);

    $dataArray['id_article'] = $article_id;
    $dataArray['id_parent'] = $answerto;
    $dataArray['id_user'] = '0';
    $dataArray['user_name'] = $name == 'Ваше имя' ? 'Гость' : $name;
    $dataArray['text'] = $comment;

    $newComment = $commentajax->new_comment($dataArray);

    if ($newComment > 0) {

        $currentTime = time();

        $data['html'] = '
    <div class="comment" id="comment_' . $newComment . '">
        <div class="comment-point-warp">
            <div class="comment-point">
            </div>
        </div>
        <div class="comment-text grey">
        <p>' . $dataArray['text'] . '</p>
        </div>
        <div class="comment-footer">
            <div id="rating_' . $newComment . '" class="comment-rating">
            <a href="#" class="comment-like"></a>
                <div class="rating-rate">0</div>
                <a href="#" class="comment-dislike"></a></div>
                
                    ' . $dataArray['user_name'] . ',
                     ' . date("j", $currentTime) . '&nbsp;'
                . FormatTime::ru_month(date("n", $currentTime)) . '&nbsp;'
                . date("Y , G:i", $currentTime) . '
                    <a href="#" class="answer-for-comment">Ответить</a>';
        if (User::get()->check_rights('DC'))
            $data['html'] .='&nbsp;<a id="deletecommment_' . $newComment . '" class="deletecomment" href="#">Опубликовать</a>';
        else
            $data['html'] .='&nbsp;(Ваш комметарий появится после проверки модератором)';
        $data['html'] .='</div>
            </div>';
    }
} else 
    $data = 0;

echo json_encode($data);
