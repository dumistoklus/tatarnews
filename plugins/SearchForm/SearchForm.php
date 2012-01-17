<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class SearchForm implements IPlugin
{
    public static function name() {
        return __CLASS__;
    }

    public static function load($side = 0, $order = 0)
    {
        $data['placeholder'] = 'Поиск...';
        $data['button_text'] = '&nbsp;';

        return SearchFormView::print_form($data);
    }
}

class SearchFormView
{
    public static function print_form($data) {
        $output = '<div id="search">'.
                    '<div id="search-overlay">'.$data['placeholder'].'</div>'.
                    '<form action="/?page=search" id="searchform">'.
                        '<a href="#" id="search-button">'.$data['button_text'].'</a>'.
						'<input type="hidden" name="cx" value="partner-pub-6462459799271273:1782259178" />'.
						'<input type="hidden" name="cof" value="FORID:11" />'.
		                '<input type="hidden" name="page" value="search" />'.
						'<input type="hidden" name="ie" value="UTF-8" />'.
                        '<input type="text" id="search-field" name="q" size="55" />'.
                        '<input type="submit" value="Искать" name="sa" id="alt-search-button" />'.
                    '</form>'.
                '</div>';

        return $output;
    }
}