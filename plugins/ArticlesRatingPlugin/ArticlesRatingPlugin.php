<?php
class ArticlesRatingAjax {

    private $user_id = null;
    private $article_id = null;
    private $provider;

    public function __construct($user_id, $article_id) {

        $this->article_id = $article_id;
        $this->user_id = $user_id;

        DB_Provider::Instance()->loadProvider('Plugins.ArticlesRatingPlugin');

        $this->provider = new ArticlesRatingPluginProvider($this->user_id, $this->article_id);
    }

    public function make_rating_voice($voice) {

        $makeVoice = $this->provider->make_rating_voice($voice);

        return $makeVoice;
    }
}
