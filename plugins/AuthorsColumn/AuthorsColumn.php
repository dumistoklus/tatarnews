<?php

class AuthorsColumn implements IPlugin
{
    public static function name() {
        return __CLASS__;
    }

    public static function load($side, $order)
    {
        DB_Provider::Instance()->loadProvider('Plugins.Articles');

        $Provider = new ListOfArticlesByCatProvider(1, 28);

        $View = new AuthorsColumnContainerView($Provider->articles());

        return $View->printColumns();
    }
}

class AuthorsColumnContainerView
{
    private $columns = array();

    function __construct($columnsData)
    {
        $this->columns = $columnsData;
    }

    public function printColumns()
    {
        $header = $this->header();
        $columns = $this->columnsView();

        if($columns)
        {
            return $header . $columns;
        }

        return '';
    }

    private function header()
    {
        return '<div class="zagol-warp">
                    <span class="zagol-link-warp">
                        <a href="/?page=articles&amp;article_cat=28" class="zagol-link">Авторская колонка</a>
                    </span>
                    <div class="zagol-line"></div>
                </div>';
    }

    private function columnsView()
    {
        $columns = '';
        foreach($this->columns as $column)
        {
            $columns .= '<div class="author-column">'.
                        '<h2 class="article-header"><a href="/?page=articles&article_id=' .$column['id']. '">' .$column['header'] . '</a></h2>'
                        . $column['preview'] .
                        '</div>';
        }

        return $columns;
    }
}