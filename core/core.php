<?php
Logger::start_logging();

//error_reporting(E_ALL);

class Side implements Iterator
{

    const HEADER = 1;
    const CENTER = 2;
    const RIGHT = 3;
    const LEFT = 4;
    const BOTTOM = 5;

    private $sides = array();
    private $sides_names = array();

    private $iterator_position = 0;

    function  __construct() {
        $refl = new ReflectionClass(__CLASS__);

        $sides = $refl->getConstants();

        $this->sides = array_values($sides);
        $this->sides_names = array_keys($sides);

        $this->iterator_position = 0;
    }

    function  rewind() {
        $this->iterator_position = 0;
    }

    function  current() {
        return $this->sides[$this->iterator_position];
    }

    function  key() {
        return $this->sides_names[$this->iterator_position];
    }

    function  next() {
        ++$this->iterator_position;
    }

    function  valid() {
        return isset($this->sides[$this->iterator_position]);
    }
}

class env {
    private static $init;
    private $vars;
    private $env_set_log;

    public static function vars() {
        return (self::$init === null) ? self::$init = new self() : self::$init;
    }

    function  __construct() {
        $this->vars['ROOT_PATH'] = $_SERVER['DOCUMENT_ROOT'];

        $this->vars['SERVER'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '127.0.0.1';

        $this->vars['ADMINISTRATION_AJAX'] = $this->SERVER.'/administration/ajax/';

        $this->vars['HUA_MD5'] = md5((isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'n\a'));

        $this->vars['PLUGINS_DIR'] = $this->ROOT_PATH . '/plugins/';

        $this->vars['VIEWS_TEMPLATE'] = $this->ROOT_PATH.'/views/templates/';

        $this->vars['PROVIDER_PATH'] = $this->ROOT_PATH.'/core/';

        $this->vars['PAGE_SCHEMA_XML'] = $this->ROOT_PATH.'/views/page_schema.xml';

        $this->vars['PAGE'] = isset($_GET['page']) ? $_GET['page'] : 'index';

        if(!isset($_GET['page']))
            $this->vars['URI'] = '/?page='.$this->vars['PAGE'];
        else
            $this->vars['URI'] = $_SERVER['REQUEST_URI'];

    }

    function  __get($name) {
        return (isset($this->vars[$name])) ? $this->vars[$name] : '';
    }

    function __set($name, $value)
    {
        if(isset($this->vars[$name]))
            $this->env_set_log[$name][] = $this->vars[$name];

        $this->vars[$name] = $value;
    }

    public function env_set_log() {
        return $this->env_set_log;
    }

}

class Value
{
    public static function is_numeric($var)
    {
        return preg_match('/^[0]{0}?[1-9]{0,}$/', $var);
    }

