<?php

class PersonPluginProvider {

    private $person;
    private $articlesAboutPerson;

    public function get_person_by_id($id) {

        $id = (int) $id;

        $sql = 'SELECT * FROM ' . PREFIX . 'persons WHERE id = ' . $id . ' LIMIT 1;';

        $this->person = get_bd_data($sql);

        if (count($this->person) > 0) {
            $sql = 'SELECT * FROM ' . PREFIX . 'articles
            INNER JOIN ' . PREFIX . 'article2person ON ' . PREFIX . 'article2person.id_person = ' . $this->person[0]["id"] . ' AND ' . PREFIX . 'article2person.id_article = ' . PREFIX . 'articles.id
            ';
            $this->articlesAboutPerson = get_bd_data($sql);

            return $this->format_person();
        }

        return $this->person;
    }

    public function get_person() {
        $sql = 'SELECT * FROM ' . PREFIX . 'persons ORDER BY RAND() LIMIT 1;';

        $this->person = get_bd_data($sql);

        if (count($this->person) > 0) {
            $sql = 'SELECT * FROM ' . PREFIX . 'articles
            INNER JOIN ' . PREFIX . 'article2person ON ' . PREFIX . 'article2person.id_person = ' . $this->person[0]["id"] . ' AND ' . PREFIX . 'article2person.id_article = ' . PREFIX . 'articles.id
            ';
            $this->articlesAboutPerson = get_bd_data($sql);

            return $this->format_person();
        }

        return $this->person;
    }
    
    public function get_articles($id_user) {
        
        $id_user = (int)$id_user;
        
        $sql = 'SELECT * FROM ' . PREFIX . 'articles
            INNER JOIN ' . PREFIX . 'article2person ON ' . PREFIX . 'article2person.id_person = ' . $id_user. ' AND ' . PREFIX . 'article2person.id_article = ' . PREFIX . 'articles.id
            ';

        $this->articlesAboutPerson = get_bd_data($sql);
        
        return $this->articlesAboutPerson;
    }

    public function get_all_person() {
        $sql = 'SELECT * FROM ' . PREFIX . 'persons ORDER BY RAND() LIMIT 10;';

        $this->personlist = get_bd_data($sql);

        return $this->personlist;
    }

    public function get_all_person_by_limit($start = 0, $limit = 10) {

        $sql = 'SELECT * FROM ' . PREFIX . 'persons ORDER BY id DESC'; // LIMIT ' . $start . ' , ' . $limit;

        $personlist = get_bd_data($sql);

        return $personlist;
    }

    public function count() {

        $sql = 'SELECT COUNT(*) as c FROM ' . PREFIX . 'persons';

        $result = get_bd_data($sql);

        return $result[0]['c'];
    }

    private function format_person() {

        $formatted_person = array();
        $formatted_person = $this->person[0];
        $formatted_person['articlesAboutPerson'] = $this->articlesAboutPerson;

        return $formatted_person;
    }

}

class PersonListPluginProvider {
    
    private $count;
    private $persons;
    private $currentTime;
    private $SQL_SELECT_PERSON;

    function __construct($start = 0, $limit = 0) {

        $start = (int) $start;
        $limit = (int) $limit;

        $this->SQL_SELECT_PERSON = 'SELECT * FROM ' . PREFIX . 'persons';

        if ($start == 0 && $limit == 0)
            $this->SQL_SELECT_PERSON = $this->SQL_SELECT_PERSON . ' ORDER BY name';
        else
            $this->SQL_SELECT_PERSON = $this->SQL_SELECT_PERSON . ' ORDER BY name LIMIT ' . $start . ', ' . $limit;
    }

    public function count() {
        if ($this->count == null)
            $this->init_count();

        return $this->count;
    }

    public function persons() {
        if ($this->persons == null)
            $this->init_persons();

        return $this->persons;
    }

    private function init_count() {

        $this->count = get_bd_data('SELECT COUNT(*) FROM ' . PREFIX . 'persons');
        $this->count = $this->count[0]['COUNT(*)'];
    }

    protected function init_persons() {

        $this->persons = get_bd_data($this->SQL_SELECT_PERSON);
    }
}

class Person2ArticlesProvider {

    private $articles = array();

    public function get_all_articles() {

        if (count($this->articles) == 0) {

            $this->init_articles();
        }

        return $this->articles;
    }

    public function add_article2person($id_person, $id_article) {

        $id_article = (int) $id_article;
        $id_person = (int) $id_person;

        $sql = 'SELECT COUNT(*) FROM ' . PREFIX . 'article2person 
            WHERE id_article = ' . $id_article . ' AND id_person = ' . $id_person;
        $count = get_bd_data($sql);

        if ($count[0]["COUNT(*)"] > 0)
            return 2;

        $sql = 'INSERT INTO ' . PREFIX . 'article2person (id_article , id_person)
            VALUES ("' . $id_article . '" , "' . $id_person . '")';

        return affectedRowsQuery($sql);
    }

    private function init_articles() {

        $sql = 'SELECT id, header FROM ' . PREFIX . 'articles ORDER BY id DESC';

        $this->articles = get_bd_data($sql);
    }

}

