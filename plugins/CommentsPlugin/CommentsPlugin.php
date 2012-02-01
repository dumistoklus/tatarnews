<?php

class Comments {

    private $commentsArray = array();
    private $commentsArrayFormatted = array();
    private $id_article = null;

    public function __construct($id_article) {

        $this->id_article = (int) $id_article;

        DB_Provider::Instance()->loadProvider('Plugins.CommentsPlugin');

        $provider = new CommentsPluginProvider($this->id_article);

        $this->commentsArray = $provider->comments_array();

        $this->commentsArrayFormatted['childs'] = $this->get_schema();
    }

    public function commentsArrayFormatted() {

        return $this->commentsArrayFormatted;
    }

    public function get_schema($id_parent = 0) {

        $result = array();

        foreach ($this->commentsArray as $comment) {

            if ($comment['id_parent'] == $id_parent) {

                $comment['childs'] = $this->get_schema($comment['id']);
                $result[] = $comment;
            }
        }
        return $result;
    }

}

class CommentsPlugin implements IPlugin {

    public static function name() {
        return __CLASS__;
    }

    public static function load($side=0, $order=0) {

        if(env::vars()->article_id > 0) {

        $commentsWiew = new CommentsView(new Comments(env::vars()->article_id));

        return $commentsWiew->print_view();
        }
    }

}

class CommentsView {

    public $commentsArray; //многомерный массив комментариев
    public $commentshtml = '';

    public function __construct(Comments $comments) {

        $this->commentsArray = $comments->commentsArrayFormatted();
    }

    public function print_view() {

        $resulthtml =
            '<div id="comments-warp">
                <div id="comments">
                    <div id="comments-title">
                        <div class="zagol-warp">
                            <span class="zagol-link-warp">
                            Комментарии
                            </span>
                            <div class="zagol-line"></div>
                        </div>
                    </div>'.
		            $this->print_view_tree().
                '</div>';

            $resulthtml .= '
                <div class="new-comment">
                    <a href="#" class="answer-for-comment">Написать комментарий</a>
                </div>';
	    
        $resulthtml .='</div>';

        return $resulthtml;
    }

    public function print_view_tree($commentsView = 1) {

        if ($commentsView == 1)
            $commentsView = $this->commentsArray['childs'];

        if (count($commentsView) > 0) {
            foreach ($commentsView as $comment) {

                if ($comment["isdelete"] == 0 || User::get()->check_rights('DC')) {
                    $this->commentshtml .= '
            <div class="comment" id="comment_' . $comment["id"] . '">
                <div class="comment-point-warp">
                    <div class="comment-point"></div>
                </div>
                <div  id="commenttext_'.$comment["id"].'" class="comment-text';
                if ($comment["isdelete"] == 1)
                    $this->commentshtml .= ' grey ';
                $this->commentshtml .= '">
                    <p>
                        ' . $comment["text"] . '
                    </p>
                </div>
                <div  id="commentfooter_'.$comment["id"].'"class="comment-footer">
                        ';

                    if ($comment["rating"] > 0)
                        $this->commentshtml .= '
                    <div id="rating_'.$comment["id"].'" class="comment-rating comment-rating-green">
                        <a href="#" class="comment-like"></a>
                            <div class="rating-rate">+'.$comment["rating"].'</div>
                        <a href="#" class="comment-dislike"></a>
                    </div>';

                    else if ($comment["rating"] == 0)
                        $this->commentshtml .= '
                    <div  id="rating_'.$comment["id"].'"  class="comment-rating">
                        <a href="#" class="comment-like"></a>
                            <div class="rating-rate">'.$comment["rating"].'</div>
                        <a href="#" class="comment-dislike"></a>
                    </div>';

                    else if ($comment["rating"] < 0)
                        $this->commentshtml .= '
                    <div  id="rating_'.$comment["id"].'"  class="comment-rating comment-rating-red">
                        <a href="#" class="comment-like"></a>
                            <div class="rating-rate">'.$comment["rating"].'</div>
                        <a href="#" class="comment-dislike"></a>
                    </div>';

                        $this->commentshtml .= '
                    ' . $comment["user_name"] . ',  ' . date("j", $comment["created"]) . '&nbsp;'
                            . FormatTime::ru_month(date("n", $comment["created"])) . '&nbsp;'
                            . date("Y , G:i", $comment["created"]);

                        $this->commentshtml .= '
                         <a href="#" class="answer-for-comment">Ответить</a>';
                    if(User::get()->check_rights('DC')) {
                        if ($comment["isdelete"] == 0)
                            $this->commentshtml .= '&nbsp;<a id="deletecommment_'.$comment["id"].'_0" class="deletecomment" href="#">Удалить</a>';
                        else
                            $this->commentshtml .= '&nbsp;<a id="deletecommment_'.$comment["id"].'_1" class="deletecomment" href="#">Опубликовать</a>';
                    }
                    $this->commentshtml .= '
                </div>';
                    
                } else {

                    $this->commentshtml .= '
                    <div class="comment" id="comment_' . $comment["id"] . '">
                        <div class="comment-point-warp">
                            <div class="comment-point">
                            </div>
                        </div>

                        <div class="comment-deleted">
                            Комментарий проверяется модератором
                        </div>';

                }

                $this->print_view_tree($comment["childs"]);

                $this->commentshtml .= '</div>';
            }
        }

        return $this->commentshtml;
    }

}

class CommentsAjax {

    private $article_id = null;

    public function __construct($article_id) {

        $this->article_id = $article_id;

        DB_Provider::Instance()->loadProvider('Plugins.CommentsPlugin');

        $this->provider = new CommentsPluginProvider($this->article_id);
    }

    public function new_comment($dataArray) {

        return $this->provider->set_new_comment($dataArray);
    }
    
    public function last_comment() {

        $newcomment = $this->provider->get_last_comment();

        return $newcomment;
    }

}

class CommentsRatingAjax {

    public $provider;

    public function __construct($id_user, $id_comment) {

        DB_Provider::Instance()->loadProvider('Plugins.CommentsPlugin');

        $this->provider = new CommentRatingProvider($id_user, $id_comment);
    }

    public function is_user_make_rating_voice() {

        $ismakeVoice = $this->provider->is_user_make_voice();

        return $ismakeVoice;
    }

    public function make_rating_voice($voice) {

        $makeVoice = $this->provider->make_reiting_voice($voice);

        return $makeVoice;
    }

    public function get_rating() {

        $rating = $this->provider->get_rating();

        return $rating;
    }
}

class CommentDeleteAjax {

    private $provider;

    public function __construct($id_comment) {

        DB_Provider::Instance()->loadProvider('Plugins.CommentsPlugin');

        $this->provider = new CommentProvider($id_comment);
    }

    public function change_active($isdelete) {

        $delete = $this->provider->change_active($isdelete);

        return $delete;
    }
}