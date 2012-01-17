<?php

class Home implements IPlugin {

    public static function name() {
        return __CLASS__;
    }

    public static function load($order = 0, $side = 0) {
        if (isset($_GET['action']) && $_GET['action'] = 'edit' && User::get()->isAuth()) {

            $r = new HomeEdit();
            return $r->print_view();
        } else {

            $h = new HomeView();
            return $h->print_view();
        }
    }

}

class HomeView {

    private $user_data;
    private $have_anything = false;
    private $not_enough_data_error = null;

    function __construct() {
        if (User::get()->isAuth() && !isset($_GET['user'])) {
            $this->user_data = User::get()->user_data();
            if(count($this->user_data) > 2)
                $this->not_enough_data_error = '<a href="/?page=home&action=edit">Укажите данные</a> о себе';
        } else if (isset($_GET['user'])) {
            Module::load('Utils.Users.Info');
            $data = new UserDataUtils($_GET['user']);
            $this->user_data = $data->user_data();
            if($_GET['user'] == User::get()->id() && count($this->user_data) > 2)
                $this->not_enough_data_error = '<a href="/?page=home&action=edit">Укажите данные</a> о себе';
            else if(count($this->user_data) > 2)
                    $this->not_enough_data_error = 'Пользователь не указал данные';
        }

        if ($this->user_data['id'] == 0)
            URIManager::redirect_to();
        else {

            DB_Provider::Instance()->loadProvider('Utils.Users');
            $articlesLike = new ArticlesLike($this->user_data['id']);
            $articles = $articlesLike->get_articles_like();
            if (count($articles) > 0)
                $this->user_data['articles_like'] = $articles;
        }
    }

    public function print_view() {
        $buf = '';

        $output[] = '<div id="cabinet">
                    <div class="zagol-warp">
                        <span class="zagol-link-warp">';
        if(isset($_GET['user']) && $_GET['user'] == User::get()->id())
            $output[] = '<a href="/?page=home&action=edit" class="zagol-link">Редактировать</a>';
        if(empty($_GET['user']))
            $output[] = '<a href="/?page=home&action=edit" class="zagol-link">Редактировать</a>';
        $output[] = '</span>
                        <div class="zagol-line">
                            ' . $this->nickname . '
                        </div>
                    </div>
                    <dl>';

        if ($this->name) {
            $this->have_anything = true;
            $output[] = '<dt>&nbsp;</dt><dd class="cabinet-myname">' . $this->name . '</dd>';
        }
        if ($this->time()) {
            $output[] = '<dt class="cabinet-birthday">Родился</dt>
                    <dd class="cabinet-birthday">' . $this->time() . '
                        <br />
                        <br />
                    </dd>';
            $this->have_anything = true;
        }
        if ($this->fb) {
            $output[] = '<dt class="cabinet-link">Мой facebook</dt>
                    <dd class="cabinet-link">
                        <a href="' . $this->fb . '" rel="nofollow">' . $this->fb . '</a>
                    </dd>';
            $this->have_anything = true;
        }
        if ($this->tw) {
            $output[] = '<dt class="cabinet-link">Мой twitter</dt>
                    <dd class="cabinet-link">
                        <a href="' . $this->tw . '" rel="nofollow">' . $this->tw . '</a>
                    </dd>';
            $this->have_anything = true;
        }
        if ($this->blog) {
            $output[] = '<dt class="cabinet-link">Мой блог</dt>
                    <dd class="cabinet-link">
                        <a href="' . $this->blog . '" rel="nofollow">' . $this->blog . '</a>
                    </dd>';
            $this->have_anything = true;
        }
        if ($this->vk) {
            $output[] = '<dt class="cabinet-link">Мой vkontakte</dt>
                    <dd class="cabinet-link">
                        <a href="' . $this->vk . '" rel="nofollow">' . $this->vk . '</a>
                    </dd>';
            $this->have_anything = true;
        }
        if ($this->site) {
            $output[] = '<dt class="cabinet-link">Мой сайт</dt>
                    <dd class="cabinet-link">
                        <a href="' . $this->site . '" rel="nofollow">' . $this->site . '</a>
                    </dd>';
            $this->have_anything = true;
        }
        if ($this->about) {
            $output[] = '<dt class="cabinet-about">Я о себе</dt>
                    <dd class="cabinet-about">' . $this->about . '</dd>';
            $this->have_anything = true;
        }
        if($this->user_data['name'] == '' &&
                $this->user_data['vk'] == '' &&
                $this->user_data['tw'] == '' &&
                $this->user_data['fb'] == '' &&
                $this->user_data['site'] == '' &&
                $this->user_data['blog'] == '' &&
                $this->user_data['about'] == '' &&
                $this->user_data['birth_date'] == '0000-00-00')
            $output[]= '<dl>
                    <dt>&nbsp;</dt>
                    <dd class="cabinet-myname grey">' . $this->not_enough_data_error . '</dd></dl>';
        if ($this->articles_like) {
            $output[] = '<dt class="cabinet-ilike">Мне нравятся</dt>
                <dd class="cabinet-ilike">
                    <ul>';
            $articles = $this->articles_like;

            if (count($this->articles_like) >= 5)
                $countArticles = 5;
            else
                $countArticles = count($this->articles_like);

            for ($i = 0; $i < $countArticles; $i++) {
                $output[] = '
                        <li>
                            <a href="/?page=articles&article_id=' . $articles[$i]["id"] . '">
                                ' . $articles[$i]["header"] . '</a>
                                от ' . date('j', $articles[$i]["date"]) . '
                                    ' . FormatTime::ru_month(date('n', $articles[$i]["date"])) . '
                                        ' . date('Y', $articles[$i]["date"]) . ' г.
                        </li>';
            }

            $output[] = '<li>
                            <a href="/?page=articles&like=' . $this->id . '">Все понравившиеся статьи...</a>
                        </li>
                    </ul>
                </dd>
            </dl>';
        }

        
        foreach ($output as $key => $value) {

            $buf .= $value;
        }

        return $buf . '</div>';
    }

