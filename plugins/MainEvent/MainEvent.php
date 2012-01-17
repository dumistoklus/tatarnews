<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class MainEvent implements IPlugin
{
    private static $event;
    
    public static function  name() {
        return __CLASS__;
    }

    public static function  load($side = 0, $order = 0) {
        if(self::$event === null) self::init();
        
        return MainEventView::view(self::$event);
    }

    private static function init() {
        DB_Provider::Instance()->loadProvider('Plugins.MainEvent');

        $provider = new MainEventProvider();
        self::$event = $provider->get_event();
    }
}

class MainEventView
{
    private static $data;

    public static function view($data) {

        self::$data = $data;

        if(self::void_event()) return '';

        return '<div id="sobitie">'.
                    '<div class="zagol-warp">'.
                        '<span class="zagol-link-warp"><a href="'.URIManager::clean_page('articles', 'article_cat').'&article_cat='.$data['cat_id'].'" class="zagol-link">'.$data['theme'].'</a></span>'.
                        '<div class="zagol-line"></div>'.
                    '</div>'.
                    '<a href="'.URIManager::clean_page('articles', 'article_id').'&article_id='.$data['href'].'" id="sobitie-link">'.$data['content'].'</a>'.
                  '</div>';
    }

    private static function void_event(){

        return (empty(self::$data['theme']) && empty(self::$data['href']) && empty(self::$data['content']));
    }
}