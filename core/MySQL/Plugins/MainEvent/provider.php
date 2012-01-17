<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class MainEventProvider
{
    public function get_event()
    {
        $sql = 'SELECT e.article_id, e.preview, ct.name, ct.id AS cat_id FROM '.PREFIX.'main_event e ' .
               'LEFT JOIN '.PREFIX.'articles art ON art.id = e.article_id '.
               'LEFT JOIN '.PREFIX.'cats ct ON ct.id = art.cat '.
               'WHERE e.date_start < UNIX_TIMESTAMP() AND  UNIX_TIMESTAMP() < e.date_end LIMIT 1;';
        
        $data = get_bd_data($sql);
        $event['theme'] = array();
        $event['href'] = array();
        $event['content'] = array();

        if(!empty($data)) {
            $event['theme'] = $data[0]['name'];
            $event['href'] = $data[0]['article_id'];
            $event['content'] = $data[0]['preview'];
            $event['cat_id'] = $data[0]['cat_id'];
        }

        return $event;

    }
}
?>