    public static function is_md5($hash) {
	    return (ctype_xdigit($hash) && strlen($hash) == 32);
    }
}

function  add_file($filename, $where = '') {
	require_once $filename;
	Logger::append_include($filename, $where);
}

function dump_array($var) {
	ob_start();
	echo "<pre>";
	print_r($var);
	echo "</pre>";
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

function isValidMD5($hash) {
	return Value::is_md5($hash);
}

function is_isset_post() {
	$isset = true;
	$args = func_get_args();

	if(func_num_args() == 0) $isset = false;

	foreach($args as $post_data) {
		$isset = $isset && isset($_POST[$post_data]);
	}

	return $isset;
}

function to_ext_datastore_json($array) {

	$result['total'] = sizeof($array);
	$result['results'] = $array;

	return $result;
}

function print_json($array) {
	echo json_encode( to_ext_datastore_json($array));
}

function goto404() {
	header('HTTP/1.1 404 Not found');
}

function mctime() {
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	return $mtime[1] + $mtime[0];
}

interface IPlugin {
    public static function load($side, $order);
    public static function name();
}

interface IContainer {
    public static function schema($page);
}

class Logger {
	private static $logging = false;
	private static $log_mode = 'ERROR|SQL|DUMP|FILES|PROVIDERS';
	private static $log_array = array();
	private static $start_time;
	private static $sql_time = 0;

	public static function start_logging($mode = '') {
		self::$logging = true;

		if(self::$start_time === null) {
			self::$start_time = mctime();
		}

		if($mode != '') {
			self::$log_mode = $mode;
			self::$log_mode = array_flip(explode('|', self::$log_mode));
		}
	}

	public static function stop_logging() {
		self::$logging = false;
	}



	public static function append_sql($sql, $where = '', $time = '') {

		self::append_to_log($where, $sql, 'SQL');

		if(is_numeric($time)) {
			self::$sql_time += $time;
		}
	}

	public static function append_array_dump(&$array, $where = '') {
		self::append_to_log($where, dump_array($array), 'DUMP');
	}

	public static function append_error($error, $where = '') {
		self::append_to_log($where, $error, 'ERROR');
	}

	public static function append_var_dump($var, $where = '') {
		self::append_to_log($where, $var, 'DUMP');
	}

	public static function append_include($filename, $where = '') {
		self::append_to_log($where, $filename, 'FILES');
	}

    public static function append_provider($name, $where = '') {
 		self::append_to_log($where, $name, 'PROVIDER');
    }

	private static function append_to_log($where= '', $what, $type) {
		if(self::$logging && isset(self::$log_mode[$type])) {
			array_push(self::$log_array, array('time' => time(), 'message' => $what, 'type' => $type, 'where' => $where ) );
		}
	}

	public static function get_log_array() {
		return self::$log_array;
	}

	public static function elapsed_time() {
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$tend = $mtime;

		return ($tend - self::$start_time);
	}

	public static function sql_time($type = 't') {
		switch ($type) {
			case '%' : return (self::$sql_time / self::elapsed_time() * 100);

			default:
				return self::$sql_time;
		}

	}

	public static function print_log() {
		$log = self::$log_array;

		if(!empty($log)) {
			?>
			<style>
			.cms-log {
				font-size: 12px;
				font-family: Arial;
				line-height: 1.2em;
			}
			</style>
			<?php
			$sql_count = 0;
			$error_count = 0;
			$files_count = 0;
			foreach ($log as $log_value) {
				$message = date("[d-m-Y H:i:s ]", $log_value['time']).$log_value['where'].'> '.$log_value['message'];

				switch($log_value['type']) {
					case 'SQL':
						echo '<p style="color:#356B00;" class="cms-log">'.$log_value['type'].$message.'</p>';
						$sql_count++;
						break;
					case 'ERROR' :
						echo '<p style="color:#C0000C;" class="cms-log">'.$log_value['type'].$message.'</p>';
						$error_count++;
						break;
					case 'FILES' :
						echo '<p style="color:#00185C;" class="cms-log">'.$log_value['type'].$message.'</p>';
						$files_count++;
						break;
					default:
						echo '<p style="color:#2F2F2F" class="cms-log">'.$log_value['type'].$message.'</p>';
				}

			}
			echo '<p style="color:#999" class="cms-log">COUNT ';
			echo 'SQL: '.$sql_count.', ';
			echo 'FILES: '.$files_count.' (+1: core.php), ';
			echo 'Errors: '.$error_count;
			echo '<br />TOTAL TIME: '.self::elapsed_time().' sec <br /> ';
			echo 'SQL TIME: '.self::sql_time().'sec ('.round(self::sql_time('%'), 2).'%) <br /> ';
			echo 'MEMORY: '.Logger::max_mem().' Mb</p>';
		}
	}

	public static function max_mem($format = 'Mb') {

		switch($format) {
			case 'Mb' : return round( memory_get_peak_usage() / 1048576, 2 ); break;
			case 'Kb' : return round( memory_get_peak_usage() / 1024, 2 ); break;
			case 'b' : return memory_get_peak_usage(); break;
		}
	}

	public static function save($page = '') {
		$log = self::$log_array;
		if(!empty($log)) {
			$sql = '';
			foreach($log as $value) {
				if(isset(self::$log_mode[$value['type']])) {

					$sql .= '("'.mysql_real_escape_string(addslashes(htmlspecialchars($page, ENT_QUOTES))).'","'.$value['where'].'","'.$value['type'].'", "'.mysql_real_escape_string(addslashes($value['message'])).'", "'.mysql_real_escape_string(addslashes($_SERVER['REMOTE_ADDR'])).'", "'.$value['time'].'"),';

				}
			}

			$sql = substr($sql, 0, -1);
			query('INSERT INTO '.PREFIX.'logs (`page`,`where`,`type`, `message`,`ip`, `time`) VALUES '.$sql);
		}
	}
}

class PageController {
	protected static $instance = array();
	protected static $page = '';

	public static function edit_page($page) {

		Logger::append_var_dump($page, __CLASS__.':'.__LINE__.' $name');
		self::normalize_cache();

		if(self::isNameAndIsset($page)) {
                        $page = trim($page);
			$edit = new EditPage(self::$instance[$page]);
			self::$page = $page;
			self::$instance[$page] = $edit;
			return $edit;
		}
		else if(self::isObjectAndIsset($page)) {
			$edit = new EditPage(self::$instance[$page->name()]);
			self::$page = $page->name();
			self::$instance[$page->name()] = $edit;
			return $edit;
		}

		return new UnknownPage();
	}

	public static function create_page($name) {

            return new EditPage(new NewPage($name));
	}

	public static function load($page = '') {
        if(!is_object($page))
            $page = trim($page);

        self::normalize_cache();

		Logger::append_var_dump($page, __CLASS__.':'.__LINE__.' $page');

		if(self::is_page_not_isset_in_cache($page)) {
			$p = $page;
			$page = new Page($page);
			if($page->isIsset()) {
				self::$page = $page->name();
				self::$instance[self::$page] = $page;
				unset($page);
				return self::$instance[self::$page];
			}

			Logger::append_error('page "'.$p.'" not isset', __CLASS__.':'.__FUNCTION__.':'.__LINE__);

		}

		if(self::is_get_last_page_from_cache($page)) {
			return self::$instance[self::$page];
		}

                if(self::isNameAndIsset($page)) {
 			self::$page = $page;
			return self::$instance[$page];
                }

                if(self::isObjectAndIsset($page)) {
			self::$page = $page->name();
			return self::$instance[$page->name()];
                }

		return new UnknownPage();
	}

	public static function get_cache() {
		self::normalize_cache();
		return self::$instance;
	}

	public static function get_actual_page_name() {
		self::normalize_cache();
		return self::$page;
	}

	public static function push($page) {

             if(is_object($page))
             {
                $c = get_class($page);
                if ( $c == 'Page' || $c == 'EditPage' || $c == 'NewPage')
                {
                    if($page->isIsset() == true && self::is_page_in($page->name()) == false) {
			self::$instance[$page->name()] = $page;
			self::$page = $page->name();
			return true;
                    }
		}
             }
		Logger::append_error('NOT OBJECT', __CLASS__.':'.__FUNCTION__.':'.__LINE__);
		Logger::append_array_dump($page, '^');
		return false;
	}

	public static function pop($page) {

            $page = trim($page);
            if (isset(self::$instance[$page])) {

            $obj = self::$instance[$page];
            unset(self::$instance[$page]);

            if (self::$page == $obj->name())
                self::$page = null;

            return $obj;
            }
            Logger::append_error('NOT ISSET ' . $page, __CLASS__ . ':' . __FUNCTION__ . ':' . __LINE__);
            return new UnknownPage();
	}

	public static function clean() {
		self::$instance = array();
		self::$page = '';
	}

	public static function is_page_in($name) {
		self::normalize_cache();
		return isset(self::$instance[$name]);
	}

    /**
     * @static
     * @return array
     *
     * provider не хранит результат. Каждый раз будет выполняться запрос на получение всех старниц.
     */
    public static function get_all_pages()
    {
        $provider = new PageControllerProvider();

        return $provider->pages();
    }

	private static function isNameAndIsset($page) {
		if(is_string($page)) {
			if(isset(self::$instance[$page])) {
				return true;
			}
		}
		return false;
	}

	private static function isObjectAndIsset($obj) {
		if(is_object($obj))	{
			if(isset(self::$instance[$obj->name()])) {
				return true;
			}
		}
		return false;
	}
	private static function is_page_not_isset_in_cache($page) {
		return ($page != '' && !isset(self::$instance[$page]));
	}

	private static function is_get_last_page_from_cache($page) {
		return ($page == '' && isset(self::$instance[self::$page]));
	}

	private static function normalize_cache() {
		if( sizeof(self::$instance) != 0 ) {

			foreach (self::$instance as $page_name => $object) {

				if($page_name != $object->name()) {

					if($page_name == self::$page) self::$page = $object->name();

					self::$instance[$object->name()] = $object;

					unset(self::$instance[$page_name]);

				}

				if(!$object->isIsset())  {
					self::$page = null;
					unset(self::$instance[$object->name()]);
				}
			}

		}
	}
}

class UnknownPage extends EditPage {

	function __construct() {
		$this->id =
		$this->name =
		$this->title =
		$this->description =
		$this->content =
		$this->keywords =
		$this->page_isset = false;
	}

	public function delete() {
		return false;
	}

	private function setvalue($name, $value) {
		return $name;
	}

}

class Page {
	protected $id = false;
	protected $name = false;
	protected $keywords = false;
	protected $description = false;
	protected $title = false;
	protected $content = false;
	protected $page_isset = false;
	protected $provider = false;

	function __construct($page) {

		$this->provider = new PageProvider($page);
		$this->set_page_data( $this->provider->getPageData() );

	}

	public function id() {
		return $this->id;
	}

	public function name() {
		return $this->name;
	}

	public function description() {
		return $this->description;
	}

	public function content() {
		return $this->content;
	}

	public function title() {
		return $this->title;
	}

	public function isIsset() {
		if($this->id && $this->name && $this->page_isset) return true;
		return false;
	}

	public function keywords() {
		return $this->keywords;
	}

	public function delete() {

		$delete_success = $this->provider->delete();

		if($delete_success) {
			$this->page_isset = false;
			return true;
		}

		return false;
	}

	protected function set_page_data($page_data) {
		$this->id = $page_data['id'];
		$this->name = $page_data['name'];
		$this->title = $page_data['title'];
		$this->description = $page_data['description'];
		$this->content = stripslashes($page_data['content']);
		$this->keywords = stripslashes($page_data['keywords']);
		$this->page_isset = stripslashes($page_data['page_isset']);
	}
}

class NewPage extends Page {

	function __construct($name) {
                Logger::append_var_dump($name, __CLASS__.':'.__LINE__.' $name');

                $name = trim($name);

                if(!PageController::is_page_in($name)) {

                    $this->provider = new PageProvider();

                    $page_id = $this->provider->createPage($name);

                    if($page_id) {
                            $this->id = $page_id;
                            $this->name = $name;
                            $this->page_isset = true;
                    };
                }
	}
}

class EditPage extends Page {

	function __construct($page_object) {

		if($page_object->isIsset()) {
			$this->id = $page_object->id();
			$this->provider = new PageProvider($this->id);
			$this->page_isset = true;
			$this->set_page_data($page_object);

                        if(!PageController::is_page_in($this->name)) {
                            PageController::push($this);
                        }
		}
		else  {
                        Logger::append_error('page "'.$this->name.'" not isset', __CLASS__.':'.__LINE__);
		}
	}

	public function set_name($name) {
            if ($this->page_isset == true) {
                if ($this->provider->isValidName($name))
                    return $this->setvalue('name', $name);
            }
            return false;

	}
	public function set_title($title) {
		return $this->setvalue('title', $title);
	}
	public function set_description($description) {
		return $this->setvalue('description', $description);
	}
	public function set_content($content) {
		return $this->setvalue('content', $content);
	}
	public function set_keywords($keywords) {
		return $this->setvalue('keywords', $keywords);
	}

	private function setvalue($name, $value) {
		if($this->page_isset) {
			$value = $this->provider->updatePageValue($name, $value);
			if($value) {
				$this->$name = $value;
				return $value;
			}
		}
		return false;
	}

	protected function set_page_data($page_object) {
		$this->name =	$page_object->name;
		$this->title = $page_object->title;
		$this->description = $page_object->description;
		$this->content = $page_object->content;
		$this->keywords = $page_object->keywords;
	}

}

class Plugins {

	private $plugins_collector = array();
	private $page_id;
	private $isIsset;
	private $plugins;

	private $plugins_by_side = array();

	function __construct($page) {

		if(gettype($page) != 'object') $page = PageController::load($page);

		if($page) {
			$this->page_id = $page->id();

			$provider = new PluginsProvider();

			$this->plugins_collector = $provider->getPagePlugins($this->page_id);

			if(sizeof($this->plugins_collector) > 0) {
				$this->isIsset = true;
				$this->set_plugins();
				//нужно оптимизировать
				$this->set_plugins_by_side(Side::HEADER);
				$this->set_plugins_by_side(Side::CENTER);
				$this->set_plugins_by_side(Side::RIGHT);
				$this->set_plugins_by_side(Side::LEFT);
				$this->set_plugins_by_side(Side::BOTTOM);

			} else $this->page_plugins_not_isset();
		} else $this->page_plugins_not_isset();
	}

	public function load_plugins() {
		if($this->plugins) {
			foreach($this->plugins as $plugin) {
                            PluginModule::load($plugin);
			}
		}
	}

	public function get_plugins() {
		return $this->plugins;
	}

	public function get_side($side) {
		if($this->isIsset) {
			if(isset($this->plugins_by_side[$side]))
			return $this->plugins_by_side[$side];
		}
		return false;
	}

        public function get_raw_plugins() {
            return $this->plugins_collector;
        }

	private function page_plugins_not_isset() {
		$this->plugins_collector = false;
		$this->isIsset = false;
		$this->plugins = false;
	}

	private function set_plugins() {
		$names = array();
		if($this->plugins_collector) {
			foreach ($this->plugins_collector as $data) {
				array_push($names,  $data['name']);
			}
			//$names = array_unique($names);
			$this->plugins = $names;
			return true;
		}
		return false;
	}

	private function set_plugins_by_side($side) {
		if($this->isIsset) {
			$plugins = array();
			foreach($this->plugins_collector as $data) {
				if($data['side'] == $side) {
					$plugins[$data['order']] = $data['name'];
				}
			}
			ksort($plugins);
			$this->plugins_by_side[$side] = $plugins;
		}
		return false;
	}
}

class Module {

    private static $modules = array();

    public static function load($module) {
        $way = explode('.', $module);

        $path = env::vars()->ROOT_PATH.'/core/';

        foreach($way as $part) {
            $path .= '/'.$part;
        }

        $path .= '.php';

        if(file_exists($path)) {
            self::$modules[] = $module;
            add_file($path);
        }
    }

    public static function loaded_modules()
    {
        return self::$modules;
    }
}

class PluginModule {
    private static $plugins = array();

    public static function load($plugin) {
        $way_to_plugin = env::vars()->PLUGINS_DIR . $plugin . '/' . $plugin . '.php';

        if (file_exists($way_to_plugin)) {
            self::$plugins[] = $plugin;
            add_file($way_to_plugin, __FILE__);
        }
    }

    public static function loaded_plugins() {
        return self::$plugins;
    }
}

class LoadViewTemplate
{
    public static function load($mode, $log_file = __FILE__) {
        $filename = env::vars()->VIEWS_TEMPLATE.$mode.'.php';
        add_file($filename, $log_file);
    }
}


class FormatString {

    public static function cutText($str, $maxLen){

    if(strlen($str)>$maxLen){
          preg_match('/^.{0,'.$maxLen.'} .*?/is', $str, $match);
          $result = $match[0];
          }else{
              $result = $str;
          }

        return $result;

    }
}

class FormatTime {

    private static $months = array(
        1 => 'января',
        2 => 'февраля',
        3 => 'марта',
        4 => 'апреля',
        5 => 'мая',
        6 => 'июня',
        7 => 'июля',
        8 => 'августа',
        9 => 'сентября',
        10 => 'октября',
        11 => 'ноября',
        12 => 'декабря',
    );

     private static $months_en = array(
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    );


    public static function ru_month($month) {
        if(isset(self::$months[$month]))
            return self::$months[$month];

        return '';
    }

	public static function ru_date( $format, $time = null ) {

		if ( !$time )
			$time = time();

		if ( empty( $format ) ) {
			$format = 'j F Y G:i';
		}

		$date = date( $format, $time );

		return str_replace( self::$months_en, self::$months, $date );
	}
}

class FormatWordByCount {

    // функция "по последней цифре" возвращает значение из массива $type, которое соответствует последней цифре в числе
    // $number - число на основании которого делаем падеж
    // 0 элемент массива - (например один "человек")
    // 1 элемент массива - (например, два "человека")
    // 2 элемент массива - (например, пять "человек")
    public static function render_padezh_chisla( $number, $array  ) {

        $number = (string)$number;
        $i = strlen($number) - 1;

        if ( $number{$i} >= 5 OR $number{$i} == 0 OR ( $i >= 1 AND $number{$i - 1} == 1 ) ) {
            return $array[2];
        } elseif ( $number{$i} == 1 ) {
            return $array[0];
        } else {
            return $array[1];
        }
    }
}

class CyrUtils
{
    public static function convert($string)
    {
        if(!self::is_utf8($string))
            $string = @iconv('Windows-1251', 'UTF-8', $string);

        return $string;

    }

    public static function is_utf8($string) {
        return preg_match('%^(?:
              [\x09\x0A\x0D\x20-\x7E]            # ASCII
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
            |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
            |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
        )*$%xs', $string);

    }
}

class URIManager
{
    public static function clean_params()
    {
        $params = func_get_args();

        $uri = env::vars()->URI;

        foreach($params as $param)
        {
            $uri = preg_replace('/&'.$param.'([^&]{0,})/', '', $uri);
        }

        return $uri;
    }

    public static function clean($param)
    {
        env::vars()->URI = preg_replace('/&'.$param.'([^&]{0,})/', '', env::vars()->URI);
    }

    public static function clean_page()
    {
        $params = func_get_args();

        $uri = preg_replace('/(\?page=)[\w]+/', '$1'.$params[0], env::vars()->URI);

        unset($params[0]);

        foreach($params as $param)
        {
            $uri = preg_replace('/&'.$param.'([^&]{0,})/', '', $uri);
        }

        return $uri;
    }

    public static function self_redirect()
    {
        self::redirect_to(env::vars()->URI);
    }

    public static function redirect_to($way = '')
    {
        URIManager::clean('logout');
        header('Location: http://'.env::vars()->SERVER.$way);
    }
}

add_file(env::vars()->ROOT_PATH.'/core/provider.php', __FILE__);
add_file(env::vars()->ROOT_PATH.'/core_plugins/authorization/authorization.php', __FILE__);