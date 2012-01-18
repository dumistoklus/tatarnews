<?php

class Company {

    private $dataArray = array();

    public function __construct($id = null) {

        DB_Provider::Instance()->loadProvider('Plugins.CompanyPlugin');

        $provider = new CompanyPluginProvider();

        if ($id != null) {

            $id = (int) $id;
            $this->dataArray = $provider->get_company_by_id($id);
            
        } else {

            $this->dataArray = $provider->get_company();
        }
    }

    public function dataArray() {

        return $this->dataArray;
    }

    public function __get($name) {

        return $this->dataArray[$name];
    }

}

class CompanyList {

    private $provider;

    public function __construct($start = 0, $limit = 0) {

        DB_Provider::Instance()->loadProvider('Plugins.CompanyPlugin');

        $this->provider = new CompanyListPluginProvider($start, $limit);
    }

    public function companyArray() {

        return $this->provider->company();
    }
    
    public function count() {
        
        return $this->provider->count();
    }

}

class CompanyPlugin implements IPlugin {

    public static function name() {
        return __CLASS__;
    }

    public static function load($side=0, $order=0) {
        if ($side == Side::CENTER && isset($_GET['id']) && $_GET['id'] != null) {

            return CompanyView::print_view_by_id(new Company($_GET['id']));
        } else if ($side == Side::CENTER) {

             if (isset($_GET['a_nav']) && (int) $_GET['a_nav'] != 0) {
                $currentpage = (int) $_GET['a_nav'];
            } else
                $currentpage = 1;

            $limitStart = $currentpage * 10 - 10;

            $currentURI = '/?page=company&a_nav=';

            PluginModule::load('PageNavBar');
            $company = new CompanyList($limitStart, 10);
            $navbar = new PageNavBar($currentURI, $company->count(), $currentpage);

            return CompanyView::print_view_all($company) . $navbar->get();
        } else if ($side == Side::RIGHT || $side == Side::LEFT) {

            return CompanyView::print_view(new Company());
        }
    }

}

class CompanyView {

    public static function print_view_all(CompanyList $companylist) {

        HeaderViewData::init()->set_title('Каталог компаний - TatarNews.ru');

        $list = $companylist->companyArray();

        if (count($list) > 0) {

            $companyhtml = '
                <div class="companys">
                    <div class="zagol-warp"><span class="zagol-link-warp">Каталог компаний</span>
                        <div class="zagol-line"></div>
                    </div>';

            foreach ($list as $company) {

                $companyhtml .= '<div class="company">';

            if ($company['id'] && $company['img'])
                $companyhtml .= '<a href="/?page=company&id=' . $company["id"] . '"  class="company-logo">
                    <img src="/images/company/' . $company["img"] . '"/></a>';

            $companyhtml .= '<div class="company-name">';

            if ($company['id'] && $company['name'])
                $companyhtml .= '<span class="company-name-font"><span class="punctuation">&laquo;</span><a href="/?page=company&id=' . $company["id"] . '">' . $company["name"] . '</a>&raquo;</span>';

            if ($company['industry'])
                $companyhtml .= '<span class="company-podp">' . $company["industry"] . '</span>';

            $companyhtml .= '</div>';

            $companyhtml .= ' <div class="company-text">';

            if ($company['about'])
                $companyhtml .= '<p>' . $company["about"] . '</p>';

            $companyhtml .='</div></div>';
            
            }

            $companyhtml .='</div>';
            
        } else {

            $companyhtml = '
                <div class="companys">
                    <div class="zagol-warp"><span class="zagol-link-warp">Каталог компаний</span>
                        <div class="zagol-line"></div>
                    </div>
                    <h2 style="text-align: center;" class="grey">В данный момент нет данных о компаниях!</h2>
                </div>';
        }

        return $companyhtml;
    }

