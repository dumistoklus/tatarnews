<?php

class UsersCollection {
	
	private $start;
	private $limit;
	private $usersCount;
	
	function __construct() {
		
		$this->usersCount = $this->getUsersCount();
		
	}
	
	public function count() {
		return $this->usersCount;
	}
	
	public function GetUsers($start, $limit) {

		$this->start = $start;
		$this->limit = $limit;
		
		return array($this->usersCount, $this->getUsersByLimit());
	}
	
	private function getUsersCount() {
		$result = query('SELECT COUNT(*) FROM '.PREFIX.'users', __FILE__.':'.__CLASS__.':'.__FUNCTION__.':'.__LINE__);
		
		if($result) {
			$count = mysql_fetch_row($result);
			return $count[0];
		}

		return 0;
	}
	
	private function getUsersByLimit() {
		
		$lim = '';
		
		if($this->limit > 0) {
			$lim = 'LIMIT '.(int)$this->start.','.(int)$this->limit;
		}

		$sql = 'SELECT `id`, `nickname`, `name`, `email`, (SELECT `group` FROM fms_users_group WHERE fms_users_group.`id` = fms_users.`group`) AS `group` FROM '.PREFIX.'users '.$lim;
		
		return get_bd_data($sql);
		
	}
	
}

class UsersManager {

	public function Groups() {
		return get_bd_data('SELECT * FROM '.PREFIX.'users_group');
	}
	
	public function CreateUser($email, $nickname, $group, $password, $name) {
		
		$validator = new ValidateNewUserInfo();
		
		$validator->email = $email = trim($email);
		$validator->nickname = $nickname = trim($nickname);
		$validator->group = $group = $group;
		$validator->password = $password = trim($password);
        $validator->name = $name = trim($name);
		
		$result = $validator->Validate();
		
		if($result !== true)
            return $result;
		
		if($this->InsertNewUserInDB( $email, $nickname, $group, $password, $name )) {
			return 'SUCCESS';
		}
		
        return $this->getError();
	}
	
	public function Edit($user_id, $nickname, $email, $group, $name) {
		
		$email = trim($email);
		
		$validator = new ValidateUserInfo();
		
		$validator->user_id = $user_id;
		$validator->email = $email;
		$validator->group = $group;
		$validator->nickname = $nickname;
		$validator->name = $name = trim($name);

		$result = $validator->Validate();
		
		if($result === true) {
			$sql = 'UPDATE '.PREFIX.'users SET nickname="'.$nickname.'", email="'.$email.'", name="'.$name.'", `group`='.$group.' WHERE id='.$user_id.' LIMIT 1';
			return affectedRowsQuery($sql);	
		}
		
		return $result;

	}
	
	public function Delete($ids) {

		return affectedRowsQuery('DELETE FROM '.PREFIX.'users WHERE id IN ('.implode(',', $ids).')');
	}

    public function InsertNewUserInDB( $email, $nickname, $group, $password, $name, $iactivated = 1 ) {

        $sql = 'INSERT INTO '.PREFIX.'users (email, nickname, password, `group`, name, iactivated ) VALUES ("'.$email.'", "'.$nickname.'", "'.md5(sha1( $password ) ).'", "'.$group.'", "'.$name.'", '.$iactivated.')';

        if ( affectedRowsQuery($sql) > 0 ) {
            return mysql_insert_id();
        }

        return FALSE;
    }

    public function getError() {
        
        if(preg_match('(email|nickname)', mysql_error(), $matches)) {
			switch($matches[0]) {
				case 'email' : return 'MAYBE_EMAIL';
				case 'nickname': return 'MAYBE_NICKNAME';
			}
		}
    }

    public function CheckNicknameExist( $nickname ) {

        $result = query('SELECT COUNT(*) FROM '.PREFIX.'users WHERE `nickname` LIKE "'.$nickname.'"', __FILE__.':'.__CLASS__.':'.__FUNCTION__.':'.__LINE__);

		if( $result ) {
			$count = mysql_fetch_row($result);
			return $count[0];
		}

		return 0;
    }

    public function updateNickNameAndActivateStatus( $nickname, $user_id ) {

        $sql = 'UPDATE '.PREFIX.'users SET nickname ="'.$nickname.'", iactivated = 1  WHERE id='.$user_id.' LIMIT 1';
		return affectedRowsQuery($sql);
    }
}

class ValidateUserInfo {
	
	public $user_id;
	public $group;
	public $nickname;
	public $email;
	public $password;
    public $name;
	
	public function Validate() {

		if($this->is_key()) {

			if($this->is_email()) {

				if($this->is_group()) {

					if($this->is_nickname()) {

                        if($this->is_name()) {
						    return true;
                        }

                         return 'NAME_FAIL';
					}

					return 'NICKNAME_FAIL';
				}

				return 'GROUP_FAIL';
			}

			return 'EMAIL_FAIL';
		}

		return 'ID_FAIL';
	}

	protected function is_key() {
		if(is_numeric($this->user_id) && $this->user_id > 0) return true;
		return false;
	}

    protected function is_email() {

		$size = strlen($this->email);
		return filter_var($this->email, FILTER_VALIDATE_EMAIL) && (5 < $size) && ($size < 150);
	}

	protected function is_group() {

		return is_numeric($this->group) && $this->group > 0;
	}

	protected function is_nickname() {

		return preg_match('/^[a-zA-Z0-9]{2,40}$/', $this->nickname);
	}

	protected function is_password() {
		$size = strlen($this->password);
		return $size > 5;
	}

    protected function is_name() {
        return preg_match('/^[A-zА-я0-9\s]+$/u', $this->name);
    }
}

class ValidateNewUserInfo extends ValidateUserInfo
{
	public function Validate() {

		if($this->is_email()) {

			if($this->is_group()) {

				if($this->is_nickname()) {

					if($this->is_password()) {

                        if($this->is_name()) {
						    return true;
                        }

                        return 'NAME';
					}

					return 'PASSWORD';
				}

				return 'NICKNAME';
			}

			return 'GROUP';
		}

		return 'EMAIL';
	}
}

class ValidateRegistrationData extends ValidateUserInfo {

	public function Validate() {

		if($this->is_email()) {

            if($this->is_password()) {

                if ( $this->check_password() ) {
                    return true;
                }

                return 'PASSWORD_NOT_MATCH';
            }

            return 'PASSWORD';
        }

		return 'EMAIL';
	}

    private function check_password() {
        return ( $this->password == $this->passwordagain );
    }
}

class ValidateNickname extends ValidateUserInfo {

	public function Validate() {

		return $this->is_nickname();
    }
}