<?php
class ManagerUserEditProvider {

    protected $dataArray = array();
    protected $allOK = true;
    private $id = 0;

    public function __construct($id) {

        $this->id = (int) $id;

    }

    public function isValidURL($url) {

        if (preg_match('/^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/', $url))
                return true;
        return false;

    }

    public function set_name($name) {

        $name = trim($name);

        if ($name == '')
            $this->dataArray['name'] = '';
        else if (preg_match('/^[- А-яA-z0-9]{1,100}$/u', $name))
            $this->dataArray['name'] = $name;

        else
            $this->allOK = false;
    }

    public function set_password($password) {

        $password = trim($password);

        if (strlen($password)>5)
            $this->dataArray['password'] = md5(sha1( $password ) );

        else
            $this->allOK = false;
    }

     public function set_birth_date($birth_date) {

        $birth_date = trim($birth_date);

        if (preg_match('/^[1-2][0-9]{3}-[0-1][0-9]-[0-3][0-9]$/', $birth_date))
            $this->dataArray['birth_date'] = $birth_date;

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

    public function set_fb($fb) {

        $fb = trim($fb);

        if ($fb == '')
            $this->dataArray['fb'] = '';
        else if ($this->isValidURL ($fb))
            $this->dataArray['fb'] = $fb;

        else
            $this->allOK = false;
    }

    public function set_vk($vk) {

        $vk = trim($vk);

        if ($vk == '')
            $this->dataArray['vk'] = '';
        else if ($this->isValidURL ($vk))
            $this->dataArray['vk'] = $vk;

        else
            $this->allOK = false;
    }

    public function set_tw($tw) {

        $tw = trim($tw);

        if ($tw == '')
            $this->dataArray['tw'] = '';
        else if ($this->isValidURL ($tw))
            $this->dataArray['tw'] = $tw;

        else
            $this->allOK = false;
    }

    public function set_blog($blog) {

        $blog = trim($blog);
        
        if ($blog == '')
            $this->dataArray['blog'] = '';
        else if ($this->isValidURL ($blog))
            $this->dataArray['blog'] = $blog;

        else
            $this->allOK = false;
    }

    public function set_site($site) {

        $site = trim($site);

        if ($site == '')
            $this->dataArray['site'] = '';
        else if ($this->isValidURL ($site))
            $this->dataArray['site'] = $site;

        else
            $this->allOK = false;
    }

    public function set_about($about) {

        $about = trim($about);

        if (preg_match('/^[- А-яA-z0-9.\(\);,.!?\"=№:]{0,500}$/u', $about))
            $this->dataArray['about'] = $about;

        else
            $this->allOK = false;
    }

    public function update_user() {

        $data = $this->dataArray;
        $data['id'] = $this->id;

        if (count($data) > 0 && $this->allOK != false) {

            foreach ($data as $field => $value) {

                $value = mysql_real_escape_string($value);

                $updateArray[] = $field . '= "' . $value . '"';
            }

            $selectString = implode(' AND ', $updateArray);

            $sql = 'SELECT * FROM ' . PREFIX . 'users WHERE '.$selectString;
            //var_dump($sql);

            $isDif = get_bd_data($sql);

            if(isset($isDif[0]))
                return 2;

            $updateString = implode(' , ', $updateArray);

            $sql = 'UPDATE ' . PREFIX . 'users SET ' . $updateString . ' WHERE id = ' . $this->id.' LIMIT 1';

            $result = affectedRowsQuery($sql);

            return $result;
        }

        return false;
    }

}
 
