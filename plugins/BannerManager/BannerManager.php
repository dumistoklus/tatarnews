<?php

class BannerManager implements IPlugin
{
    public static function name() {
        return __CLASS__;
    }

    public static function load($side, $order) {

        if(!User::get()->isAuth() || !User::get()->check_rights('B') ) {
            goto404();
            return;
        }

        DB_Provider::Instance()->loadProvider('Plugins.BannerManager');

        HeaderViewData::init()->append_script( '/js/brbrbr.js' );
        HeaderViewData::init()->append_script( '/js/jquery-ui-1.8.12.custom.min.js' );
        HeaderViewData::init()->append_style( '/css/jquery-ui-1.8.12.custom.css' );


        $BannerManagerView = new BannerManagerView();
        return $BannerManagerView->get_output();
    }
}

class BannerManagerView {

    private $templatesList;
    private $errors;
    private $template;
    private $template_id;
    private $messages = array(
        'bannercreated' => 'Создан новый шаблон баннера',
        'bannerdeleted' => 'Баннер удален'
    );

    public function __construct() {

        $TemplateController = new TemplateController;
        $this->template = $TemplateController->get_template();
        $this->errors = $TemplateController->get_errors();
        $this->template_id = $TemplateController->get_template_id();
        $this->templatesList = BannerSelector::init()->get_templates_list();
        $this->its_image = $TemplateController->get_banner_type();

        new BannerController;
    }
    
    public function get_output() {

        $messages = $this->get_messages();

        $errors = '';
        $templatesList = '';
        $tabs1 = '<div id="tabs">'.
                        '<ul>'.
                            '<li><a href="#tabs-1">Загрузить изображение</a></li>'.
                            '<li><a href="#tabs-2">HTML - код</a></li>'.
                        '</ul>'.
                        '<div id="tabs-1">'.
                            '<dl>'.
                                '<dt class="form-title">&nbsp;</dt>'.
                                '<dd class="cabinet-birthday"><input type="file" name="image" /></dd>'.
                                '<dt class="form-title">Размер баннера:</dt>'.
                                '<dd class="cabinet-birthday">ширина <input type="number" name="width" value="'.$this->template['width'].'" class="typetext smallint" />px.&nbsp; &nbsp; &nbsp; высота:'.
                                    '<input type="text" name="height" value="'.$this->template['height'].'" class="typetext smallint" />px.</dd>'.
                                '<dt class="form-title">Ссылка:</dt>'.
                                '<dd class="cabinet-birthday"><input type="text" value="'.$this->template['link'].'" name="link" class="typetext" style="width: 400px;" /></dd>'.
                            '</dl>'.
                        '</div>'.
                        '<div id="tabs-2">';
        $tabs2 = '</div>'.
                    '</div>';
        $template_html = htmlspecialchars( $this->template['html'], ENT_QUOTES );

        if ( $this->hasErrors() ) {

            $errors .= '<div class="warning"><ul>';
            foreach ( $this->errors as $error )
                $errors .= '<li>'.$error.'</li>';
            $errors .= '</ul></div>';
        }

        if ( $this->hasTemplates() ) {

            $templatesList = '<form action="/?page=BannerManager" method="post">'.
                                '<dl><dt>&nbsp;</dt><dd class="cabinet-birthday"><select name="template_id">';
            foreach ( $this->templatesList as $template ) {
                $templatesList .= '<option value="'.$template['template_id'].'"';
                if ( $this->template_id == $template['template_id'])
                    $templatesList .= 'selected="selected"';
                $templatesList .= '>'.$template['template_name'].'</option>';
            }
            $templatesList .= '</select></dd><dt class="form-title"><a href="/?page=BannerManager">Создать новый</a></dt><dd>'.
                              '<input type="submit" value="Отредактировать шаблон"  class="button" />'.
                              '<input type="submit" name="deletetemplate" value="Удалить" onclick="return confirm(\'Вы действительно хотите удалить шаблон?\');" class="button" />'.
                              '</dd></dl></form><div class="zagol-line"></div>';
        }

        if ( $this->template_id ) {
            $template_id = '<input type="hidden" value="'.$this->template_id.'" name="template_id" />'.
                           '<input type="hidden" value="1" name="edittemplate" />';
        } else {
            $template_id = '<input type="hidden" value="1" name="newtemplate" />';
        }

        if ( $this->template_id ) {
            $tabs1 = $tabs2 = '';
        }

        $output = $messages.
                  $errors.
                  '<div id="brbrbr-manager" style="background-color: #ddd; padding-bottom: 20px;">'.$templatesList.'
        <form action="/?page=BannerManager" method="post" enctype="multipart/form-data">'.
            '<dl>'.
                '<dt class="form-title">Название:</dt>'.
                '<dd class="cabinet-birthday"><input type="text" value="'.$this->template['template_name'].'" name="template_name" class="typetext" style="width: 400px;" /></dd>'.
                '<dt class="form-title">Содержимое:</dt>'.
                '<dd class="cabinet-birthday">'.
                        $tabs1.
                            '<textarea name="html" id="aboutme">'.$template_html.'</textarea>'.
                        $tabs2.
                '</dd>'.
                '<dt class="form-title">Баннер виден:</dt>'.
                '<dd class="cabinet-birthday">с <input type="text" name="date_start" value="'.$this->template['date_start'].'" class="typetext datepicker" /> по '.
                    '<input type="text" name="date_end" value="'.$this->template['date_end'].'" class="typetext datepicker" /></dd>'.
                '<dt class="cabinet-birthday">&nbsp;</dt>'.
                '<dd class="cabinet-birthday">'.
                    $template_id.
                    '<input type="submit" value="Сохранить шаблон баннера" class="button" />'.
                    '</dd>'.
                '</dl>'.
        '</form></div>';

        return $output;
    }

