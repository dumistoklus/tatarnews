<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 19.04.11
 * Time: 11:44
 * To change this template use File | Settings | File Templates.
 */
 
class UserDataUtils
{
    private $uid;
    private $user_data;
    
    function __construct($uid)
    {
        $this->uid = (int)$uid;
        $this->init_user();
    }

    public function user_data()
    {
        return $this->user_data;
    }

    public function isUnknownUser()
    {
        return ($this->user_data['id'] > 0);
    }

    private function init_user()
    {
        DB_Provider::Instance()->loadProvider('Utils.Users');

        $provider = new UserInfoProvider($this->uid);
        $this->user_data = $provider->user_data();
    }
}