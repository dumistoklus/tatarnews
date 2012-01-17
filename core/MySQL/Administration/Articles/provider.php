<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 21.04.11
 * Time: 15:01
 * To change this template use File | Settings | File Templates.
 */
 
class ArticlesCatsProvider
{
    public function cats()
    {
        return get_bd_data('SELECT * FROM '.PREFIX.'cats');
    }
}

class NewArticleProvider
{
    public $header;
    public $lid;
    public $preview;
    public $content;
    public $image;
    public $source;
    public $cat;
    public $date;
    public $archive;

    public function create()
    {
        $header = $this->header = filter_string($this->header);
        $lid =  filter_string($this->lid);
        $preview = mysql_real_escape_string($this->preview);
        $content = mysql_real_escape_string($this->content);
        $image =  filter_string($this->image);
        $source = filter_var($this->source, FILTER_SANITIZE_URL);
        $cat = filter_var($this->cat, FILTER_SANITIZE_NUMBER_INT);
        $date = $this->date = filter_var($this->date, FILTER_SANITIZE_NUMBER_INT);
        $archive = ((int)$this->archive > 0) ? $this->archive : 0;
        $sql = "INSERT INTO ".PREFIX."articles (`date`, `image`, `header`, `preview`, `lid`, `content`, `source`, `cat`, `archive_id`) VALUES ('".($date == '' ? time() : $date)."', '".$image."', '".$header."', '".$preview."', '".$lid."', '".$content."', '".$source."', ".($cat == '' ? '1' : $cat).", ".($archive > 0 ? $archive : '""').");";
        query($sql);
        return mysql_insert_id();
    }
}

class ArticlesListManagerProvider
{
    private $start;
    private $limit;
    private $sql;

    function __construct($start, $limit)
    {
        $this->start = (int)$start;
        $this->limit = (int)$limit;

        $this->sql = "SELECT art.id, art.header, ct.name AS cat_name, art.cat AS cat_id, art.date AS create_date, art.archive_id, CONCAT(na.number, ' (', na.number_total, ')') AS archive_name, ev.article_id IS NOT NULL AS main_event FROM ".PREFIX."articles art
                        LEFT JOIN ".PREFIX."cats ct ON ct.id = art.cat
                        LEFT JOIN ".PREFIX."newspaper_archive na ON na.id = art.archive_id
                        LEFT JOIN ".PREFIX."main_event ev ON ev.article_id = art.id ORDER BY art.id DESC LIMIT ".$this->start.", ".$this->limit;

    }

    function get_list()
    {
        
	return get_bd_data($this->sql);
    }

    function count()
    {
        $count = get_bd_data('SELECT COUNT(*) FROM '.PREFIX.'articles');
        return $count[0]['COUNT(*)'];
    }
}

class ArticleContentProvider {

    private $sql;

    function __construct($aid) {
        $aid = (int)$aid;
        $this->sql = 'SELECT id, header, preview, content, lid, image, source FROM '.PREFIX.'articles WHERE id = '.$aid.' LIMIT 1';
    }

    function article() {
        return get_bd_data($this->sql);
    }
}

class EditArticleProvider {
    private $aid;
    private $image;
    private $header;
    private $lid;
    private $preview;
    private $content;
    private $source;
    private $cat;
    private $archive;
    private $mainevent;
    private $allValid = true;

    function __construct($aid)
    {
        $this->aid = (int)$aid;
    }

    function edit() {

        if(!$this->allValid && $this->aid < 1) return false;

        $sql = 'UPDATE '.PREFIX.'articles SET image="'.$this->image.'", preview="'.$this->preview.'", header="'.$this->header.'", lid="'.$this->lid.'", content="'.$this->content.'", source="'.$this->source.'", cat='.$this->cat.', archive_id='.$this->archive.' WHERE id='.$this->aid.' LIMIT 1';
        if(!query($sql)) return false;

        $allok = true;

        if($this->mainevent == true) {
            DB_Provider::Instance()->loadProvider('Administration.MainEvent');
            $p = new NewMainEventProvider();

            $p->aid = $this->aid;
            $p->date_start = time();
            $p->preview = $this->header;

            $allok = $p->create();
        }

        if($allok) return true;

        return 'not_event';
    }

    public function setArchive($archive)
    {
        if(is_numeric($archive) && $archive > 0)
            $this->archive = $archive;
        else
            $this->allValid = $this->allValid && false;
    }

    public function setCat($cat)
    {
        if(is_numeric($cat) && $cat > 0)
            $this->cat = $cat;
        else
            $this->allValid = $this->allValid && false;
    }

    public function setContent($content)
    {
        $this->content = filter_html_text($content);
    }

    public function setHeader($header)
    {
        $this->header = filter_string($header);
    }

    public function setImage($image)
    {
        $this->image = filter_string($image);
    }

    public function setLid($lid)
    {
        $this->lid = filter_string($lid);
    }

    public function setMainevent($mainevent)
    {
        if($mainevent == 'true')
            $this->mainevent = (bool)$mainevent;
        else
            $this->allValid = $this->allValid && false;
    }

    public function setPreview($preview)
    {
        $this->preview = filter_html_text($preview);
    }

    public function setSource($source)
    {
        $this->source = filter_string($source);
    }
}

class DeleteArticleProvider {

    private $idString = '';

    public function __construct($ids) {

        if (count($ids) > 0) {
            foreach ($ids as $id) {

                $idArray[] = (int) $id;
            }

            $this->idString = implode(' , ', $idArray);
        }
    }

    public function delete_article() {

        $sql = 'DELETE FROM ' . PREFIX . 'articles WHERE id IN (' . $this->idString . ')';

        $result = affectedRowsQuery($sql);

        return $result;
    }

}
