<?php
/*
 * DB tables:
 * users
 */

define('ROOT_USER', 2);

class User {
	private static $init;
	private $user_data;
	private $sid;
	private $hash;
	private $isAuth = false;
	private $provider;
	private $rights;

	public static function get() {
		return (self::$init === null) ? self::$init = new self() : self::$init;
	}
	
	public static function ip() {
		return mysql_real_escape_string(htmlspecialchars($_SERVER['REMOTE_ADDR'], ENT_QUOTES));
	}
	
	function __construct() {
		$this->provider = new UserProvider();
	}

	public function Authorization() {
		return $this->isAuth = authorization::start()->isAuthorized();
	}

	public function Authentication($email, $password) {
		new AuthUserByPass($email, $password);
		$this->isAuth = AuthUserByPass::isAutentificate();
	}

	public function set_user_info($data) {
		$this->user_data = $data;
	}

	public function isAuth() {
		return $this->isAuth;
	}

	public function isAdmin() {
		return ($this->isAuth() && ($this->in_group(ROOT_USER) || $this->check_rights('A')));
	}

	public function id() {
		if(isset($this->user_data['id'])) return $this->user_data['id'];
		return false;
	}

	public function nickname() {
		if(isset($this->user_data['nickname'])) return $this->user_data['nickname'];
		return false;
	}

	public function email() {
		if(isset($this->user_data['email'])) return $this->user_data['email'];
		return false;
	}

	public function group() {
		if(isset($this->user_data['group'])) return $this->user_data['group'];
		return false;
	}

	public function in_group($group) {
		if($this->user_data['group'] == $group) return true;
		return false;
	}

	public function hash() {
		return $this->hash;
	}

	public function sid() {
		return $this->sid;
	}

	public function set_hash($hash) {
		$this->hash = $hash;
	}

	public function set_sid($sid) {
		$this->sid = $sid;
	}
	
	public function check_rights() {
		if($this->rights == null) {
			$this->rights = new Group($this->group());
		}		
		
		return $this->rights->isset_rights(func_get_args());
	}
	
	public function rights_array() {
		if($this->rights == null) {
			$this->rights = new Group($this->group());
		}					
		
		return $this->rights->all_rights();
	}
	
	public function rights() {
		if($this->rights == null) {
			$this->rights = new Group($this->group());
		}	
		
		return array_keys($this->rights);				
	}

    public function user_data()
    {
        return $this->user_data;
    }

	public function logout() {
		if($this->isAuth && $this->sid) {
	
			if($this->provider->delete_session($this->sid)) {
				
				setcookie('s', '', time() - 3600, '/');
				$this->isAuth = false;
				
				return true;
			}
		}
		Logger::append_error('$this->isAuth && $this->sid == false', __CLASS__.':'.__FUNCTION__.':'.__LINE__);
		Logger::append_array_dump($this,  __CLASS__.':'.__FUNCTION__.':'.__LINE__);
		return false;
	}
}

class authorization {

	private static $init;
	private $isSuccess = false;
	private $provider;

	public static function start() {
		return (self::$init === null) ? self::$init = new self() : self::$init;
	}

	function __construct() {
		$this->provider = new UserProvider();
		
		$session = $this->provider->get_session();
		
		if($session) {

			$this->isSuccess = true;
			new AuthUserByUID($session['uid'], $session['hash'], $session['sid']);

		}

	}

	public function isAuthorized() {
		return $this->isSuccess;
	}
}

class AuthUserByUID {

	protected static $isAutentificate = false;

	public static function isAutentificate() {
		return self::$isAutentificate;
	}


	function __construct($uid, $hash, $sid) {
		$provider = new UserProvider();
		
		$user_data = $provider->get_user_by_uid($uid);
		
		if($user_data) {
			$user = User::get();
			$user->set_user_info($user_data);
			$user->set_hash($hash);
			$user->set_sid($sid);
			
			self::$isAutentificate = true;
			$this->set_cookies($sid, $hash);
		}
	}
	
	private function set_cookies($sid, $hash) {
		$s = $sid.'|'.$hash;
		setcookie('s', $s, time() + 3600, '/');
	}
}

class AuthUserByPass {

	protected static $isAutentificate = false;

	public static function isAutentificate() {
		return self::$isAutentificate;
	}

	function __construct($email, $password) {
		$provider = new UserProvider();
		
		$user_data = $provider->get_user_by_email_and_password($email, $password);
				
		if($user_data) {
			$user = User::get();
			$user->set_user_info($user_data);
			$id = $user->id();
				
			if($id) {
				$microtime = microtime(true);
				$hash = md5($id.$_SERVER['HTTP_USER_AGENT'].$microtime);

					
				$sid = $provider->insert_session($id ,$hash, $microtime);

				if($sid) {
					$user->set_hash($hash);
					$user->set_sid($sid);

					$this->set_cookies($sid, $hash);
					self::$isAutentificate = true;
				}
			}
			else {
				Logger::append_error('All seems ok, but auth failed. See details below', __CLASS__.':'.__FUNCTION__.':'.__LINE__);
				Logger::append_array_dump($user);
			}
		}
	}

	private function set_cookies($sid, $hash) {	
		$s = $sid.'|'.$hash;
		setcookie('s', $s, time() + 3600, '/');
        URIManager::self_redirect();
	}
}

class Group {
	private $id;
	private $rights;
	
	function __construct($group) {
		$provider = new GroupProvider();
		$this->id = $group;
		$rights = $provider->get_rights($group);
		
		$this->rights = $rights;
	}
	
	public function id() {
		return $this->id;
	}
	
	public function all_rights() {
		return $this->rights;
	}
	
	public function isset_rights() {
		
		$rights = func_get_args();
		$rights_count = func_num_args();
		
		$isset = true;
		
		for($i = 0; $i < $rights_count; $i++) {
			if(is_array($rights[$i])) {
				
				$size = sizeof($rights[$i]);
				
				for($k = 0; $k < $size; $k++) {
					$isset = $isset && isset($this->rights[$rights[$i][$k]]);
				}
			}
			
			else {
				$isset = $isset && isset($this->rights[$rights[$i]]);
			}

		}
		
		return $isset;
	}
}
//INIT PLUGIN
DB_Provider::Instance()->loadProvider('Plugins.Authorization');
User::get()->Authorization();
