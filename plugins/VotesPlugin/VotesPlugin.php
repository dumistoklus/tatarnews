<?php

class Vote {

    private $vote = array();
    private $name = '';
    private $answersArray = array();
    private $countVoters = null;
    private $resultsArray = array();
    private $userId = null;
    private $id = null;
    private $isUserVote = null;

    public function __construct() {

        if (User::get()->isAuth()) {

            $this->userId = User::get()->id();
        }

        DB_Provider::Instance()->loadProvider('Plugins.VotesPlugin');

        $provider = new VotesPluginProvider($this->userId);

        $this->vote = $provider->vote_array();

        if (count($this->vote) > 0) {

            $this->isUserVote = $provider->isUserVote();

            $this->name = $this->vote['name'];

            $this->id = $this->vote['id'];

            $this->resultsArray = $provider->results_array();

            $this->answersArray = $this->vote['answersArray'];

            $this->countVoters = $this->vote['countVoters'];
        }
    }

    public function __get($name) {

        return $this->$name;
    }

}

class VoteList {

    private $userId = null;
    private $voteArray = array();
    private $resultsArray = array();
    private $isUserVoteAll = array();
    private $countVotersAll = array();

    public function __construct() {

        if (User::get()->isAuth()) {

            $this->userId = User::get()->id();
        }


        DB_Provider::Instance()->loadProvider('Plugins.VotesPlugin');

        $provider = new VoteListProvider($this->userId);

        $this->voteArray = $provider->voteArray_all();

        $this->resultsArray = $provider->resultsArray_all();

        $this->countVotersAll = $provider->countvoters_all();

        if ($this->userId != null) {

            $this->isUserVoteAll = $provider->isUserVoteAll();
        }
    }

    public function __get($name) {

        return $this->$name;
    }

    public function voteArray() {

        return $this->voteArray;
    }

    public function resultsArray() {

        return $this->resultsArray;
    }

    public function isUserVoteAll() {

        return $this->isUserVoteAll;
    }

    public function countVotersAll() {

        return $this->countVotersAll;
    }

}

class VotesPlugin implements IPlugin {

    public static function name() {
        return __CLASS__;
    }

    public static function load($side=0, $order=0) {

        if (isset($_GET['page']) && $_GET['page'] == 'votes') {

            $votelist = new VoteList();

            return VoteView::print_view_all($votelist);
        }

        $vote = new Vote();

        if (!User::get()->isAuth() && count($vote->vote) > 0) {

            return VoteView::print_view_results($vote);
        } else if ($vote->isUserVote == false && count($vote->vote) > 0) {

            return VoteView::print_view_vote($vote);
        } else if (count($vote->vote) > 0) {

            return VoteView::print_view_results($vote);
        }
    }

}

class VoteView {

