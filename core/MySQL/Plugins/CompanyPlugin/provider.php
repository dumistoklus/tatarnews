<?php

class CompanyPluginProvider
{
    private $company=array();

    public function get_company() {
        
        $sql = 'SELECT * FROM '.PREFIX.'company ORDER BY RAND() LIMIT 1;';

        $this->company = get_bd_data($sql);


        if (count($this->company) > 0)
                
                return $this->company[0];

        return $this->company;

    }
    
    public function get_company_by_id($id) {

        $id = (int)$id;

        $sql = 'SELECT * FROM '.PREFIX.'company WHERE id = '.$id.' LIMIT 1;';

        $this->company = get_bd_data($sql);


        if (count($this->company) > 0)

                return $this->company[0];

            return $this->company;

    }

}

class CompanyListPluginProvider {

    private $count;
    private $company;
    private $currentTime;
    private $SQL_SELECT_COMPANY;

    function __construct($start = 0, $limit = 0) {

        $start = (int) $start;
        $limit = (int) $limit;

        $this->SQL_SELECT_COMPANY = 'SELECT * FROM ' . PREFIX . 'company';

        if ($start == 0 && $limit == 0)
            $this->SQL_SELECT_COMPANY = $this->SQL_SELECT_COMPANY . ' ORDER BY name';
        else
            $this->SQL_SELECT_COMPANY = $this->SQL_SELECT_COMPANY . ' ORDER BY name LIMIT ' . $start . ', ' . $limit;
    }

    public function count() {
        if ($this->count == null)
            $this->init_count();

        return $this->count;
    }

    public function company() {
        if ($this->company == null)
            $this->init_company();

        return $this->company;
    }

    private function init_count() {

        $this->count = get_bd_data('SELECT COUNT(*) FROM ' . PREFIX . 'company');
        $this->count = $this->count[0]['COUNT(*)'];
    }

    protected function init_company() {

        $this->company = get_bd_data($this->SQL_SELECT_COMPANY);
    }

}

class ManagerCompanyProvider {

    protected $dataArray = array();
    protected $allOK = true;

    public function __construct() {

        ;
    }

    public function set_name($name) {

        $name = trim($name);

        if (strlen($name)<300 && strlen($name)>0)
            $this->dataArray['name'] = $name;

        else
            $this->allOK = false;
    }

    public function set_img($img) {

        $img = trim($img);

        if (strlen($img)<100)
            $this->dataArray['img'] = $img;

        else
            $this->allOK = false;
    }

    public function set_dob($dob) {

        $dob = trim($dob);

        if ($dob == '')
            $this->dataArray['dob'] = '0000-00-00';
        else if (preg_match('/[0-2][0-9]{3}-[0-1][0-9]-[0-3][0-9]/', $dob))
            $this->dataArray['dob'] = $dob;

        else
            $this->allOK = false;
    }

    public function set_industry($industry) {

        $industry = trim($industry);

        if (strlen($industry)<500)
            $this->dataArray['industry'] = $industry;

        else
            $this->allOK = false;
    }

    public function set_products($products) {

        $products = trim($products);

        if (strlen($products)<500)
            $this->dataArray['products'] = $products;

        else
            $this->allOK = false;
    }

    public function set_revenue($revenue) {

        $revenue = trim($revenue);

        if (strlen($revenue)<300)
            $this->dataArray['revenue'] = $revenue;

        else
            $this->allOK = false;
    }

    public function set_profit($profit) {

        $profit = trim($profit);

        if (strlen($profit)<500)
            $this->dataArray['profit'] = $profit;

        else
            $this->allOK = false;
    }

    public function set_director($director) {

        $director = trim($director);

        if (strlen($director)<500)
            $this->dataArray['director'] = $director;

        else
            $this->allOK = false;
    }

    public function set_number_of_emplayees($number_of_emplayees) {

        $number_of_emplayeess = trim($number_of_emplayees);

        if (strlen($number_of_emplayees)<100)
            $this->dataArray['number_of_emplayees'] = $number_of_emplayees;

        else
            $this->allOK = false;
    }

    public function set_history($history) {

        $history = trim($history);

        if (strlen($history)<50000)
            $this->dataArray['history'] = $history;

        else
            $this->allOK = false;
    }

    public function set_about($about) {

        $about = trim($about);

        if (strlen( $about)<50000)
            $this->dataArray['about'] = $about;

        else
            $this->allOK = false;
    }

    public function set_adress($adress) {

        $adress = trim($adress);

        if (strlen($adress)<100)
            $this->dataArray['adress'] = $adress;

        else
            $this->allOK = false;
    }


    public function set_phone($phone) {

        $phone = trim($phone);

        if (strlen($phone)<100)
            $this->dataArray['phone'] = $phone;

        else
            $this->allOK = false;
    }

    public function set_email($email) {

        $email = trim($email);

        if ($email == '')
            $this->dataArray['email'] = '';
        else if (filter_var($email, FILTER_VALIDATE_EMAIL))
            $this->dataArray['email'] = $email;

        else
            $this->allOK = false;
    }

    public function set_site($site) {

        $site = trim($site);

        if ($site == '')
            $this->dataArray['site'] = '';
        else if (filter_var($site, FILTER_VALIDATE_URL))
            $this->dataArray['site'] = $site;

        else
            $this->allOK = false;
    }

    public function set_guide($guide) {

        $guide = trim($guide);

        if (strlen($guide)<200)
            $this->dataArray['guide'] = $guide;

        else
            $this->allOK = false;
    }

}

class NewCompanyProvider extends ManagerCompanyProvider{

    public function create_company() {

        if (count($this->dataArray) > 0 && $this->allOK != false) {

            foreach ($this->dataArray as $field => $value) {

                $value = mysql_real_escape_string($value);

                $fieldsArray[] = $field;
                $valuesArray[] = '"' . $value . '"';
            }

            $fieldsString = implode(' , ', $fieldsArray);
            $valuesString = implode(' , ', $valuesArray);

            $sql = 'INSERT INTO ' . PREFIX . 'company (' . $fieldsString . ') VALUES (' . $valuesString . ')';

            $result = affectedRowsQuery($sql);

            return $result;
        }

        return false;
    }

}

class EditCompanyProvider extends ManagerCompanyProvider {

    private $id = 0;

    public function __construct($id) {

        $this->id = (int) $id;

        if ($this->id == 0)
            return false;
    }

    public function update_company() {

        if (count($this->dataArray) > 0 && $this->allOK != false) {

            foreach ($this->dataArray as $field => $value) {

                $value = mysql_real_escape_string($value);

                $updateArray[] = $field . '= "' . $value . '"';
            }

            $updateString = implode(' , ', $updateArray);

            $sql = 'UPDATE ' . PREFIX . 'company SET ' . $updateString . ' WHERE id = ' . $this->id;

            $result = affectedRowsQuery($sql);

            return $result;
        }

        return false;
    }

}

class DeleteCompanyProvider {

    private $idString = '';

    public function __construct($ids) {

        if (count($ids) > 0) {
            foreach ($ids as $id) {

                $idArray[] = (int) $id;
            }

            $this->idString = implode(' , ', $idArray);
        }
    }

    public function delete_company() {

        $sql = 'DELETE FROM ' . PREFIX . 'company WHERE id IN (' . $this->idString . ')';

        $result = affectedRowsQuery($sql);

        return $result;
    }

}
?>
