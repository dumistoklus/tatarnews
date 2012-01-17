<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Articles implements IPlugin
{
    public static function name() {
        return __CLASS__;
    }

    public static function load($side, $order) {

        DB_Provider::Instance()->loadProvider('Plugins.Articles');

        URIManager::clean('article_id');
        URIManager::clean('a_nav');

        if(self::isSingleMode())
        {
            return self::singleMode();
        }
        else if(self::isArticleCat())
        {
            return self::listingModeByCat((int)$_GET['article_cat']);
        }
        else if (self::isArticleTag())
        {
            return self::listingModeByTag((int)$_GET['tag_id']);
        }
        else if (self::isArticleLike())
        {
            return self::listingModeByLike((int)$_GET['like']);
        }
        else
        {
            return self::listingMode();
        }
    }

    private static function isSingleMode()
    {
        $article_id = isset($_GET['article_id']) ? (int)$_GET['article_id'] : 0;

        return (PageController::load()->name() == 'articles' && $article_id > 0 && !isset($_GET['article_cat']));
    }

    private static function isArticleCat()
    {
        return isset($_GET['article_cat']);
    }

    private static function isArticleLike()
    {
        return isset($_GET['like']);
    }

    private static function isArticleTag()
    {
        return isset($_GET['tag_id']);
    }

    private static function singleMode()
    {
        $provider = new ArticleProvider((int)$_GET['article_id']);

        $view = new ArticleView($provider);

        return $view->print_article();
    }

    private static function listingMode()
    {
        $page = (isset($_GET['a_nav'])) ? (int)$_GET['a_nav'] : 1;

        $view = new ListOfArticlesView($page);

        $output = $view->print_articles();
        $output .= $view->print_navbar();

        return $output;
    }

    private static function listingModeByCat($article_cat)
    {
        $page = (isset($_GET['a_nav'])) ? (int)$_GET['a_nav'] : 1;

        $view = new ListOfArticlesViewByCategory($page, $article_cat);

        $output = $view->print_articles();
        $output .= $view->print_navbar();

        HeaderViewData::init()->set_title( $view->get_title(), TRUE );

        return $output;

    }
    
    protected static function listingModeByTag($article_tag)
    {
        $page = (isset($_GET['a_nav'])) ? (int)$_GET['a_nav'] : 1;

        $view = new ListOfArticlesViewByTag($page, $article_tag);

        $output = $view->print_articles();
        $output .= $view->print_navbar();

        return $output;
    }

    protected static function listingModeByLike($like)
    {
        $page = (isset($_GET['a_nav'])) ? (int)$_GET['a_nav'] : 1;

        $view = new ListOfArticlesViewByLike($page, $like);

        $output = $view->print_articles();
        $output .= $view->print_navbar();

        return $output;
    }
}

class ArticleBasicView
{
    private static $content;
    private static $lid;
    private static $header;
    private static $tags;
    private static $images;
    private static $category;
    private static $category_id;
    private static $source;
    private static $comments_link = '';
    private static $comments_count = '';
    private static $firstInList = null;
    private static $create_time;
    private static $show_comments_counter = false;
    private static $comments_counter = 0;
    private static $article_rate = 0;

    public static function article()
    {
        $uri = URIManager::clean_page('articles', 'article_cat', 'article_id');

        $image = '';
        $class_if_image = '';
        $source = '';
        $label = '';
        $lid = '';

        if(self::have_images())
        {
            $image = '<div class="article-image">'.
                        '<img src="/images/articles_header/'.self::$images.'" />'.
                     '</div>';
            $class_if_image = ' left-isset-image';
        }

        if(self::have_source())
        {
            $source = '<a href="http://'.self::$source.'" class="article-link-out" rel="nofollow">'.mb_substr(self::$source, 0, 35).( mb_strlen( self::$source ) <=35 ? '' : '...' ).'</a>';
        }

        if(self::is_first())
        {
            $label = '<a href="/?page=articles">Статьи</a>';
        }

        if ( self::has_show_comments_counter() )
        {
			$right_footer = '<a href="'.self::$comments_link.'#comments" class="article-comment">'.self::$comments_count.'</a>';
        }
        else
        {

			$article_rate = ( self::$article_rate > 0 ) ? '+'.self::$article_rate : self::$article_rate;

			$right_footer = '<a href="#" class="article-like">&uarr;</a> '.
		        '<span class="article-like-num">'.
		        $article_rate.
		        '</span>'.
		    '<a href="#comments" class="article-dislike">&darr;</a>';
        }

        if ( self::have_lid() ) {
            $lid = '<div class="article-lit">'.self::$lid.'</div>';
        }

        $create_time = ( self::$create_time ) ? FormatTime::ru_date( 'j F Y', self::$create_time ).'&nbsp;&nbsp;&nbsp;&nbsp;' : '';


        $output = '<div class="article">'.
                       '<div class="zagol-warp">'.
                            '<span class="zagol-link-warp">'.
                            '<a href="'.$uri.'&amp;article_cat='.self::$category_id.'" class="zagol-link">'.self::$category.'</a>'.
                            '</span>'.
                                '<div class="zagol-line">'.
                                    $label.
                                '</div>'.
                        '</div>'.$image.
                    	'<div class="article-text'.$class_if_image.'">'.
                       	     '<h2 class="article-header">'.self::$header.'</h2>'.
                       		 html_entity_decode($lid, ENT_QUOTES).
                       		 self::$content.
                   		'</div>'.
	                    $right_footer.
	                    '<div class="article-tags">'.self::$tags.'&nbsp;</div>'.
	                    '<div class="article-author">
		                    <div class="article-footer">'.$create_time.
		                    $source.
		                    '</div>'.
	                 	'</div>'.
	              '</div>';
        self::clean();
        return $output;
    }

