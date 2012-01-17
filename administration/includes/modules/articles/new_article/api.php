<?php

include_once '../../../../../core/core.php';

if(!User::get()->check_rights('A', 'NA')) {
	echo "Access Denied";
	Logger::append_error('Illegal access: '.User::ip(), __FILE__.':'.__FUNCTION__);
	die;
}

$task = '';

define('ARTICLES_HEADER', '/images/articles_header/');

if(isset($_REQUEST['task'])) {
	$task = $_REQUEST['task'];
}

switch ($task) {
    case 'GET_IMAGES':
        echo images_list();
        break;
    case 'DELETE_IMAGE':
        if(!User::get()->check_rights('FU')) break;
        echo delete_image();
        break;
    case 'GET_CATS':
        echo categories();
        break;
    case 'UPLOAD_FILE':
        if(!User::get()->check_rights('FU')) {
            break;
        }
        echo upload_images();
        break;
    case 'GET_ARCHIVE':
        echo archive();
        break;
    case 'SAVE_ARTICLE':
         echo save_article();
         break;
    defualt:
        echo '{failure: true}';
}

function images_list() {
    $dir = ARTICLES_HEADER;
    $dir_thumbs = ARTICLES_HEADER.'.thumbs/';

    $images = array();
    $d = dir(env::vars()->ROOT_PATH.$dir);
    while($name = $d->read()){
        if(!preg_match('/\.(jpg|gif|png|JPG|GIF|PNG)$/', $name)) continue;
        $size = filesize(env::vars()->ROOT_PATH.$dir.$name);
        $lastmod = filemtime(env::vars()->ROOT_PATH.$dir.$name)*1000;
        $thumb = md5_file(env::vars()->ROOT_PATH.$dir.$name)."_100_100_2.jpg";
        $images[] = array('name' => $name, 'size' => $size,
                'lastmod' => $lastmod, 'url' => $dir.$name,
                'thumb_url' => $dir_thumbs.$thumb);
    }
    $d->close();
    $o = array('images'=>$images);
    return json_encode($o);
}

function delete_image()
{
    $dir = ARTICLES_HEADER;
    $dir_thumbs = ARTICLES_HEADER.'.thumbs/';
    if(isset($_POST['images'])) {
        $arrayImg = explode(";", $_POST['images']);

        foreach($arrayImg as $imgname) {
            if ($imgname != "") {
                unlink(env::vars()->ROOT_PATH.$dir_thumbs.md5_file(env::vars()->ROOT_PATH.$dir.$imgname)."_100_100_2.jpg");
                unlink(env::vars()->ROOT_PATH.$dir.$imgname);
            }
        }

        return '{success: true}';
    }

    return '{success: false}';
}