    public static function print_view_all(VoteList $votelist) {

        HeaderViewData::init()->set_title('Голосования - TatarNews.ru');

        $resultsArray = $votelist->resultsArray();
        $isUserVoteArray = $votelist->isUserVoteAll();
        $countVoters = $votelist->countVotersAll();
        $currentTime = time();

        $votelisthtml = '
            <div class="polls">
                <div class="zagol-warp">
                    <div class="zagol-line">Голосования</div>
                </div>';

        foreach ($votelist->voteArray() as $vote) {

            if (User::get()->id() && empty($isUserVoteArray[$vote['id']]) && $vote['date_end'] > $currentTime && $vote['active'] == 1) {

                $votelisthtml .= '
                <a name="pool' . $vote["id"] . '"></a>
              <div id = "vote_' . $vote["id"] . '" class="poll-one">
                <div class="poll-header">
                <a name="polls"></a>' . $vote["name"] . '</div>
                <div class="poll-answers">
                  <form action="#polls">
                    <div class="poll-options">';

                foreach ($vote["answers"] as $id => $answer) {

                    $votelisthtml .= '
                    <div class="poll-option">
                        <label>
                          <input  type="radio" name="answer" value="' . $id . '" />
                         ' . $answer . '</label>
                      </div>';
                }

                $votelisthtml .= '
                    </div>
                    <div class="poll-button">
                      <input class = "voteBtn" type="button" value="Голосовать" />
                    </div>
                  </form>
                </div>
              </div>';
            } else {

                $votelisthtml .= '
            <a name="pool' . $vote["id"] . '"></a>
              <div class="poll-one">
                <div class="poll-header">' . $vote["name"] . '</div>
                ';

                foreach ($vote["answers"] as $id => $answer) {

                    if (isset($resultsArray[$vote['id']][$id]) && $resultsArray[$vote['id']][$id] != 0) {

                        $percent = round($resultsArray[$vote['id']][$id] / $countVoters[$vote['id']] * 100);
                        $result = $resultsArray[$vote['id']][$id];
                    } else {
                        $percent = 0;
                        $result = 0;
                    }
                    $votelisthtml .= '
                <table class="poll-lines">
                  <tr>
                    <td class="poll-text poll-percent">' . $percent . '%</td>
                    <td class="poll-answer">' . $answer . '</td>
                  </tr>
                  <tr>
                    <td class="poll-text poll-hint">' . $result . '</td>

                    <td><div class="poll-line" style="width: ' . $percent . '%;"></div></td>
                  </tr>
                </table>';
                }

                $votelisthtml .= '
                <div class="poll-hint">Проголосовало: ';

                if(isset($countVoters[$vote["id"]]))
                    $votelisthtml .= $countVoters[$vote["id"]];
                else {
                    $votelisthtml .= '0';
                    $countVoters[$vote["id"]] = 0;
                }

                $arrayForFomatWords = array(0=>'человек',
                            1=>'человека',
                            2=>'человек');

                $votelisthtml .=' '.FormatWordByCount::render_padezh_chisla($countVoters[$vote["id"]],$arrayForFomatWords).'. ';

                if ($currentTime < $vote["date_end"]) {

                    $votelisthtml .= 'Голосование еще не закончено. ';
                } else if ($vote["active"] == 1) {

                    $votelisthtml .= 'Голосование закончено. ';

                    $formatdate = new FormatTime();

                    $votelisthtml .= date('j', $vote["date_end"]) . '&nbsp;'
                            . $formatdate->ru_month(date('n', $vote["date_end"])) . '&nbsp;'
                            . date('Y', $vote["date_end"]) . ' года';
                }


                $votelisthtml .= '
                    </div>
              </div>';
            }
        }

        $votelisthtml .= '
            </div>
              <script type="text/javascript">
              $(".voteBtn").click(function () {
              var voteId = $(this).parent().parent().parent().parent().attr("id");
              var answerId = $(this).parent().parent().parent().find("form :radio[name=answer]:checked").val();
              $.ajax({
              url: "ajax/VotesListPlugin.php",
              type: "POST",
              data: ({voteId : voteId, answerId : answerId}),
              success: function (data) {
                    if(data != 0)
                        $("#"+voteId).html(data);
                    else
                        alert("Ваш голос не был учтён!");
                        }
                    });
              });
              </script>
            ';

        return $votelisthtml;
    }

