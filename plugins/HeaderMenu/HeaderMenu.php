<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class HeaderMenu implements IPlugin
{
    public static function name()
    {
        return __CLASS__;
    }

    public static function load($side, $plugin) {
        PluginModule::load('HeaderMenuLinks');
        PluginModule::load('SearchForm');

        return HeaderMenuView::print_data();
    }
}

class HeaderMenuView {

    public static function print_data() {
        $output = '<div id="topmenuwarp"><div id="topmenu">';
        $output .= SearchForm::load();
        $output .= HeaderMenuLinks::load();
        $output .= '</div></div>';

        return $output;
    }

}