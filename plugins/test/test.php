<?php

class test implements IPlugin {
	private static $instance;
	private $mode;

        public static function instance() {
            return self::$instance;
        }


        public static function load($side, $order) {
		if(self::$instance === null) self::$instance = new self($side, $order);
		
		$obj = self::$instance;
		
		return $obj->print_data($side.':'.$order);
		
	}

        public static function name() {
            return __CLASS__;
        }
	
	function __construct() {
		$this->mode = BodyViewData::get()->mode();
	}
	
	private function print_data($data) {
		
		switch($this->mode) {
			
			case 'base' :
				return test_base_view::print_data($data);
		}
		
	}
}

class test_base_view {
	public static function print_data($data) {
		return '<p>'.$data.'</p>';
	}
}