    public static function print_view_vote(Vote $vote) {

        $votehtml = '<div id="voteajax" class="mainpolls">
                    <div class="zagol-warp">
                        <span class="zagol-link-warp">
                        <a href="/?page=votes#pool' . $vote->id . '" class="zagol-link">Голосование</a>
                        </span>
                        <div class="zagol-line">
                        </div>
                    </div>

           <div id="mainpoll">
                        <div class="poll-header">
                            <a name="polls"></a>' . $vote->name . '</div>
            <form action="#polls">
                <div class="poll-options">';

        $votehtml .='<input id="voteId" type="hidden" value="' . $vote->id . '">';

        foreach ($vote->answersArray as $id => $answer)
            $votehtml .='<div class="poll-option"><label>
                <input type="radio" name="answer" value="' . $id . '" />
                ' . $answer . '</label></div>';

        $votehtml .='</div><div class="poll-button">
            <input id="voteBtn" type="button" value="Голосовать" />
          </div>
        </form>
        </div>';

        $votehtml .='
               <div class="zagol-warp">
                        <span class="zagol-link-warp-left">
                        <a href="/?page=votes#pool' . $vote->id . '" class="zagol-link">Проголосовавших: ' . $vote->countVoters . '</a>
                        </span>
                        <div class="zagol-line">
                        </div>
                    </div>
                    </div>


              <script type="text/javascript" src = "js/jquery-1.2.6.js" ></script>
              <script type="text/javascript">
              $("#voteBtn").click(function () {
              var voteId = $("#voteId").val();
              var answerId = $("form :radio[name=answer]:checked").val();
              $.ajax({
              url: "ajax/VotesPlugin.php",
              type: "POST",
              data: ({voteId : voteId, answerId : answerId}),
              success: function (data) {
                    if (data != 0)
                        $("#voteajax").html(data);
                    else
                        alert("Голос не был учтён!");
                            }
                        });
              });
              </script>
        ';

        return $votehtml;
    }

    public static function print_view_results(Vote $vote) {

        $results = $vote->resultsArray;
        $countVoters = $vote->countVoters;


        $votehtml = '<div class="mainpolls">
      <div class="zagol-warp">
                        <span class="zagol-link-warp">
                        <a href="/?page=votes#pool'.$vote->id.'" class="zagol-link">Голосование</a>
                        </span>
                        <div class="zagol-line">
                        </div>
                    </div>

           <div id="mainpoll">
                        <div class="poll-header">
                <a name="polls"></a>' . $vote->name . '</div>';

        foreach ($vote->answersArray as $id => $answer) {

            if ($results[$id] != 0)
                $percent = round($results[$id] / $countVoters * 100);
            else
                $percent = 0;

            $votehtml .=
                    '<table class="poll-lines">
                 <tr>
                    <td class="poll-text poll-percent">' . $percent . '%</td>
                    <td class="poll-answer">' . $answer . '</td>
                </tr>
                <tr>
                    <td class="poll-text poll-hint">' . $results[$id] . '</td>
                    <td><div class="poll-line" style="width: ' . $percent . '%;"></div></td>
                </tr>
            </table>';
        }

        $votehtml .='</div>
                        <div class="zagol-warp">
                        <span class="zagol-link-warp-left">
                        <a href="/?page=votes#pool'.$vote->id.'" class="zagol-link">Проголосовавших: ' . $countVoters . '</a>

                        </span>
                        <div class="zagol-line">
                        </div>
                    </div>
                    </div>
';

        return $votehtml;
    }

}

class VotesAjax {

    public $userId;
    public $voteId;
    public $answerId;

    public function __construct($userId, $voteId, $answerId) {

        $this->userId = $userId;
        $this->voteId = $voteId;
        $this->answerId = $answerId;

        DB_Provider::Instance()->loadProvider('Plugins.VotesPlugin');
    }

    public function makeVoice() {

        $provider = new VotesPluginProvider($this->userId, $this->voteId);

        $db = $provider->makeVoice($this->userId, $this->voteId, $this->answerId);

        return $db;
    }