    public static function print_view(Company $company) {


        $data = $company->dataArray();

        if (count($data) > 0) {

            $companyhtml = '<div class="company-in-right-block">
                <div class="zagol-warp">
                    <span class="zagol-link-warp"><a href="/?page=company" class="zagol-link">Каталог компаний</a></span>

                                <div class="zagol-line"></div>
                                    </div>';
            if ($company->id && $company->img)
                $companyhtml .= '<a href="/?page=company&amp;id=' . $company->id . '"  class="company-logo">
                    <img  width="100" src="/images/company/' . $company->img . '"/></a>';

            if ($company->id && $company->name)
                $companyhtml .= '<h2 class="company-name">
                    <span class="company-name-font">
                    <span class="punctuation">&laquo;</span>
                    <a href="/?page=company&amp;id=' . $company->id . '">'
                    . $company->name .
                    '</a>&raquo;</span>
                        </h2>';

            if ($company->industry)
                $companyhtml .= '<div class="company-podp">' . $company->industry . '</div>';


            $companyhtml .= ' <div class="company-text">';

            if ($company->about)
                $companyhtml .= '<p>' . $company->about . '</p>';

            if ($company->dob && $company->dob != '0000-00-00') {
               $dob = explode('-', $company->dob);

                $companyhtml .= '<p>Дата создания: ' .  (int)$dob[2] .'
                '.FormatTime::ru_month((int)$dob[1]).'
                '.(int)$dob[0].'г.</p>';
            }

            if ($company->adress)
                $companyhtml .= '<p>Адресс: <a href="http://maps.yandex.ru/?text=' . urldecode( $company->adress ) . '">' . $company->adress . '</a></p>';

            if ($company->phone)
                $companyhtml .= '<p>Телефон: ' . $company->phone . '</p>';

            if ($company->email)
                $companyhtml .= '<p>e-mail: ' . $company->email . '</p>';

            if ($company->site)
                $companyhtml .= '<p><a href=' . $company->site . '>' . $company->site . '</a></p>';

            $companyhtml .='</div></div>';
        } else {

            $companyhtml = '';
        }

        return $companyhtml;
    }

    public static function print_view_by_id(Company $company) {


        $data = $company->dataArray();

        HeaderViewData::init()->set_title($data['name'].' - TatarNews.ru');

        if (count($data) > 0) {

            $companyhtml = '<div class="companys">
                         <div class="zagol-warp"><span class="zagol-link-warp"><a href="/?page=company" class="zagol-link">Каталог компаний</a></span>
                            <div class="zagol-line"></div>
                                </div>
                <div class="company">';

            if ($company->img)
                $companyhtml .= '<div class="company-logo"><img src="/images/company/' . $company->img . '"/></div>';

            $companyhtml.='<div class="company-name">';

            if ($company->id && $company->name)
                $companyhtml .= '<span class="company-name-font"><span class="punctuation">&laquo;</span>' . $data["name"] . '&raquo;</span>';

            if ($company->industry)
                $companyhtml .= '<span class="company-podp">' . $company->industry . '</span>';

            $companyhtml.='</div>
                <div class="company-big-text">';

            if ($company->about)
                $companyhtml .= '<p>' . $company->about . '</p>';

            $companyhtml.='<div id="content">';

            if ($company->history)
                $companyhtml .= $company->history;

            $companyhtml.='<table class="company-char">';

            if ($company->dob && $company->dob != '0000-00-00') {
                $dob = explode('-', $company->dob);

                $companyhtml .= '<tr><td style="width: 30%;">Дата создания: </td>
                                <td>' .  (int)$dob[2] .'
                                '.FormatTime::ru_month((int)$dob[1]).'
                                 '.(int)$dob[0].'г. </td></tr>';
            }

            if ($company->products)
                $companyhtml .= '<tr><td style="width: 30%;">Товары:</td>
                                <td>' . $company->products . '</td></tr>';

            if ($company->revenue)
                $companyhtml .= '<tr><td style="width: 30%;">Выручка:</td>
                                <td>' . $company->revenue . '</td></tr>';

            if ($company->profit)
                $companyhtml .= '<tr><td style="width: 30%;">Чистая прибыль:</td>
                                <td>' . $company->profit . '</td></tr>';

            if ($company->director)
                $companyhtml .= '<tr><td style="width: 30%;">Руководитель:</td>
                                <td>' . $company->director . '</td></tr>';

            if ($company->number_of_emplayees)
                $companyhtml .= '<tr><td style="width: 30%;">Число сотрудников:</td>
                                <td>' . $company->number_of_emplayees . '</td></tr>';

            if ($company->adress)
                $companyhtml .= '<tr><td style="width: 30%;">Адрес:</td>
                                <td><a href="http://maps.yandex.ru/?text=' . urlencode( $company->adress ) . '">' . $company->adress . '</a></td></tr>';

            if ($company->phone)
                $companyhtml .= '<tr><td style="width: 30%;">Телефон:</td>
                                <td>' . $company->phone . '</td></tr>';

            if ($company->email)
                $companyhtml .= '<tr><td style="width: 30%;">e-mail:</td>
                                <td>' . $company->email . '</td></tr>';

            if ($company->site)
                $companyhtml .= '<tr><td style="width: 30%;">Сайт:</td>
                                <td><a href=' . $company->site . '>' . $company->site . '</td></tr>';

            if ($company->guide)
                $companyhtml .= '<tr><td style="width: 30%;">Руоководство:</td>
                                <td>' . $company->guide . '</td></tr>';

            $companyhtml .='</table>
                            </div>
                            </div>
                            </div>
                            </div>';
        } else {

            $companyhtml = '<div class="companys">
                        <div class="zagol-warp"><span class="zagol-link-warp"><a href="/?page=company" class="zagol-link">Каталог компаний</a></span>
                            <div class="zagol-line"></div>
                                </div>
                                    <div class="company"></div></div>';
        }

        return $companyhtml;
    }

}

