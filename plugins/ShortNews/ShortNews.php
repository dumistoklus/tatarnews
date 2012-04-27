<?php

class ShortNewsList {

    private $news;
    private $provider;
    private $id;
    private $count;

    public function __construct($id=null) {

        if ($id != null)
            $this->id = $id;

        DB_Provider::Instance()->loadProvider('Plugins.ShortNews');
        
        $this->provider = new ShortNewsProvider();
    }

    public function compact_news() {
        
        $this->news =$this->provider->get_news_by_limit(5);

        return $this->news;
    }

    public function news_list($limitStart) {

        $this->news =$this->provider->get_news_by_limit($limitStart);

        return $this->news;
    }

    public function news_one() {

        $this->news =$this->provider->get_news_by_id($this->id);

        return $this->news;
    }

    public function count_news() {

        $this->count = $this->provider->count();

        return $this->count;
    }
}


class ShortNews implements IPlugin
{
    public static function  name() {
        return __CLASS__;
    }

    public static function  load($side, $order) {

        if ($side == Side::CENTER && isset($_GET['id'])) {

            return ShortNewsView::print_news_one(new ShortNewsList($_GET['id']));
        } else if ($side == Side::RIGHT) {

            return ShortNewsView::print_compact_news(new ShortNewsList());
        } else if ($side == Side::CENTER){
            $list = ShortNewsView::print_news_list(new ShortNewsList());
            return $list;
        }
    }
}

class ShortNewsView
{
    public static function print_compact_news(ShortNewsList $shortNewsList) {

        $news_array = $shortNewsList->compact_news();

        if(empty($news_array)) return '';
        
        $output = '<div class="short-news-in-right-block">'.
                    '<div class="zagol-warp">'.
                        '<span class="zagol-link-warp">'.
                            '<a href="/?page=shortnews" class="zagol-link">Короткие новости</a></span>'.
                       '<div class="zagol-line"></div>'.
                    '</div>';

        foreach($news_array as $date => $news) {
            list($month, $day) = explode(':', $date);
            
            $output .= '<div class="short-news-date">'.$day.' '.FormatTime::ru_month($month).'</div>';

            foreach($news as $item) {
                $output .= '<div class="short-news">'.
                            '<span class="short-news-time">'.$item['date'].'</span>'.
                            '<a href="/?page=shortnews&amp;id='.$item['id'].'" class="short-news-link">'.$item['content'].'</a>'.
                            '</div>';
            }
        }

        return $output.'</div>';
    }

    public static function print_news_list(ShortNewsList $shortNewsList) {

        HeaderViewData::init()->set_title('Короткие новости - TatarNews.ru');

        if (isset($_GET['a_nav']) && (int)$_GET['a_nav'] != 0) {
            $currentpage = (int)$_GET['a_nav'];
        } else
            $currentpage = 1;
        
        $limitStart = $currentpage*10-10;

        $news_array = $shortNewsList->news_list($limitStart);

        if(empty($news_array))

            return '
            <div id="shortnews">
                <div class="zagol-warp">
                    <div class="zagol-line">
                        Короткие новости
                    </div>
                </div>
                <div style="text-align: center;" class="cabinet-myname grey"">Нет новостей!</div>
            </div>';

        $output = '<div id="shortnews">
                        <div class="zagol-warp">
                            <div class="zagol-line">
                                Короткие новости
                            </div>
                        </div>
                        <ul class="nolist">';

        foreach($news_array as $date => $news) {

             list($month, $day) = explode(':', $date);

            $output .= '<li class="short-news-date">
                            '.$day.' '.FormatTime::ru_month($month).'
                        </li>';

            foreach($news as $item) {
                $output .= '
                        <li class="cf shortmews-one">
                            <div class="shortnews-time">
                                '.$item['date'].'
                            </div>
                            <div class="shortnews-text">
                                <a href="/?page=shortnews&amp;id='.$item['id'].'" class="short-news-link">
                                '.$item['content'].'
                                </a>
                            </div>
                        </li>';
            }
        }

        $output .= '</ul>
                </div>';

        $currentURI = '/?page=shortnews&a_nav=';

        PluginModule::load('PageNavBar');

        $navbar = new PageNavBar($currentURI, $shortNewsList->count_news(), $currentpage);

        return $output.$navbar->get();
    }

    public static function print_news_one(ShortNewsList $shortNewsList) {

        $news = $shortNewsList->news_one();

        $title = FormatString::cutText($news["short_news"],100);

        HeaderViewData::init()->set_title($title.'... - TatarNews.ru');

        $output = '
            <div id="shortnews">
                <div class="zagol-warp">
                    <span class="zagol-link-warp">
                        <a href="/?page=shortnews" class="zagol-link">Короткие новости</a>
                    </span>
                    <div class="zagol-line">
                    </div>
                </div>
                <div class="short-news-date-left">
                        '.date('j', $news["date"]) . '&nbsp;'
                            . FormatTime::ru_month(date('n', $news["date"])) . '&nbsp;'
                            . date('Y G:i', $news["date"]) . '
                </div>
                <div class="cf shortnews-one">
                    <div class="shortnews-title">
                          '.$news["short_news"].'
                    </div>
                    <div class="shortnews-content">
                            <p>'.$news["text"].'</p>
                    </div>
                </div>
            </div>
            ';

        return $output;
    }


}

class ManagerShortNews {

    protected $dataArray = array();
    protected $provider;

    public function set_shortNews($shortNews) {

        $this->provider->set_shortNews($shortNews);
    }

    public function set_text($text) {

        $this->provider->set_text($text);
    }

    public function set_date($date) {

        $this->provider->set_date($date);
    }
}

class NewShortNews extends ManagerShortNews {

    public function __construct() {

        DB_Provider::Instance()->loadProvider('Plugins.ShortNews');

        $this->provider = new NewShortNewsProvider();
    }

    public function create_shortNews() {

        $result = $this->provider->create_shortNews();

        return $result;
    }

}

class EditShortNews extends ManagerShortNews {

    public function __construct($id) {

        DB_Provider::Instance()->loadProvider('Plugins.ShortNews');

        $this->provider = new EditShortNewsProvider($id);
    }

    public function update_shortNews() {

        $result = $this->provider->update_shortNews();

        return $result;
    }

}

class DeleteShortNews {
    
    private $provider;

    public function __construct($ids) {

        DB_Provider::Instance()->loadProvider('Plugins.ShortNews');

        $this->provider = new DeleteShortNewsProvider($ids);
    }

    public function delete_news() {

        return $this->provider->delete_news();
    }

}

