<?php

class HeaderViewData {
	private static $instance;
	
	private $title;
	private $meta = array();
	private $scripts = array();
	private $styles = array();	
	private $styles_with_condition = array();
	public static function init() {
		return (self::$instance === null) ? self::$instance = new self() : self::$instance;
	}
	
	public function set_title($title, $force = false) {
		if($this->title === null || $force)  $this->title = $title;
	}

	public function title() {
		return ($this->is_isset_title()) ? $this->title : false;
	}
	
	public function is_isset_title() {
		return ($this->title !== null) ? true : false;
	}
	
	public function append_style($file, $type = 'all') {
		$this->styles[] = array('file' => $file, 'media' => $type);
	}
	
	public function append_styles_with_condition($file, $condition, $media = 'all') {
		$this->styles_with_condition[] = array('file' => $file, 'condition' => $condition, 'media' => $media);
	}
	
	public function styles() {
		return $this->styles;
	}
	
	public function styles_with_condition() {
		return $this->styles_with_condition;
	}
	
	public function append_script($file) {
		$this->scripts[] = $file;
	}

	public function scripts() {
		return $this->scripts;
	}
	
	public function append_meta($name, $content, $force = false) {
		if(isset($this->meta[$name]) && !$force) return false;
		
		$this->meta[$name] = $content;
		
		return true;
	}
	
	public function meta() {
		return $this->meta;
	}
}

class BodyViewData {
	protected static $instance;
	
	private $mode; //или mobile , зависит от view файла у плагина	
	private $plugins_controller;
    private $page;
    private $sides_plugins;
	
	public static function load($page, $mode = 'base') {		
		if(self::$instance === null) self::$instance = new self($page, $mode);
	}
	
	public static function get() {
		return (self::$instance !== null) ? self::$instance : false;
	}

	function __construct($page, $mode) {
		
		self::$instance = $this;

		$this->mode = $mode;
		
		$this->page = PageController::load($page);

        $this->init_plugins();
        $this->init_header();

        LoadViewTemplate::load($mode, __FILE__);

	}
	
	public function mode() {
		return $this->mode;
	}

	public function header() {
		return $this->sides_plugins[Side::HEADER];
	}
	
	public function right() {
		return $this->sides_plugins[Side::RIGHT];
	}
	
	public function center() {
		return $this->sides_plugins[Side::CENTER];
	}
	
	public function left() {
		return $this->sides_plugins[Side::LEFT];
	}
	
	public function bottom() {
		return $this->sides_plugins[Side::BOTTOM];
	}

    private function init_plugins(){
        $this->plugins_controller = new Plugins($this->page);

		$this->plugins_controller->load_plugins();

        $sides = new Side();

        foreach($sides as $side)
        {
            $this->sides_plugins[$side] = $this->init_side_plugins($side);
        }
    }

    private function init_header()
    {
		$header_data = HeaderViewData::init();
		$header_data->set_title($this->page->title());

		$tmp = $this->page->keywords();
		if($tmp != '') {
			$header_data->append_meta('keywords', $tmp);
		}

		$tmp = $this->page->description();
		if($tmp != '') {
			$header_data->append_meta('description', $tmp);
		}	        
    }
	
	private function init_side_plugins($side) {
		$plugins_output = '';
		$plugins_array = $this->plugins_controller->get_side($side);
		
		if(is_array($plugins_array)) {
			foreach ($plugins_array as $order => $plugin) {
                if(class_exists($plugin))
				    $plugins_output .= $plugin::load($side, $order);
			}		
		}
		
		return $plugins_output;
	}
	
}

class Template {
	
	function __construct($page, $mode = 'base') {
		
		$filename =env::vars()->VIEWS_TEMPLATE . $mode . '.php';
				
		if(file_exists($filename)) {			
			BodyViewData::load($page, $mode);			
		}
	}
}