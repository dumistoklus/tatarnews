<?php

class Discussed {

    private $discussedArticles = array();

    public function __construct() {

        DB_Provider::Instance()->loadProvider('Plugins.DiscussedPlugin');

        $provider = new DiscussedPluginProvider();
        $this->discussedArticles = $provider->discussed_articles();
    }

    public function discussedArticles() {

        return $this->discussedArticles;
    }

}

class DiscussedPlugin implements IPlugin {

    public static function name() {
        return __CLASS__;
    }

    public static function load($side=0, $order=0) {

        return DiscussedView::print_view(new Discussed());
    }

}

class DiscussedView {

    public static function print_view(Discussed $discussed) {

        $discussedArticles = $discussed->discussedArticles();

        if (count($discussedArticles) > 0) {

            $discussedhtml = '<div class="zagol-warp"><span class="zagol-link-warp zagol-non-link">Самые обсуждаемые</span>
                                    <div class="zagol-line"></div>
                                </div>
                                <ul class="most">';

            foreach ($discussedArticles as $news) {

                $discussedhtml.= '<li><a href="/?page=articles&amp;article_id=' . $news["id"] . '">' . $news["header"] . '</a></li>';
            }

            $discussedhtml.= '</ul>';
            
        } else {

            $discussedhtml = '';
        }

        return $discussedhtml;
    }

}