<?php

class Comments {

    private $commentsArray = array();
    private $id_article = null;

    public function __construct($id_article) {

        $this->id_article = $id_article;

        DB_Provider::Instance()->loadProvider('Plugins.CommentsPlugin');

        $provider = new CommentsPluginProvider($this->id_article);

        $this->commentsArray = $provider->get_comments();
    }

    public function commentsArray() {

        return $this->commentsArray;
    }

}

class CommentsPlugin implements IPlugin {

    public static function name() {
        return __CLASS__;
    }

    public static function load($side=0, $order=0) {

        return CommentsView::print_view(new Comments(1));
    }

}

class CommentsView {

    public static function print_view(Comments $comments) {

        $list = $comments->commentsArray();
        $formatdate = new FormatTime();

        
          $commentshtml = '
          <div id="comments-warp">
          <div id="comments">
          <div id="comments-title">
          <div class="zagol-warp">
          <span class="zagol-link-warp">
          Комментарии
          </span>
          <div class="zagol-line">
          </div>
          </div>
          </div>';
         

        if (count($list) > 0) {

            $level = 0;

            foreach ($list as $comment) {

                if ($comment['level'] != 0) {

                    $levelparent = $level;
                    $level = $comment['level'];
                    $countdivs = $levelparent - $level;

                    for ($i = 0; $i <= $countdivs; $i++)
                        $commentshtml .= '
                                </div><!---->';

                    $commentshtml .= '<div class="comment" id="comment_' . $comment["id"] . '">

                <div class="comment-point-warp">
                    <div class="comment-point">
                    </div>
                </div>
                <div class="comment-text">
                    <p>
                        ' . $comment["text"] . '
                    </p>
                </div>

                <div class="comment-footer">
                    <div class="comment-rating comment-rating-red">
                        <a href="#" class="comment-like"></a>
                        <div class="rating-rate">
                            -2
                        </div>
                        <a href="#" class="comment-dislike"></a>
                    </div>
                    <a href="/user/' . $comment["user_name"] . '">' . $comment["user_name"] . '</a>,
                    , ' . date("j", $comment["created"]).'&nbsp;'
                            .$formatdate->ru_month(date("n", $comment["created"])). '&nbsp;'
                            .date("Y, G", $comment["created"]).'
                    <a href="#" class="answer-for-comment">Ответить</a>
                </div>';
                }
            }

            $levelparent = $level;
            $level = 1;
            $countdivs = $levelparent - $level;

            for ($i = 0; $i <= $countdivs; $i++)
                $commentshtml .= '
                                </div><!--endcom-->';
            
        } else {

            $commentshtml = '';
        }

        if (User::get()->isAuth()) {

            $commentshtml .= '
                </div>
                <div class="new-comment">
                    <a href="#" class="answer-for-comment">Написать комментарий</a>
                </div>';
        } else {

            $commentshtml .= '
                <div class="not-comments">
                Оставлять комментарии могут только зарегистрированные пользователи.
                <br />
                <a href="#" class="login">Войдите</a>
                или
                <a href="#" class="registration">зарегистрируйтесь</a>
                </div>';
        }

        $commentshtml .= '</div>';

        return $commentshtml;
    }

}

class CommentsAjax {

    private $article_id = null;

    public function __construct($article_id) {

        $this->article_id = $article_id;

        DB_Provider::Instance()->loadProvider('Plugins.CommentsPlugin');
    }

    public function  new_comment($dataArray) {

        $provider = new CommentsPluginProvider($this->article_id);

        $newcomment = $provider->set_new_comment($dataArray);

        return $newcomment;
    }
    
}

class ManagerPerson {
    /*
      name                имя
      sirname             фамилия
      lastname            отчество
      dob                 дата рождения
      img                 картинка
      education           образование
      scope               сфера деятельности
      post                должность
      job                 место работы
      career              этапы карьеры
      coordinates         координаты
      phone               телефон
      fax                 факс
      email               e-mail
      unknown_contact     неизвестный контакт
      marital             семейное положение
      pob                 место рождения
     */

    protected $dataArray = array();
    protected $provider;

    public function set_name($name) {

        $this->provider->set_name($name);
    }

    public function set_sirname($sirname) {

        $this->provider->set_sirname($sirname);
    }

    public function set_lastname($lastname) {

        $this->provider->set_lastname($lastname);
    }

    public function set_img($img) {

        $this->provider->set_img($img);
    }

    public function set_dob($dob) {

        $this->provider->set_dob($dob);
    }

    public function set_education($education) {

        $this->provider->set_education($education);
    }

    public function set_scope($scope) {

        $this->provider->set_scope($scope);
    }

    public function set_post($post) {

        $this->provider->set_post($post);
    }

    public function set_job($job) {

        $this->provider->set_job($job);
    }

    public function set_career($career) {

        $this->provider->set_career($career);
    }

    public function set_coordinates($coordinates) {

        $this->provider->set_coordinates($coordinates);
    }

    public function set_phone($phone) {

        $this->provider->set_phone($phone);
    }

    public function set_fax($fax) {

        $this->provider->set_fax($fax);
    }

    public function set_email($email) {

        $this->provider->set_email($email);
    }

    public function set_unknown_contact($unknown_contact) {

        $this->provider->set_unknown_contact($unknown_contact);
    }

    public function set_marital($marital) {

        $this->provider->set_marital($marital);
    }

    public function set_pob($pob) {

        $this->provider->set_pob($pob);
    }

}

class NewPerson extends ManagerPerson {

    public function __construct() {

        DB_Provider::Instance()->loadProvider('Plugins.PersonPlugin');

        $this->provider = new NewPersonProvider();
    }

    public function create_person() {

        $result = $this->provider->create_person();

        return $result;
    }

}

class EditPerson extends ManagerPerson {

    public function __construct($id) {

        DB_Provider::Instance()->loadProvider('Plugins.PersonPlugin');

        $this->provider = new EditPersonProvider($id);
    }

    public function update_person() {

        $result = $this->provider->update_person();

        return $result;
    }

}

class DeletePerson {

    public function __construct($id) {

        DB_Provider::Instance()->loadProvider('Plugins.PersonPlugin');

        $this->provider = new DeletePersonProvider($id);
    }

    public function delete_person() {

        $result = $this->provider->delete_person();

        return $result;
    }

}