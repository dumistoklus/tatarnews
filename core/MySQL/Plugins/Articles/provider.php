<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class ArticleProvider
{
    private $article = array();
    private $article_id;
    function  __construct($id) {

        $this->article_id = (int)$id;

        $article = get_bd_data('SELECT art.id, art.date, art.rating, cat.name AS cat, cat.id AS cat_id, art.header, art.preview, art.lid, art.content, art.source, art.image FROM '.PREFIX.'articles art '.
                               'INNER JOIN '.PREFIX.'cats cat ON cat.id = art.cat WHERE art.id='.$this->article_id.' LIMIT 1');
        if(isset($article[0]))
        {
            $this->article = $article[0];
        }
        else
        {
            $this->article = array('id' => '', 'date' => '', 'cat' => '', 'header' => '', 'preview' => '', 'lid' => '', 'content' => '', 'source'=> '', 'image' => '', 'rating' => '');
        }
    }

    public function article()  {
        return $this->article;
    }

    public function tags()
    {
        $tags_sql = 'SELECT ta.article_id, t.id, t.name FROM '.PREFIX.'article_tags ta '.
                    'INNER JOIN '.PREFIX.'tags t ON t.id = ta.tag_id WHERE ta.article_id = '.$this->article_id;

        $bd_tags = get_bd_data($tags_sql);

        $tags = array();

        foreach($bd_tags as $tag) {
            $tags[] = array('name' => $tag['name'], 'id' => $tag['id']);
        }

        return $tags;
    }
}

class UpdateArticleProvider {

    public function __construct( $article_id ){
        $this->article_id = (int)$article_id;
    }

    public function PlusComment(){
        $sql = 'UPDATE `'.PREFIX.'articles` SET `comments_count` = `comments_count` + 1 WHERE `id` ='.$this->article_id.' LIMIT 1';
        $result = affectedRowsQuery($sql);

        return ($result > 0 );
    }

    public function MinusComment(){
        $sql = 'UPDATE `'.PREFIX.'articles` SET `comments_count` = `comments_count` - 1 WHERE `id` ='.$this->article_id.' LIMIT 1';
        $result = affectedRowsQuery($sql);

        return ($result > 0 );
    }
}

class ListOfArticlesProvider
{
    protected $articles_list;
    protected $articles_tags;
    protected $page_start;
    protected $page_limit = 10;
    protected $sql_appends = '';
    protected $sql_count_appends = '';
    protected $sql_tables_appends;

    protected $articles_ids;

    protected $articles_count;

    public function __construct($page)
    {
        $this->init($page);
    }

    public function articles()
    {
        return $this->articles_list;
    }

    public function count() {
        if($this->articles_count === null)
            $this->init_count();

        return $this->articles_count;
    }

    public function tags($aid) {
        if(isset($this->articles_tags[$aid]))
            return $this->articles_tags[$aid];
        return array();
    }
    
    protected function init($page)
    {
        $this->page_start = ($page - 1) * $this->page_limit;
        $this->init_articles();
        $this->init_tags();
    }

    protected function init_articles()
    {
        $articles_sql = 'SELECT art.id, art.`comments_count`, art.date, art.image, art.header, cat.id AS cat_id, art.preview, art.source, cat.name AS category FROM `'.PREFIX.'articles` art '.
                        'INNER JOIN '.PREFIX.'cats cat ON cat.id = art.cat '.$this->sql_tables_appends.
                        $this->sql_appends.' ORDER BY art.date DESC LIMIT '.$this->page_start. ', '.$this->page_limit;
        $this->articles_list = get_bd_data($articles_sql);

        $this->articles_ids = '';

        foreach ($this->articles_list as $article) {
            $this->articles_ids .= $article['id'].',';
        }

        $this->articles_ids = substr($this->articles_ids, 0, -1);
    }

    protected function init_tags()
    {
        $tags_sql = 'SELECT ta.article_id, t.id, t.name FROM '.PREFIX.'article_tags ta '.
                    'INNER JOIN '.PREFIX.'tags t ON t.id = ta.tag_id WHERE ta.article_id IN ('.$this->articles_ids.')';

        $this->articles_tags = get_bd_data($tags_sql);

        $tags = array();

        foreach($this->articles_tags as $tag) {
            $tags[ $tag['article_id'] ][] = array('name' => $tag['name'], 'id' => $tag['id']);
        }

        $this->articles_tags = $tags;
    }

    protected function init_count()
    {
        $count_sql = 'SELECT COUNT(*), id FROM '.PREFIX.'articles'.$this->sql_count_appends;
        $count = get_bd_data($count_sql);
        $this->articles_count = $count[0]['COUNT(*)'];
    }
}

class ListOfArticlesByCatProvider extends ListOfArticlesProvider
{
    private $article_cat;
    
    function __construct($page, $article_cat)
    {
        $this->article_cat = $article_cat;

        if($this->article_cat > 0)
        {
            $this->sql_appends = 'WHERE cat.id = '.$this->article_cat;
            $this->sql_count_appends = ' WHERE cat = '. $this->article_cat;
        }

        parent::__construct($page);
    }
}

class ListOfArticlesByTagProvider extends ListOfArticlesProvider
{
    private $article_tag;

    function __construct($page, $article_tag)
    {
        $this->article_tag = $article_tag;

        if($this->article_tag > 0)
        {
            $this->sql_tables_appends = 'INNER JOIN '.PREFIX.'article_tags tg ON tg.tag_id = '.$article_tag. ' AND art.id = tg.article_id ';
            $this->sql_count_appends = ' art INNER JOIN '.PREFIX.'article_tags tg ON tg.tag_id = '.$article_tag. ' AND art.id = tg.article_id  WHERE tg.tag_id = '. $this->article_tag;
        }

        parent::__construct($page);
    }
}

class ListOfArticlesByLikeProvider extends ListOfArticlesProvider
{
    private $like;

    function __construct($page, $like)
    {
        $this->like = $like;

        if($this->like > 0)
        {
            $this->sql_tables_appends = 'INNER JOIN '.PREFIX.'user2articles_rating uar ON uar.id_article = art.id WHERE uar.id_user = '.$this->like.' AND uar.voice =1';
            $this->sql_count_appends = ' art INNER JOIN '.PREFIX.'user2articles_rating uar ON uar.id_article = art.id WHERE uar.id_user = '.$this->like.' AND uar.voice =1';
        }

        parent::__construct($page);
    }
}
?>
