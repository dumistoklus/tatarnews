<?php

class PageNavBar {


    protected $current_page;
    protected $pages_count;
    protected $link;
    protected $range = 4;
    protected $count_of_elements_per_page = 10;

    function __construct($link, $pages_count, $current_page = 1) {
        $this->link = $link;
        $this->pages_count = $pages_count;
        $this->current_page = $current_page;
    }

    function get() {
        /*
         * @$cur_page - текущая страница
         * @$count    - элементов на одной странице
         * @$total    - всего элементов
         * @$link     - начало ссылки
         * @$range    - сколько ссылок на соседние страницы должны быть видны
        */
        $cur_page = $this->current_page;
        $count = $this->count_of_elements_per_page;
        $total = $this->pages_count;
        $range = $this->range;

        $pg_cnt = ceil( $total / $count );

        if ($pg_cnt <= 1) return "";
        $res = '';

        $idx_back = $cur_page - 1;
        $idx_next = $cur_page + 1;

        if ( $cur_page > 1 ) {
            $res .= '<a href="'.$this->link.$idx_back.'" class="pgs-link"><b class="pgs-arrow">&larr;</b>Предыдущая</a><span class="pgs">';
        } else {
            $res .= '<span class="pgs-link"><b class="pgs-arrow">&larr;</b>Предыдущая</span><span class="pgs">';
        }

        // предыдущ страница
        if ( $idx_back >= 0 ) {
            if ( $cur_page > ( $range + 1 ) ) {
                $res .= '<a href="'.$this->link.'1" class="pgs-link">1</a>';
                if ( $cur_page > ( $range + 2 ) ) {
                    $res .= '...';
                }
            }
        }

        $idx_fst = max( $cur_page - $range, 1 );
        $idx_lst = min( $cur_page + $range, $pg_cnt );
        if ( $range == 0 ) {
            $idx_fst = 1;
            $idx_lst = $pg_cnt;
        }

        for ( $i = $idx_fst; $i <= $idx_lst; $i++ ) {

            if ( $i == $cur_page ) {
                $res .= '<span class="currentpage">'.$i.'</span>';
            } else {
                $res .= '<a href="'.$this->link.$i.'" class="pgs-link">'.$i.'</a>';
            }
        }

        if ( $idx_lst < $pg_cnt ) {
            if ( $cur_page < ( $pg_cnt - $range) ) {
                if ( $cur_page + 1 < ( $pg_cnt - $range) ) {
                    $res .= '...';
                }
                $res .= '<a href="'.$this->link.$pg_cnt.'" class="pgs-link">'.$pg_cnt.'</a>';
            }

        }

        if ( $cur_page < $pg_cnt ) {
            $res .= '</span><a href="'.$this->link.$idx_next.'" class="pgs-link">Следующая<b class="pgs-arrow">&rarr;</b></a>';
        } else {
            $res .= '</span><span class="pgs-link">Следующая<b class="pgs-arrow">&rarr;</b></span>';
        }

        return '<div class="pagenav"><div class="pagenavlist">'.$res.'</div></div>';
    }
}