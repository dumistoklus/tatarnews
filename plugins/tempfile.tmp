<?php

class Person {

    private $dataArray = array();

    public function __construct($id = null) {

        DB_Provider::Instance()->loadProvider('Plugins.PersonPlugin');

        $provider = new PersonPluginProvider();

        $id = (int) $id;

        if ($id != null) {

            $this->dataArray = $provider->get_person_by_id($id);
        } else {

            $this->dataArray = $provider->get_person();
        }
    }

    public function dataArray() {

        return $this->dataArray;
    }

    public function __get($name) {

        return $this->dataArray[$name];
    }

}

class Person2Articles {

    private $provider;

    public function __construct() {
        
        DB_Provider::Instance()->loadProvider('Plugins.PersonPlugin');
        
        $this->provider = new Person2ArticlesProvider;
    }


    public function all_articles() {
        
        return $this->provider->get_all_articles();
    }
    
    public function add_article2person($id_person, $id_article) {
        
        return $this->provider->add_article2person($id_person, $id_article);
    }
}

class PersonList {

    private $provider;

    public function __construct($start = 0, $limit = 0) {

        DB_Provider::Instance()->loadProvider('Plugins.PersonPlugin');

        $this->provider = new PersonListPluginProvider($start, $limit);
    }

    public function personArray() {

        return $this->provider->persons();
    }
    
    public function count() {
        
        return $this->provider->count();
    }

}

class PersonPlugin implements IPlugin {

    public static function name() {
        return __CLASS__;
    }

    public static function load($side=0, $order=0) {

        if ($side == Side::CENTER && isset($_GET['id']) && !empty($_GET['id'])) {

            return PersonView::print_view_by_id(new Person($_GET['id']));
        } else if ($side == Side::CENTER) {

            if (isset($_GET['a_nav']) && (int) $_GET['a_nav'] != 0) {
                $currentpage = (int) $_GET['a_nav'];
            } else
                $currentpage = 1;

            $limitStart = $currentpage * 10 - 10;

            $currentURI = '/?page=person&a_nav=';

            PluginModule::load('PageNavBar');
            $person = new PersonList($limitStart, 10);
            $navbar = new PageNavBar($currentURI, $person->count(), $currentpage);

            return PersonView::print_view_all($person) . $navbar->get();
        } else if ($side == Side::RIGHT) {

            return PersonView::print_view(new Person());
        }
    }

}

class PersonView {

    public static function print_view_all(PersonList $personlist) {

        HeaderViewData::init()->set_title('Личности - TatarNews.ru');

        $list = $personlist->personArray();

        if (count($list) > 0) {
            
            $personhtml = '<div class="persons">
                  <div class="zagol-warp"><span class="zagol-link-warp">Личности</span>
                    <div class="zagol-line"></div>
                  </div>
                  <table class="w100">';

            $parity = 1; //кратность 2

            foreach ($list as $person) {

                if ($parity%2 != 0) $personhtml.='<tr>';

                 $personhtml .= '<td><div class="persona-in-right-block">
                    <div class="persona-header cf">';

                if ($person['id'] && $person['img'])
                    $personhtml .= '<a href="/?page=person&amp;id=' . $person["id"] . '">
                    <img width="100" src="/images/persons/' . $person["img"] . '" class="persona-image" /></a>';
                else
                     $personhtml .= '<a href="/?page=person&amp;id=' . $person["id"] . '">
                    <img width="100" src="/images/persons/nophoto.jpg" class="persona-image" /></a>';

                if ($person['id'] && $person['name'] || $person['sirname'] || $person['lastname'])
                    $personhtml .= '<a href="/?page=person&amp;id=' . $person["id"] . '" class="persona-name">' . $person["name"] . '&nbsp;
                    ' . $person["sirname"] . '&nbsp;
                    ' . $person["lastname"] . '</a>';

                if ($person['post'])
                    $personhtml .= '<div class="persona-dolzhnost">' . $person["post"] . '</div>';


                $personhtml .= '</div><div class="persona-text">';

                if ($person['dob'] && $person["dob"] != '0000-00-00') {

                    $dob = explode('-', $person["dob"]);

                    $personhtml .= '<p>Дата рождения: ' .  (int)$dob[2] .'
                    '.FormatTime::ru_month((int)$dob[1]).'
                    '.(int)$dob[0].'г.</p>';
                }

                if ($person['pob'])
                    $personhtml .= '<p>Место рождения: ' . $person["pob"] . '</p>';

                if ($person['scope'])
                    $personhtml .= '<p>Сфера деятельности: ' . $person["scope"] . '</p>';

                if ($person['education'])
                    $personhtml .= '<p>Образование: ' . $person["education"] . '</p>';

                if ($person['job'])
                    $personhtml .= '<p>Место работы: ' . $person["job"] . '</p>';

                $personhtml .='</div></div></td>';

                 if ($parity%2 == 0) $personhtml.='</tr>';

                 ++$parity;

            }

            if (count($list)%2 != 0) $personhtml .= '</tr>';

            $personhtml .= '</table></div>';

        } else {

            $personhtml = '
                <div class="persons">
                  <div class="zagol-warp"><span class="zagol-link-warp">Личности</span>
                    <div class="zagol-line"></div>
                  </div>
                  <h2 style="text-align: center;" class="grey">В данный момент нет данных о личностях!</h2>
                </div>';
        }

        return $personhtml;
    }

