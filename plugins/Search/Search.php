<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Search implements IPlugin
{
    public static function name() {
        return __CLASS__;
    }

    public static function load($side = 0, $order = 0) {
        return SearchView::init()->get_output();
    }
}

class SearchView
{

    private $search_query;
    private static $instance;

    public static function init() {
        return (self::$instance === null) ? self::$instance = new self() : self::$instance;
    }

    public function __construct() {
        $this->route_search();
    }

    public function get_output() {

        HeaderViewData::init()->set_title( $this->get_title(), TRUE );

		$output = '<div id="cabinet"><form action="/?page=search" id="cse-search-box">'.
					'<div>'.
                        '<input type="hidden" name="cx" value="partner-pub-6462459799271273:1782259178" />'.
                        '<input type="hidden" name="cof" value="FORID:11" />'.
                        '<input type="hidden" name="ie" value="UTF-8" />'.
                        '<input type="text" name="q" size="55" />'.
                        '<input type="hidden" name="page" value="search" />'.
                        '<input type="submit" name="sa" value="Поиск" />'.
                    '</div>'.
					'</form>'.
					'<script type="text/javascript" src="http://www.google.ru/coop/cse/brand?form=cse-search-box&amp;lang=ru"></script>'.
					'<div id="cse-search-results"></div>'.
					'<script type="text/javascript">'.
                        'var googleSearchIframeName = "cse-search-results";'.
						'var googleSearchFormName = "cse-search-box";'.
						'var googleSearchFrameWidth = 795;'.
						'var googleSearchDomain = "www.google.ru";'.
						'var googleSearchPath = "/cse";'.
					'</script>'.
					'<script type="text/javascript" src="http://www.google.com/afsonline/show_afs_search.js"></script>'.
				'</div>';
        return $output;
    }

    public function route_search() {
        if ( isset( $_POST['q'] ) ) {
            $this->search_query = mb_substr( trim( $_POST['q'] ), 0, 200, 'UTF-8' );
            return TRUE;
        }

        return FALSE;
    }

    private function get_title() {
        if ( !empty( $this->search_query ) ) {
            return 'Поиск по запросу &laquo;'.$this->search_query.'&raquo;';
        }

        return 'Поиск на TatarNews.ru';
    }
}