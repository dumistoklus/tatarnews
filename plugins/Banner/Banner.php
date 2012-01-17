<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


class Banner implements IPlugin
{
    public static function name() {
        return __CLASS__;
    }

    public static function load($side, $order)
    {
        $itsBannerManager = isset( $_GET['page'] ) && mb_strtolower( $_GET['page'] ) == 'bannermanager' && User::get()->check_rights('B');

        if ( $itsBannerManager ) {
            PluginModule::load('BannersManager');
            DB_Provider::Instance()->loadProvider('Plugins.BannerManager');

            return ManagerBannerRender::load( $side, $order );
        }

        if($side == Side::HEADER && $order = 2) {
            return HeaderBannerAndLogo::load($order);
        }

        else if ($side == Side::RIGHT)
        {
            return RightBanner::load($order);
        }

        else if ($side == Side::CENTER)
        {
            return CenterBanner::load($order);
        }

        else if ($side == Side::BOTTOM)
        {
            return FooterBanner::load($order);
        }
    }
}

class BannerDataLoader
{
    private static $instance;
    private $BannerProvider;
    private $lastOrderForThisSide = array();
    private $banners = array();

    public static function init() {
        return (self::$instance === null) ? self::$instance = new self() : self::$instance;
    }

    public function __construct() {

        DB_Provider::Instance()->loadProvider('Plugins.Banner');

        $this->BannerProvider = new BannerProvider();
        $this->get_banners_from_db();
    }

    public function get_banner($side, $order) {



        if(isset($this->banners[$side][$this->get_last_order_for_side( $side )])) {

            $BannersOnThisSideAndOrder = $this->banners[$side][$this->get_last_order_for_side( $side )];
            ++$this->lastOrderForThisSide[$side];
            $count = count( $BannersOnThisSideAndOrder );

            if ( $count > 1 ) {
                return $BannersOnThisSideAndOrder[mt_rand( 0, $count - 1 )]['html'];
            }

            return $BannersOnThisSideAndOrder[0]['html'];
        }

        return '';
    }

    private function get_banners_from_db() {

        $banners = $this->BannerProvider->get_banners();
         if ( !empty( $banners ) ) {
            foreach ( $banners as $value ) {
                $banners_on_side[$value['side']][$value['order']][] = $value;
            }

            foreach ( $banners_on_side as $side => $banner_on_order ) {
                foreach ( $banner_on_order as $banner_array ) {
                    $this->banners[$side][] = $banner_array;
                }
            }
        }
    }

    private function get_last_order_for_side( $side ) {

        if ( isset( $this->lastOrderForThisSide[$side] ) ) {
            return $this->lastOrderForThisSide[$side];
        }

        return $this->lastOrderForThisSide[$side] = 0;
    }
}

class HeaderBannerAndLogo
{
    public static function load($order) {
        return '<div class="header topheader">'.
                '<div class="fr">'.BannerDataLoader::init()->get_banner(Side::HEADER, $order).'</div>'.
                '<div id="logo"><a href="/"><img src="/css/img/logo.png" /></a></div>'.
               '</div>';
    }
}

class RightBanner
{
    public static function load($order) {
        return '<div class="brbrbr-in-right-block">'.
                    BannerDataLoader::init()->get_banner(Side::RIGHT, $order).
                '</div>';
    }
}

class CenterBanner
{
    public static function load($order) {
        return '<div class="brbrbr-bottom">'.BannerDataLoader::init()->get_banner(Side::CENTER, $order).'</div>';
    }
}

class FooterBanner
{
    public static function load($order) {
        return '<div class="brbrbr-rast">'.BannerDataLoader::init()->get_banner(Side::BOTTOM, $order).'</div>';
    }
}

class ManagerBannerRender
{
    public static function load( $side, $order) {
        return '<div style="padding: 10px; text-align:center">'.BannerManagerDataLoader::init()->get_banner_selector($side, $order).'</div>';
    }
}