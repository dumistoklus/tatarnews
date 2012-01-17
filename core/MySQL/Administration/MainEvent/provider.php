<?php

class NewMainEventProvider
{
    public $aid;
    public $preview;
    public $date_start;
    public $date_end;

    public function create()
    {
        $this->saintize_vars();

        if($this->date_end < $this->date_start)
        {
            $this->date_end = $this->date_start + (60 * 60 * 24 * 7);
        }

        if($this->aid > 0 && $this->preview != '' && $this->date_start > 0) {
            query('TRUNCATE TABLE `'.PREFIX.'main_event`');
            return (bool)query('INSERT INTO '.PREFIX.'main_event (article_id, preview, date_start, date_end) VALUES ("'.$this->aid.'","'.$this->preview.'","'.$this->date_start.'","'.$this->date_end.'");');
        }

        return false;
    }

    private function saintize_vars()
    {
        $this->aid = filter_var($this->aid, FILTER_SANITIZE_NUMBER_INT);
        $this->preview = trim(filter_string($this->preview));
        $this->date_start = filter_var($this->date_start, FILTER_SANITIZE_NUMBER_INT);
        $this->date_end = filter_var($this->date_end, FILTER_SANITIZE_NUMBER_INT);
    }
}