<?php

class QuestionList {

    private $provider;

    public function __construct($all = false, $start = 0, $limit = 0, $random = false) {

        DB_Provider::Instance()->loadProvider('Plugins.Questions');

        $this->provider = new QuestionListProvider($all, $start, $limit, $random);
    }

    public function questions() {

        return $this->provider->questions();
    }

    public function format_questions() {

        return $this->provider->format_questions();
    }

    public function answers() {

        return $this->provider->answers();
    }

    public function count() {

        return $this->provider->count();
    }

}

class Questions implements IPlugin {

    public static function name() {
        return __CLASS__;
    }

    public static function load($side=0, $order=0) {

        if ($side == Side::CENTER) {

            if (is_isset_post('text', 'id_question') && User::get()->isAuth()) {

                $answer = new Answer();
                $answer->set_id_question($_POST['id_question']);
                $answer->set_user_id(User::get()->id());
                $answer->set_user_login(User::get()->nickname());
                $answer->set_text($_POST['text']);

                $answer->add_answer();
            }
            
            if (User::get()->check_rights('CQ') && isset($_GET['id_answer'])) {
                
                $answer = new Answer();
                
                $answer->deactivate_answer($_GET['id_answer']);
            }

            if (isset($_GET['a_nav']) && (int) $_GET['a_nav'] != 0) {
                $currentpage = (int) $_GET['a_nav'];
            } else
                $currentpage = 1;

            $limitStart = $currentpage * 10 - 10;

            $currentURI = '/?page=questions&a_nav=';

            PluginModule::load('PageNavBar');
            $questionList = new QuestionList(false, $limitStart, 10);
            $navbar = new PageNavBar($currentURI, $questionList->count(), $currentpage);

            return QuestionView::print_view_all($questionList) . $navbar->get();
        } else
        {
            return QuestionView::print_view(new QuestionList(false, 0,5));
        }
    }

}

class QuestionView {

    public static function print_view_all(QuestionList $questionList) {

        HeaderViewData::init()->set_title('Вопросы - TatarNews.ru');

        $dataArray = $questionList->format_questions();

        if (count($dataArray) > 0) {
            $output = '
                <div class="persons">
                  <div class="zagol-warp"><span class="zagol-link-warp">Вопросы</span>
                    <div class="zagol-line"></div>
                  </div>
                  <script type="text/javascript">
                    $(document).ready(function(){
                    var question_anchor = 0;
                    ';           
            if(isset($_GET['qid']))
                $output .= 'question_anchor = '. $_GET['qid'];
            $output .='
                    $(".answers").hide();
                    $(".question-form").hide();
                    if(question_anchor == 0)
                        $(".answers:first").slideDown("normal");
                    else
                        $("#answers_"+question_anchor).slideDown("normal");
                     })
                    </script>';
            foreach ($dataArray as $question) {
                $output .= '
                    <a name="q'.$question["id"].'">
                    <div class="questions-text">
                        <a href="#" class="questions-header">
                           ' . $question["text"] . '
                        </a>
                    </div>';
                $output .= '<div id="answers_'.$question["id"].'" class="answers">';
                if (isset($question["answers"])) {
                    foreach ($question["answers"] as $answer) {
                        $output .= '
                            <div  class="answer-text">
                                <p>'
                                . $answer['text'] .
                                '</p>
                            </div>
                            <div class="answer-footer">                        
                    <a href="/?page=home&user=' . $answer["user_id"] . '">' . $answer["user_login"] . '</a>
                    , ' . date("j", $answer["created"]) . '&nbsp;'
                                . FormatTime::ru_month(date("n", $answer["created"])) . '&nbsp;'
                                . date("Y , G:i", $answer["created"]);
                        if (User::get()->check_rights('CQ'))
                            $output .= ' <a id="deleteanswer_'.$answer["id"].'" class="delete-answer" href="/?page=questions&id_answer=' . $answer["id"] . '">Удалить</a>';
                        $output .= '</div>';
                    } 
                } else                         
                    $output .= '<div class="grey noanswers">Нет записей к данному вопросу.</div>';

                if (User::get()->isAuth())
                    $output .= '
                    <div class="questionanswer">
                        <a class="add-answer" href="#">Добавить запись</a>
                        <div class="question-form">
                            <form class="answer-form" action="#" method="post">
                                <div>
                                <textarea name="text" class="answer-textarea"></textarea>
                                </div>
                                <input type="submit" class="add-answer-button" value="Добавить запись" />
                                <span class="loader"></span>
                                <input type="hidden" name="id_question" value="' . $question["id"] . '" />
                            </form>
                        </div>
                    </div>';
                $output .= '</div>';
            }
            $output .= '
                </div>';
        } else {

            $output = ' 
                <div class="persons">
                  <div class="zagol-warp"><span class="zagol-link-warp">Вопросы</span>
                    <div class="zagol-line"></div>
                  </div>
                  <h2 style="text-align: center;" class="grey">На данный момент вопросов нет!</h2>
                </div>';
        }

        return $output;
    }

    public static function print_view(QuestionList $questionList) {

        $questions = $questionList->questions();
        
        if (!$questions)
            return '';

        $output = '<div class="short-news-in-right-block">
                <div class="zagol-warp">
                <span class="zagol-link-warp">
                <a href="/?page=questions" class="zagol-link">Вопросы</a></span>
                <div class="zagol-line"></div>
                </div>';

        foreach ($questions as $question) {

            $output .= '
                <div class="questions-in-right">                
                   <a  href="/?page=questions&qid='.$question['id'].'#q'.$question['id'].'">' . $question['text'] . '</a>
                </div>';
        }

        $output .= '</div>';

        return $output;
    }

}

class Answer {

    protected $provider;

    public function __construct() {

        DB_Provider::Instance()->loadProvider('Plugins.Questions');

        $this->provider = new AnswerProvider();
    }

    public function set_user_id($user_id) {

        $this->provider->set_user_id($user_id);
    }

    public function set_user_login($user_login) {

        $this->provider->set_user_login($user_login);
    }

    public function set_id_question($id_question) {

        $this->provider->set_id_question($id_question);
    }

    public function set_text($text) {

        $this->provider->set_text($text);
    }

    public function add_answer() {

        return $this->provider->add_answer();
    }

    public function deactivate_answer($id) {

        return $this->provider->deactivate_answer($id);
    }

}

class Question {

    protected $provider;

    public function __construct() {

        DB_Provider::Instance()->loadProvider('Plugins.Questions');

        $this->provider = new QuestionProvider();
    }

    public function set_text($text) {

        $this->provider->set_text($text);
    }

    public function add_question() {

        return $this->provider->add_question();
    }
    
    public function edit_question($id) {

        return $this->provider->edit_question($id);
    }

    public function change_active($id, $active) {

        return $this->provider->change_active($id, $active);
    }

}