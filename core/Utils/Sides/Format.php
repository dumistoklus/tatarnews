<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


class FormatExtJsSide
{
    const HEADER = 'NorthPanel';
    const RIGHT = 'EastPanel';
    const LEFT = 'WestPanel';
    const CENTER = 'CenterPanel';
    const BOTTOM = 'SouthPanel';

    public static function convert($side) {
        if(is_numeric($side)) {
            switch ($side) {
                case Side::HEADER : return self::HEADER; break;
                case Side::RIGHT : return self::RIGHT; break;
                case Side::LEFT : return self::LEFT; break;
                case Side::CENTER : return self::CENTER; break;
                case Side::BOTTOM : return self::BOTTOM; break;
            }
        }
    }
}
?>
