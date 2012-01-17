<?php

class CustomPages implements IPlugin
{
    private static $content;
    private static $title;
    private static $keywords;
    private static $description;

    public static function name() {
        return __CLASS__;
    }

    public static function load($side, $order) {

        DB_Provider::Instance()->loadProvider('Plugins.CustomPages');

        /*

        $CustomPageEditor = new CustomPageEditor;

        if ( $CustomPageEditor->route_custom_page_id() AND !$CustomPageEditor->edit_page() ) {
            $errors = $CustomPageEditor->get_errors();
            $output = '<ul>';
            foreach ( $errors as $error ) {
              $output .= '<li>'.$error.'</li>';
            }
            $output .= '</ul>';

            return $output;
        }

         $CustomPageCreator = new CustomPageCreator;

        if (  !$CustomPageCreator->create_page() ) {
            $errors = $CustomPageCreator->get_errors();
            $output = '<ul>';
            foreach ( $errors as $error ) {
              $output .= '<li>'.$error.'</li>';
            }
            $output .= '</ul>';

            return $output;
        }
         
        */


        $PageManipulator = new PageManipulator;
        if ( $PageManipulator->route_page() AND $PageManipulator->get_page() ) {

            self::$content = $PageManipulator->get_content();
            self::$title = $PageManipulator->get_title();
            self::$keywords = $PageManipulator->get_keywords();
            self::$description = $PageManipulator->get_description();
            return self::get_output();
        }

        goto404();
        return;
    }

    private static function get_output() {

        $HeaderViewData = HeaderViewData::init();

        if ( !empty( self::$title ) )
            $HeaderViewData->set_title( self::$title, TRUE );
        if ( !empty( self::$keywords ) )
            $HeaderViewData->append_meta( 'keywords', self::$keywords, TRUE );
        if ( !empty( self::$description ) )
            $HeaderViewData->append_meta( 'description', self::$description, TRUE );

        return '<div id="cabinet">'.
                   '<div class="zagol-warp">'.
                        '<div class="zagol-line">'.
                            self::$title.
                        '</div>'.
                    '</div>'.
                   self::$content.
               '</div>';
    }
}

class PageManipulator {

    private $page_id;
    private $PageProvider;
    private $content;
    private $title;
    private $description;
    private $keywords;

    public function route_page() {

        if ( isset( $_GET['page_id'] ) ) {
            $page_id = (int)$_GET['page_id'];
            if ( $page_id != 0 ) {
                $this->page_id = $page_id;
                return TRUE;
            }
        }
        
        return FALSE;
    }

    public function get_page() {

        $this->PageProvider = new PageManipulatorProvider;
        $result = $this->PageProvider->get_page_where_id( $this->page_id );

        if ( $result ) {
            $this->content = $result[0]['content'];
            $this->title = $result[0]['title'];
            $this->description = $result[0]['description'];
            $this->keywords = $result[0]['keywords'];
            return TRUE;
        }

        return FALSE;
    }

    public function get_title() {
        return $this->title;
    }

    public function get_keywords() {
        return $this->keywords;
    }

    public function get_description() {
        return $this->description;
    }

    public function get_content() {
        
        return $this->content;
    }
}

class CustomPageManager {

    protected $content = '';
    protected $title = '';
    protected $description = '';
    protected $keywords = '';

    protected $errors = array();
    
    public function check_page() {

        if ( empty( $this->content ) ) {
            $this->errors[] = 'Надо указать содержимое страницы';
        }

        if ( empty( $this->title ) ) {
            $this->errors[] = 'Надо указать заголовок страницы';
        }

        if ( empty( $this->errors ) )
            return TRUE;

        return FALSE;
    }

    public function route_page() {

        if ( isset( $_POST['content'], $_POST['title'], $_POST['description'], $_POST['keywords'] ) ) {

            $this->content = trim( $_POST['content'] ) ;
            $this->title = trim( $_POST['title'] ) ;
            $this->description = trim($_POST['description'] ) ;
            $this->keywords = trim( $_POST['keywords'] ) ;
            if($this->title == '')
                    return false;
            return TRUE;
        }

        return FALSE;
    }

    public function get_errors() {
        return $this->errors;
    }

    public function get_page() {
        return array(
            'content' => $this->content,
            'title' => $this->title,
            'description' => $this->description,
            'keywords' => $this->keywords
        );
    }
}

class CustomPageCreator extends CustomPageManager {

    public function create_page() {

        if ( $this->route_page() ) {

            if ( $this->check_page() ) {
                
                DB_Provider::Instance()->loadProvider('Plugins.CustomPages');
                
                $CustomPageProvider = new CustomPageProvider;
                if ( $CustomPageProvider->create_custom_page( $this->get_page(), User::get()->id() ) ) {
                    return TRUE;
                }

                $this->errors[] = 'Не удалось создать страницу';
            }
        }

        return FALSE;
    }
}

class CustomPageEditor extends CustomPageManager {

    private $custom_page_id = 0;

    public function edit_page() {

        if ( $this->route_page() ) {

            if ( $this->check_page() ) {
                
                if($this->route_custom_page_id()) {

                DB_Provider::Instance()->loadProvider('Plugins.CustomPages');
                $CustomPageProvider = new CustomPageProvider;
                if ( $CustomPageProvider->edit_custom_page( $this->get_page(), $this->custom_page_id ) ) {
                    return TRUE;
                }

                $this->errors[] = 'Не удалось изменить страницу. Возможно, изменять нечего';
                }
            }
        }

        return FALSE;
    }
    
    public function delete_pages($ids) {
        
        DB_Provider::Instance()->loadProvider('Plugins.CustomPages');
        $customPageProvider = new CustomPageProvider();
        return $customPageProvider->delete_pages($ids);
    }

    public function route_custom_page_id() {
        
        if ( isset( $_POST['page_id'] ) ) {
            $this->custom_page_id = (int)$_POST['page_id'];
            if ( $this->custom_page_id > 0 )
                return TRUE;
        }

        return FALSE;
    }
}

class CustomPageList {

    private $provider;

    public function __construct($start = 0, $limit = 0) {

        DB_Provider::Instance()->loadProvider('Plugins.CustomPages');

        $this->provider = new CustomPageListProvider($start, $limit);
    }

    public function pages() {

        return $this->provider->pages();
    }
    
    public function count() {
        
        return $this->provider->count();
    }

}