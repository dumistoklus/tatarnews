<?php

class QuestionListProvider {

    private $count;
    private $questions = array();
    private $answers = array();
    private $formatQuestions = array();
    private $questionsWithAnswers = array();
    private $SQL_SELECT_QUESTIONS;
    private $SQL_COUNT;

    function __construct($all = false, $start = 0, $limit = 0, $random = false) {

        $start = (int) $start;
        $limit = (int) $limit;

        if ($random == false)
            $sort = 'ORDER BY created DESC';
        else
            $sort = 'ORDER BY RAND()';
        if ($all == false)
            $where = 'WHERE active = 1';
        else
            $where = '';

        $this->SQL_SELECT_QUESTIONS = 'SELECT * FROM ' . PREFIX . 'questions ' . $where . ' ' . $sort;

        if ($start == 0 && $limit == 0)
            $this->SQL_SELECT_QUESTIONS = $this->SQL_SELECT_QUESTIONS;
        else
            $this->SQL_SELECT_QUESTIONS = $this->SQL_SELECT_QUESTIONS . ' LIMIT ' . $start . ', ' . $limit;
        
        $this->SQL_COUNT = 'SELECT COUNT(*) FROM ' . PREFIX . 'questions ' . $where;
    }

    public function count() {
        if ($this->count == null)
            $this->init_count();

        return $this->count;
    }

    public function questions() {
        if (count($this->questions) == 0)
            $this->init_questions();

        return $this->questions;
    }

    public function answers() {
        if (count($this->answers) == 0)
            $this->init_answers();

        return $this->answers;
    }

    public function format_questions() {

        if (count($this->formatQuestions) == 0)
            $this->init_format_questions();

        return $this->formatQuestions;
    }

    private function init_format_questions() {

        $this->questions();

        $this->answers();

        if (count($this->questions) > 0) {

            foreach ($this->questions as $question)
                $this->formatQuestions[$question['id']] = $question;

            foreach ($this->answers as $answer)
                $this->formatQuestions[$answer['id_question']]['answers'][] = $answer;
        }
    }

    private function init_count() {

        $this->count = get_bd_data($this->SQL_COUNT);
        $this->count = $this->count[0]['COUNT(*)'];
    }

    protected function init_questions() {

        $this->questions = get_bd_data($this->SQL_SELECT_QUESTIONS);
    }

    private function init_answers() {

        if (count($this->questions) == null)
            return $this->answers;

        foreach ($this->questions as $question)
            $ids[] = '"' . $question['id'] . '"';
        $idsString = implode(' , ', $ids);

        $this->answers = get_bd_data('SELECT * FROM ' . PREFIX . 'answers 
            WHERE id_question IN (' . $idsString . ') AND active = 1 ORDER BY created DESC');
    }

}

class AnswerProvider {

    private $dataArray = array();
    private $allOK = true;

    public function set_user_id($user_id) {

        $user_id = (int) $user_id;

        if ($user_id > 0)
            $this->dataArray['user_id'] = $user_id;

        else
            $this->allOK = false;
    }

    public function set_user_login($user_login) {

        $user_login = trim($user_login);

        if (strlen($user_login) < 200 && strlen($user_login) > 0)
            $this->dataArray['user_login'] = $user_login;

        else
            $this->allOK = false;
    }

    public function set_id_question($id_question) {

        $id_question = (int) $id_question;

        if ($id_question > 0)
            $this->dataArray['id_question'] = $id_question;

        else
            $this->allOK = false;
    }

    public function set_text($text) {

        $text = trim($text);

        if (strlen($text) < 500 && strlen($text) > 0)
            $this->dataArray['text'] = $text;

        else
            $this->allOK = false;
    }

    public function add_answer() {

        if (count($this->dataArray) > 0 && $this->allOK != false) {

            $this->dataArray['created'] = time();
            $this->dataArray['active'] = 1;

            foreach ($this->dataArray as $field => $value) {

                $value = mysql_real_escape_string($value);

                $fieldsArray[] = $field;
                $valuesArray[] = '"' . $value . '"';
            }

            $fieldsString = implode(' , ', $fieldsArray);
            $valuesString = implode(' , ', $valuesArray);

            $sql = 'INSERT INTO ' . PREFIX . 'answers (' . $fieldsString . ') VALUES (' . $valuesString . ')';

            return affectedRowsQuery($sql);
        }

        return false;
    }

    public function deactivate_answer($id) {

        $id = (int) $id;

        if ($id == 0)
            return 0;

        $sql = 'UPDATE ' . PREFIX . 'answers SET active = 0 WHERE id = ' . $id . ' LIMIT 1';

        return affectedRowsQuery($sql);
    }

}

class QuestionProvider {

    private $dataArray = array();
    private $allOK = true;

    public function set_text($text) {

        $text = trim($text);

        if (strlen($text) < 500 && strlen($text) > 0)
            $this->dataArray['text'] = $text;

        else
            $this->allOK = false;
    }

    public function add_question() {

        if (count($this->dataArray) > 0 && $this->allOK != false) {

            $this->dataArray['created'] = time();
            $this->dataArray['active'] = 1;

            foreach ($this->dataArray as $field => $value) {

                $value = mysql_real_escape_string($value);

                $fieldsArray[] = $field;
                $valuesArray[] = '"' . $value . '"';
            }

            $fieldsString = implode(' , ', $fieldsArray);
            $valuesString = implode(' , ', $valuesArray);

            $sql = 'INSERT INTO ' . PREFIX . 'questions (' . $fieldsString . ') VALUES (' . $valuesString . ')';

            return affectedRowsQuery($sql);
        }

        return false;
    }

    public function change_active($id, $active) {

        $id = (int) $id;
        $active = (int) $active;

        if ($id == 0)
            return 0;

        if ($active == 1)
            $active = 0;
        else
            $active = 1;

        $sql = 'UPDATE ' . PREFIX . 'questions SET active = ' . $active . ' WHERE id = ' . $id . ' LIMIT 1';
     
        return affectedRowsQuery($sql);
    }
    
    public function edit_question($id) {

        $id = (int)$id;
        if($id == 0)
            return 0;
        
        if (count($this->dataArray) > 0 && $this->allOK != false) {

            foreach ($this->dataArray as $field => $value) {

                $value = mysql_real_escape_string($value);

                $updateArray[] = $field . '= "' . $value . '"';
            }
            
            $updateString = implode(' , ', $updateArray);

            $sql = 'UPDATE ' . PREFIX . 'questions SET ' . $updateString . ' WHERE id = ' . $id . ' LIMIT 1';
            //var_dump($sql);
            return affectedRowsQuery($sql);
        }

        return 0;
    }

}