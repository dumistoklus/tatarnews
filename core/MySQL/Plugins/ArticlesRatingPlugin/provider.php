<?php

class ArticlesRatingPluginProvider {

    public $id_user;
    public $id_article;

    public function __construct($id_user, $id_article) {

        $this->id_user = (int) $id_user;
        $this->id_article = (int) $id_article;
    }

    public function make_rating_voice($voice) {

        if ($this->is_user_make_voice() == false) {

            if ($voice == 1) {

                $sql = 'UPDATE ' . PREFIX . 'articles SET rating = rating + 1
                WHERE id = ' . $this->id_article;

                $this->set_user2articles_rating($voice);

                $result = affectedRowsQuery($sql);
                return $result;
                
            } else if ($voice == -1) {

                $sql = 'UPDATE ' . PREFIX . 'articles SET rating = rating - 1
                WHERE id = ' . $this->id_article;

                $this->set_user2articles_rating($voice);

                $result = affectedRowsQuery($sql);

                return $result;
            }
        }
        return false;
    }

    public function set_user2articles_rating($voice) {

        $sql = 'INSERT INTO ' . PREFIX . 'user2articles_rating (id_user, id_article, voice)
            VALUES (' . $this->id_user . ',' . $this->id_article . ', '.$voice.')';

        $result = affectedRowsQuery($sql);

        return $result;
    }

    public function is_user_make_voice() {

        $sql = 'SELECT * FROM ' . PREFIX . 'user2articles_rating
            WHERE id_user = ' . $this->id_user . ' AND id_article = ' . $this->id_article.' LIMIT 1';

        $isUserMakeVoice = get_bd_data($sql);

        if (count($isUserMakeVoice) > 0)
            return true;
        else
            return false;
    }

}
