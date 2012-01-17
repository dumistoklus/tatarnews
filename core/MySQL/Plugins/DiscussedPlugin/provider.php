<?php

class DiscussedPluginProvider {

    private $discussedArticles = array();
    private $thisWeekTimestamp;

    public function __construct() {

        $this->thisWeekTimestamp = time() - 7 * 24 * 60 * 60;

        $this->discussedArticles = $this->get_discussed_articles();
    }

    public function discussed_articles() {

        return $this->discussedArticles;
    }

    private function get_discussed_articles() {

        $sql = 'SELECT id, header, cnt.total FROM '.PREFIX.'articles art
            INNER JOIN (SELECT id_article, created, COUNT(*) as total FROM '.PREFIX.'comments GROUP BY id_article ORDER BY total DESC) cnt ON art.id = cnt.id_article
                WHERE cnt.created > ' . $this->thisWeekTimestamp . ' LIMIT 10';

        $result = get_bd_data($sql);

        return $result;
    }

}

?>
