<?php

class PageManipulatorProvider {

    public function get_page_where_id( $id ) {

        $banners = get_bd_data('SELECT `page_id`, `content`, `title`, `keywords`, `description` FROM `'.PREFIX.'custom_pages` WHERE `page_id` = '.$id.' LIMIT 1');

        return $banners;
    }
}

class CustomPageProvider {

    public function create_custom_page( $page, $user_id ) {

        $user_id = (int)$user_id;
        $page = $this->escape_page( $page );

        $sql = 'INSERT INTO `'.PREFIX.'custom_pages` (`title`, `description`, `keywords`, `content`, `creator`)
        VALUES ("'.$page['title'].'", "'.$page['description'].'", "'.$page['keywords'].'", "'.$page['content'].'", '.$user_id.' )';

        if ( affectedRowsQuery($sql) )
            return mysql_insert_id();

        return FALSE;
    }

    public function edit_custom_page( $page, $custom_page_id ) {

        $page = $this->escape_page( $page );

        $sql = 'UPDATE `'.PREFIX.'custom_pages` SET `title` = "'.$page['title'].'", `description` = "'.$page['description'].'", `keywords` = "'.$page['keywords'].'", `content` = "'.$page['content'].'"
                WHERE `page_id` = '.$custom_page_id;
        //var_dump($sql);
        $result = affectedRowsQuery($sql);

        return $result > 0;
    }
    
    public function delete_pages($ids) {
        
        if (count($ids) > 0) {
            foreach ($ids as $id) {
                $idArray[] = (int) $id;
            }
            $idString = implode(' , ', $idArray);            
        $sql = 'DELETE FROM ' . PREFIX . 'custom_pages WHERE page_id IN (' . $idString . ')';

        return affectedRowsQuery($sql);
        }
        return 0;
    }

    private function escape_page( $page ) {

        $page['title'] = mysql_real_escape_string( $page['title'] );
        $page['description'] = mysql_real_escape_string( $page['description'] );
        $page['keywords'] = mysql_real_escape_string( $page['keywords'] );
        $page['content'] = mysql_real_escape_string( $page['content'] );

        return $page;
    }
}

class CustomPageListProvider {

    private $count;
    private $pages;
    private $SQL_SELECT_CUSTOM_PAGES;

    function __construct($start = 0, $limit = 0) {

        $start = (int) $start;
        $limit = (int) $limit;

        $this->SQL_SELECT_CUSTOM_PAGES = 'SELECT * FROM ' . PREFIX . 'custom_pages ORDER BY page_id DESC';

        if ($start == 0 && $limit == 0)
            $this->SQL_SELECT_CUSTOM_PAGES = $this->SQL_SELECT_CUSTOM_PAGES;
        else
            $this->SQL_SELECT_CUSTOM_PAGES = $this->SQL_SELECT_CUSTOM_PAGES . ' LIMIT ' . $start . ', ' . $limit;
    }

    public function count() {
        if ($this->count == null)
            $this->init_count();

        return $this->count;
    }

    public function pages() {
        if ($this->pages == null)
            $this->init_pages();

        return $this->pages;
    }

    private function init_count() {

        $this->count = get_bd_data('SELECT COUNT(*) FROM ' . PREFIX . 'custom_pages');
        
        $this->count = $this->count[0]['COUNT(*)'];
    }

    protected function init_pages() {

        $this->pages = get_bd_data($this->SQL_SELECT_CUSTOM_PAGES);
    }

}