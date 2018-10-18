<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id$
 # @copyright xt:Commerce International Ltd., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

class seo_modRewrite {

    public $activated = _SYSTEM_MOD_REWRITE;
    public $string_glue = '-';

    function _lookUpforUrl() {
        global $xtPlugin,$db,$page_data,$language,$xtLink,$current_product_id,$current_category_id,$current_content_id,$current_manufacturer_id;

        $allowed_mainfiles = explode(',',_SYSTEM_ALLOWED_MAINFILES);
        $system_404 = true;

        ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_lookUpforUrl_top')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
            return $plugin_return_value;
        
        if ($this->activated=='true') {
            $org_page = $_SERVER['REQUEST_URI'];

            $page_url_data = $this->_cleanUpUrl($org_page);
  
            $this->org_page = $page_url_data['url']; //cleaned from Subfolders
            $this->clean_page = $page_url_data['url_clean']; // only SEO URL for Database Check

            $url = $this->_UrlHash($this->clean_page);

            $where = '';
            ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_lookUpforUrl_where')) ? eval($plugin_code) : false;

            $query = "SELECT * FROM ".TABLE_SEO_URL." WHERE url_md5=? ".$where." LIMIT 0,1";

            $rs = $db->CacheExecute($query, array($url));

			if (!defined('XT_WIZARD_STARTED')){ // skip for xtWizard
				//check in redirected urls 
				
				 if ($rs->RecordCount()==0){
					$query = "SELECT * FROM ".TABLE_SEO_URL_REDIRECT." WHERE url_md5=? ".$where." LIMIT 0,1";
					$rs_redirect = $db->CacheExecute($query, array($url));
					if ($rs_redirect->RecordCount()>0){
						if ($rs_redirect->fields['url_text_redirect']!=''){
							/*if redirect url contains http:// */
						    if (strpos($rs_redirect->fields['url_text_redirect'],'http://') !== false){
						        $xtLink->_redirect($rs_redirect->fields['url_text_redirect'],301); // redirect to the pre-defined ur
						        exit();
						    }
							$tmp_link  = $xtLink->_link(array('seo_url'=>$rs_redirect->fields['url_text_redirect']));
							$xtLink->_redirect($tmp_link,301); // redirect to the pre-defined url
							exit();
						}
					}
				 }
			}
            // seo url, but not index ?
            if($system_404==true){
				if (!defined('XT_WIZARD_STARTED')){
					if (($rs->RecordCount()==0) && (($rs_redirect->RecordCount()==0) || $rs_redirect->fields['url_text_redirect']=='')){
					
						if (_SYSTEM_MOD_REWRITE_DEFAULT != 'true')  { // UBo ++
							if (!in_array($page_url_data['url_clean'],$allowed_mainfiles) && $page_url_data['url_clean']!='') {
								if (_SYSTEM_MOD_REWRITE_404=='true') {
									$this->Log404page();
									$this->faultHandler(404);
								 //   $tmp_link  = $xtLink->_link(array('page'=>'404'));
								}else{
									$this->Log404page();
									$tmp_link  = $xtLink->_link(array('page'=>'index'));
									$xtLink->_redirect($tmp_link);
								}
							}
						}  // UBo--

						return -1;
					}
                }
            }

            ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_lookUpforUrl_check')) ? eval($plugin_code) : false;

            // switch to other language if needed
            if ($rs->fields['language_code']!=$language->code){
                $language->_getLanguage($rs->fields['language_code']);
                $_SESSION['selected_language'] = $rs->fields['language_code'];
            }

            if ($rs->RecordCount()==0) return false;

            switch ($rs->fields['link_type']) {

                case '1': // products
                    $_GET['page']='product';
                    $_GET['info'] = (int)$rs->fields['link_id'];
                    return true;
                    break;

                case '2': // categories
                    $_GET['page']='categorie';
                    $_GET['cat'] = (int)$rs->fields['link_id'];
                    return true;
                    break;

                case '3': // content
                    $_GET['page']='content';
                    $_GET['coID'] = (int)$rs->fields['link_id'];
                    if($rs->fields['url_text'] == $language->code.'/index'){
                        $_GET['page']='index';
                    }
                    return true;
                    break;

                case '4': // manufacturers
                    $_GET['page']='manufacturers';
                    $_GET['mnf'] = (int)$rs->fields['link_id'];
                    return true;
                    break;
					
				case '1000': // seo_plugins pages
					require_once _SRV_WEBROOT.'/xtFramework/classes/class.seo_plugins.php';
			
					$seo_plugins = new seo_plugins();
					$res = $seo_plugins->getPluginByID($rs->fields['link_id']);
					$_GET['page']=$res['code'];
                    $_GET['plugin']=(int)$rs->fields['link_id'];
                    return true;
                    break;
                default:
                    ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_lookUpforUrl_switch')) ? eval($plugin_code) : false;
                    if(isset($plugin_return_value))
                    return $plugin_return_value;
            }

            ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_lookUpforUrl_bottom')) ? eval($plugin_code) : false;
            if(isset($plugin_return_value))
                return $plugin_return_value;
                
            // nothing found ->404
            // checking if page available
            if (_SYSTEM_MOD_REWRITE_404=='true') {
				$this->Log404page();
                $this->faultHandler(404);
            }else{
                $this->Log404page();
                $tmp_link  = $xtLink->_link(array('page'=>'index'));
                $xtLink->_redirect($tmp_link);
            }
        }
    }

