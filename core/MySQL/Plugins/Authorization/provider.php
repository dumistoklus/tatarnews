<?php

class UserProvider {
	
	private $sid = '0';
	private $hash = '0';
	
	function __construct() {
		if($this->issetAuthCookies()) {
			list($sid, $hash) = explode('|', $_COOKIE['s']);
			
			$this->sid = $sid;
			$this->hash = $hash;
			
			if(!$this->isValidCookies()) {
				$this->sid = '0';
				$this->hash = '0';				
			}
			
		}
	}
	
	public function delete_session($sid) {
		
		$sql = 'DELETE FROM '.PREFIX.'sessions WHERE sid='.(int)$sid;
				
		$result = query($sql, __CLASS__.':'.__FUNCTION__.':'.__LINE__);
		
		if($result) return true;
		Logger::append_error(mysql_error(), __CLASS__.':'.__FUNCTION__.':'.__LINE__);
		return false;
	}
	
	public function get_session() {
		if($this->issetAuthCookies()) {
			if($this->isValidCookies()) {		
				$sql = 'SELECT uid, md5_hua, hash, sid FROM '.PREFIX.'sessions WHERE sid="'.$this->sid.'" AND hash="'.$this->hash.'" LIMIT 1';
				
				$result = query($sql, __CLASS__.':'.__FUNCTION__.':'.__LINE__);
				
					if($result) {
						$data = mysql_fetch_assoc($result);
						if($data['uid'] != '' && $data['uid'] != 0 && $data['md5_hua'] == env::vars()->HUA_MD5) {
							return $data;
						}
						else if(sizeof($data) > 1) {
							Logger::append_error('UID is 0 or null. See details below', __CLASS__.':'.__FUNCTION__.':'.__LINE__);
							Logger::append_array_dump($data);
						}
					}
			}
		}
		
		return false;
	}
	
	public function get_user_by_uid($uid) {
		$sql = 'SELECT * FROM '.PREFIX.'users WHERE id='.(int)$uid.' LIMIT 1';

		$result = query($sql, __CLASS__.':'.__FUNCTION__.':'.__LINE__);
		
		if($result) {
			$data = mysql_fetch_assoc($result);
				
			if($data['id'] != '') {
				return $data;	
			}
		}
		
		return false;
	}
	
	public function get_user_by_email_and_password($email, $password) {
		$password = md5(sha1($password));

		$validate = new ValidateEmailAndPassword();
		$validate->email = $email;
		$validate->password = $password;

		if($validate->isValid()) {
				
			$sql = 'SELECT * FROM '.PREFIX.'users WHERE email="'.mysql_real_escape_string($email).'" AND password="'.$password.'" AND `iactivated` = 1 LIMIT 1';

			$result = query($sql, __CLASS__.':'.__FUNCTION__.':'.__LINE__);
				
			if($result) {
				$data = mysql_fetch_assoc($result);

				if($data['id'] != '') {	
					return $data;
				}	
			}
		}
	}
	
	public function insert_session($id ,$hash, $microtime) {
			
		$sql = 'INSERT INTO '.PREFIX.'sessions (uid, hash, md5_hua, creation_date) VALUES ("'.$id.'", "'.$hash.'", "'.env::vars()->HUA_MD5.'", "'.$microtime.'")';
			
		$result = query($sql, __CLASS__.':'.__FUNCTION__.':'.__LINE__);
		
		if($result) return mysql_insert_id();
		return false;
	}
	
	private function isValidCookies() {		
		return (is_numeric($this->sid) && isValidMD5($this->hash));
	}
	
	private function issetAuthCookies() {
		return isset($_COOKIE['s']);
	}
	
}

class AdminUserProvider {
	
	private $user;
	
	function __construct() {
		$this->user = User::get();
	}
	
