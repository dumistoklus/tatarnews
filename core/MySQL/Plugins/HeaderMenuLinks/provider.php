<?php

class HeaderMenuLinksProvider {

    protected $sql_all;

    function __construct()
    {
        $this->sql_all = 'SELECT `id`, `name` FROM `' . PREFIX . 'cats` ORDER BY `order`, `name`';
    }

    public function get_all_cats() {

        return get_bd_data($this->sql_all);
    }

    function count() {
        $c = get_bd_data('SELECT COUNT(*) FROM '.PREFIX.'cats');

        if(!isset($c[0]['COUNTS(*)'])) return 0;

        return $c[0]['COUNTS(*)'];
    }

}

class CatsAdministrationProvider extends HeaderMenuLinksProvider
{
    protected $sql_with_limit;
    
    function __construct($start = 0, $limit = 0) {
        parent::__construct();

        if(!is_numeric($limit) && !is_numeric($start))
        {
            $limit = 10;
            $start = 0;
        }

        $this->sql_with_limit = $this->sql_all.' LIMIT '.$start.', '.$limit;
    }

    function get_with_limit() {
        return get_bd_data($this->sql_with_limit);
    }
}