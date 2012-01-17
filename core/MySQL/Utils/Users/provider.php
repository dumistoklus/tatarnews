<?php

class UserInfoProvider
{
    private $uid;
    private $user_array;

    function __construct($uid)
    {
        if(is_numeric($uid) && $uid > 0)
        {
            $this->uid = (int)$uid;
            $this->init_user();
        }
        else $this->user_not_isset(); 
    }

    public function user_data()
    {
        return $this->user_array;
    }

    private function init_user()
    {
        $info = get_bd_data('SELECT * FROM '.PREFIX.'users WHERE id='.$this->uid.' LIMIT 1');
        if(isset($info[0])) {
            $this->user_array = $info[0];

        }
        else
        {
            $this->user_not_isset();
        }

    }

    private function user_not_isset()
    {

        $this->user_array = new UnknownUserInfoProvider();
    }
}

class ArticlesLike {

    private $uid = 0;

    function __construct($uid) {
        
        if (is_numeric($uid) && $uid > 0) {

            $this->uid = $uid;
        }
    }

    public function get_articles_like() {

        $sql = 'SELECT art.id, art.header,art.date FROM ' . PREFIX . 'user2articles_rating uar
                    INNER JOIN ' . PREFIX . 'articles art ON art.id = uar.id_article
                    WHERE uar.id_user = ' . $this->uid . ' AND  uar.voice = 1  ORDER BY art.date DESC';
        
        $articlesLike = get_bd_data($sql);

        return $articlesLike;
    }

}

class UnknownUserInfoProvider implements ArrayAccess
{
    public function offsetSet($key, $value) {}

    public function offsetUnset($key) {}

    public function offsetGet($key)
    {
        if($key == 'birth_date') return '0000-00-00';
        return false;
    }

    public function offsetExists($key) {
        if($key == 'birth_date') return true;
    }
}