    public function view() {

        $provider = new VotesPluginProvider($this->userId, $this->voteId);

        $vote = $provider->vote_array();

        $results = $provider->results_array();

        $votehtml = '
                    <div class="zagol-warp">
                        <span class="zagol-link-warp">
                        <a href="/?page=votes#pool'.$vote["id"].'" class="zagol-link">Голосование</a>
                        </span>
                        <div class="zagol-line">
                        </div>
                    </div>

           <div id="mainpoll">
                        <div class="poll-header">
                <a name="polls"></a>' . $vote['name'] . '</div>';

        foreach ($vote['answersArray'] as $id => $answer) {

            if ($results[$id] != 0)
                $percent = round($results[$id] / $vote["countVoters"] * 100);
            else
                $percent = 0;

            $votehtml .=
                    '<table class="poll-lines">
                 <tr>
                    <td class="poll-text poll-percent">' . $percent . '%</td>
                    <td class="poll-answer">' . $answer . '</td>
                </tr>
                <tr>
                    <td class="poll-text poll-hint">' . $results[$id] . '</td>
                    <td><div class="poll-line" style="width: ' . $percent . '%;"></div></td>
                </tr>
            </table>';
        }

        $votehtml .='</div>
                        <div class="zagol-warp">
                        <span class="zagol-link-warp-left">
                        <a href="/?page=votes#pool'.$vote["id"].'" class="zagol-link">Проголосовавших: ' . $vote["countVoters"] . '</a>

                        </span>
                        <div class="zagol-line">
                        </div>
                    </div>';

        return $votehtml;
    }

    public function viewList() {

        $provider = new VotesPluginProvider($this->userId, $this->voteId);

        $vote = $provider->vote_array();

        $results = $provider->results_array();

        $currentTime = time();

        $votehtml = '<div class="poll-header">' . $vote["name"] . '</div>';

        foreach ($vote['answersArray'] as $id => $answer) {

            if ($results[$id] != 0)
                $percent = round($results[$id] / $vote["countVoters"] * 100);
            else
                $percent = 0;

            $votehtml .= '
            <table class="poll-lines">
                 <tr>
                    <td class="poll-text poll-percent">' . $percent . '%</td>
                    <td class="poll-answer">' . $answer . '</td>
                </tr>
                <tr>
                    <td class="poll-text poll-hint">' . $results[$id] . '</td>
                    <td><div class="poll-line" style="width: ' . $percent . '%;"></div></td>
                </tr>
            </table>';
        }

        $votehtml .= '<div class="poll-hint">Проголосовало: '.$vote["countVoters"].' человек.';

            if ($currentTime < $vote["date_end"]) {

                    $votehtml .= 'Голосование еще не закончено';
                } else if ($vote["active"] == 1) {

                    $votehtml .= 'Голосование закончено ';

                    $formatdate = new FormatTime();

                    $votehtml .= date('j', $vote["date_end"]) . '&nbsp;'
                            . $formatdate->ru_month(date('n', $vote["date_end"])) . '&nbsp;'
                            . date('Y', $vote["date_end"]) . ' года';
                }


            return $votehtml;
    }

}

class ManagerVote {
    /*
      name                название голосования
      answers             массив ответов начиная с 1
      active              активность 1 или 0
      date_start          дата начала unixtime
      date_end            дата окончания    unixtime
     */

    protected $dataArray = array();
    protected $provider;

    public function set_name($name) {

        $this->provider->set_name($name);
    }

    public function set_answers($answers) {

        $this->provider->set_answers($answers);
    }

    public function set_active($active) {

        $this->provider->set_active($active);
    }

    public function set_date_start($date_start) {

        $this->provider->set_date_start($date_start);
    }

    public function set_date_end($date_end) {

        $this->provider->set_date_end($date_end);
    }

}

class NewVote extends ManagerVote {

    public function __construct() {

        DB_Provider::Instance()->loadProvider('Plugins.VotesPlugin');

        $this->provider = new NewVoteProvider();
    }

    public function create_vote() {

        $result = $this->provider->create_vote();

        return $result;
    }

}

class EditVote extends ManagerVote {

    public function __construct($id) {

        DB_Provider::Instance()->loadProvider('Plugins.VotesPlugin');

        $this->provider = new EditVoteProvider($id);
    }

    public function update_vote() {

        $result = $this->provider->update_vote();

        return $result;
    }

}

class DeleteVote {

    public function __construct($id) {

        DB_Provider::Instance()->loadProvider('Plugins.PersonPlugin');

        $this->provider = new DeleteVoteProvider($id);
    }

    public function delete_vote() {

        $result = $this->provider->delete_vote();

        return $result;
    }

}