    private function get_messages() {
        if ( isset( $_GET['mess'] ) AND isset($this->messages[$_GET['mess']]) ) {
            return '<div class="warning">'.$this->messages[$_GET['mess']].'</div>';
        }
    }

    private function hasErrors() {
        return !empty( $this->errors );
    }

    private function hasTemplates() {
        return !empty( $this->templatesList );
    }
}

class BannerSelector {

    private static $instance;
    private $TemplatesList = array();

    public function __construct() {

        $this->SelectTemplatesListFromDB();
        PluginModule::load('Banner');
    }

    public static function init() {
        return (self::$instance === null) ? self::$instance = new self() : self::$instance;
    }

    public function getBannerSelector( $side, $order ) {

        DB_Provider::Instance()->loadProvider('Plugins.Banner');
        DB_Provider::Instance()->loadProvider('Plugins.BannerManager');

        $bannersOnThisSideAndOrder = BannerManagerDataLoader::init()->get_banners( $side, $order );
        $count = count( $bannersOnThisSideAndOrder );

        $output = '<div style="background-color: #ddd; margin-bottom: 10px; padding: 5px;">';
        if ( $count )
            $output .= '<ul class="nolist">';

        for( $i = 0; $i < $count; $i++ ) {

            if ( isset( $this->TemplatesList[$bannersOnThisSideAndOrder[$i]['template_id']] ) )

            $output .= '<li>'.$this->TemplatesList[$bannersOnThisSideAndOrder[$i]['template_id']]['template_name'].
                       ' <a href="/?page=BannerManager&amp;deletebanner='.$bannersOnThisSideAndOrder[$i]['banner_id'].'">X</a></li>';

        }
        if ( $count )
            $output .= '</ul>';

        $output .= '<form action="/?page=BannerManager" method="post">'.
                    $this->get_select( $side, $order ).
                    '<input type="submit" value="Добавить" /></form></div>';

        return $output;
    }

    public function get_templates_list() {
        return $this->TemplatesList;
    }

    private function get_select( $side, $order ) {

        $output = '<div style="padding-bottom:5px;"><select name="banner['.$side.']['.$order.']"><option value="0">--- выберите баннер ---</option>';

        foreach ( $this->TemplatesList as $template  ) {

            if ( strtotime( $template['date_end'] ) < time() AND $template['date_end'] != '0000-00-00' )
                continue;

            $output .= '<option value="'.$template['template_id'].'"';

            if ( strtotime( $template['date_end'] ) >= time() OR $template['date_end'] == '0000-00-00'  ) {
                $output .= ' style="color: green;">';
            } else {
                $output .= '>с '.$template['date_start'];
            }

            $output .= ' '.$template['template_name'].'</option>';
        }

        $output .= '</select></div>';

        return $output;
    }

