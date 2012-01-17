<?php

class PageSchemeModel {
	
	private $xml;
	private $schema_array = array();
	
	function __construct($way_to_xml = '') {
		if($way_to_xml == '') $way_to_xml = env::vars()->PAGE_SCHEMA_XML;
		
		if(file_exists($way_to_xml)) {
			$this->xml = new DOMDocument();
			$this->xml->load($way_to_xml);
			
			$this->schema_array = $this->generate_schema();
		}
	}
	
	public function get() {
		if(sizeof($this->schema_array) > 0) {
			return $this->schema_array;
		}
		
		return array();
	}
	
	private function generate_schema() {
		$page_scheme = array();
		
		foreach ($this->xml->documentElement->childNodes as $region) {
				
		    if ($region->nodeType == 1 && $region->nodeName == "region") {
		    		$side = $region->getAttributeNode('side')->value;    		

		    		$side_visiblity = true;
		                     
		            if($region->hasAttribute('visible')) {
		            	$visiblity = $region->getAttributeNode('visible')->value;
		            	
		            	if($visiblity == 'false') {
		            		$side_visiblity = false;
		            	}
		            	
		            }
		            
		             $page_scheme[] = array('side' => $side, 'visible' => $side_visiblity);
		    }
		}
		
		return $page_scheme;
	}
}