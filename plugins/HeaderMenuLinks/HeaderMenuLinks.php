<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class HeaderMenuLinks implements IPlugin
{
    private static $link = '/?page=articles&amp;article_cat=';

    public static function  name() {
        return __CLASS__;
    }

    public static function load($side = 0, $order = 0)
    {

        DB_Provider::Instance()->loadProvider('Plugins.HeaderMenuLinks');

        $HeaderMenuLinksProvider = new HeaderMenuLinksProvider;
        $cats = $HeaderMenuLinksProvider->get_all_cats();

        $links = array();
        $vert_links = array();
        $length = 0;
        $cat_count = 0;

        foreach ( $cats as $category ) {
            
            $length += mb_strlen( $category['name'], 'UTF-8' );
            ++$cat_count;
            if ( $length < 85 ) {
                $links[] = array( 'href' => self::$link.$category['id'], 'label' => $category['name'] );
            } else {
                $vert_links[] = array( 'href' => self::$link.$category['id'], 'label' => $category['name'] );
            }
        }

       $output = '';

        $output .= HeaderMenuLinksView::print_selectable_links($vert_links);
        $output .= HeaderMenuLinksView::print_main_links($links);

        return $output;
    }
}

class HeaderMenuLinksView
{
    public static function print_main_links($links) {
        $output = '<div id="razdel-links">';

        foreach($links as $link) 
            $output .= '<a href="'.$link['href'].'" class="topmenu-link">'.$link['label'].'</a>';
        
        $output .= '</div>';

        return $output;
    }

    public static function print_selectable_links($links) {
        $output = '<div id="selectable-link">'.
                    'Другие'.
                   '<div id="selectable-menu">';

        foreach($links as $link)
            $output .= '<a href="'.$link['href'].'" class="vert-menu-link">'.$link['label'].'</a>';

        $output .= '</div></div>';

        return $output;
    }
}