    private function SelectTemplatesListFromDB() {

        $TemplatesList = BannerManagerProvider::init()->get_templates_list();

        foreach ($TemplatesList as $template ) {
            $this->TemplatesList[$template['template_id']] = $template;
        }
    }
}

class TemplateController {

    private $template_id = 0;
    private $errors = array();
    private $template = array(
        'html' => '',
        'template_name' => '',
        'date_start' => '0000-00-00',
        'date_end' => '0000-00-00',
        'width' => 0,
        'height' => 0,
        'link' => ''
    );
    private $its_image = false;
    private $banner_path = '/images/brbrbr/';

    public function __construct() {

        if ( $this->route_template_id() ) {
            $this->SelectTemplateFromDB();
            if ( isset( $_POST['edittemplate'] )) {
                $this->edittemplate();
            } else if ( isset( $_POST['deletetemplate'] ) ){
                $this->deletetemplate();
            }
        }

        else if ( isset( $_POST['newtemplate'] ) ) {
            $this->newtemplate();
        }
    }

    public function get_errors() {
        return $this->errors;
    }

    public function get_template() {
        return $this->template;
    }

    public function get_template_id() {
        return $this->template_id;
    }

    public function get_banner_type() {
        return $this->its_image;
    }

    private function route_template_id() {

        if ( isset( $_POST['template_id'] ) ) {
            return $this->template_id = (int)$_POST['template_id'];
        }
        return FALSE;
    }

    private function SelectTemplateFromDB() {

        $template = BannerManagerProvider::init()->get_template( $this->template_id );

        if ( !empty( $template ) ) {
            $this->template = $template[0];
            $this->template['width'] = 0;
            $this->template['height'] = 0;
            $this->template['link'] = '';
        }
    }

    private function edittemplate(){

        if ( $this->checkparams() ) {
            
            if ( BannerManagerProvider::init()->save_template( $this->template_id, $this->template ) ) {
                BannerManagerProvider::init()->save_banners( $this->template_id, $this->template );
                $this->errors[] = 'Сохранено';
                return TRUE;
            }

            $this->errors[] = 'Изменений не было';
        }

        return FALSE;
    }

    private function newtemplate() {
        
        if ( $this->checkparams() ) {

            if ( $this->its_image )
                $this->create_banner();

            if ( BannerManagerProvider::init()->insert_template( $this->template ) ) {
                URIManager::redirect_to('/?page=BannerManager&mess=bannercreated');
                return TRUE;
            } else {
                $this->errors[] = 'Не удалось создать новый шаблон';
            }
        }
        return FALSE;
    }

    private function checkparams() {

        if ( isset(
                $_POST['template_name'],
                $_POST['html'],
                $_POST['date_start'],
                $_POST['date_end']
        ) ) {

            $errors = array();

            $this->template = array(
                'template_name' => trim( $_POST['template_name'], ENT_QUOTES ),
                'html' => trim($_POST['html']),
                'date_start' => $this->set_right_date( $_POST['date_start'] ),
                'date_end' => $this->set_right_date( $_POST['date_end'] ),
                'width' => isset( $_POST['width'] ) ? (int)$_POST['width'] : 0,
                'height' => isset( $_POST['height'] ) ? (int)$_POST['height'] : 0,
                'link' => isset( $_POST['link'] ) ? trim( $_POST['link'] ) : 0
            );

            $this->its_image = ( isset( $_FILES['image'] ) || !empty( $this->template['link'] ) );

            if ( empty( $this->template['template_name'] ) )
                $errors[] = 'Укажите название баннера';

            if ( !$this->its_image ) {
                if ( empty( $this->template['html'] ) )
                $errors[] = 'Баннер пуст';
            }

            if ( empty( $errors) )
                return TRUE;

            $this->errors = $this->errors + $errors;
            return FALSE;

        } else {
            $this->errors[] = 'Произошла неизвестная ошибка';
            return FALSE;
        }
    }

    private function create_banner() {

        $image = new Image;

        if ( !$image->check_image() ) {
            $this->errors = $this->errors + $image->get_error();
            return FALSE;
        }

        if ( !$image->upload_image( env::vars()->ROOT_PATH.$this->banner_path ) ) {
            $this->errors = $this->errors + $image->get_error();
            return FALSE;
        }

        if ( $this->template['width'] != 0 || $this->template['height'] != 0 ) {
            $image->resize_banner( $this->template['width'], $this->template['height'] );
        }

        if ( !empty( $this->template['link']) )
            $this->template['html'] .= '<a href="'.$this->template['link'].'" rel="nofollow">';

        $this->template['html'] .= '<img src="'.$this->banner_path.$image->get_filename().'" width="'.$image->get_width().'" height="'.$image->get_heigth().'" />';

        if ( !empty( $this->template['link']) )
            $this->template['html'] .= '</a>';
    }

