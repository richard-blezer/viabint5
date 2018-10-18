<?php

/*
	V4.96 24 Sept 2007  (c) 2000-2007 John Lim (jlim#natsoft.com.my). All rights reserved.
	  Released under both BSD license and Lesser GPL library license.
	  Whenever there is any discrepancy between the two licenses,
	  the BSD license will take precedence.
	  Set tabs to 4 for best viewing.

  	This class provides recordset pagination with
	First/Prev/Next/Last links.

	Feel free to modify this class for your own use as
	it is very basic. To learn how to use it, see the
	example in adodb/tests/testpaging.php.

	"Pablo Costa" <pablo@cbsp.com.br> implemented Render_PageLinks().

	Please note, this class is entirely unsupported,
	and no free support requests except for bug reports
	will be entertained by the author.

	Modification and Changes for xt:Commerce by Matthias Hinsche http://www.bui-hinsche.de


*/
class ADODB_Pager {
	var $id; 	// unique id for pager (defaults to 'adodb')
	var $db; 	// ADODB connection object
	var $sql; 	// sql used
	var $rs;	// recordset generated
	var $curr_page;	// current page number before Render() called, calculated in constructor
	var $rows;		// number of rows per page
    var $linksPerPage=10; // number of links per page in navigation bar
    var $showPageLinks;
	var $seo_link = 'true';
	var $gridHeader = false;
	var $htmlSpecialChars = true;

	var $cache = 0;  #secs to cache with CachePageExecute()

	var $moreLinks = '...';
	var $startLinks = '...';


	//----------------------------------------------
	// constructor
	//
	// $db	adodb connection object
	// $sql	sql statement
	// $id	optional id to identify which pager,
	//		if you have multiple on 1 page.
	//		$id should be only be [a-z0-9]*
	//
	function ADODB_Pager(&$db,$sql,$id = '', $param='', $showPageLinks = false, $seo_link='true')
	{
	global $PHP_SELF;

		$curr_page = $id.'curr_page';

		$this->sql = $sql;
		$this->id = $id;
		$this->db = $db;
		$this->showPageLinks = $showPageLinks;
		$this->param = $param;
		$this->seo_link = $seo_link;

		$next_page = $id.'next_page';

		if (isset($_GET[$next_page])) {
			$_SESSION[$curr_page] = (integer) $_GET[$next_page];
		}
		if (empty($_SESSION[$curr_page])) $_SESSION[$curr_page] = 1; ## at first page

		$this->curr_page = $_SESSION[$curr_page];
		unset($_SESSION[$curr_page]);
	}

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
			$rs = &$this->db->PageExecute($this->sql,$rows,$this->curr_page);
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

	function getSQLData()
	{
		$data = array();

		while(!$this->rs->EOF){

			$data[] = $this->rs->fields;

			$this->rs->MoveNext();
		}$this->rs->Close();


		return $data;
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

				$link_array = array();

				$link_array = array('page'=>$page->page_name, 'params'=>$this->param . $this->id.'&next_page='.$pos, 'conn'=>_SYSTEM_CONNECTION);

				$page_link = $xtLink->_link($link_array);

				$numbers .= '<a class="navigation_link" href="'.$page_link.'">'.$this->startLinks.'</a>&nbsp;';
            }

			for($i=$start; $i <= $end; $i++) {
	            if ($this->rs->AbsolutePage() == $i){
	           		$numbers .= '<span class="navigation_selected">'.$i.'</span>&nbsp;';
				}else{

					$link_array = array();
					$link_array = array('page'=>$page->page_name, 'params'=>$this->param . $this->id.'&next_page='.$i, 'conn'=>_SYSTEM_CONNECTION);
					
					$page_link = $xtLink->_link($link_array);

	            	$numbers .= '<a class="navigation_link" href="'.$page_link.'">'.$i.'</a>&nbsp;';
				}

            }
			if ($this->moreLinks && $end < $pages){

				$link_array = array();
				$link_array = array('page'=>$page->page_name, 'params'=>$this->param . $this->id.'&next_page='.$i, 'conn'=>_SYSTEM_CONNECTION);
				$page_link = $xtLink->_link($link_array);

				$numbers .= '<a class="navigation_link" href="'.$page_link.'">'.$this->moreLinks.'</a>&nbsp;';
			}

		return $numbers;
	}
    
    function getPageLinksArray(){
		global $xtLink, $page;

        $pages        = $this->rs->LastPageNo();
       // $linksperpage = $this->linksPerPage ? $this->linksPerPage : $pages;
        $start=1;
        $end = $pages;
        /*
            for($i=1; $i <= $pages; $i+=$linksperpage)
            {
                if($this->rs->AbsolutePage() >= $i)
                {
                    $start = $i;
                }
            }
			$numbers = array();
            $end = $start+$linksperpage-1;
            if($end > $pages) $end = $pages;
        */
/*
			if ($this->startLinks && $start > 1) {
				$pos = $start - 1;

				$link_array = array();

				$link_array = array('page'=>$page->page_name, 'params'=>$this->param . $this->id.'&next_page='.$pos, 'conn'=>_SYSTEM_CONNECTION);

				$page_link = $xtLink->_link($link_array);

				$numbers['startLinks'] = $page_link;
            }
*/


			for($i=$start; $i <= $end; $i++) {
	       //     if ($this->rs->AbsolutePage() == $i){
                    $link_array = array();
					$link_array = array('page'=>$page->page_name, 'params'=>html_entity_decode($this->param) . $this->id.'&next_page='.$i, 'conn'=>_SYSTEM_CONNECTION);
					
					$page_link = $xtLink->_link($link_array);
	           		$numbers[$i] = $page_link;
			/*
					}else{

					$link_array = array();
					$link_array = array('page'=>$page->page_name, 'params'=>$this->param . $this->id.'&next_page='.$i, 'conn'=>_SYSTEM_CONNECTION);
					
					$page_link = $xtLink->_link($link_array);

	            	$numbers[$i] = $page_link;
				}
			*/

            }
        /*
			if ($this->moreLinks && $end < $pages){

				$link_array = array();
				$link_array = array('page'=>$page->page_name, 'params'=>$this->param . $this->id.'&next_page='.$i, 'conn'=>_SYSTEM_CONNECTION);
				$page_link = $xtLink->_link($link_array);

				$numbers['moreLinks'] = $page_link;
			}
        */
		return $numbers;
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


?>