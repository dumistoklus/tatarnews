<?php

class ContainerProvider {
	
	private $page_id;
	
	function __construct($page_id) {
		$this->page_id = $page_id;
	}
	
	public function getContainerPluginsForThisPage() {
		
		$sql = 'SELECT c.container_side, c.container_order, c.plugin_order, p.file_name AS plugin '; 
		$sql .='FROM `'.PREFIX.'plugins_container` c ';
		$sql .='LEFT JOIN '.PREFIX.'plugins p ON p.id = c.plugin_id ';
		$sql .='WHERE page_id = '.(int)$this->page_id;
		
		$result = query($sql);
		
		$plugins = array();
		
		if($result) {
			while($data = mysql_fetch_assoc($result)) {

				$plugins[$data['container_side']][$data['container_order']][$data['plugin_order']] = $data['plugin'];
				
			}
		}
		
		return $plugins;
	}
}