    private function set_right_date( $date ) {
        return ( strtotime( trim( $date ) ) ) ? date( 'Y-m-d', strtotime( trim( $date ) ) ) : '0000-00-00';
    }

    private function deletetemplate() {
        if ( BannerManagerProvider::init()->delete_template( $this->template_id ) ) {

            URIManager::redirect_to('/?page=BannerManager&mess=bannerdeleted');
            return TRUE;
        }
    }
}

class Image {

    private $errors = array();
    private $file;
    private $max_file_size = 10485760;
    private $width;
    private $height;
    private $filetype;
    private $destination_path;

    public function check_image() {
        
        if ( !isset( $_FILES['image'] ) ) {
            $this->error[] = 'Изображение не загружено!';
            return FALSE;
        }

        $this->file = $_FILES['image'];

        if ( $this->file['size'] > $this->max_file_size ) {
            $this->error[] = 'Изображение слишком большое!';
            return FALSE;
        }

        if ( preg_match( '/(\.(?:jpg|jpeg|gif|png))/i', $this->file['name'], $razresh ) != 1 ) {
            $this->error[] = 'Можно только JPEG, PNG и GIF!';
            return FALSE;
        }

        if ( !is_uploaded_file( $this->file['tmp_name'] ) ) {
            $this->error[] = 'Файл не загружен';
            return FALSE;           
        }

        $size_img = getimagesize( $this->file['tmp_name'] );

        if ( $size_img === FALSE ) {
            $this->errors[] = 'Изображение повреждено или имеет неизвестный формат';
            return FALSE;
        }

        $this->filetype = mb_strtolower( $razresh[0] );
        list($this->width, $this->height) = $size_img;
        return TRUE;
    }

    public function get_error() {
        return $this->errors;
    }

    public function get_width() {
        return $this->width;
    }

    public function get_heigth() {
        return $this->height;
    }

    public function get_filename() {
        return $this->file['name'];
    }

    public function upload_image( $dest_path ) {

        $this->destination_path = $dest_path;
        if ( move_uploaded_file( $this->file['tmp_name'] , $dest_path.$this->file['name'] ) ) {
            $this->error[] = 'Файл не загружен';
            return TRUE;
        }

        return FALSE;
    }

    public function resize_banner( $width, $height ) {

        $dest_width = ( $width != 0 && $width < $this->width ) ? $width : $this->width;
        $dest_height = ( $height != 0 && $height < $this->height ) ? $height : $this->height;

        $result_of_resizing = $this->resizeimg( $this->destination_path.$this->file['name'], $this->destination_path.$this->file['name'], $dest_width, $dest_height );

        if ( $result_of_resizing ) {
            $this->width = $result_of_resizing['newwidth'];
            $this->height = $result_of_resizing['newheight'];
            return TRUE;
        }
        
        return FALSE;
    }