    public static function print_view(Person $person) {

        $data = $person->dataArray();

        if (count($data) > 0) {

            $personhtml = '<div class="persona-in-right-block"><div class="zagol-warp">
            <span class="zagol-link-warp"><a href="/?page=person" class="zagol-link">Личности</a></span>
                            <div class="zagol-line"></div>
                          </div>
                          <div class="persona-header cf">';

            if ($person->id && $person->img)
                $personhtml .= '<a href="/?page=person&amp;id=' . $person->id . '">
                <img width="100" src="/images/persons/' . $person->img . '" class="persona-image" /></a>';
            else
                $personhtml .= '<a href="/?page=person&amp;id=' . $person->id . '">
                <img width="100" src="/images/persons/nophoto.jpg" class="persona-image" /></a>';

            if ($person->id && $person->name || $person->sirname || $person->lastname)
                $personhtml .= '<a href="/?page=person&amp;id=' . $person->id . '" class="persona-name">' . $person->name . '&nbsp;
                ' . $person->sirname . '&nbsp;
                ' . $person->lastname . '</a>';

            if ($person->post)
                $personhtml .= '<div class="persona-dolzhnost">' . $person->post . '</div>';


            $personhtml .= '</div><div class="persona-text">';

            if ($person->dob && $person->dob != '0000-00-00') {
                $dob = explode('-', $person->dob);

                $personhtml .= '<p>Дата рождения: ' .  (int)$dob[2] .'
                '.FormatTime::ru_month((int)$dob[1]).'
                '.(int)$dob[0].'г.</p>';
            }

            if ($person->pob)
                $personhtml .= '<p>Место рождения: ' . $person->pob . '</p>';

            if ($person->scope)
                $personhtml .= '<p>Сфера деятельности: ' . $person->scope . '</p>';

            if ($person->education)
                $personhtml .= '<p>Образование: ' . $person->education . '</p>';

            if ($person->job)
                $personhtml .= '<p>Место работы: ' . $person->job . '</p>';

            $personhtml .='</div></div>';
        } else {

            $personhtml = '';
        }

        return $personhtml;
    }

