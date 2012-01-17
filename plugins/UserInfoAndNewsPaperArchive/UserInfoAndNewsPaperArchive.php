<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


class UserInfoAndNewsPaperArchive implements IPlugin
{
    public static function name() {
        return __CLASS__;
    }

    public static function load($side = 0, $order = 0)
    {
        PluginModule::load('UserInfo');
        PluginModule::load('NewspaperArchive');

        $output = '<div class="header">'.
                  UserInfo::load().
                  NewspaperArchive::load().
                  '</div>';

        return $output;
    }
}