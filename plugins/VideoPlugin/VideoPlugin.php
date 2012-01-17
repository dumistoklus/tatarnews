<?php

class Video {

    private $lastVideo = array();
    private $provider;

    public function __construct() {

        DB_Provider::Instance()->loadProvider('Plugins.VideoPlugin');

        $this->provider = new VideoPluginProvider();
    }

    public function lastVideo() {

        $this->lastVideo = $this->provider->get_last_video();

        return $this->lastVideo;
    }

    public function addVideo($link, $date) {

        return $this->provider->add_video($link, $date);
    }

    public function deleteVideo($id) {

        return $this->provider->delete_video($id);
    }

}

class VideoAll {

    private $videoList = array();
    private $provider;
    private $count = 0;
    private $isAdmin;

    public function __construct($isAdmin = null) {

        $this->isAdmin = $isAdmin;

        DB_Provider::Instance()->loadProvider('Plugins.VideoPlugin');

        $this->provider = new VideoListPluginProvider($this->isAdmin);

        if (isset($_GET['a_nav']) && (int) $_GET['a_nav'] != 0) {

            $this->limitStart = (int) $_GET['a_nav'] - 10;
            $this->limitEnd = (int) $_GET['a_nav'];
        }
    }

    public function videoList($limitStart = 0) {

        $this->videoList = $this->provider->get_video_list($limitStart);

        return $this->videoList;
    }

    public function countVideos() {

        $this->count = $this->provider->get_count();

        return (int) $this->count;
    }

}

class VideoPlugin implements IPlugin {

    public static function name() {
        return __CLASS__;
    }

    public static function load($side=0, $order=0) {

        if (isset($_GET['page']) && $_GET['page'] == 'video') {

            if (isset($_POST['date']) && isset($_POST['link']) && User::get()->check_rights('CV')) {

                $link = $_POST['link'];
                $date = $_POST['date'];
                $video = new Video();
                $result = $video->addVideo($link, $date);
            }

            if (isset($_GET['delete']) && User::get()->check_rights('CV')) {

                $id = $_GET['delete'];
                $video = new Video();
                $result = $video->deleteVideo($id);
            }

            if (User::get()->check_rights('CV'))
                $isAdmin = 1;
            else
                $isAdmin = null;

            return VideoView::print_view_all(new VideoAll($isAdmin));
        } else {

            return VideoView::print_view(new Video());
        }
    }

}

class VideoView {

    public static function print_view(Video $video) {

        $dataArray = $video->lastVideo();
        if(count($dataArray)>0) {
        $height = round(365 * $dataArray['proportion']);
        $output = '
        <div class="zagol-warp">
            <span class="zagol-link-warp">
                <a href="/?page=video" class="zagol-link">Видео дня</a>
            </span>
            <div class="zagol-line">
            </div>
        </div>
        <iframe title="YouTube video player" width="365" height="' . $height . '" src="' . $dataArray['link'] . '" frameborder="0" allowfullscreen wmode="transparent">
        </iframe>
        ';
        } else 
            $output = '';

        return $output;
    }

    public static function print_view_all(VideoAll $videoAll) {

        HeaderViewData::init()->set_title('Видео - TatarNews.ru');
        HeaderViewData::init()->append_script( '/js/video.js' );
        HeaderViewData::init()->append_script('/js/jquery-ui-1.8.12.custom.min.js');
        HeaderViewData::init()->append_style('/css/jquery-ui-1.8.12.custom.css');


        if (isset($_GET['a_nav']) && (int) $_GET['a_nav'] != 0) {
            $currentpage = (int) $_GET['a_nav'];
        } else {

            $currentpage = 1;
        }

        $limitStart = $currentpage * 10 - 10;

        $dataArray = $videoAll->videoList($limitStart);


        $output = '
                    <div id="shortnews">
                        <div class="zagol-warp">
                            <div class="zagol-line">
                                Видео
                            </div>
                        </div>
                        <ul class="nolist">';

        if (User::get()->check_rights('CV'))
            $output .= '
                        <li>
                         <form action="/?page=video" method="post" id="addvideo">
                            <input type="text" name="link" style="width: 400px;" value="Ссылка на видео" class="typetext"/>
                            <input type="text" id="datepicker" name="date" value="'.date("Y-m-d").'" class="typetext">
                            <input type="submit" value="Добавить видео" class="button fr" />
                         </form>
                        </li>
                        ';

        foreach ($dataArray as $video) {

            $height = round(700 * $video['proportion']);

            $output .= '<li class="short-news-date">';

            if (User::get()->check_rights('CV'))
                $output .= '<a id = "deletevideo_' . $video["id"] . '" class="fl grey" href="/?page=video&delete=' . $video["id"] . '">Удалить</a>';

            $output .= date("j", $video['date']) . '
                                ' . FormatTime::ru_month(date("n", $video['date'])) . '
                                    ' . date("Y", $video['date']) . ' г.
                        </li>';

            $output .= '<li style="text-align:center;">
                    <iframe title="YouTube video player" width="700" height="' . $height . '" src="' . $video['link'] . '" frameborder="0" allowfullscreen wmode="transparent">
                    </iframe>
                </li>
        ';
        }

        $output .= '</ul>
            </div>';

        $currentURI = '/?page=video&a_nav=';

        PluginModule::load('PageNavBar');

        $navbar = new PageNavBar($currentURI, $videoAll->countVideos(), $currentpage);

        return $output . $navbar->get();
    }

}