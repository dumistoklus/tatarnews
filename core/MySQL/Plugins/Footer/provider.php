<?php

class FooterProvider {

    private $footerlist = array();
    private $footermenu = array();

    public function __construct() {

        ;
    }

    public function footerlist() {

        $this->get_footerlist();
        return $this->footerlist;
    }

    public function footermenu() {

        $this->get_footermenu();
        return $this->footermenu;
    }

    private function get_footerlist() {

        $sql = 'SELECT * FROM ' . PREFIX . 'footer LIMIT 1';

        $result = get_bd_data($sql);

        if (isset($result[0]))
            $this->footerlist = $result[0];
    }

    private function get_footermenu() {

        $sql = 'SELECT * FROM ' . PREFIX . 'footermenu ORDER BY id';

        $this->footermenu = get_bd_data($sql);
    }

}

class FooterMenuProvider {

    private $dataArray = array();
    protected $allOK = true;

    public function set_name($name) {

        $name = trim($name);

        if (strlen($name) < 200 && strlen($name) > 0)
            $this->dataArray['name'] = $name;

        else
            $this->allOK = false;
    }

    public function set_link($link) {

        $link = trim($link);

        if (strlen($link) < 100 && strlen($link) > 0)
            $this->dataArray['link'] = $link;

        else
            $this->allOK = false;
    }

    public function add_punkt() {

        if (isset($this->dataArray['name']) && isset($this->dataArray['link']) && $this->allOK != false) {

            $name = mysql_real_escape_string($this->dataArray['name']);
            $link = mysql_real_escape_string($this->dataArray['link']);

            $sql = 'INSERT INTO ' . PREFIX . 'footermenu
             (name, link) VALUES ("' . $name . '", "' . $link . '")';

            $result = affectedRowsQuery($sql);

            return $result;
        }
        return $this->link;
    }

    public function update_punkt($id) {

        $id = (int) $id;

        if (count($this->dataArray) > 0 && $this->allOK != false) {

            foreach ($this->dataArray as $field => $value) {

                $value = mysql_real_escape_string($value);

                $updateArray[] = $field . '= "' . $value . '"';
            }

            $updateString = implode(' , ', $updateArray);

            $sql = 'UPDATE ' . PREFIX . 'footermenu SET ' . $updateString . ' WHERE id = ' . $id . ' LIMIT 1';

            $result = affectedRowsQuery($sql);

            return $result;
        }

        return false;
    }

    public function delete_punkt($ids) {

        if (count($ids) > 0) {
            foreach ($ids as $id) {

                $idArray[] = (int) $id;
            }

            $idString = implode(' , ', $idArray);
        }

        $sql = 'DELETE FROM ' . PREFIX . 'footermenu  WHERE id IN (' . $idString . ')';

        $result = affectedRowsQuery($sql);

        return $result;
    }

}

class ManagerFooterProvider {

    protected $dataArray = array();
    protected $allOK = true;

    public function __construct() {

        ;
    }

    public function set_address($address) {

        $address = trim($address);

        if (strlen($address) < 200)
            $this->dataArray['address'] = $address;

        else
            $this->allOK = false;
    }

    public function set_address_for_mail($address_for_mail) {

        $address_for_mail = trim($address_for_mail);

        if (strlen($address_for_mail) < 200)
            $this->dataArray['address_for_mail'] = $address_for_mail;

        else
            $this->allOK = false;
    }

    public function set_phone_reception($phone_reception) {

        $phone_reception = trim($phone_reception);

        if (strlen($phone_reception) < 200)
            $this->dataArray['phone_reception'] = $phone_reception;

        else
            $this->allOK = false;
    }

    public function set_phone_correspondent($phone_correspondent) {

        $phone_correspondent = trim($phone_correspondent);

        if (strlen($phone_correspondent) < 200)
            $this->dataArray['phone_сorrespondent'] = $phone_correspondent;

        else
            $this->allOK = false;
    }

    public function set_phone_commercial($phone_commercial) {

        $phone_commercial = trim($phone_commercial);

        if (strlen($phone_commercial) < 200)
            $this->dataArray['phone_commercial'] = $phone_commercial;

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

    public function set_chief_editor($chief_editor) {

        $chief_editor = trim($chief_editor);

        if (strlen($chief_editor) < 200)
            $this->dataArray['chief_editor'] = $chief_editor;

        else
            $this->allOK = false;
    }

    public function set_first_deputy($first_deputy) {

        $first_deputy = trim($first_deputy);

        if (strlen($first_deputy) < 200)
            $this->dataArray['first_deputy'] = $first_deputy;

        else
            $this->allOK = false;
    }

    public function set_secretary($secretary) {

        $secretary = trim($secretary);

        if (strlen($secretary) < 200)
            $this->dataArray['secretary'] = $secretary;

        else
            $this->allOK = false;
    }

    public function set_deputy($deputy) {

        $deputy = trim($deputy);

        if (strlen($deputy) < 200)
            $this->dataArray['deputy'] = $deputy;

        else
            $this->allOK = false;
    }

    public function set_text($text) {

        $text = trim($text);

        if (strlen($text) < 500)
            $this->dataArray['text'] = $text;

        else
            $this->allOK = false;
    }

}

class EditFooterProvider extends ManagerFooterProvider {

    public function update_footer() {

        if (count($this->dataArray) > 0 && $this->allOK != false) {

            foreach ($this->dataArray as $field => $value) {

                $value = mysql_real_escape_string($value);

                $updateArray[] = $field . '= "' . $value . '"';
            }

            $updateString = implode(' , ', $updateArray);

            $sql = 'UPDATE ' . PREFIX . 'footer SET ' . $updateString . ' WHERE id = 1';

            $result = affectedRowsQuery($sql);

            return $result;
        }

        return false;
    }

}
