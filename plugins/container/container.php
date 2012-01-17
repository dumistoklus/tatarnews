<?php
class container implements IPlugin, IContainer
{
	private static $loaded = false;
	private static $provider;
	private static $plugins;
	private static $side;
	private static $order;

	public static function load($side, $order)
	{
		self::$side = $side;
		self::$order = $order;
		
		if(self::$loaded === false) {
			
			self::first_load();
			self::$loaded = true;			
			
		}
		
		$content = self::container_content();
		
		return ContainerView::print_view($content);
	}

        public static function  schema($page) {
            self::first_load($page);
            return self::$plugins;
        }

        public static function name() {
            return __CLASS__;
        }
	
	private static function first_load($page='')
	{

		$page_id = PageController::load($page)->id();
		
		DB_Provider::Instance()->loadProvider('Plugins.Container');
		
		self::$provider = new ContainerProvider($page_id, self::name());
			
		self::$plugins = self::$provider->getContainerPluginsForThisPage();

	}
	
	private static function container_content() {
		
		$content = '';
		
		if(self::is_isset_plugins_on_this_side()) {

			$content = self::plugins_content();
		}
		
		return $content;
		
	}
	
	private static function is_isset_plugins_on_this_side() {
		
		$side = self::$side;
		$order = self::$order;
		
		return isset( self::$plugins[$side][$order] );
		
	}
	
	private static function plugins_content() {
		
		$output = '';
		
		foreach (self::$plugins[self::$side][self::$order] as $plugin_order => $plugin) {
			
			$filename = env::vars()->PLUGINS_DIR . $plugin.'/'.$plugin.'.php';
			
			if(file_exists($filename)) {
				add_file($filename);
				$output .= $plugin::load(self::$side, $plugin_order);
			}
			
		}
		
		return $output;
		
	}
}

class ContainerView {
	public static function print_view($data) {
		return '<div style="background: red;">'.$data.'</div>';
	}
}