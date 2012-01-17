<?php

class ShortNewsProvider {

    private $news;
    private $count;

    public function get_news_by_limit($limitStart) {

        $limitSatrt = (int) $limitStart;
        if ($limitSatrt == 5)
            $limit = '0 , 5'; 
        else
            $limit = $limitSatrt . ' , 10';

        $sql = 'SELECT * FROM ' . PREFIX . 'short_news ORDER BY `date` DESC LIMIT ' . $limit;

        $this->news = get_bd_data($sql);

        return $this->format_news();
    }

    public function count() {

        $this->get_count();

        return $this->count;
    }

    public function get_news_by_id($id) {

        $id = (int) $id;

        $sql = 'SELECT * FROM ' . PREFIX . 'short_news WHERE id = ' . $id . ' LIMIT 1';

        $oneNews = get_bd_data($sql);
        return $oneNews[0];
    }

    private function format_news() {
        $formatted_news_by_days = array();

        foreach ($this->news as $news) {

            $formatted_news_by_days[date('n:j', $news['date'])][] = array(
                'id' => $news['id'],
                'content' => $news['short_news'],
                'date' => date('G:i', $news['date'])
            );
        }

        return $formatted_news_by_days;
    }

    private function get_count() {

        $sql = 'SELECT COUNT(*) as c FROM ' . PREFIX . 'short_news';

        $result = get_bd_data($sql);

        $this->count = $result[0]['c'];
    }

}

class ManagerShortNewsProvider {

    protected $dataArray = array();
    protected $allOK = true;

    public function __construct() {

        ;
    }

    public function set_shortNews($short_news) {

        $short_news = trim($short_news);

        if (strlen($short_news) > 0)
            $this->dataArray['short_news'] = $short_news;

        else
            $this->allOK = false;
    }

    public function set_text($text) {

        $text = trim($text);

        if (strlen($text) > 0)
            $this->dataArray['text'] = $text;

        else
            $this->allOK = false;
    }

    public function set_date($date) {

        $date = (int) $date;

        if ($date > 0)
            $this->dataArray['date'] = $date;

        else
            $this->dataArray['date'] = time();
    }

}

class NewShortNewsProvider extends ManagerShortNewsProvider {

    public function create_shortNews() {

        if (empty($this->dataArray['date']))
            $this->dataArray['date'] = time();

        if (isset($this->dataArray['short_news']) && isset($this->dataArray['date']) && isset($this->dataArray['text']) && $this->allOK != false) {

            foreach ($this->dataArray as $field => $value) {

                $value = filter_string($value, true);

                $fieldsArray[] = $field;
                $valuesArray[] = '"' . $value . '"';
            }

            $fieldsString = implode(' , ', $fieldsArray);
            $valuesString = implode(' , ', $valuesArray);

            $sql = 'INSERT INTO ' . PREFIX . 'short_news (' . $fieldsString . ') VALUES (' . $valuesString . ')';

            $result = affectedRowsQuery($sql);

            return $result;
        }

        return false;
    }

}

class EditShortNewsProvider extends ManagerShortNewsProvider {

    private $id = 0;

    public function __construct($id) {

        $this->id = (int) $id;

        if ($this->id == 0)
            return false;
    }

    public function update_shortNews() {

        if (count($this->dataArray) > 0 && $this->allOK != false) {

            foreach ($this->dataArray as $field => $value) {

                $value = filter_string($value, true);

                $updateArray[] = $field . '= "' . $value . '"';
            }

            $updateString = implode(' , ', $updateArray);

            $sql = 'UPDATE ' . PREFIX . 'short_news SET ' . $updateString . ' WHERE id = ' . $this->id . ' LIMIT 1';

            $result = affectedRowsQuery($sql);

            return $result;
        }

        return false;
    }

}

class DeleteShortNewsProvider {

    private $idString = '';

    public function __construct($ids) {

        if (count($ids) > 0) {
            foreach ($ids as $id) {

                $idArray[] = (int) $id;
            }

            $this->idString = implode(' , ', $idArray);
        }
    }

    public function delete_news() {

        $sql = 'DELETE FROM ' . PREFIX . 'short_news WHERE id IN (' . $this->idString . ')';

        return affectedRowsQuery($sql);
    }
}
