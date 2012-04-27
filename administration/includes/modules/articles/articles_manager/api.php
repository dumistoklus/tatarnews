<?php
include_once '../../../../../core/core.php';
DB_Provider::Instance()->loadProvider('Administration.Users');

if(!User::get()->check_rights('A', 'NA')) {
	echo "Access Denied";
	Logger::append_error('Illegal access: '.User::ip(), __FILE__.':'.__FUNCTION__);
	die;
}

DB_Provider::Instance()->loadProvider("Administration.Articles");

$task = '';

if(isset($_POST['task'])) {
	$task = $_POST['task'];
}

switch ($task) {
    case 'GET_ARTICLES':
        echo articles_list();
        break;
    case 'THIRD_COL':
        third_col();
        break;
    case 'GET_ARTICLE':
        echo sel_article();
        break;
    case 'SAVE_ARTICLE':
         echo save_article();
         break;
     case 'DELETE_ARTICLE':
        delete_article();
        break;
    defualt:
        echo '{failure: true}';
}

function articles_list()
{
    $start = 0;
    $limit = 37;
    
    if(is_isset_post('start', 'limit'))
    {
        $start = $_POST['start'];
        $limit = $_POST['limit'];
    }

    $p = new ArticlesListManagerProvider($start, $limit);

    $result = to_ext_datastore_json($p->get_list());
    $result['total'] = $p->count();

    return json_encode($result);
}

function sel_article() {
    if(is_isset_post('articleId'))
    {
        $p = new ArticleContentProvider($_POST['articleId']);
        return json_encode(to_ext_datastore_json($p->article()));
    }
}

function save_article() {
    if(is_isset_post('aid', 'image', 'header', 'lid', 'preview', 'content', 'source', 'cat', 'archive', 'mainevent'))
    {
        DB_Provider::Instance()->loadProvider('Administration.Articles');

        $p = new EditArticleProvider($_POST['aid']);
        $p->setArchive($_POST['archive']);
        $p->setCat($_POST['cat']);
        $p->setDate($_POST['created_date'],$_POST['created_time']);
        $p->setContent($_POST['content']);
        $p->setHeader($_POST['header']);
        $p->setImage($_POST['image']);
        $p->setLid($_POST['lid']);
        $p->setMainevent($_POST['mainevent']);
        $p->setPreview($_POST['preview']);
        $p->setSource($_POST['source']);

        return (int)$p->edit();
    }

    return 0;
}

function delete_article() {

    PluginModule::load('CompanyPlugin');

    if (is_isset_post('article_ids')) {
        
         DB_Provider::Instance()->loadProvider('Administration.Articles');
 
        $deleteArticle = new DeleteArticleProvider(json_decode($_POST['article_ids']));

        echo $deleteArticle->delete_article();
    }
}

function third_col() {

    PluginModule::load('CompanyPlugin');

    if (is_isset_post('article_ids')) {

        DB_Provider::Instance()->loadProvider('Administration.Articles');

        $thirdColArticle = new ThirdColProvider(json_decode($_POST['article_ids']));

        echo $thirdColArticle->setThirdCol();
    }
}