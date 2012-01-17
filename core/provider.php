<?php

class DB_Provider {
	
	private static $instance;
	private $db_type;

    private $loaded_providers = array();
	
	public static function init($db) {
		if(self::$instance === null) self::$instance = new self($db);
	}
	
	public static function Instance() {
		return (self::$instance !== null) ? self::$instance : false;
	}
	
	function __construct($db) {
		switch($db) {
			case 'MySQL': 
				new MySQL_Provider('MySQL');
				$this->db_type = 'MySQL';
				break;
				
			default:
				throw new Exception('db provider doesn\'t exists');
		}
	}
	
	public function loadProvider($provider) {

        if($this->is_loaded($provider)) return;

		$way_part = explode('.', $provider);
		
		$way = env::vars()->PROVIDER_PATH . $this->db_type.'/';
		
		foreach ($way_part as $part) {
			$way .= $part.'/';
		}
		
		$way .= 'provider.php';
		
		if(file_exists($way)) {
            $this->loaded_providers[$provider] = true;

            Logger::append_provider($provider);
            
			add_file($way);
		}
		else {
			throw new Exception($way.' - This provider doesn\'t exists');
		}
	}

    public function is_loaded($provider) {
        return isset($this->loaded_providers[$provider]);
    }
}

class MySQL_Provider {
	function __construct($provider) {
		add_file(env::vars()->PROVIDER_PATH . $provider . '/provider.php');
	}
}


DB_Provider::init('MySQL');