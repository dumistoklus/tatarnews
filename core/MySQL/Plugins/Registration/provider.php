<?php

interface RegistrationProvider {

    public function CreateUserOnEmail( $user_id, $hash );
}

class PreCheckRegistration implements RegistrationProvider {

    public function CreateUserOnEmail( $user_id, $hash ) {

        $sql = "INSERT INTO `".PREFIX."registration` (`user_id`, `time`, `hash`) VALUES ('".$user_id."', ".time().", '".md5( $hash )."')";

        if ( affectedRowsQuery($sql) )
            return mysql_insert_id();

        return FALSE;
    }

    public function GetRegistrationData( $key, $hash ) {

        $sql = 'SELECT `user_id` FROM `'.PREFIX.'registration` WHERE `reg_id` = '.$key.' AND `hash` = "'.md5( $hash ).'"';

		return get_bd_data($sql);
    }

    public function DeleteActivationKey( $key ) {

        $sql = 'DELETE FROM `'.PREFIX.'registration` WHERE `reg_id` = "'.$key.'"';

        return affectedRowsQuery($sql);       
    }

}