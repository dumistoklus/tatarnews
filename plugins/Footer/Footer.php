<?php

class FooterList {

    private $dataArray = array();
    private $footermenu = array();

    public function __construct() {

        DB_Provider::Instance()->loadProvider('Plugins.Footer');

        $provider = new FooterProvider();
        
            $this->dataArray = $provider->footerlist();
            $this->footermenu = $provider->footermenu();
    }

    public function dataArray() {

        return $this->dataArray;
    }

    public function footermenu() {

        return $this->footermenu;
    }

    public function __get($name) {

        return $this->dataArray[$name];
    }

}

class Footer implements IPlugin {

    public static function name() {
        return __CLASS__;
    }

    public static function load($side=0, $order=0) {

        return FooterView::print_view(new FooterList());

    }
}
class FooterView {
    /*
    address - адресс
	address_for_mail - адресс для писем
	phone_reception -телеон приёмной
	phone_сorrespondent - телефон корреспонденты
	phone_commercial - телефон рекламы
	email
	chief_editor -главный редактор
	first_deputy - первый заместитель
	secretary - ответственный секретарь
	deputy - заместитель
	text - копирайт
     */

    public static function print_view(FooterList $footerlist) {

   $footerArray = $footerlist->dataArray();
   $menuArray = $footerlist->footermenu();

   $footerArray["linktoadress"] = urlencode( $footerArray["address"] );

    $output = '
        <div id="footer">';

        $output .= '
        <table>
            <tr>
                <td style="width:30%;">';
        if(isset($footerArray["address"]))
        $output .= 'Адрес: <a href="http://maps.yandex.ru/?text='.$footerArray["linktoadress"].'" rel="nofollow">
                    '.$footerArray["address"].'</a><br />';
        
        if(isset($footerArray["address_for_mail"]))
        $output .= 'Адрес для писем: '.$footerArray["address_for_mail"];
        
        $output .= '<br /> Телефоны:
                   <table style="margin-left: 20px;">';

        if(isset($footerArray["phone_reception"]))
        $output .= '<tr>
                        <td>приемная</td>
                        <td>'.$footerArray["phone_reception"].'</td>
                    </tr>';

        if(isset($footerArray["phone_сorrespondent"]))
        $output .= '<tr>
                        <td style="padding-right: 20px;">корреспонденты</td>
                        <td>'.$footerArray["phone_сorrespondent"].'</td>

                    </tr>';
        if(isset($footerArray["phone_commercial"]))
        $output .= '<tr>
                        <td>реклама</td>
                        <td>'.$footerArray["phone_commercial"].'</td>
                    </tr>';

        $output .= '
                    </table>';

        if(isset($footerArray["email"]))
        $output .= '
                    Email: <a href="mailto:'.$footerArray["email"].'">'.$footerArray["email"].'</a>';

        $output .= '
                    </td>
                <td style="width: 20%;">';

        if(isset($footerArray["chief_editor"]))
        $output .= '
                    <div class="poltora">
                        <strong>Главный редактор:</strong><br />'.$footerArray["chief_editor"].'
                    </div>';

        if(isset($footerArray["first_deputy"]))
        $output .= '
                    <div class="poltora">
                        <strong>Первый заместитель:</strong><br />'.$footerArray["first_deputy"].'
                    </div>';

        if(isset($footerArray["secretary"]))
        $output .= '
                    <div class="poltora">
                        <strong>Ответственный секретарь:</strong><br />'.$footerArray["secretary"].'
                    </div>';

        if(isset($footerArray["deputy"]))
        $output .= '
                    <div class="poltora">
                        <strong>Заместитель:</strong><br />'.$footerArray["deputy"].'
                    </div>';

        $output .= '
            </td>';
         if(isset($footerArray["text"]))
        $output .= '
                <td style="width: 26%; padding-right: 6%;">
                '.$footerArray["text"].'
                </td>';

        $output .= '
                <td style="width: 18%;">';

        if (count($menuArray)>0) {
            foreach ($menuArray as $punkt) {
               $output .= '
                   <a href='.$punkt["link"].'>'.$punkt["name"].'</a><br />';
            }
        }

        $output .= '
                    </td>
            </tr>
        </table>';


    $output .='
    </div>';

        return $output;
    }
}

class FooterMenu {

    private $provider;
    
    public function __construct() {

        DB_Provider::Instance()->loadProvider('Plugins.Footer');

        $this->provider = new FooterMenuProvider();
    }

    public function set_name($name) {

        $this->provider->set_name($name);
    }

    public function set_link($link) {

        $this->provider->set_link($link);
    }

    public function add_punkt() {

        return $this->provider->add_punkt();
    }

    public function delete_punkt($ids) {

        return $this->provider->delete_punkt($ids);
    }

    public function update_punkt($id) {

        return $this->provider->update_punkt($id);
    }
}

class ManagerFooter {
    /*
    address - адресс
	address_for_mail - адресс для писем
	phone_reception -телеон приёмной
	phone_сorrespondent - телефон корреспонденты
	phone_commercial - телефон рекламы
	email
	chief_editor -главный редактор
	first_deputy - первый заместитель
	secretary - ответственный секретарь
	deputy - заместитель
	text - копирайт
     */

    protected $dataArray = array();
    protected $provider;

    public function set_address($address) {

        $this->provider->set_address($address);
    }

    public function set_address_for_mail($address_for_mail) {

        $this->provider->set_address_for_mail($address_for_mail);
    }

    public function set_phone_reception($phone_reception) {

        $this->provider->set_phone_reception($phone_reception);
    }

    public function set_phone_correspondent($phone_correspondent) {

        $this->provider->set_phone_correspondent($phone_correspondent);
    }

    public function set_phone_commercial($phone_commercial) {

        $this->provider->set_phone_commercial($phone_commercial);
    }

    public function set_email($email) {

        $this->provider->set_email($email);
    }

    public function set_chief_editor($chief_editor) {

        $this->provider->set_chief_editor($chief_editor);
    }

    public function set_first_deputy($first_deputy) {

        $this->provider->set_first_deputy($first_deputy);
    }

    public function set_secretary($secretary) {

            $this->provider->set_secretary($secretary);
    }

    public function set_deputy($deputy) {

            $this->provider->set_deputy($deputy);
    }

    public function set_text($text) {

            $this->provider->set_text($text);
    }   
}

class EditFooter extends ManagerFooter {

    public function __construct() {

        DB_Provider::Instance()->loadProvider('Plugins.Footer');

        $this->provider = new EditFooterProvider();
    }

    public function update_footer() {

        return $this->provider->update_footer();
    }

}
