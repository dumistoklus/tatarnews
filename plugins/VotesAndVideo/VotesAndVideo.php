<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 12.04.11
 * Time: 17:51
 * To change this template use File | Settings | File Templates.
 */
 
class VotesAndVideo implements IPlugin
{
    public static function name()
    {
        return __CLASS__;
    }

    public static function load($side = 0, $order = 0)
    {
        PluginModule::load('VotesPlugin');
        $votes = VotesPlugin::load();
        PluginModule::load('VideoPlugin');
        $video = VideoPlugin::load();
        $ouptut = '<table class="two-columns"><tr><td class="two-columns-left">'.
                    '<div class="two-columns-left-inner">'.
                        $votes.
                        '</div>'.
                        '</td>'.
                        '<td class="two-columns-right">'.
                            '<div class="two-columns-right-inner">'.
                                $video.
                            '</div>'.
                        '</td>'.
                    '</tr>'.
                '</table>';

        return $ouptut;
    }
}