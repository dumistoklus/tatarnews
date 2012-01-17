<?php

class CommentsPluginProvider {

    private $id_article = null;
    private $comments = array();
    private $lastkey = null;               //максимальный ключ
    private $currentTime;

    public function __construct($id_article) {

        $this->id_article = (int) $id_article;
        $this->currentTime = time();
    }

    public function get_comments() {

        $sql = 'SELECT * FROM ' . PREFIX . 'comments WHERE id_article = ' . $this->id_article . ' ORDER BY left_key';

        $this->comments = get_bd_data($sql);

        return $this->comments;
    }

    private function get_max_leftkey() {

        $sql = 'SELECT * FROM ' . PREFIX . 'comments WHERE id_article = ' . $this->id_article.' AND left_key = 1';

        $result = get_bd_data($sql);
        $this->lastkey = $result[0]['right_key'];
    }

    public function set_new_comment($dataArray) {

        if (isset($dataArray['text']) && isset($dataArray['user_id']) && isset($dataArray['user_name'])) {

            $this->get_max_leftkey();
            $newLeftKey = $this->lastkey;
            $newRightKey = $this->lastkey + 1;
            $newRightKeyCore = $this->lastkey + 2;
            $newLevel = 1;

            $sql = 'UPDATE ' . PREFIX . 'comments SET right_key = "' . $newRightKeyCore . '"
                WHERE id_article = ' . $this->id_article . ' AND left_key = 1';

            $data = affectedRowsQuery($sql);

            if ($data == false)
                return false;


            $sql = 'INSERT INTO ' . PREFIX . 'comments (user_id, user_name, text, left_key,right_key, level, id_article, created)
                    VALUES (' . $dataArray["user_id"] . ' ,
                    ' . $dataArray["user_name"] . ',
                    ' . $dataArray["text"] . ',
                    ' . $newLeftKey . ',
                    ' . $newRightKey . ',
                    ' . $newLevel . ',
                    ' . $this->id_article . ',
                    ' . $this->currentTime . ')';

            $data = affectedRowsQuery($sql);

            if ($data == false)
                return false;

            $sql = 'SELECT * FROM ' . PREFIX . 'comments WHERE left_key = ' . $newLeftKey;

            $comment = get_bd_data($sql);

            return $comment[0];
        }

        return false;
    }

}