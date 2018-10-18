<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Mario Zanier
 * Date: 21.02.14
 * Time: 12:55
 * (c) Mario Zanier, mzanier@xt-commerce.com
 */

class xtcommerce_Pager extends ADODB_Pager{

    function getData($rows=10)
    {
        global $ADODB_COUNTRECS;

        $this->rows = $rows;

        if ($this->db->dataProvider == 'informix') $this->db->cursorType = IFX_SCROLL;

        $savec = $ADODB_COUNTRECS;
        if ($this->db->pageExecuteCountRows) $ADODB_COUNTRECS = true;
        if ($this->cache)
            $rs = &$this->db->CachePageExecute($this->cache,$this->sql,$rows,$this->curr_page);
        else
        {
            $rs = $this->db->PageExecute($this->sql,$rows,$this->curr_page);
        }
        $ADODB_COUNTRECS = $savec;

        $this->rs = &$rs;

        $pages_array = array('first' => $this->getFirst(),
            'last' => $this->getLast(),
            'pages' => $this->getPageLinksArray(),
            'next' => $this->getNext(),
            'prev' => $this->getPrevious()
        );

        $xtc_data = array('data' => $this->getSQLData(),
            'count' => $this->getPageCount(),
            'data_count' => $this->rs->_maxRecordCount,
            'pages' => $pages_array
        );

        return $xtc_data;

    }

    function getFirst(){
        global  $xtLink, $page;

        $link_array = array('page'=>$page->page_name, 'params'=>$this->param . $this->id.'&next_page=1', 'conn'=>_SYSTEM_CONNECTION);
        $page_link = $xtLink->_link($link_array);

        return $page_link;
    }

    function getLast(){
        global $xtLink, $page;

        if (!$this->db->pageExecuteCountRows) return;

        $link_array = array('page'=>$page->page_name, 'params'=>$this->param . $this->id.'&next_page='.$this->rs->LastPageNo(), 'conn'=>_SYSTEM_CONNECTION);
        $page_link = $xtLink->_link($link_array);

        return $page_link;
    }

    function getNext(){
        global $xtLink, $page;

        $Page = $this->rs->AbsolutePage();
        $new_page = $Page + 1;

        $link_array = array('page'=>$page->page_name, 'params'=>$this->param . $this->id.'&next_page='.$new_page, 'conn'=>_SYSTEM_CONNECTION);
        $page_link = $xtLink->_link($link_array);

        return $page_link;
    }

    function getPrevious(){
        global $xtLink, $page;

        if (!$this->db->pageExecuteCountRows) return;

        $Page = $this->rs->AbsolutePage();
        $new_page = $Page - 1;

        $link_array = array('page'=>$page->page_name, 'params'=>$this->param . $this->id.'&next_page='.$new_page, 'conn'=>_SYSTEM_CONNECTION);
        $page_link = $xtLink->_link($link_array);

        return $page_link;
    }

    function getPageLinks(){
        global $xtLink, $page;

        $pages        = $this->rs->LastPageNo();
        $linksperpage = $this->linksPerPage ? $this->linksPerPage : $pages;
        for($i=1; $i <= $pages; $i+=$linksperpage)
        {
            if($this->rs->AbsolutePage() >= $i)
            {
                $start = $i;
            }
        }
        $numbers = '';
        $end = $start+$linksperpage-1;
        if($end > $pages) $end = $pages;


        if ($this->startLinks && $start > 1) {
            $pos = $start - 1;

            $link_array = array('page'=>$page->page_name, 'params'=>$this->param . $this->id.'&next_page='.$pos, 'conn'=>_SYSTEM_CONNECTION);

            $page_link = $xtLink->_link($link_array);

            $numbers .= '<a class="navigation_link" href="'.$page_link.'">'.$this->startLinks.'</a>&nbsp;';
        }

        for($i=$start; $i <= $end; $i++) {
            if ($this->rs->AbsolutePage() == $i){
                $numbers .= '<span class="navigation_selected">'.$i.'</span>&nbsp;';
            }else{

                $link_array = array('page'=>$page->page_name, 'params'=>$this->param . $this->id.'&next_page='.$i, 'conn'=>_SYSTEM_CONNECTION);

                $page_link = $xtLink->_link($link_array);

                $numbers .= '<a class="navigation_link" href="'.$page_link.'">'.$i.'</a>&nbsp;';
            }

        }
        if ($this->moreLinks && $end < $pages){

            $link_array = array('page'=>$page->page_name, 'params'=>$this->param . $this->id.'&next_page='.$i, 'conn'=>_SYSTEM_CONNECTION);
            $page_link = $xtLink->_link($link_array);

            $numbers .= '<a class="navigation_link" href="'.$page_link.'">'.$this->moreLinks.'</a>&nbsp;';
        }

        return $numbers;
    }

    function getPageLinksArray(){
        global $xtLink, $page;

        $pages        = $this->rs->LastPageNo();
        $start=1;
        $end = $pages;

        for($i=$start; $i <= $end; $i++) {
            $link_array = array('page'=>$page->page_name, 'params'=>html_entity_decode($this->param) . $this->id.'&next_page='.$i, 'conn'=>_SYSTEM_CONNECTION);

            $page_link = $xtLink->_link($link_array);
            $numbers[$i] = $page_link;
        }
        return $numbers;
    }

    function getSQLData()
    {
        $data = array();

        while(!$this->rs->EOF){

            $data[] = $this->rs->fields;

            $this->rs->MoveNext();
        }$this->rs->Close();


        return $data;
    }

    function getPageCount(){

        if (!$this->db->pageExecuteCountRows) return '';
        $lastPage = $this->rs->LastPageNo();
        if ($lastPage == -1) $lastPage = 1; // check for empty rs.
        if ($this->curr_page > $lastPage) $this->curr_page = 1;

        $data = array('actual_page' => $this->curr_page, 'last_page' => $lastPage);

        return $data;

    }

}