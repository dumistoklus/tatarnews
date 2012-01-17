<?php

class VotesPluginProvider {

    private $voteArray = array();
    private $countVoters = null;
    private $resultsArray = array();
    private $userId = null;
    private $voteId = null;
    private $isUserVote;
    private $votelist = array();
    private $currentTimestamp;

    public function __construct($userId = null, $voteId = null) {

        $this->userId = (int)$userId;

        $this->voteId = (int)$voteId;

        $this->currentTimestamp = time();

        if ($this->voteId == null) {

            $this->voteArray = $this->get_voteArray();

            if (count($this->voteArray) > 0 && $this->isUserVote != true) {

                $this->isUserVote = $this->get_isUserVote();
            }
            
        } else {

            $this->voteArray = $this->get_voteArray_by_id($this->voteId);
        }

        if (count($this->voteArray)>0) {

            $this->resultsArray = $this->get_resultsArray();
        }
    }

    public function makeVoice($userId, $voteId, $answerId) {

        $userId = (int)$userId;
        $voteId = (int)$voteId;
        $answerId = (int)$answerId;

        if ($userId != 0 && $voteId != 0 && $answerId != 0) {


            $isVote = get_bd_data('SELECT COUNT(*) as c  FROM ' . PREFIX . 'voters
                WHERE id_user = ' . $userId . ' AND id_vote = ' . $voteId);


            if($isVote[0]['c'] == 0) {
                $sql = 'INSERT INTO ' . PREFIX . 'voters (id_vote, id_answer, id_user) VALUES (' . $voteId . ' , ' . $answerId . ', ' . $userId . ')';
                $data = affectedRowsQuery($sql);
            
                return $data;
            }
        }
        return 0;
    }

    public function vote_array() {

        return $this->voteArray;
    }

    public function results_array() {

        return $this->resultsArray;
    }

    public function isUserVote() {

        return $this->isUserVote;
    }

    private function get_isUserVote() {

        if ((int)$this->userId != null) {

            $sql = 'SELECT COUNT(*)  FROM ' . PREFIX . 'voters WHERE id_user = ' . $this->userId . ' AND id_vote = ' . $this->voteArray["id"];

            $result = get_bd_data($sql);

            if ($result[0]['COUNT(*)'] > 0)
                return true;
        }

        return false;
    }

    private function get_vote_id_where_user_dont_vote() {

        $sql = 'SELECT id_vote FROM ' . PREFIX . 'voters WHERE id_user  = '.$this->userId;

        $result = get_bd_data($sql);

        return $result;
    }

    private function get_voteArray_by_id($vote_id) {

        if (Value::is_numeric($vote_id)) {

            $sql = 'SELECT * FROM ' . PREFIX . 'votes WHERE id = ' . $vote_id . '
            AND date_start < ' . $this->currentTimestamp . '
                AND date_end > ' . $this->currentTimestamp . '
                    AND active = 1
                LIMIT 1';

            $this->voteArray = get_bd_data($sql);

            $sql = 'SELECT COUNT(*) FROM ' . PREFIX . 'voters WHERE id_vote = ' . $this->voteArray[0]["id"];

            $this->countVoters = get_bd_data($sql);

            return $this->format_voteArray();
        }
        return $this->voteArray;
    }

    private function get_voteArray() {
        
        if($this->userId != null){

            $sql = 'SELECT * FROM ' . PREFIX . 'votes
                WHERE id NOT IN(SELECT id_vote FROM fms_voters WHERE id_user = '.$this->userId.') AND
                date_start < ' . $this->currentTimestamp . '
                AND date_end > ' . $this->currentTimestamp . '
                    AND active = 1 ORDER BY RAND() LIMIT 1';

            $this->voteArray = get_bd_data($sql);
        }

        if (count($this->voteArray) == 0) {
        $sql = 'SELECT * FROM ' . PREFIX . 'votes  WHERE date_start < ' . $this->currentTimestamp . '
                AND date_end > ' . $this->currentTimestamp . '
                    AND active = 1 ORDER BY RAND() LIMIT 1';

        $this->voteArray = get_bd_data($sql);
        }
        if (count($this->voteArray) == 0) {

            $sql = 'SELECT * FROM ' . PREFIX . 'votes 
                    ORDER BY RAND() LIMIT 1';

            $this->voteArray = get_bd_data($sql);

            $this->isUserVote = true;
        }


        if (count($this->voteArray) > 0) {
            $sql = 'SELECT COUNT(*) FROM ' . PREFIX . 'voters WHERE id_vote = ' . $this->voteArray[0]["id"];

            $this->countVoters = get_bd_data($sql);

            $this->voteArray = $this->format_voteArray();

            return $this->voteArray;
        }

        return $this->voteArray;
    }

    private function get_resultsArray() {

        $sql = 'SELECT COUNT(*) as c, id_answer as a FROM ' . PREFIX . 'voters WHERE id_vote = ' . $this->voteArray["id"] . ' GROUP BY id_answer ORDER BY id_answer';

        $this->resultsArray = get_bd_data($sql);

        return $this->format_resultsArray();
    }

    private function format_resultsArray() {

        $formatted_resultsArray = array();

        foreach ($this->resultsArray as $result) {

            $formatted_resultsArray[$result['a']] = $result['c'];
        }

        foreach ($this->voteArray['answersArray'] as $id => $answer) {

            if (empty($formatted_resultsArray[$id]))
                $formatted_resultsArray[$id] = 0;
        }

        return $formatted_resultsArray;
    }

    private function format_voteArray() {

        $formatted_voteArray = array();
        $formatted_voteArray['answersArray'] = unserialize($this->voteArray[0]["answers"]);
        $formatted_voteArray['name'] = $this->voteArray[0]['name'];
        $formatted_voteArray['id'] = $this->voteArray[0]['id'];
        $formatted_voteArray['date_end'] = $this->voteArray[0]['date_end'];
        $formatted_voteArray['date_start'] = $this->voteArray[0]['date_start'];
        $formatted_voteArray['active'] = $this->voteArray[0]['active'];
        $formatted_voteArray['countVoters'] = $this->countVoters[0]['COUNT(*)'];

        return $formatted_voteArray;
    }

}

class VoteListProvider {