function upload_images()
{
    $dir = env::vars()->ROOT_PATH.ARTICLES_HEADER;
    
    if(!is_dir($dir.'/.thumbs')) {
        mkdir($dir.'/.thumbs');
    }

    $dbfile = $dir.'/.thumbs/.db';
    if(is_file($dbfile)) {
        $dbfilehandle = fopen($dbfile, "r");
        $dblength = filesize($dbfile);
        if($dblength>0) $dbdata = fread($dbfilehandle, $dblength);
        fclose($dbfilehandle);
        //$dbfilehandle = fopen($dbfile, "w");
    } else {
        //$dbfilehandle = fopen($dbfile, "w");
    }

    if(!empty($dbdata)) {
        $files = unserialize($dbdata);
    }
    else $files = array();
   
    sort($_FILES['img']);
    $ufiles = $_FILES['img'];

    if (strpos($ufiles[3], '.') !== false) {
        $ext = explode('.', $ufiles[3]);
        $extension = array_pop($ext);
        $filename = substr($ufiles[3], 0, strlen($ufiles[3]) - strlen($extension) - 1);
        
    } else {
        return '{success:false, failed: 1, uploaded: 0}';
    }
    $allowed = array('jpeg','jpg','gif','png');

    if(!in_array(strtolower($extension),$allowed)) {
        return '{success:false, failed: 1, uploaded: 0}';
    }
    
    Module::load('Utils.Images.PHPExifReader');
    Module::load('Utils.Images.Image_Toolbox');
   
    $t = new Image_Toolbox($_FILES['img'][1]);
    
    $t->newOutputSize(130, 0, 0, false, '#FFFFFF');
    $t->save($ufiles[1], 'jpg', 80);

    $md5 = md5_file($ufiles[1]);
    $file = $md5.'.'.$extension;
    
    $files[$file]['imageinfo'] = getimagesize($ufiles[1]);
    
    if(empty($files[$file]['imageinfo'])) {
        return '{success:false, failed: 1, uploaded: 0}';
    }


    if(!move_uploaded_file($ufiles[1],$dir.'/'.$filename.'.'.$extension)) {
         return '{success:false, failed: 1, uploaded: 0}';
    }

    $link = str_replace(array('/\\','//','\\\\','\\'),'/', '/'.str_replace(realpath(env::vars()->ROOT_PATH),'',realpath($dir.'/'.$filename.'.'.$extension)));
    $path = pathinfo($link);
    $path = $path['dirname'];


    if($extension=='jpg' || $extension=='jpeg') {
        $er = new PHPExifReader($dir.'/'.$filename.'.'.$extension);
        $files[$file]['exifinfo'] = $er->getImageInfo();

        $files[$file]['general'] = array(
            'filename' => $file,
            'name'	=> $filename,
            'ext'	=> $extension,
            'path'	=> $path,
            'link'	=> $link,
            'size'	=> filesize($dir.'/'.$filename.'.'.$extension),
            'date'	=> filemtime($dir.'/'.$filename.'.'.$extension),
            'width'	=> $files[$file]['imageinfo'][0],
            'height'=> $files[$file]['imageinfo'][1],
            'md5'	=> $md5
        );
    } else {
        $files[$file]['general'] = array(
            'filename' => $file,
            'name'	=> $filename,
            'ext'	=> $extension,
            'path'	=> $path,
            'link'	=> $link,
            'size'	=> filesize($dir.'/'.$filename.'.'.$extension),
            'date'	=> filemtime($dir.'/'.$filename.'.'.$extension),
            'width'	=> $files[$file]['imageinfo'][0],
            'height'=> $files[$file]['imageinfo'][1],
            'md5'	=> $md5
        );
    }



    $dbfilehandle = fopen($dbfile, "w");
    fwrite($dbfilehandle, serialize($files));
    fclose($dbfilehandle);

    $t = new Image_Toolbox($dir.'/'.$filename.'.'.$extension);
    $t->newOutputSize(100, 100, 2, false, '#FFFFFF');
    $t->save($dir.'/.thumbs/'.$md5.'_100_100_2.jpg', 'jpg', 80);

    return '{success:true, failed: 0, uploaded: 1}';
}

function categories()
{
    DB_Provider::Instance()->loadProvider('Administration.Articles');

    $p = new ArticlesCatsProvider();

    return json_encode(to_ext_datastore_json($p->cats()));
}
function archive()
{
    DB_Provider::Instance()->loadProvider('Administration.NArchive');
    if(!isset($_POST['getAll']))
        $provider = new NewsPaperArchiveAdministrationProvider(0, 10);
    else
        $provider = new NewsPaperArchiveAdministrationProvider();
    $archive = $provider->archive();

    $result = array();

    for($i = 0; $i < sizeof($archive); $i++)
    {
        $result[$i]['id'] = $archive[$i]['id'];
        $result[$i]['name'] = $archive[$i]['number'].' ('.$archive[$i]['number_total'].') '.date('d.m.Y', $archive[$i]['date_start']).' - '.date('d.m.Y', $archive[$i]['date_end']);
    }

    $result = to_ext_datastore_json($result);

    return json_encode($result);
}
function save_article()
{
    /*
     *
     * [header] => ывп
     [lid] => выпывап
    [preview] => <p>выпывп</p>
    [content] => <p>ывпывп</p>
    [image] => 546.jpg
    [source] => пывапывап
    [cat] => 15
    [date] => 1301947200
     */
    if(is_isset_post('header', 'lid', 'preview', 'content', 'image', 'source', 'cat', 'date', 'archive'))
    {
        DB_Provider::Instance()->loadProvider('Administration.Articles');

        $article = new NewArticleProvider();
        $article->cat = $_POST['cat'];
        $article->content = $_POST['content'];
        $article->date = $_POST['date'];
        $article->image = $_POST['image'];
        $article->header = $_POST['header'];
        $article->lid = $_POST['lid'];
        $article->preview = $_POST['preview'];
        $article->source = $_POST['source'];
        $article->archive = $_POST['archive'];

        $aid = $article->create();

        $success = ($aid > 0);

        if (!$success) return false;

        if($_POST['mainevent'] == 'true')
        {
            DB_Provider::Instance()->loadProvider('Administration.MainEvent');

            $p = new NewMainEventProvider();
            $p->aid = $aid;
            $p->preview = $article->header;
            $p->date_start = $article->date;

            $sc = $p->create();
            if(!$sc) return 'not_event';
        }

        return $success;
    }
    else return false;
}