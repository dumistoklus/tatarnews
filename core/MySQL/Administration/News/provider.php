<?php

class NewsListAdministrationProvider
{
    private $sql;
    
    function __construct($start = 0, $limit = 0)
    {
        $this->sql = 'SELECT * FROM '.PREFIX.'short_news ORDER BY date DESC ';

        if(is_numeric($limit) && $limit > 0 && is_numeric($start)) {
            $this->sql .= 'LIMIT '.$start.','.$limit;
        }
    }

    function get_list() {
        return get_bd_data($this->sql);
    }

    function count() {
        $count = get_bd_data('SELECT COUNT(*) FROM '.PREFIX.'short_news');

        if(!isset($count[0]['COUNT(*)'])) return 0;

        return $count[0]['COUNT(*)'];
    }
}