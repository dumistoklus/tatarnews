<?php

class Archive {

    private $newsList;
    private $numberList;
    private $provider;
    private $lastNumber;
    private $articles;
    private $number;
    private $countInNumber;

    public function __construct() {

        DB_Provider::Instance()->loadProvider('Plugins.NewsPaperArchive');

        $this->provider = new NewsPaperArchiveProvider();
    }

    public function get_numberList($year) {

        $this->numberList = $this->provider->number_list($year);

        return $this->numberList;
    }

    public function get_lastNumber() {

        $this->lastNumber = $this->provider->last_number();

        return $this->lastNumber;
    }

    public function count_by_number($number) {

        $this->countInNumber = $this->provider->count_by_number($number);

        return $this->countInNumber;
    }

    public function get_articles($number, $limitStart) {

        $this->articles = $this->provider->articles_by_number($number, $limitStart);

        return $this->articles;
    }

    public function get_number($id) {

        $this->number = $this->provider->number_by_id($id);

        return $this->number;
    }

}

class NewspaperArchive implements IPlugin {

    public static function name() {
        return __CLASS__;
    }

    public static function load($side = 0, $order = 0) {

        if ($side == Side::CENTER) {

            return NewspaperArchiveView::print_main(new Archive());
        } else if ($side == Side::RIGHT) {

            return NewspaperArchiveView::print_right(new Archive());
        } else {
            return NewspaperArchiveView::print_top(new Archive());
        }
    }

}

class NewspaperArchiveView {

    public static function print_top(Archive $archive) {

        $lastNumber = $archive->get_lastNumber();

        $output = '
        <a href="/?page=archive"> ' . $lastNumber["number"] . ' (' . $lastNumber["number_total"] . ')</a>
            в продаже с ' . date("j", $lastNumber["date_start"]) . ' ' . FormatTime::ru_month(date("n", $lastNumber["date_start"]));

        return $output;
    }

    public static function print_right(Archive $archive) {

        $currentYear = date('Y', time());

        if (isset($_GET['year']) && (int) $_GET['year'] != 0)
            $year = (int) $_GET['year'];
        else
            $year = $currentYear;

        $numbers = $archive->get_numberList($year);

        $output = '
            <div class="short-news-in-right-block">
                <div class="zagol-warp">
                    <span class="zagol-link-warp">
                    <a href="/?page=archive&year=' . $year . '" class="zagol-link">Номера ' . $year . ' года</a>
                    </span>
                    <div class="zagol-line">
                    </div>
                </div>';

        foreach ($numbers as $number) {

            $monthStart = date("n", $number["date_start"]);
            $monthEnd = date("n", $number["date_end"]);
            $monthStringStart = '';
            if ($monthStart != $monthEnd)
                $monthStringStart = FormatTime::ru_month($monthStart);

            $output .= '<div class="newspaper-list">
            <a href="/?page=archive&year=' . $year . '&number=' . $number["id"] . '" class="short-news-link">
            <strong>№' . $number["number"] . '</strong> (' . $number["number_total"] . ')
            с ' . date("j", $number["date_start"]) . ' ' . $monthStringStart . ' по ' . date("j", $number["date_end"]) . ' '
                    . FormatTime::ru_month($monthEnd) . '
            </a>
        </div>
        ';
        }

        for ($i = $currentYear; $i >= 2006; $i--) {
            if ($i != $year) {
                $output .= '
                <div class="short-news-date">
                <a href="/?page=archive&year=' . $i . '">посмотреть номера ' . $i . ' года</a>
                </div>';
            }
        }
        $output .= '</div>';

        return $output;
    }

    public static function print_main(Archive $archive) {

        HeaderViewData::init()->set_title('Архив номеров - TatarNews.ru');

        if (isset($_GET['number']) && (int) $_GET['number'] != 0)
            $number = (int) $_GET['number'];
        else {
            $currentNumber = $archive->get_lastNumber();
            $number = $currentNumber["id"];
        }
        if (empty($currentNumber))
            $currentNumber = $archive->get_number($number);

        if (isset($_GET['a_nav']) && (int) $_GET['a_nav'] != 0)
            $currentpage = $_GET['a_nav'];
        else
            $currentpage = 1;

        $limitStart = $currentpage * 10 - 10;

        $articles = $archive->get_articles($number, $limitStart);

        $output = '
            <div id="articles">';

        $i = 'first';
        if (count($articles) > 0) {
            foreach ($articles as $article) {

                $output .= '
            <div class="article">
                <div class="zagol-warp">
                    <span class="zagol-link-warp">
                    <a href="/?page=articles&article_cat=' . $article['cat'] . '" class="zagol-link">' . $article['category'] . '</a>
                    </span>
                    <div class="zagol-line">';

                if (isset($i)) {
                    $output .= '№ ' . $currentNumber["number"] . ' (' . $currentNumber["number_total"] . ')
                от ' . date("j", $currentNumber["date_start"]) . ' ' . FormatTime::ru_month(date("n", $currentNumber["date_start"])) . ' -
                ' . date("j", $currentNumber["date_end"]) . ' ' . FormatTime::ru_month(date("n", $currentNumber["date_end"])) . '
                ' . date("Y", $currentNumber["date_end"]) . 'г';
                    unset($i);
                }

                $output .= '</div>
                </div>';

                if (isset($article["image"]) && strlen($article["image"]) > 1)
                    $output .= '<div class="article-image">
                    <img src="/images/news/' . $article["image"] . '" />
                </div>';

                $output .= '<div class="article-text';

                if (isset($article["image"]) && strlen($article["image"]) > 1)
                    $output .=' left-isset-image';

                $output .='">
                    <h2 class="article-header">
                        <a href="/?page=articles&article_id=' . $article["id"] . '">
                        ' . $article["header"] . '
                        </a>
                    </h2>
                ' . $article["preview"] . '
                </div>
            </div>';
            }
        } else {
            $output .= '<div style="text-align: center;" class="cabinet-myname grey">В данном номере пока нет статей.</div>';
        }
        $output .= '</div>';

        PluginModule::load('PageNavBar');

        $currentURI = '/?page=archive';
        if (isset($_GET['year']))
            $currentURI .= '&year=' . $_GET['year'];
        if (isset($_GET['number']))
            $currentURI .= '&number=' . $_GET['number'];
        $currentURI .= '&a_nav=';

        $navbar = new PageNavBar($currentURI, $archive->count_by_number($currentNumber["id"]), $currentpage);

        return $output . $navbar->get();
    }

}