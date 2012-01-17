<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 12.04.11
 * Time: 17:58
 * To change this template use File | Settings | File Templates.
 */
 
class DiscussedAndPopular implements IPlugin
{
    public static function name()
    {
        return __CLASS__;
    }

    public static function load($side = 0, $order = 0)
    {
        PluginModule::load('DiscussedPlugin');
        PluginModule::load('PopularPlugin');

        $discussed = DiscussedPlugin::load();
        $popular = PopularPlugin::load();
        
        $output = '<table class="two-columns">'.
                    '<tr>'.
                        '<td class="two-columns-left">'.
                            '<div class="two-columns-left-inner">'.
                                $popular.
                            '</div>'.
                        '</td>'.
                        '<td class="two-columns-right">'.
                            '<div class="two-columns-right-inner">'.
                                $discussed.
                            '</div>'.
                        '</td>'.
                    '</tr>'.
                '</table>';

        return $output;
    }
}