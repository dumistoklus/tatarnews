<?php

class CommentsPluginProvider {

    private $id_article = null;
    private $comments = array();
    private $currentTime;

    public function __construct($id_article = null) {

        $this->id_article = (int) $id_article;
        $this->currentTime = time();
    }

    public function comments_array() {

        $this->comments = $this->get_comments();

        return $this->comments;
    }

    private function get_comments() {

        $sql = 'SELECT * FROM ' . PREFIX . 'comments WHERE id_article = ' . $this->id_article;

        $this->comments = get_bd_data($sql);

        $this->format_comments();

        return $this->comments;
    }

    private function format_comments() {
        for ($i=0; $i<count($this->comments); $i++ ) {
            $this->comments[$i]['created'] = $this->comments[$i]['created'];
        }
    }

    public function set_new_comment($dataArray = array()) {


        if (isset($dataArray["id_user"]) && isset($dataArray["user_name"]) && isset($dataArray["text"]) &&
        trim($dataArray["id_user"]) != '' && trim($dataArray["user_name"]) != '' && trim($dataArray["text"]) != '') {

            $dataArray["id_user"] = (int)$dataArray["id_user"];
            $dataArray["user_name"] = mysql_real_escape_string($dataArray["user_name"]);
            $dataArray["text"] = mysql_real_escape_string($dataArray["text"]);
            $dataArray["id_parent"] = (int)$dataArray["id_parent"];


            $sql = 'INSERT INTO ' . PREFIX . 'comments (id_article,
            id_user, user_name, text, created, id_parent, isdelete)
            VALUES (' . $dataArray["id_article"] . ',
            "' . $dataArray["id_user"] . '",
            "' . $dataArray["user_name"] . '",
            "' . $dataArray["text"] . '",
            "' . $this->currentTime . '",
            "' . $dataArray["id_parent"] . '",
            1    
        )';

            $result = affectedRowsQuery($sql);

            if ( $result > 0) {

                $comment_id = mysql_insert_id();

                DB_Provider::Instance()->loadProvider('Plugins.Articles');

                $UpdateArticleProvider = new UpdateArticleProvider($dataArray["id_article"]);
                $UpdateArticleProvider->PlusComment();

                return $comment_id;
            }
        }
        return false;
    }

    public function get_last_comment() {

         $sql = 'SELECT * FROM ' . PREFIX . 'comments
             WHERE id_article = ' . $this->id_article.'
                 ORDER BY id DESC LIMIT 1';

        $comment = get_bd_data($sql);

        if($comment[0]) return $comment[0];
        else return false;
    }

}
class CommentProvider {

    private $id;
    private $comment;

    public function __construct($id) {

        $this->id = (int)$id;
    }

    public function change_active($isdelete) {

        if ( !$this->get_comment() )
            return FALSE;
        if($isdelete == 1)
            $isdelete = 0;
        else
            $isdelete = 1;
        $sql = 'UPDATE ' . PREFIX . 'comments SET `isdelete` = '.$isdelete.'
                WHERE id = '.$this->id.' LIMIT 1';

        $result = affectedRowsQuery($sql);

        return $result;
    }

    public function get_comment() {
        
        $sql = 'SELECT * FROM `' . PREFIX . 'comments`  WHERE `id` = '.$this->id;
        $result = get_bd_data($sql);
        $this->comment =$result[0];

        return !empty( $this->comment );
    }
}
class CommentRatingProvider {

    public $id_user;
    public $id_comment;

    public function __construct($id_user, $id_comment) {
        
        $this->id_user = (int)$id_user;
        $this->id_comment = (int)$id_comment;
    }

    public function make_reiting_voice($voice) {

        if ($this->is_user_make_voice() == false) {

            if($voice == 1) {

            $sql = 'UPDATE ' . PREFIX . 'comments SET rating = rating + 1
                WHERE id = '.$this->id_comment.' AND id_user <> '.$this->id_user;

            $this->set_user2comment_reiting();
            
            $result = affectedRowsQuery($sql);


            return $result;
            } else if ($voice == -1) {

            $sql = 'UPDATE ' . PREFIX . 'comments SET rating = rating - 1
                WHERE id = '.$this->id_comment.' AND id_user <> '.$this->id_user;

            $this->set_user2comment_reiting();

            $result = affectedRowsQuery($sql);

            return $result;
            }

        }
        return false;
    }

    public function set_user2comment_reiting() {

        $sql = 'INSERT INTO ' . PREFIX . 'user2comment_rating (id_user, id_comment)
            VALUES (' . $this->id_user . ',' . $this->id_comment . ')';

        $result = affectedRowsQuery($sql);

        return $result;
    }

    public function is_user_make_voice() {

        $sql = 'SELECT * FROM ' . PREFIX . 'user2comment_rating
            WHERE id_user = ' . $this->id_user . ' AND id_comment = ' . $this->id_comment;

        $isUserMakeVoice = get_bd_data($sql);

        if (count($isUserMakeVoice) > 0)
            return true;
        else
            return false;
    }

    public function  get_reiting() {

        $sql = 'SELECT reiting FROM ' . PREFIX . 'comments  WHERE id = '.$this->id_comment;

        $reiting = get_bd_data($sql);

        if(isset($reiting[0]['reiting']))
            return $reiting[0]['reiting'];
        else
            return false;
    }

}