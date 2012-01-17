<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 15.04.11
 * Time: 14:23
 * To change this template use File | Settings | File Templates.
 */

DB_Provider::Instance()->loadProvider('Plugins.NewsPaperArchive');

class NewsPaperArchiveAdministrationProvider extends NewsPaperArchiveProvider
{
    function __construct($start = 0, $limit = 0)
    {

        $start = (int)$start;
        $limit = (int)$limit;
        
        parent::__construct();
        if($start == 0 && $limit == 0)
            $this->SQL_SELECT_ARCHIVE = $this->SQL_SELECT_ARCHIVE.' ORDER BY number_total DESC';
        else
            $this->SQL_SELECT_ARCHIVE = $this->SQL_SELECT_ARCHIVE.' ORDER BY number_total DESC LIMIT '.$start.', '.$limit;
    }
}

class NewArchiveProvider
{
    public $number;
    public $number_total;
    public $date_start;
    public $date_end;

    public function create()
    {
        if($this->check_vars()) {
            $r = query('INSERT INTO '.PREFIX.'newspaper_archive (number,number_total,date_start,date_end) VALUES ("'.$this->number.'","'.$this->number_total.'","'.$this->date_start.'","'.$this->date_end.'")');
            if($r) return 1;
        }

        return 0;
    }

    protected function check_vars()
    {
        $this->number = (int)$this->number;
        $this->number_total = (int)$this->number_total;
        $this->date_start = filter_var($this->date_start, FILTER_SANITIZE_NUMBER_INT);
        $this->date_end = filter_var($this->date_end, FILTER_SANITIZE_NUMBER_INT);

        $success = true;

        $success = $success && ($this->number > 0);
        $success = $success && ($this->number_total > 0);
        $success = $success && ($this->date_start > 0);
        $success = $success && ($this->date_end > $this->date_start);

        return $success;
    }
}

class EditArchiveProvider extends NewArchiveProvider
{
    protected $id;

    function __construct($id)
    {
        $this->id = (int)$id;
    }

    function create()
    {
        if($this->check_vars() && $this->id > 0)
        {
            $r = query('UPDATE '.PREFIX.'newspaper_archive SET number = "'.$this->number.'" ,number_total = "'.$this->number_total.'",date_start = "'.$this->date_start.'",date_end = "'.$this->date_end.'" WHERE id = "'.$this->id.'"');
            if($r) return 1;
        }
        return 0;
    }
}