    /**
     * show 404 page
     *
     * @param string $status
     */
    function faultHandler($status='404') {
        global $xtLink,$page;

        $page_data = array('page'=>$status, 'page_action'=>'');
        $page = new page($page_data);
    }
	
	/* Log in db not found pages*/
	function Log404page(){
        include_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.bruto_force_protection_404.php';
        $bfp_404 = new bruto_force_protection_404();
        $current_page = $_SERVER['REQUEST_URI'];
        $cleared_page = $this->_cleanUpUrl($current_page);
        $not_allowed = array('.png','.gif','.jpg','.zip','.gz','.rar','.jpeg','.mpeg');
        $continue = true;
        foreach($not_allowed as $n){
            if (strstr($cleared_page["url_clean"],$n)!==false) {
                $continue = false;
            }
        }
        if ($continue){
             $bfp_404->escalateFailedPageLoad();
            /*Check is user is not banned for too many 404 pages loaded.
             *  If not logged the current page in db for redirect*/
            if (!$bfp_404->_isLocked()){
               
                if ($_SERVER["QUERY_STRING"]!='') $cleared_page["url_clean"] .= '?'.$_SERVER["QUERY_STRING"];
                save404Url($cleared_page["url_clean"]);
            } 
        }
        
    }
	
    function _cleanUpUrl($page){
        global $xtPlugin, $xtLink, $language;

        ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_cleanUpUrl_top')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
        return $plugin_return_value;

        if(_SRV_WEB != '/'){
            $page = str_replace(_SRV_WEB,'',$page);
        }else{
            $page = substr($page, 1);
        }

        if ($_SERVER['QUERY_STRING']!='')
        $page = str_replace('?'.$_SERVER['QUERY_STRING'],'',$page);
		
		if ((_SYSTEM_SSL_PROXY=='true') && (stristr($_SERVER['REQUEST_URl'], 'xtadmin/') === FALSE) && ($_SERVER['QUERY_STRING']!=''))
		{
			$_dir = explode('/',$_SERVER['REQUEST_URI']);
			unset($_dir[0]);
			if (count($_dir) > 1 && ($_dir[1]!=$language->code) && (stristr($_dir[1], 'next_page=')===FALSE)){
				unset($_dir[1]);
			}
			$page=implode('/',$_dir);
			$page = str_replace('?'.$_SERVER['QUERY_STRING'],'',$page);
		}
			
        if(_SYSTEM_SEO_FILE_TYPE!=''){
            $page_clean = $this->_cleanUrlFromFileType($page, '.'._SYSTEM_SEO_FILE_TYPE);
        }else{
            $page_clean = $page;
        }

        ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_cleanUpUrl_page_cleaner')) ? eval($plugin_code) : false;

        $page_array= array('url'=>$page, 'url_clean'=>$page_clean);

        ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_cleanUpUrl_bottom')) ? eval($plugin_code) : false;

        return $page_array;
    }