class ManagerCompany {
    /*
      name                  название
      img                   логотип
      dob                   дата создания
      industry              отрасль
      products              товары
      revenue               выручка
      profit                чистая прибыль
      director              руководитель
      number_of_emplayees   число сотрудников
      about                 описание
      history               история создания компании
      adress                адресс
      phone                 телефон
      email                 e-mail
      site                  сайт
      guide                 руководство
     */

    protected $dataArray = array();
    protected $provider;

    public function set_name($name) {

        $this->provider->set_name($name);
    }

    public function set_img($img) {

        $this->provider->set_img($img);
    }

    public function set_dob($dob) {

        $this->provider->set_dob($dob);
    }

    public function set_industry($industry) {

        $this->provider->set_industry($industry);
    }

    public function set_products($products) {

        $this->provider->set_products($products);
    }

    public function set_revenue($revenue) {

        $this->provider->set_revenue($revenue);
    }

    public function set_profit($profit) {

        $this->provider->set_profit($profit);
    }

    public function set_director($director) {

        $this->provider->set_director($director);
    }

    public function set_number_of_emplayees($number_of_emplayees) {

        $this->provider->set_number_of_emplayees($number_of_emplayees);
    }

    public function set_about($about) {

        $this->provider->set_about($about);
    }

    public function set_history($history) {

        $this->provider->set_history($history);
    }

    public function set_adress($adress) {

        $this->provider->set_adress($adress);
    }

    public function set_phone($phone) {

        $this->provider->set_phone($phone);
    }

    public function set_email($email) {

        $this->provider->set_email($email);
    }

    public function set_site($site) {

        $this->provider->set_site($site);
    }

    public function set_guide($guide) {

        $this->provider->set_guide($guide);
    }

}

class NewCompany extends ManagerCompany {

    public function __construct() {

        DB_Provider::Instance()->loadProvider('Plugins.CompanyPlugin');

        $this->provider = new NewCompanyProvider();
    }

    public function create_company() {

        $result = $this->provider->create_company();

        return $result;
    }

}

class EditCompany extends ManagerCompany {

    public function __construct($id) {

        DB_Provider::Instance()->loadProvider('Plugins.CompanyPlugin');

        $this->provider = new EditCompanyProvider($id);
    }

    public function update_company() {

        $result = $this->provider->update_company();

        return $result;
    }

}

class DeleteCompany {

    public function __construct($ids) {

        DB_Provider::Instance()->loadProvider('Plugins.CompanyPlugin');

        $this->provider = new DeleteCompanyProvider($ids);
    }

    public function delete_company() {
        
        $result = $this->provider->delete_company();

        return $result;
    }
}