    function __get($name) {
        if (isset($this->user_data[$name])) {
            return $this->user_data[$name];
        }

        return false;
    }

    private function time() {
        list($year, $month, $day) = explode('-', $this->birth_date);

        if ($year == '0000') {
            $this->have_anything = $this->have_anything || false;
            return false;
        }
        $this->have_anything = $this->have_anything || true;
        $month = (int) $month;
        $day = (int) $day;
        return $day . ' ' . FormatTime::ru_month($month) . ' ' . $year . ' года';
    }

}

class HomeEdit {

    private $user_data;

    function __construct() {

        if (User::get()->isAuth() && !isset($_GET['user'])) {
            $this->user_data = User::get()->user_data();
        }
    }

    public function print_view() {

        $output = '
            <div id="cabinet">
                <div class="zagol-warp">
                    <span class="zagol-link-warp">
                        <a href="/?page=home" class="zagol-link">Вернутся в Личный кабинет</a>
                    </span>
                    <div class="zagol-line">
                        Редактирование Личных данных
                    </div>
                </div>
                <form action="#" method="post" id="useredit">
                    <dl>
                        <dt class="form-bigtitle">Ваше имя</dt>
                        <dd style="padding-bottom: 20px;">
                            <input type="text" name="name" value="' . $this->name . '" class="typetext cabinet-myname-form" />
                        </dd>
                        <dt class="cabinet-birthday form-title">Дата рождения</dt>
                        <dd class="cabinet-birthday">
                            <select name="day" class="typetext">
                                <option value="0">День</option>';


        $dob = explode('-', $this->birth_date);

        for ($i = 1; $i <= 31; $i++) {
            $output .= '<option ';
            if ($dob[2] == $i)
                $output .= ' selected="selected" ';
            $output .= 'value="' . $i . '">' . $i . '</option>';
        }
        $output .='</select>

                <select name="month" class="typetext">
                    <option value="0">Месяц</option>';

        for ($i = 1; $i <= 12; $i++) {
            $output .= '<option ';
            if ($dob[1] == $i)
                $output .= ' selected="selected" ';
            $output .= 'value="' . $i . '">' . FormatTime::ru_month($i) . '</option>';
        }


        $output .=' </select>
                <select name="year" class="typetext">
                    <option value="0">Год</option>';

        for ($i = 2011; $i > 1900; $i--) {
            $output .= '<option ';
            if ($dob[0] == $i)
                $output .= ' selected="selected" ';
            $output .= 'value="' . $i . '">' . $i . '</option>';
        }

        $output .= '</select>
            </dd>
            <dt class="cabinet-link">&nbsp;</dt>
            <dd class="cabinet-link">
                <br />
                <br />
                Сайты и контакты </dd>
            <dt class="cabinet-link form-title">facebook</dt>
            <dd class="cabinet-link">

                <input type="text" name="fb" value="' . $this->fb . '" class="typetext" />
            </dd>
            <dt class="cabinet-link form-title">twitter</dt>
            <dd class="cabinet-link">
                <input type="text" name="tw" value="' . $this->tw . '" class="typetext" />
            </dd>
            <dt class="cabinet-link form-title">Адрес блога</dt>
            <dd class="cabinet-link">

                <input type="text" name="blog" value="' . $this->blog . '" class="typetext" />
            </dd>
            <dt class="cabinet-link form-title">vkontakte</dt>
            <dd class="cabinet-link">

                <input type="text" name="vk" value="' . $this->vk . '" class="typetext" />
            </dd>
            <dt class="cabinet-link form-title">Мой сайт</dt>
            <dd class="cabinet-link">
                <input type="text" name="site" value="' . $this->site . '" class="typetext" />
            </dd>
            <dt class="cabinet-about">О себе</dt>
            <dd class="cabinet-about">

                <textarea name="about" id="aboutme">' . $this->about . '</textarea>
            </dd>
            <dt>&nbsp;</dt>
            <dd>
                <input type="submit" value="Сохранить" class="button" />
            </dd>
            <dt class="cabinet-link">&nbsp;</dt>
        </dl>

    </form>
    <form id="changePassword" action="#" method="post">
        <dl>
            <dd class="cabinet-link">
                <br />
                <br />
                <br />
                Изменение пароля</dd>

            <dt class="cabinet-link form-title">Новый пароль:</dt>
            <dd class="cabinet-link">
                <input type="password" name="password" class="typetext" />
            </dd>
            <dt class="cabinet-link form-title">Еще раз:</dt>
            <dd class="cabinet-link">
                <input type="password" name="password2" class="typetext" />
            </dd>

            <dt>&nbsp;</dt>
            <dd>
                <input type="submit" value="Изменить пароль" class="button" />
            </dd>
        </dl>
    </form>
</div>
';
        return $output;
    }

    function __get($name) {
        if (isset($this->user_data[$name])) {
            return $this->user_data[$name];
        }

        return false;
    }

}

class UserEditManager {

    protected $dataArray = array();
    protected $provider;

    public function __construct($id) {

        DB_Provider::Instance()->loadProvider('Plugins.Home');

        $this->provider = new ManagerUserEditProvider($id);
    }

    public function set_name($name) {

        $this->provider->set_name($name);
    }

    public function set_password($password) {

        $this->provider->set_password($password);
    }

    public function set_email($email) {

        $this->provider->set_email($email);
    }

    public function set_birth_date($birth_date) {

        $this->provider->set_birth_date($birth_date);
    }

    public function set_fb($fb) {

        $this->provider->set_fb($fb);
    }

    public function set_vk($vk) {

        $this->provider->set_vk($vk);
    }

    public function set_tw($tw) {

        $this->provider->set_tw($tw);
    }

    public function set_blog($blog) {

        $this->provider->set_blog($blog);
    }

    public function set_site($site) {

        $this->provider->set_site($site);
    }

    public function set_about($about) {

        $this->provider->set_about($about);
    }

    public function update_user() {

        $result = $this->provider->update_user();

        return $result;
    }

}