    function _cleanUrlFromFileType($page, $value){
        global $xtPlugin;

        ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_cleanUrlFromFileType_top')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
        return $plugin_return_value;

        if(preg_match('/'.$value.'/', $page)){
            $start_pos_val = strpos($page, $value);
            $page = substr($page, 0, $start_pos_val);
            return $page;
        }else{
            return $page;
        }

    }

    function _cleanUrlFromValue($page, $value){
        global $xtPlugin;

        ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_cleanUrlFromValue_top')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
        return $plugin_return_value;

        if(preg_match('/'.$value.'/', $page)){
            $start_pos_val = strpos($page, '/'.$value.'/');
            $page = substr($page, 0, $start_pos_val);
            return $page;
        }else{
            return $page;
        }

    }

    function _getPageData(){
        global $xtPlugin;

        ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_getPageData_top')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
        return $plugin_return_value;

        if($this->clean_page != $this->org_page){
            $page_data = str_replace($this->clean_page, '', $this->org_page);
        }else{
            $page_data = $this->org_page;
        }

        $new_data=array();
        $new_data = $this->_splitValues($page_data);

        $check_data = $this->_splitValues($this->org_page);

        foreach($_GET as $key=>$val) {
            $new_data[$key] = $val;
        }

        $this->page_values = $this->cleanUpData($new_data, $check_data);
        return $this->page_values;

    }

