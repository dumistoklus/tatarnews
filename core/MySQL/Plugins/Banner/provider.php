<?php

class BannerProvider {

    public function get_banners() {
        $banners = get_bd_data('SELECT bnn.`banner_id`, bnn.`date_start`, bnn.`date_end`, bnn.`template_id`, bnn.`order`, bnn.`side`, templates.`html`  FROM `'.PREFIX.'banners` bnn '.
                               'LEFT JOIN `'.PREFIX.'banner_templates` as templates ON bnn.`template_id` = templates.`template_id`'.
                               'WHERE bnn.`date_start` <= "'.date( 'Y-m-d' ).'" AND (bnn.`date_end` >= "'.date( 'Y-m-d' ).'" OR bnn.`date_end` = "0000-00-00") ORDER BY `order`');

        return $banners;
    }
}