    private function resizeimg( $filename, $smallimage, $newwidth, $newheight, $ser = FALSE ) {

		// определим коэффициент сжатия изображения, которое будем генерить
		$ratio = $newwidth/$newheight;
		// получим размеры исходного изображения
		$size_img = getimagesize($filename);


		// Если размеры меньше, то масштабирования не нужно
		if (($size_img[0]<$newwidth) && ($size_img[1]<$newheight)) {
            if ( $filename != $smallimage )
			    copy( $filename, $smallimage );
			return true;
		}
		// получим коэффициент сжатия исходного изображения
		$src_ratio=$size_img[0]/$size_img[1];

		// Здесь вычисляем размеры уменьшенной копии, чтобы при масштабировании сохранились
		// пропорции исходного изображения

		if ( !$ser ) {
			if ($ratio<$src_ratio)
			{
				$newheight = floor( $newwidth/$src_ratio );
			}
			else
			{
				$newwidth = floor( $newheight*$src_ratio );
			}
		}

		// создадим пустое изображение по заданным размерам
		$dest_img = imagecreatetruecolor($newwidth, $newheight);
		if ($size_img[2]==2)  $src_img = @imagecreatefromjpeg($filename);
		else if ($size_img[2]==1) $src_img = @imagecreatefromgif($filename);
		else if ($size_img[2]==3) $src_img = @imagecreatefrompng($filename);

		if ( !$src_img ) {
			return FALSE;
		}

		$sw = $sh = 0;
		if ( $ser ) {
			// если ширина меньше высоты
			if ( $src_ratio < 1 ) {
				$sh = ceil( ( $size_img[1] - $size_img[0] ) / 2 );
				$size_img[1] -= 2*$sh;
			} else {
				$sw = ceil( ( $size_img[0] - $size_img[1] ) / 2 );
				$size_img[0] -= 2*$sw;
			}
		}

		// масштабируем изображение	 функцией imagecopyresampled()
		// $dest_img - уменьшенная копия
		// $src_img - исходной изображение
		// $w - ширина уменьшенной копии
		// $h - высота уменьшенной копии
		// $size_img[0] - ширина исходного изображения
		// $size_img[1] - высота исходного изображения
		imagecopyresampled($dest_img, $src_img, 0, 0, $sw, $sh, $newwidth, $newheight, $size_img[0], $size_img[1] );
		// сохраняем уменьшенную копию в файл
		if ($size_img[2]==2)  imagejpeg($dest_img, $smallimage);
		else if ($size_img[2]==1) imagegif($dest_img, $smallimage);
		else if ($size_img[2]==3) imagepng($dest_img, $smallimage);
		// чистим память от созданных изображений
		imagedestroy($dest_img);
		imagedestroy($src_img);
		return array( 'newwidth' => $newwidth, 'newheight' => $newheight );
	}
}

class BannerController {

    private $side;
    private $order;
    private $template_id;

    public function __construct() {
        
        if ( isset( $_POST['banner'] ) ) {
            $this->init();
        }

        if ( isset( $_GET['deletebanner'] ) ) {
            $this->delete_banner( $_GET['deletebanner'] );
        }
    }

    private function init() {

        foreach ( $_POST['banner'] as $bannerSide => $bannerOrderArray ) {
            if ( is_array( $bannerOrderArray ) ) {
                foreach ( $bannerOrderArray as $bannerOrder => $template_id ) {
                    if ( $this->check_side_position_template_id( $bannerSide, $bannerOrder, $template_id ) )
                        $this->create_banner();
                }
            }
        }
    }

    private function check_side_position_template_id( $side, $order, $template_id ) {
        $side = (int)$side;
        $order = (int)$order;
        $template_id = (int)$template_id;

        if ( 1 > $side OR $side > 5 )
            return FALSE;

        if ( $order <= 0 )
            return FALSE;

        if ( $template_id <= 0 )
            return FALSE;

        $this->side = $side;
        $this->order = $order;
        $this->template_id = $template_id;

        return TRUE;
    }

    private function create_banner() {

        if ( BannerManagerProvider::init()->create_banner( $this->side, $this->order, $this->template_id ) ) {
            URIManager::redirect_to('/?page=BannerManager');
            return TRUE;
        }

        return FALSE;
    }

    private function delete_banner( $banner_id ) {

        $banner_id = (int)$banner_id;
        if ( $banner_id == 0 )
            return false;

        if ( BannerManagerProvider::init()->delete_banner( $banner_id ) ) {
            URIManager::redirect_to('/?page=BannerManager');
            return TRUE;
        }

        return FALSE;
    }
}

class BannerManagerDataLoader {

    private static $instance;
    private $banners;
    private $BannerProvider;

    public static function init() {
        return (self::$instance === null) ? self::$instance = new self() : self::$instance;
    }

    public function __construct() {

        $this->BannerProvider = new BannerManagerProvider();
        $this->get_future_banners_from_db();
    }

    public function get_banners( $side, $order ) {

        if ( isset ( $this->banners[$side][$order] ) )
            return $this->banners[$side][$order];

        return array();
    }

    public function get_future_banners_from_db() {

        $banners = $this->BannerProvider->get_future_banners();
        if ( !empty( $banners ) ) {
            foreach ( $banners as $value ) {
                $this->banners[$value['side']][$value['order']][] = $value;
            }
        }
    }

     public function get_banner_selector($side, $order) {

         return BannerSelector::init()->getBannerSelector( $side, $order );
     }
}