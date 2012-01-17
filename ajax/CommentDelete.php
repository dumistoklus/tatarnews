<?php

include '../core/core.php';

if (isset($_POST['id_comment']))
    $id_comment = $_POST['id_comment'];

$result['id_delete'] = 0;

if (User::get()->check_rights('DC')) {

    $idArray = explode('_', $id_comment);
    if ($idArray) {
        $id = $idArray[1];
        $isdelete = $idArray[2];
    }

    PluginModule::load('CommentsPlugin');
    $commentDelete = new CommentDeleteAjax($id);
    $delete = $commentDelete->change_active($isdelete);
    if ($delete > 0) {
        $result['id_delete'] = $id;
        if ($isdelete == 1)
            $result['deletehtml'] = '&nbsp;<a id="deletecommment_' . $id . '_0" class="deletecomment" href="#">Удалить</a>';
        else
            $result['deletehtml'] = '&nbsp;<a id="deletecommment_' . $id . '_1" class="deletecomment" href="#">Опубликовать</a>';
    }
}

echo json_encode($result);