    public static function print_view_by_id(Person $person) {

        HeaderViewData::init()->set_title($person->name . '
                ' . $person->sirname . '
                ' . $person->lastname.' - TatarNews.ru' );

        $data = $person->dataArray();

        if (count($data) > 0) {

            $personhtml = '<div class="persona">
                            <div class="zagol-warp"><span class="zagol-link-warp"><a href="/?page=person" class="zagol-link">Личности</a></span>
                        <div class="zagol-line"></div>
                    </div>
                <div class="person cf">';

            if ($person->id && $person->img)
                $personhtml .= '<div class="fl"><img width="200" src="/images/persons/' . $person->img . '" class="persona-image" /></div>';
            else
                $personhtml .= '<div class="fl"><img width="200" src="/images/persons/nophoto.jpg" class="persona-image" /></div>';

            $personhtml .= '<div class="person-left-margin">';


            if ($person->id && $person->name || $person->sirname || $person->lastname)
                $personhtml .= '<h2 class="persona-name">' . $person->name . '&nbsp;
                ' . $person->sirname . '&nbsp;
                ' . $person->lastname . '</h2>';

            if ($person->post)
                $personhtml .= '<div class="persona-dolzhnost">' . $person->post . '</div>';


            $personhtml .= '<div class="persona-description">';

            if ($person->dob && $person->dob != '0000-00-00') {

                $dob = explode('-', $person->dob);

                $personhtml .= '<p>Дата рождения: ' .  (int)$dob[2] .'
                '.FormatTime::ru_month((int)$dob[1]).'
                '.(int)$dob[0].'г.</p>';
            }

            if ($person->pob)
                $personhtml .= '<p>Место рождения: ' . $person->pob . '</p>';

            if ($person->scope)
                $personhtml .= '<p>Сфера деятельности: ' . $person->scope . '</p>';

            if ($person->education)
                $personhtml .= '<p>Образование: ' . $person->education . '</p>';

            if ($person->job)
                $personhtml .= '<p>Место работы: ' . $person->job . '</p>';

            if ($person->career)
                $personhtml .= '<p>Этапы карьеры: ' . $person->career . '</p>';

            if ($person->coordinates)
                $personhtml .= '<p>Координаты: ' . $person->coordinates . '</p>';

            if ($person->marital)
                $personhtml .= '<p>Семейное положение: ' . $person->marital . '</p>';

            if ($person->phone)
                $personhtml .= '<p>Телефон: ' . $person->phone . '</p>';

            if ($person->fax)
                $personhtml .= '<p>Факс: ' . $person->fax . '</p>';

            if ($person->email)
                $personhtml .= '<p>e-mail: ' . $person->email . '</p>';

            if ($person->unknown_contact)
                $personhtml .= '<p>Дополнительный контакт: ' . $person->unknown_contact . '</p>';

            $personhtml .= '</div></div>';

            if ($person->articlesAboutPerson) {
                $personhtml .= '<div class="fl persona-second-side">Связанные статьи : </div>
                    <div class="person-left-margin persona-second-side"> ';
                foreach ($person->articlesAboutPerson as $articleAboutPerson) {
                    $personhtml .= '<a href="/?page=articles&amp;article_id=' . $articleAboutPerson["id"] . '">' . $articleAboutPerson["header"] . '</a>';
                }
                $personhtml .= '</div>';
            }
            $personhtml .='</div></div>';
        } else {

            $personhtml = '<div class="persona">
                         <div class="zagol-warp"><span class="zagol-link-warp"><a href="/?page=person" class="zagol-link">Личности</a></span>
                                <div class="zagol-line"></div>
                                    </div></div>';
        }

        return $personhtml;
    }
}

class ManagerPerson {
    /*
      name                имя
      sirname             фамилия
      lastname            отчество
      dob                 дата рождения
      img                 картинка
      education           образование
      scope               сфера деятельности
      post                должность
      job                 место работы
      career              этапы карьеры
      coordinates         координаты
      phone               телефон
      fax                 факс
      email               e-mail
      unknown_contact     неизвестный контакт
      marital             семейное положение
      pob                 место рождения
     */

    protected $dataArray = array();
    protected $provider;

    public function set_name($name) {

        $this->provider->set_name($name);
    }

    public function set_sirname($sirname) {

        $this->provider->set_sirname($sirname);
    }

    public function set_lastname($lastname) {

        $this->provider->set_lastname($lastname);
    }

    public function set_img($img) {

        $this->provider->set_img($img);
    }

    public function set_dob($dob) {

        $this->provider->set_dob($dob);
    }

    public function set_education($education) {

        $this->provider->set_education($education);
    }

    public function set_scope($scope) {

        $this->provider->set_scope($scope);
    }

    public function set_post($post) {

        $this->provider->set_post($post);
    }

    public function set_job($job) {

        $this->provider->set_job($job);
    }

    public function set_career($career) {

        $this->provider->set_career($career);
    }

    public function set_coordinates($coordinates) {

        $this->provider->set_coordinates($coordinates);
    }

    public function set_phone($phone) {

        $this->provider->set_phone($phone);
    }

    public function set_fax($fax) {

        $this->provider->set_fax($fax);
    }

    public function set_email($email) {

        $this->provider->set_email($email);
    }

    public function set_unknown_contact($unknown_contact) {

        $this->provider->set_unknown_contact($unknown_contact);
    }

    public function set_marital($marital) {

        $this->provider->set_marital($marital);
    }

    public function set_pob($pob) {

        $this->provider->set_pob($pob);
    }

}

class NewPerson extends ManagerPerson {

    public function __construct() {

        DB_Provider::Instance()->loadProvider('Plugins.PersonPlugin');

        $this->provider = new NewPersonProvider();
    }

    public function create_person() {

        $result = $this->provider->create_person();

        return $result;
    }

}

class EditPerson extends ManagerPerson {

    public function __construct($id) {

        DB_Provider::Instance()->loadProvider('Plugins.PersonPlugin');

        $this->provider = new EditPersonProvider($id);
    }

    public function update_person() {

        $result = $this->provider->update_person();

        return $result;
    }

}

class DeletePerson {

    public function __construct($ids) {

        DB_Provider::Instance()->loadProvider('Plugins.PersonPlugin');

        $this->provider = new DeletePersonProvider($ids);
    }

    public function delete_person() {

        return $this->provider->delete_person();
    }
    
    public function delete_article2person($id_article) {
        
        return $this->provider->delete_article2person($id_article);
    }

}

