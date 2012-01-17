<?php

class VideoPluginProvider {

    private $lastVideo = array();
    private $currentTime;

    public function __construct() {

        $this->currentTime = time();
    }

    public function get_last_video() {

        $this->init_last_video();

        return $this->lastVideo;
    }

    private function init_last_video() {

        $sql = 'SELECT * FROM ' . PREFIX . 'videos WHERE date < ' . $this->currentTime . ' ORDER BY date DESC LIMIT 1';

        $result = get_bd_data($sql);

        if (isset($result[0]))
            $this->lastVideo = $result[0];
    }

    public function add_video($link, $date) {

        $link = trim($link);
        $date = trim($date);

        $formatDate = strtotime($date);
        $parsLink = $this->pars_link($link);

        if ($parsLink != false) {

            $proportion = $this->get_proportion($link);

            $sql = 'INSERT INTO ' . PREFIX . 'videos (link , date , proportion)
                VALUES ("' . $parsLink . '" , "' . $formatDate . '" , "' . $proportion . '" )';

            $result = affectedRowsQuery($sql);

            return $result;
        }

        return 0;
    }

    public function delete_video($id) {

        $id = (int) $id;

        if ($id != 0) {

            $sql = 'DELETE FROM ' . PREFIX . 'videos WHERE id = ' . $id . ' LIMIT 1';

            $result = affectedRowsQuery($sql);

            return $result;
        }

        return 0;
    }

    private function pars_link($link) {

        preg_match('/http:\/\/www\.youtube\.com\/watch\?v\=([A-z0-9]+)/', $link, $result);

        if (isset($result[1]))
            return 'http://www.youtube.com/embed/' . $result[1] . '?wmode=transparent';
        else
            return false;
    }

    private function get_proportion($link) {

        $file = file_get_contents($link);

        preg_match('/<meta property="og:video:width" content="([0-9]+)/', $file, $width);
        preg_match('/<meta property="og:video:height" content="([0-9]+)/', $file, $height);

        if (isset($width[1]) && $width[1] != 0 && isset($height[1]) && $height[1] != 0)
            $proportion = $height[1] / $width[1];

        else
            $proportion = 0.75;

        return $proportion;
    }

}

class VideoListPluginProvider {

    private $videoList = array();
    private $count = 0;
    private $isAdmin;
    private $currentTime;

    public function __construct($isAdmin = null) {

        $this->isAdmin = $isAdmin;
        $this->currentTime = time();
    }

    public function get_video_list($limitStart = 0) {

        $this->init_video_list($limitStart);

        return $this->videoList;
    }

    public function get_count() {

        $this->init_count_videos();

        return $this->count;
    }

    private function init_video_list($limitStart = 0, $limitEnd = 10) {

        if ($this->isAdmin == null)
            $where = 'WHERE date < ' . $this->currentTime;
        else
            $where = '';

        $sql = 'SELECT * FROM ' . PREFIX . 'videos ' . $where . ' ORDER BY date DESC LIMIT ' . $limitStart . ' , 10';

        $this->videoList = get_bd_data($sql);
    }

    private function init_count_videos() {

        if ($this->isAdmin == null)
            $where = 'WHERE date < ' . $this->currentTime;
        else
            $where = '';

        $sql = 'SELECT COUNT(*) as c FROM ' . PREFIX . 'videos ' . $where;

        $result = get_bd_data($sql);

        if (isset($result[0]['c']))
            $this->count = $result[0]['c'];
    }

}