class ManagerPersonProvider {

    protected $dataArray = array();
    protected $allOK = true;

    public function __construct() {

        ;
    }

    public function set_name($name) {

        $name = trim($name);

        if (strlen($name) < 100 && strlen($name) > 0)
            $this->dataArray['name'] = $name;

        else
            $this->allOK = false;
    }

    public function set_sirname($sirname) {

        $sirname = trim($sirname);

        if (strlen($sirname) < 100)
            $this->dataArray['sirname'] = $sirname;

        else
            $this->allOK = false;
    }

    public function set_lastname($lastname) {

        $lastname = trim($lastname);

        if (strlen($lastname) < 100)
            $this->dataArray['lastname'] = $lastname;

        else
            $this->allOK = false;
    }

    public function set_img($img) {

        $img = trim($img);

        if (strlen($img) < 100)
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

    public function set_education($education) {

        $education = trim($education);

        if (strlen($education) < 500)
            $this->dataArray['education'] = $education;

        else
            $this->allOK = false;
    }

    public function set_scope($scope) {

        $scope = trim($scope);

        if (strlen($scope) < 500)
            $this->dataArray['scope'] = $scope;

        else
            $this->allOK = false;
    }

    public function set_post($post) {

        $post = trim($post);

        if (strlen($post) < 500)
            $this->dataArray['post'] = $post;

        else
            $this->allOK = false;
    }

    public function set_job($job) {

        $job = trim($job);

        if (strlen($job) < 500)
            $this->dataArray['job'] = $job;

        else
            $this->allOK = false;
    }

    public function set_career($career) {

        $career = trim($career);

        if (strlen($career) < 500)
            $this->dataArray['career'] = $career;

        else
            $this->allOK = false;
    }

    public function set_coordinates($coordinates) {

        $coordinates = trim($coordinates);

        if (strlen($coordinates) < 100)
            $this->dataArray['coordinates'] = $coordinates;

        else
            $this->allOK = false;
    }

    public function set_phone($phone) {

        $phone = trim($phone);

        if (strlen($phone) < 100)
            $this->dataArray['phone'] = $phone;

        else
            $this->allOK = false;
    }

    public function set_fax($fax) {

        $fax = trim($fax);

        if (strlen($fax) < 100)
            $this->dataArray['fax'] = $fax;

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

    public function set_unknown_contact($unknown_contact) {

        $unknown_contact = trim($unknown_contact);

        if (strlen($unknown_contact) < 100)
            $this->dataArray['unknown_contact'] = $unknown_contact;

        else
            $this->allOK = false;
    }

    public function set_marital($marital) {

        $marital = trim($marital);

        if (strlen($marital) < 100)
            $this->dataArray['marital'] = $marital;

        else
            $this->allOK = false;
    }

    public function set_pob($pob) {

        $pob = trim($pob);

        if (strlen($pob) < 100)
            $this->dataArray['pob'] = $pob;

        else
            $this->allOK = false;
    }

}

class NewPersonProvider extends ManagerPersonProvider {

    public function create_person() {

        if (count($this->dataArray) > 0 && $this->allOK != false) {

            foreach ($this->dataArray as $field => $value) {

                $value = mysql_real_escape_string($value);

                $fieldsArray[] = $field;
                $valuesArray[] = '"' . $value . '"';
            }

            $fieldsString = implode(' , ', $fieldsArray);
            $valuesString = implode(' , ', $valuesArray);

            $sql = 'INSERT INTO ' . PREFIX . 'persons (' . $fieldsString . ') VALUES (' . $valuesString . ')';

            $result = affectedRowsQuery($sql);

            return $result;
        }

        return false;
    }

}

class EditPersonProvider extends ManagerPersonProvider {

    private $id = 0;

    public function __construct($id) {

        $this->id = (int) $id;

        if ($this->id == 0)
            return false;
    }

    public function update_person() {

        if (count($this->dataArray) > 0 && $this->allOK != false) {

            foreach ($this->dataArray as $field => $value) {

                $value = mysql_real_escape_string($value);

                $updateArray[] = $field . '= "' . $value . '"';
            }
            
            $updateString = implode(' , ', $updateArray);

            $sql = 'UPDATE ' . PREFIX . 'persons SET ' . $updateString . ' WHERE id = ' . $this->id . ' LIMIT 1';

            $result = affectedRowsQuery($sql);

            return $result;
        }

        return false;
    }

}

class DeletePersonProvider {

    private $idString = '';

    public function __construct($ids) {

        if (count($ids) > 0) {
            foreach ($ids as $id) {

                $idArray[] = (int) $id;
            }

            $this->idString = implode(' , ', $idArray);
        }
    }

    public function delete_person() {

        $sql = 'DELETE FROM ' . PREFIX . 'persons WHERE id IN (' . $this->idString . ')';

        $result = affectedRowsQuery($sql);

        return $result;
    }

    public function delete_article2person($id_article) {

        $id_article = (int) $id_article;

        $sql = 'DELETE FROM ' . PREFIX . 'article2person WHERE id_article = ' . $id_article . ' AND id_person = ' . $this->idString;
        $result = affectedRowsQuery($sql);

        return $result;
    }

}