    private $userId = null;
    private $currentTimestamp = null;
    private $votelist = array();
    private $resultsArrayAll = array();
    private $idString = '';
    private $isUserVoteAll = array();
    private $countVotersAll = array();

    public function __construct($userId = null) {

        $this->userId = (int)$userId;

        $this->currentTimestamp = time();

        $this->votelist = $this->get_voteArray_all();

        if ($this->userId != 0) {

            $this->isUserVoteAll = $this->is_user_vote_all();
        }

        if (count($this->votelist) > 0) {
            
            $this->resultsArrayAll = $this->get_resultsArray_all();
            $this->countVotersAll = $this->get_countvoters_all();
        }
    }

    public function voteArray_all() {

        return $this->votelist;
    }

    public function resultsArray_all() {

        return $this->resultsArrayAll;
    }

    public function isUserVoteAll() {

        return $this->isUserVoteAll;
    }

    public function countvoters_all() {

        return $this->countVotersAll;
    }

    private function get_voteArray_all() {

        $sql = 'SELECT * FROM ' . PREFIX . 'votes
            ORDER BY date_end DESC LIMIT 10';

        $this->votelist = get_bd_data($sql);

        $this->format_voteArray();

        return $this->votelist;
    }

    private function get_resultsArray_all() {

        $this->idArray2String();

        $sql = 'SELECT COUNT(*) as c, id_answer as a , id_vote as v FROM ' . PREFIX . 'voters 
            WHERE id_vote IN (' . $this->idString . ') GROUP BY id_vote,id_answer ORDER BY id_vote';

        $this->resultsArrayAll = get_bd_data($sql);

        $this->format_resultsArray_all();

        return $this->resultsArrayAll;
    }

    private function get_countvoters_all() {

        $this->idArray2String();

        $sql = 'SELECT COUNT(*) as c, id_vote as v FROM ' . PREFIX . 'voters
            WHERE id_vote IN (' . $this->idString . ') GROUP BY id_vote ORDER BY id_vote';

        $this->countVotersAll = get_bd_data($sql);

        $this->format_countvotersAll();

        return $this->countVotersAll;
    }

    private function format_countvotersAll() {

        $formatCountvotersAll = array();

        foreach ($this->countVotersAll as $countVoters) {

            $formatCountvotersAll[$countVoters['v']] = $countVoters['c'];
        }

        $this->countVotersAll = $formatCountvotersAll;
    }

    private function idArray2String() {

        foreach ($this->votelist as $vote) {

            $idArray[] = $vote['id'];
        }

        $this->idString = implode(",", $idArray);
    }