    function cleanUpData($data='', $clean_data=''){
        global $xtPlugin;

        ($plugin_code = $xtPlugin->PluginCode('class.seo.php:cleanUpData_top')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
        return $plugin_return_value;

        if(is_array($data) && count($data) > 0 && is_array($clean_data) && count($clean_data) > 0){
            foreach ($data as $key => $val){
                if(!array_key_exists($key, $clean_data))
                $new_data[$key] = $val;
            }
        }else{
            $new_data = $data;
        }

        return $new_data;

    }


    function _splitValues($page_data){
        global $xtPlugin;

        ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_splitValues_top')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
        return $plugin_return_value;

        if(strpos($page_data, '/')==0 && strpos($page_data, '/')!==false)
        $page_data = substr($page_data, 1);

        $page_data = explode('/', $page_data);

        for ($i=0; $i<sizeof($page_data); $i+=2) {
                
            if($page_data[$i]!='')
            $new_data[$page_data[$i]] = $page_data[$i+1];
        }

        return $new_data;
    }


    function _setGetValues(){
        global $xtPlugin;

        ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_setGetValues_top')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
        return $plugin_return_value;


        if(is_array($this->page_values)&&count($this->page_values)>0){
            foreach ($this->page_values as $key=>$val){
                $_GET[$key] = $val;
            }
        }
    }


    function _getFilter($class) {
        global $xtPlugin;

        ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_getFilter_top')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
        return $plugin_return_value;

        switch ($class) {
            case 'categories_id':
                return '2';
                break;
            case 'content_id':
                return '3';
                break;
            case 'products_id':
                return '1';
                break;
            case 'manufacturers_id':
                return '4';
                break;

            default:
                ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_getFilter_switch')) ? eval($plugin_code) : false;
                if(isset($plugin_return_value))
                return $plugin_return_value;
        }
    }



    function _getCategoryUrlbasedParent($cat_id,$language_code, $store_id='') {
        global $db,$xtPlugin,$filter;


        $cat_id = (int)$cat_id;
        if ($cat_id>0) {
            $add_sql = '';
        	if ($store_id!=''){
        		$add_sql  = " and store_id = '".$store_id."'";
        	} 
            $qry = "SELECT * FROM ".TABLE_SEO_URL." WHERE link_type='2' and link_id=? and language_code=? ".$add_sql;
            $rs=$db->Execute($qry, array($cat_id, $language_code));
            if ($rs->RecordCount()==1) {
                $url_text = $rs->fields['url_text'];
                if (substr($url_text,0,3)==$language_code.'/') {
                    $url_text = substr($url_text,3);
                }
                return $url_text;
            }
        } else {
            // main category
            return '';
        }
    }

    /**
     * get SEO url for parent/actual category + add produkt/category name
     *
     * @param int $cat_id
     * @param string $url_text
     * @param string $language_code
     * @return string
     */
    function _getCategoryUrlbasedParent_DEPRECEATED($cat_id, $url_text, $language_code, $table=TABLE_CATEGORIES, $table_lang=TABLE_CATEGORIES_DESCRIPTION, $master_key='categories_id', $parent_key='parent_id', $lang_key='categories_name') {
        global $db, $xtPlugin;
        $cat_id = (int)$cat_id;

        ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_getCategoryUrlbasedParent_top')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
        return $plugin_return_value;
        
        $seo_data = '';
        if($cat_id !=0){
            require_once(_SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.recursive.php');
            $r = new recursive($table, $master_key, $parent_key);

            ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_getCategoryUrlbasedParent_class')) ? eval($plugin_code) : false;

            $r->setLangTable($table_lang);
            $r->setDisplayKey($lang_key);
            $r->setMasterLangKey($master_key);
            $r->setDisplayLang(true);
            $r->setDisplayLangCode($language_code);
            $new_data = $r->getNavigationPath($cat_id);
            $new_data = array_reverse($new_data);

            ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_getCategoryUrlbasedParent_data')) ? eval($plugin_code) : false;

            $seo_data = $r->getDisplayPath($new_data, '/');
        }
        
        if (substr($seo_data,0,1)=='/') {
            $seo_data = substr($seo_data,1);
        }

        ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_getCategoryUrlbasedParent_bottom')) ? eval($plugin_code) : false;

        if ($seo_data) {
            return array('url_text'=>$url_text,'path'=>$seo_data.'/');
        } else {
            return array('url_text'=>$url_text,'path'=>'');
        }
    }


    function _UpdateRecord($class,$id,$language_code,$data,$auto_generate=false, $tmp_copy='false',$store_id='') {
        global $db, $filter, $xtPlugin;

        $link_type = '';
		$add_to_fields='';
		if ($store_id!='') {
			$add_to_fields='store'.$store_id.'_';
		}
		
        if(!$data['url_text_'.$add_to_fields.$language_code])
            return false;

        $url_text = $filter->_filter($data['url_text_'.$add_to_fields.$language_code]);
        $url_text = $data['url_text_'.$add_to_fields.$language_code];
        
        // check if string begins with language code
        if (substr($url_text,0,3)==$language_code.'/') {
            $url_text = substr($url_text,3);
        }

        switch ($class) {

            case 'category':
                $link_type = 2;

                if ($auto_generate===true) {

                    $parent = $this->_getCategoryUrlbasedParent($data['parent_id'],$language_code,$store_id);
                    $url_text = $this->filterAutoUrlText($url_text,$language_code, $class, $id);
                    // filter url_text
                    if (($parent)!='') $url_text = $parent.'/'.$url_text;
                }

                break;

            case 'content':
                $link_type = 3;
                if ($auto_generate===true) $url_text = $this->filterAutoUrlText($url_text,$language_code, $class, $id);
                break;

            case 'manufacturer':
                $link_type = 4;
                if ($auto_generate===true) $url_text = $this->filterAutoUrlText($url_text,$language_code, $class, $id);
                break;

            case 'product':
                $link_type = 1;
                if ($auto_generate===true) {

                    $parent='';
                    if (_SYSTEM_SEO_PRODUCTS_CATEGORIES=='true') {
						$add_to_q='';
						if ($store_id!='') $add_to_q = " and store_id = '".$store_id."'";
                        $qry = "SELECT categories_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE products_id=? and master_link = 1 ".$add_to_q." LIMIT 0,1";
                        $rs = $db->Execute($qry, array($id));
                        if ($rs->RecordCount()==1) {
                            $cat_id = $rs->fields['categories_id'];
                        } else {
                            $cat_id = 0;
                        }

                        $parent = $this->_getCategoryUrlbasedParent($cat_id,$language_code,$store_id);
                    }
                    $url_text = $this->filterAutoUrlText($url_text,$language_code, $class, $id);
                    if (($parent)!='') $url_text = $parent.'/'.$url_text;

                }
                ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_UpdateRecord_switch_prod')) ? eval($plugin_code) : false;
                break;

            default:
                ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_UpdateRecord_switch')) ? eval($plugin_code) : false;
        }
		
        // only lowercases
        if ($auto_generate===true)
            $url_text=strtolower ($url_text);

        // add language parameter if required
        if(_SYSTEM_SEO_URL_LANG_BASED=='true'){
            $url_text = $language_code.'/'. $url_text;
        }

        $lookup_data = array();
        $lookup_data['url_text'] = $url_text;
        $lookup_data['link_type'] = $link_type;
        $lookup_data['link_id'] = $id;
        $lookup_data['language_code'] =$language_code;
		$lookup_data['store_id'] = $store_id;

        $url_text = $this->validateDBKeyLink ($lookup_data,'', $tmp_copy);

        $url_md5 = $this->_UrlHash($url_text);

        $seo_data = array();
        $seo_data['url_md5'] = $url_md5;
        $seo_data['url_text'] = $url_text;
		$where = '';
		if ($store_id!=''){
			$where .= " and store_id='".$store_id."'";
		}
        $seo_data['meta_keywords'] = $data['meta_keywords_'.$add_to_fields.$language_code];
        $seo_data['meta_title'] = $data['meta_title_'.$add_to_fields.$language_code];
        $seo_data['meta_description'] = $data['meta_description_'.$add_to_fields.$language_code];

        
		
        $record = $db->Execute(
            "SELECT * FROM " . TABLE_SEO_URL . " WHERE link_type=? and link_id=? and language_code=? ".$where,
            array($link_type, $id, $language_code)
        );
        if ($record->RecordCount() == 0) {

            $insert_data['language_code'] = $language_code;
            $insert_data['link_type'] = $link_type;
            $insert_data['link_id'] =$id;
			$insert_data['store_id'] =$store_id;
			
            $seo_data = array_merge($seo_data, $insert_data);
            $db->AutoExecute(TABLE_SEO_URL,$seo_data,'INSERT');
        }else{
            $db->AutoExecute(TABLE_SEO_URL,$seo_data,'UPDATE',"link_type=".$db->Quote($link_type)." and link_id=".$db->Quote($id)." and language_code=".$db->Quote($language_code)." ".$where);
        }
    }

    function _rebuildSeo($_table, $_table_lang, $_table_seo, $link_type, $seo_type, $link_name, $_master_key, $id,$store_id_field='',$store_id='',$add_table=''){
        global $xtPlugin,$db,$language,$filter;
		
        ($plugin_code = $xtPlugin->PluginCode('class.seo.php:_rebuildSeo_top')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
        return $plugin_return_value;

        $auto_generate = true;

        if($id!='all'){
            $where = " and t.".$_master_key." = '".$id."'";
        }

        $query = "select * from ".$_table . " t inner JOIN  ".$_table_lang ." td ON t.".$_master_key." = td.".$_master_key." ".$add_table." where 1=1 ".$where;
        $record = $db->Execute($query);
        if ($record->RecordCount() > 0) {
            while(!$record->EOF){

                if($record->fields[$link_name]!=''){
                    $langdata = array();
                    $data = array();
                    $add_to_sql='';
                    if ($store_id_field!='') {
                    	$add_to_sql=" and store_id = ".$record->fields[$store_id_field];
                    }
                    $rs_query = "select * from ".$_table_seo . " WHERE link_id = ? and language_code=? and link_type=? ".$add_to_sql;
                    
                    $rs= $db->Execute($rs_query, array($record->fields[$_master_key], $record->fields['language_code'], $link_type));
                    if ($rs->RecordCount() > 0) {
                        $langdata = array_merge($langdata, $rs->fields);
                    }
                        
                    $langdata['url_text'] = $record->fields[$link_name];
                     if ($store_id_field=='') {
                     	$langdata = $this->adminData_setLangCode($langdata, $record->fields['language_code']);
						$update_store_id = '';
					 }
					 else {
					 	$langdata = $this->adminData_setStoreLangCode($langdata, $record->fields['language_code'],$record->fields[$store_id_field]);
						$update_store_id = $record->fields[$store_id_field];
					 }
					 
                    $data = array_merge($record->fields, $langdata);
                    $db->Execute(
                        "DELETE FROM " . $_table_seo . " WHERE link_id = ? and language_code=? and link_type=? ".$add_to_sql,
                        array($record->fields[$_master_key], $record->fields['language_code'], $link_type)
                    );
                    $this->_UpdateRecord($seo_type,$record->fields[$_master_key], $record->fields['language_code'], $data, $auto_generate,'false',$update_store_id);
                }
                $record->MoveNext();
            } $record->Close();
        }

        return true;
    }
    
    function adminData_setLangCode ($data, $lang_code) {
    
    	if (!is_array($data)) return $data;
    	foreach ($data as $key => $val) {
    		$_lang_data[$key.'_'.$lang_code] = $val;
    	}
    	return $_lang_data;
    }
    
    function adminData_setStoreLangCode ($data, $lang_code,$store) {
    
    	if (!is_array($data)) return $data;
    	foreach ($data as $key => $val) {
    		$_lang_data[$key.'_store'.$store.'_'.$lang_code] = $val;
    	}
    	return $_lang_data;
    }


    function reIndex($type, $start=0, $end = 1000, $max=0) {
        global $db, $xtPlugin;

        ($plugin_code = $xtPlugin->PluginCode('class.seo.php:reIndex_top')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
        return $plugin_return_value;

        if ($type=='category') {
            // drop if start = 0
            if ($start == 0) {
                $db->Execute("DELETE FROM ".TABLE_SEO_URL." WHERE link_type='2'");
                $rs = $db->Execute("SELECT count(*) as count FROM ".TABLE_CATEGORIES_DESCRIPTION);
                $max = $rs->fields['count'];
            }
            
            $rs = $db->Execute("SELECT * FROM ".TABLE_CATEGORIES_DESCRIPTION." LIMIT ".(int)$start.",".(int)$end);
            if ($rs->RecordCount()>0) {
                while (!$rs->EOF) {
                    $data['url_text_'.$rs->fields['language_code']]=$rs->fields['categories_name'];
                    $this->_UpdateRecord('category',$rs->fields['categories_id'],$rs->fields['language_code'],$data,true);
                    $rs->MoveNext();
                }
            }
            return $max;
        }

        if ($type == 'product') {
            //url_text_
            if ($start == 0) {
                $db->Execute("DELETE FROM ".TABLE_SEO_URL." WHERE link_type='1'");
                $rs = $db->Execute("SELECT count(*) as count FROM ".TABLE_PRODUCTS_DESCRIPTION);
                $max = $rs->fields['count'];
            }

            $query = "SELECT * FROM ".TABLE_PRODUCTS_DESCRIPTION." LIMIT ".(int)$start.",".(int)$end;
            $rs = $db->Execute($query);

            if ($rs->RecordCount()>0) {
                while (!$rs->EOF) {
                    $data['url_text_'.$rs->fields['language_code']]=$rs->fields['products_name'];
                    $this->_UpdateRecord('product',$rs->fields['products_id'],$rs->fields['language_code'],$data,true);
                    $rs->MoveNext();
                }
            }
            return $max;

        }
        ($plugin_code = $xtPlugin->PluginCode('class.seo.php:ReIndex_bottom')) ? eval($plugin_code) : false;
    }

    /**
     * generate hash of url
     *
     * @param string $page
     * @return string
     */
    function _UrlHash($page) {
        return md5($page);
    }


    /**
     * check if link text already exists, add counter if needed
     *
     * @param array $data
     * @param int $counter
     * @return string
     */
    function validateDBKeyLink (&$data,$counter='') {
        global $db, $xtPlugin;

        ($plugin_code = $xtPlugin->PluginCode('class.seo.php:validateDBKeyLink_top')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
        return $plugin_return_value;

        // First Step: Check for other Duplicates:
        $query = "SELECT * FROM ".TABLE_SEO_URL." where url_md5=?";
        $rs = $db->Execute($query, array(md5($data['url_text'].$counter)));

        if($rs->RecordCount()==0){
            return $data['url_text'].$counter;
        }else{
                
            if($data['language_code'] == $rs->fields['language_code'] && $data['link_type'] == $rs->fields['link_type'] && $data['link_id'] == $rs->fields['link_id'] && $data['store_id'] == $rs->fields['store_id']){
                return $data['url_text'].$counter;
            }else{
                if ($data['store_id'] != $rs->fields['store_id']){
                	if($data['language_code'] == $rs->fields['language_code'] && $data['link_type'] == $rs->fields['link_type'] && $data['link_id'] == $rs->fields['link_id'])
            			return $data['url_text'].$counter;
					else{
						$counter++;
	                	return $this->validateDBKeyLink ($data,$counter);
					}
            	}else{
	                $counter++;
	                return $this->validateDBKeyLink ($data,$counter);
				}
            }
        }
    }

    /**
     *
     * new filter method
     *
     * @param $string
     * @param $language
     */
    function filterAutoUrlText($string,$language_code, $class_ = false, $id_ = false) {
        global $db;

        // remove blank spaces at and and beginning of string
        $string = trim($string);

        // remove slashes
        $string  = preg_replace("/\//","-",$string);

        $words =  preg_split( "/[\s,.]+/", $string);

        // lookup in stop word list
        $stop_words = array();
        $replace_chars_search = array();
        $replace_chars_replace = array();
        $qry = "SELECT * FROM ".TABLE_SEO_STOP_WORDS." WHERE language_code IN ('ALL',?)";
        $rs=$db->Execute($qry, array($language_code));
        while (!$rs->EOF) {

            if ($rs->fields['replace_word']==1) {
                $replace_chars_search[]='/'.$rs->fields['stopword_lookup'].'/';
                $replace_chars_replace[]=$rs->fields['stopword_replacement'];
            } else {
                $stop_words[]=$rs->fields['stopword_lookup'];
            }

            $rs->MoveNext();
        }

        // kill words from stop word list
        if (count($words)>1) {
            foreach ($words as $key=>$word) {
                $words[$key]=trim($word);
                if (in_array($word,$stop_words) or $word=='') unset($words[$key]);
            }
        }

        // merge to string again
        $string = implode($this->string_glue,$words);

        // replace chars from stop word replace list
        if (is_array($replace_chars_search)) {
            $string  = preg_replace($replace_chars_search,$replace_chars_replace,$string);
        }

        // remove everything which is not a number, letter or - / . _
        $string = preg_replace("/[^a-zA-Z0-9\-\/\.\_]/u", "", $string);

        // kill double --
        $string  = preg_replace("/(-){2,}/","-",$string);

        // remove - at the end
        $string = preg_replace ('/-$/', '', $string);

        if ($string=='') $string = (($class_)?$class_:'').'-'.(($id_)?$id_:'').'-empty';
            return $string;
    }
}