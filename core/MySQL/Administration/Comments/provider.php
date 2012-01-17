<?php

class CommentsAdminProvider {
    private $count;
    private $comments;
    private $SQL_SELECT_COMMENTS;

    function __construct($start = 0, $limit = 0) {

        $start = (int) $start;
        $limit = (int) $limit;

        $this->SQL_SELECT_COMMENTS = 'SELECT * FROM ' . PREFIX . 'comments ORDER BY created DESC';

        if ($start == 0 && $limit == 0)
            $this->SQL_SELECT_COMMENTS = $this->SQL_SELECT_COMMENTS;
        else
            $this->SQL_SELECT_COMMENTS = $this->SQL_SELECT_COMMENTS . ' LIMIT ' . $start . ', ' . $limit;
    }

    public function count() {
        if ($this->count == null)
            $this->init_count();

        return $this->count;
    }

    public function comments() {
        if ($this->comments == null)
            $this->init_comments ();
        
        return $this->comments;
    }

    private function init_count() {

        $this->count = get_bd_data('SELECT COUNT(*) FROM ' . PREFIX . 'comments');
        $this->count = $this->count[0]['COUNT(*)'];
    }

    protected function init_comments() {

        $this->comments = get_bd_data($this->SQL_SELECT_COMMENTS);
    }

}