    public static function content($content)
    {
        self::$content = $content;
    }

    public static function lid($lid)
    {
        self::$lid = $lid;
    }

    public static function header($header)
    {
        self::$header = $header;
    }

    public static function tags($tags)
    {
        $tags_t = '';

        foreach($tags as $tag)
        {
            $tags_t .= '<a href="?page=articles&amp;tag_id='.$tag['id'].'">'.$tag['name'].'</a>, ';
        }

        self::$tags = substr($tags_t, 0, -2);
    }

    public static function comments_count($count)
    {
        self::$comments_count = $count;
    }

    public static function images($images)
    {
        self::$images = $images;
    }

    public static function category($category)
    {
        self::$category = $category;
    }

    public static function category_id($category_id)
    {
        self::$category_id = $category_id;
    }

    public static function source($source)
    {
        self::$source = $source;
    }

    public static function create_time($time)
    {
        self::$create_time = $time;
    }

    public static function comments_link($link)
    {
        self::$comments_link = $link;
    }

    public static function set_show_comments_counter( $comment_status ) {
    	self::$show_comments_counter = $comment_status;
    }

    public static function set_article_rate( $rate ) {
    	self::$article_rate = $rate;
    }

    public static function maybe_it_first_in_list()
    {
        if ( self::$firstInList === null )
            self::$firstInList = true;
        else self::$firstInList = false;
    }

    public static function clean()
    {
        self::$content = null;
        self::$lid = null;
        self::$header = null;
        self::$tags = null;
        self::$images = null;
        self::$category = null;
        self::$category_id = null;
        self::$source = null;
        self::$comments_link = '';
        self::$create_time = 0;
        self::$show_comments_counter = false;
        self::$comments_counter = 0;
        self::$article_rate = 0;
    }

    private static function is_first()
    {
        return (bool)self::$firstInList;
    }

    private static function have_lid()
    {
        return !empty( self::$lid );
    }

    private static function have_images()
    {
        return !empty(self::$images);
    }

    private static function have_source()
    {
        return (self::$source != '');
    }

    private static function has_show_comments_counter() {
        return self::$show_comments_counter;
    }
}

class ArticleView
{
    private $article;
    private $tags;

    function __construct($provider)
    {
        $this->article = $provider->article();
        $this->tags = $provider->tags();

        HeaderViewData::init()->set_title($this->article['header'].' - статья на TatarNews.ru', true);
        env::vars()->article_id = $this->article['id'];
    }

    public function print_article()
    {
        if(!isset($this->article['cat_id']))
        {
            goto404();
            return '';
        }

        ArticleBasicView::images($this->article['image']);
        ArticleBasicView::tags($this->tags);
        ArticleBasicView::category_id($this->article['cat_id']);
        ArticleBasicView::category($this->article['cat']);
        ArticleBasicView::header($this->article['header']);
        ArticleBasicView::lid($this->article['lid']);
        ArticleBasicView::source($this->article['source']);
        ArticleBasicView::maybe_it_first_in_list();
        ArticleBasicView::content($this->article['content']);
        ArticleBasicView::create_time($this->article['date']);
        ArticleBasicView::set_article_rate($this->article['rating']);


        return ArticleBasicView::article();
    }
}
class ListOfArticlesView
{
    protected $provider;
    protected $title;

    function __construct($start)
    {
        $this->provider = new ListOfArticlesProvider($start);
    }

    public function print_articles() {


        $articles = $this->provider->articles();

        $output = '<div class="articles">';

        $url = URIManager::clean_page('articles', 'article_cat', 'article_id', 'tag_id');
        foreach($articles as $article) {

            ArticleBasicView::images($article['image']);
            ArticleBasicView::tags($this->get_tags($article['id']));
            ArticleBasicView::category_id($article['cat_id']);
            ArticleBasicView::category($article['category']);
            ArticleBasicView::header('<a href="'.$url.'&amp;article_id='.$article['id'].'">'.$article['header'].'</a>');
            ArticleBasicView::content($article['preview']);
            ArticleBasicView::comments_link($url.'&amp;article_id='.$article['id']);
            ArticleBasicView::create_time($article['date']);
            ArticleBasicView::set_show_comments_counter(true);
            ArticleBasicView::maybe_it_first_in_list();
            ArticleBasicView::comments_count($article['comments_count']);
            $this->title = $article['category'];

            $output .= ArticleBasicView::article();



        }

        return $output.'</div>';
    }

    public function print_navbar(  )
    {
        $cur_page = (isset($_GET['a_nav'])) ? $_GET['a_nav'] : 1 ;
        $total = $this->provider->count();

        $uri = URIManager::clean_page('articles').'&a_nav=';

        PluginModule::load('PageNavBar');

        $pnb = new PageNavBar($uri, $total, $cur_page);

        return $pnb->get();

    }

    public function get_title() {
        return !empty( $this->title ) ? $this->title.' на TatarNews.ru' : '';
    }

    protected function get_tags($aid)
    {
        return $this->provider->tags($aid);
    }
}

class ListOfArticlesViewByCategory extends ListOfArticlesView
{
    function __construct($start, $article_cat)
    {
        $this->provider = new ListOfArticlesByCatProvider($start, $article_cat);
    }
}

class ListOfArticlesViewByTag extends ListOfArticlesView
{
    function __construct($start, $article_tag)
    {
        $this->provider = new ListOfArticlesByTagProvider($start, $article_tag);
    }
}

class ListOfArticlesViewByLike extends ListOfArticlesView
{
    function __construct($start, $like)
    {
        $this->provider = new ListOfArticlesByLikeProvider($start, $like);
    }
}