    private function is_user_vote_all() {

        $this->idArray2String();

        $sql = 'SELECT id_vote FROM ' . PREFIX . 'voters
            WHERE id_user =' . $this->userId . ' AND id_vote IN (' . $this->idString . ')';

        $this->isUserVoteAll = get_bd_data($sql);

        $this->format_is_user_vote_all();

        return $this->isUserVoteAll;
    }

    private function  format_is_user_vote_all() {

        $formatIsUserVote = array();

        foreach ($this->isUserVoteAll as $vote) {

            $formatIsUserVote[$vote['id_vote']] = true;
        }

        $this->isUserVoteAll = $formatIsUserVote;
    }

    private function format_resultsArray_all() {

        $formatReultsAll = array();

        foreach ($this->resultsArrayAll as $result) {

            $formatReultsAll[$result['v']][$result['a']] = $result['c'];
        }

        $this->resultsArrayAll = $formatReultsAll;
    }

    private function format_voteArray() {

        $count = count($this->votelist);

        for ($i = 0; $i < $count; $i++) {

            $this->votelist[$i]['answers'] = unserialize($this->votelist[$i]['answers']);
        }
    }

}

class ManagerVoteProvider {

    protected $dataArray = array();
    protected $allOK = true;

    public function __construct() {

        ;
    }

    public function set_name($name) {

        $name = trim($name);

        if (preg_match('/^[- А-яA-z0-9.\(\);,.!?\"=№:]{0,300}$/u', $name))
            $this->dataArray['name'] = $name;

        else
            $this->allOK = false;
    }

    public function set_answers($answers) {

        if (count($answers) > 0) {
            foreach ($answers as $id=>$answer) {

                $answersFormat[$id+1] = $answer;
            }
            $this->dataArray['answers'] = serialize($answersFormat);
        }
        else
            $this->allOK = false;
    }

    public function set_active($active) {

        $active = trim($active);

        if (preg_match('/^[0-1]{1}$/', $active))
            $this->dataArray['active'] = $active;

        else
            $this->allOK = false;
    }

    public function set_date_start($date_start) {

        $date_start = trim($date_start);

        if (preg_match('/^[0-9]{1,60}$/', $date_start))
            $this->dataArray['date_start'] = $date_start;

        else
            $this->allOK = false;
    }

    public function set_date_end($date_end) {

        $date_end = trim($date_end);

        if (preg_match('/^[0-9]{1,60}$/', $date_end))
            $this->dataArray['date_end'] = $date_end;

        else
            $this->allOK = false;
    }

}

class NewVoteProvider extends ManagerVoteProvider {

    public function create_vote() {

        if (count($this->dataArray) > 0 && $this->allOK != false) {

            foreach ($this->dataArray as $field => $value) {

                $value = mysql_real_escape_string($value);

                $fieldsArray[] = $field;
                $valuesArray[] = '"' . $value . '"';
            }

            $fieldsString = implode(' , ', $fieldsArray);
            $valuesString = implode(' , ', $valuesArray);

            $sql = 'INSERT INTO ' . PREFIX . 'votes (' . $fieldsString . ') VALUES (' . $valuesString . ')';

            $result = affectedRowsQuery($sql);

            return $result;
        }

        return false;
    }

}

class EditVoteProvider extends ManagerVoteProvider {

    private $id = 0;

    public function __construct($id) {

        $this->id = (int) $id;

        if ($this->id == 0)
            return false;
    }

    public function update_vote() {

        if (count($this->dataArray) > 0 && $this->allOK != false) {

            foreach ($this->dataArray as $field => $value) {

                $value = mysql_real_escape_string($value);

                $updateArray[] = $field . '= "' . $value . '"';
            }

            $updateString = implode(' , ', $updateArray);

            $sql = 'UPDATE ' . PREFIX . 'votes SET ' . $updateString . ' WHERE id = ' . $this->id;

            $result = affectedRowsQuery($sql);

            return $result;
        }

        return false;
    }

}

class DeleteVoteProvider {

    private $id = 0;

    public function __construct($id) {

        $this->id = (int) $id;

        if ($this->id == 0)
            return false;
    }

    public function delete_vote() {

        $sql = 'DELETE FROM ' . PREFIX . 'votes WHERE id = ' . $this->id;

        $result = affectedRowsQuery($sql);

        return $result;
    }

}