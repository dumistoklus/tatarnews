<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class UserInfo implements IPlugin
{
    public static function name() {
        return __CLASS__;
    }

    public static function load($side = 0, $order = 0) {

        self::AuthOrLogoutUser();

        if(User::get()->isAuth())
        {
            return UserInfoView::authorized();
        }
        else {
            return UserInfoView::not_authorized();
        }

    }

    private static function AuthOrLogoutUser()
    {
        URIManager::clean('logout');

        if(isset($_GET['logout'])) {
            User::get()->logout();
            URIManager::self_redirect();
        }

        if(!User::get()->isAuth() && isset($_POST['login']) && isset($_POST['password'])) {
            User::get()->Authentication($_POST['login'], $_POST['password']);
        }
    }
}

class UserInfoView
{
    public static function authorized() {

        $output = '';

        if(User::get()->check_rights('A'))
        {
            $output .= '<a href="/administration/">Администрирование</a>';
        }

        $output .= '<a href="?page=home" class="header-menu-elem">'.User::get()->nickname().'</a>'.
                  '<a href="'.env::vars()->URI.'&amp;logout" class="header-menu-elem">Выход</a>';

        return self::main_block($output);

    }

    public static function not_authorized() {
        $output =   '<a href="#" class="header-menu-elem" id="login">Вход</a>'.
                    '<a href="#" class="header-menu-elem" id="registration">Регистрация</a>';

        return self::main_block($output);
    }

    public static function main_block($content)
    {
        return '<div class="fr">'.$content.'</div>';
    }
}