	public function get_admininistration_pages_in_extjs_tree_format_array() {
		
		$pages_sql = $this->get_user_pages_sql();

		$result = mysql_query($pages_sql);

		$groups = array();
		
		if($result) {
			
			while($data = mysql_fetch_assoc($result)) {
				
				if(!isset($groups[$data['group']])) {
					$groups[$data['group']] = $this->insert_children_array($data['group']);
				}
				
				array_push(
							$groups[$data['group']]['children'], 
							array(
								'text' => $data['title'], 
								'leaf' => true, 
								'id' =>  $data['module'].'/'.$data['path']
							)
				);
			
			}
			
			$ar = array();

			foreach($groups as $val) $ar[] = $val;

			return $ar;

		}
		
		return false;
		
	}
	
	private function get_user_pages_sql() {
		$sql = '';
		
		$rights = implode(',', $this->eject_user_rights_ids());
		$sql = 'SELECT * FROM '.PREFIX.'administration_settings WHERE `right_id` IN ('.$rights.')';
		
		return $sql;
	}
	
	private function eject_user_rights_ids() {
		$rights_array = $this->user->rights_array();

		$rights_ids = array();
		
		foreach ($rights_array as $right) {
			$rights_ids[] = (int)$right['right_id'];
		}
		
		return $rights_ids;
	}
	
	private function groups_to_sql() {
		$groups = $this->user->group();
		
		if($groups) {

			return '"'.implode('","', array_keys($groups)).'"';
		}
		
		return false;
	}
	
	private function insert_children_array($group) {
		return array(
					'text' => $group,
					'leaf' => false, 
					'children' => array()
					);
	}
}

class ValidateEmailAndPassword {
	public $email = false;
	public $password = false;

	function isValid() {
		return filter_var($this->email, FILTER_VALIDATE_EMAIL) && isValidMD5($this->password);
	}
}

class GroupProvider {
	public function get_rights($group) {
		$sql = 'SELECT * FROM  fms_rights
				INNER JOIN fms_group_rights ON  fms_rights.id = fms_group_rights.right_id AND fms_group_rights.group_id = '.(int)$group;
			
		$result = query($sql, __CLASS__.':'.__FUNCTION__.':'.__LINE__);
		
		$groups_array = array();
		
		if($result) {
			while($data = mysql_fetch_assoc($result)) {
				$groups_array[$data['right_index']]['right_index'] = $data['right_index'];
				$groups_array[$data['right_index']]['name'] = $data['name'];
				$groups_array[$data['right_index']]['group_id'] = $data['group_id'];
				$groups_array[$data['right_index']]['right_id'] = $data['right_id'];
			}
		}
		
		return $groups_array;
		
	}
	
	public function create_group($name) {
		$sql = 'INSERT INTO '.PREFIX.'users_group (`group`) VALUES ("'.mysql_real_escape_string($name).'")';
		$result = query($sql);
		
		if($result) {
			return mysql_insert_id();
		}
		
		return false;
	}
	
	public function get_groups() {
		$result = query('SELECT * FROM '.PREFIX.'users_group');
		
		$groups = array();
		
		if($result) {
			while($data = mysql_fetch_assoc($result)) 
				$groups[] = $data;
		}
		
		return $groups;
	}
	
	public function delete_group($id) {
		$id = (int) $id;
		
		if($id == 0) return false;
		
		if(!$this->is_main_group($id)) {
			$sql = 'DELETE FROM '.PREFIX.'users_group WHERE id='.$id;
			$result = query($sql);
			if($result) {				
				$sql = 'DELETE FROM '.PREFIX.'group_rights WHERE group_id='.$id;
				$result = query($sql);
				if($result) {
					$sql = 'UPDATE '.PREFIX.'users 
							INNER JOIN (SELECT id FROM '.PREFIX.'users_group WHERE `group`= "nobody") groups 
							SET `group` = groups.id WHERE '.PREFIX.'users.group = '.$id;
					$result = query($sql);
					if($result) return true;
				}
			}	
		}
		return false;
	}
	
	private function is_main_group($id) {
		$sql = 'SELECT `group` FROM '.PREFIX.'users_group WHERE id='.$id;
		
		$result = query($sql);
		
		if(!$result) {
			die('mysql error');
		}		
		else {
			
			$group = mysql_fetch_assoc($result);
			
			if ($group['group'] == 'root' || $group['group'] == 'nobody') {
				return true;
			}
			else {
				return false;
			}
		} 
		
	}
}
