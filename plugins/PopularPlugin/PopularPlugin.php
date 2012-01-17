<?php

class Popular {

    private $popularArticles;

    public function __construct() {

        DB_Provider::Instance()->loadProvider('Plugins.PopularPlugin');

        $provider = new PopularPluginProvider();

        $this->popularArticles = $provider->popular();
    }

    public function popularArticles() {
        
        return $this->popularArticles;
    }

}

class PopularPlugin implements IPlugin {

    public static function name() {
        return __CLASS__;
    }

    public static function load($side=0, $order=0) {

        return PopularView::print_view(new Popular());
    }

}

class PopularView {

    public static function print_view(Popular $popular) {

        $popularArticles = $popular->popularArticles();

        if (count($popularArticles) > 0) {

            $popularhtml = '
            <div class="zagol-warp">
                <span class="zagol-link-warp zagol-non-link">
                    Самые популярные
                </span>
                <div class="zagol-line">
                </div>
            </div>
            <ul class="most">
            ';

            foreach ($popularArticles as $popular) {
                $popularhtml .= '
            <li>
                <a href="/?page=articles&amp;article_id=' . $popular["id"] . '">
                    ' . $popular["header"] . '
                </a>
            </li>
            ';
            }

        $popularhtml .= '</ul>';


        } else {

            $popularhtml = '';
        }

        return $popularhtml;
    }

}

