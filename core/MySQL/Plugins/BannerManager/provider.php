<?php


class BannerManagerProvider {

    static $instance;

    public static function init() {
        return (self::$instance === null) ? self::$instance = new self() : self::$instance;
    }

    public function get_templates_list() {
        $banners = get_bd_data('SELECT `template_id`, `date_start`, `date_end`, `html`, `template_name` FROM `'.PREFIX.'banner_templates` '.
                               ' ORDER BY `date_start` DESC');

        return $banners;
    }

    public function get_template( $template_id )  {
        $banners = get_bd_data('SELECT `template_id`, `date_start`, `date_end`, `html`, `template_name` FROM `'.PREFIX.'banner_templates` '.
                               'WHERE `template_id`  = '.$template_id );

        return $banners;
    }

    public function save_template( $template_id, $template ) {

        $sql = 'UPDATE `'.PREFIX.'banner_templates` SET `template_name` = "'.mysql_real_escape_string( $template['template_name'] ).'",
                                                        `html` = "'.mysql_real_escape_string($template['html']).'",
                                                        `date_start` = "'.$template['date_start'].'",
                                                        `date_end` = "'.$template['date_end'].'"
                                                        WHERE `template_id` ='.$template_id.' LIMIT 1';
        return (affectedRowsQuery($sql) > 0 );
    }

    public function save_banners( $template_id, $template ) {

        $sql = 'UPDATE `'.PREFIX.'banners` SET `date_start` = "'.$template['date_start'].'", `date_end` = "'.$template['date_end'].'" '.
               'WHERE `template_id` ='.$template_id;
        return (affectedRowsQuery($sql) > 0 );
    }

    public function insert_template( $template ) {

        $template_name = filter_string($template['template_name']);
        $date_start = filter_string($template['date_start']);
        $date_end = filter_string($template['date_end']);
        $html = mysql_real_escape_string($template['html']);


        $sql = "INSERT INTO `".PREFIX."banner_templates` (`template_name`, `date_start`, `date_end`, `html`) VALUES".
               " ('".$template_name."', '".$date_start."', '".$date_end."', '".$html."')";

        return query($sql);
    }

    public function create_banner( $side, $order, $template_id ) {

        $sql = 'INSERT INTO `'.PREFIX.'banners` (`template_id`, `date_start`, `date_end`, `order`, `side`)
        SELECT `template_id`, `date_start`, `date_end`, '.$order.', '.$side.' FROM `'.PREFIX.'banner_templates` WHERE `template_id`  = '.$template_id;
        return ( affectedRowsQuery($sql) > 0 );
    }

     public function delete_banner( $banner_id ) {

        $sql = 'DELETE FROM `'.PREFIX.'banners` WHERE `banner_id`  = '.$banner_id;
        return ( affectedRowsQuery($sql) > 0 );
    }

    public function delete_template( $template_id ) {

        $template_id = (int)$template_id;

        $sql = 'DELETE FROM `'.PREFIX.'banner_templates` WHERE `template_id` ='.$template_id;
        if ( affectedRowsQuery($sql) > 0 ) {
            $sql = 'DELETE FROM `'.PREFIX.'banners` WHERE `template_id` ='.$template_id;
            query($sql);
            return TRUE;
        }

        return FALSE;
    }

    public function get_future_banners() {
         $banners = get_bd_data('SELECT bnn.`banner_id`, bnn.`date_start`, bnn.`date_end`, bnn.`template_id`, bnn.`order`, bnn.`side`, templates.`html`  FROM `'.PREFIX.'banners` bnn '.
                               'LEFT JOIN `'.PREFIX.'banner_templates` as templates ON bnn.`template_id` = templates.`template_id`'.
                               'WHERE bnn.`date_start` <= "'.date( 'Y-m-d' ).'" ORDER BY `date_start` DESC');

        return $banners;
    }
}