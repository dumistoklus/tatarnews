<?php

/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 15.04.11
 * Time: 14:43
 * To change this template use File | Settings | File Templates.
 */
class NewsPaperArchiveProvider {

    private $count;
    private $archive;
    private $numberList = array();
    private $lastNumber;
    private $articles = array();
    private $number;
    private $currentTime;
    protected $SQL_SELECT_ARCHIVE;

    function __construct() {
        $this->SQL_SELECT_ARCHIVE = 'SELECT * FROM ' . PREFIX . 'newspaper_archive';
        $this->currentTime = time();
    }

    public function count() {
        if ($this->count == null)
            $this->init_count();

        return $this->count;
    }

    public function archive() {
        if ($this->archive == null)
            $this->init_archive();

        return $this->archive;
    }

    public function number_list($year = null) {

        $this->init_number_list_by_year($year);
        return $this->numberList;
    }

    public function last_number() {

        $this->init_last_number();

        return $this->lastNumber;
    }

    public function articles_by_number($number, $limitStart) {

        $this->init_articles_by_number($number, $limitStart);

        if (count($this->articles) > 0)
            $this->format_articles();

        return $this->articles;
    }

    public function count_by_number($number) {

        $number = (int) $number;
        $sql = 'SELECT COUNT(*) as c FROM ' . PREFIX . 'articles WHERE archive_id = ' . $number;
        $result = get_bd_data($sql);

        if (isset($result[0]['c']))
            return $result[0]['c'];
        else
            return 0;
    }

    public function number_by_id($id) {

        $this->init_number_by_id($id);

        return $this->number;
    }

    private function init_count() {
        $this->count = get_bd_data('SELECT COUNT(*) FROM ' . PREFIX . 'newspaper_archive;');
        $this->count = $this->count[0]['COUNT(*)'];
    }

    private function init_last_number() {

        $sql = 'SELECT * FROM ' . PREFIX . 'newspaper_archive WHERE date_start < ' . $this->currentTime . ' ORDER BY number_total DESC LIMIT 1';

        $result = get_bd_data($sql);

        $this->lastNumber = $result[0];
    }

    protected function init_archive() {
        $this->archive = get_bd_data($this->SQL_SELECT_ARCHIVE);
    }

    private function init_articles_by_number($number, $limitStart) {

        $number = (int) $number;
        $limitStart = (int) $limitStart;

        $sql = 'SELECT * FROM ' . PREFIX . 'articles WHERE archive_id = ' . $number . ' LIMIT ' . $limitStart . ',10';

        $this->articles = get_bd_data($sql);
    }

    private function format_articles() {

        $sql = 'SELECT * FROM ' . PREFIX . 'cats';

        $cats = get_bd_data($sql);
        foreach ($cats as $cat) {
            $categories[$cat['id']] = $cat['name'];
        }

        foreach ($this->articles as $article) {

            $article['category'] = $categories[$article['cat']];
            $formatArticles[] = $article;
        }
        $this->articles = $formatArticles;
    }

    private function init_number_by_id($id) {

        $id = (int) $id;

        $sql = 'SELECT * FROM ' . PREFIX . 'newspaper_archive
        WHERE id = ' . $id . ' LIMIT 1';

        $result = get_bd_data($sql);

        $this->number = $result[0];
    }

    private function init_number_list_by_year($year) {

        if ($year == null)
            $year = date('Y', time());
        $yearStart = strtotime($year . '-01-01');
        $yearEnd = strtotime($year . '-12-31');
        $sql = 'SELECT * FROM ' . PREFIX . 'newspaper_archive
        WHERE date_start < ' . $yearEnd . ' AND date_start > ' . $yearStart . ' AND date_start < ' . $this->currentTime . ' ORDER BY number DESC';

        $this->numberList = get_bd_data($sql);
    }

}