<?php

class PopularPluginProvider {

    private $popularArticles = array();
    private $thisWeekTime;

    public function  __construct() {

        $this->thisWeekTime = time() - 7*24*60*60;

        $this->popularArticles = $this->get_popular();
    }

    public function popular() {

        return $this->popularArticles;
    }

    private function get_popular() {

        $sql = 'SELECT * FROM ' . PREFIX . 'articles 
            WHERE date > ' . $this->thisWeekTime.'
                ORDER BY rating DESC LIMIT 10';

        $this->popularArticles = get_bd_data($sql);

        return $this->popularArticles;
    }
}
