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
  
class xt_canonical{
 		private $page_name; 
 		
 		/**
 		 * get Canonical Tag 
 		 * @param $page_section string
 		 * @return string
 		 * 
 		 * */
 		public function _getCanonicalUrl($page_section)
 		{
 			global $xtLink,$language,$xtPlugin;
 			$this->page_name = $page_section;
 			switch($this->page_name)
 			{
 				case 'product':
                case 'reviews':
 					return $this->_getProductUrl();
 					break;
 				case 'content':
 					return $this->_getContentUrl();
 					break;	
 				case 'manufacturers':
 					return $this->_getManufacturersUrl();
 					break;
 				case 'categorie':
 					return $this->_getCategorieUrl();
 					break;
 				case '404':
 					return $this->_get404Url();
 					break;
 				case 'index':
 					return $this->_getIndexUrl();
 					break;
 				default:
                     ($plugin_code = $xtPlugin->PluginCode('class.canonical.php:getCanonicalUrl')) ? eval($plugin_code) : false;
                     if(isset($plugin_return_value)){
                         return $plugin_return_value;
                     }else{
						$link_url = $xtLink->_link(array('page' => $page_section),'',true);
                        return '<link rel="canonical" href="'.$link_url.'" />';
                     }
                     break;
 			}
 		}
 		/**
 		 * get Canonical Tag for Product
 		 * 
 		 * @return string
 		 * 
 		 * */
        public function _getProductUrl() {
            global $p_info, $xtLink, $db, $store_handler, $language;
            
            // check if product is slave product and either master setting is true or product setting is true
            if ($p_info->data['products_master_model']!='') {
                // check if field exists, if yes Version >= 4.2.00, if not Version < 4.2.00
                $colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='products_store_id' AND TABLE_NAME='".TABLE_PRODUCTS_DESCRIPTION."'");
                if (!$colExists)
                {
                    // Version < 4.1.10
                    $sql = "SELECT products_id,products_canonical_master,pml.products_name,url_text FROM ".TABLE_PRODUCTS .
                        " LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pml USING(products_id)" .
                        " LEFT JOIN " . TABLE_SEO_URL . " ON (" . TABLE_PRODUCTS . ".products_id=" . TABLE_SEO_URL . ".link_id and link_type=1)" .
                        " WHERE products_model=? AND
                		pml.language_code=?
                		LIMIT 0,1";
                    $arr = array($p_info->data['products_master_model'],$language->code);
                }else{
                    // Version >= 4.2.00
                    $sql = "SELECT products_id,products_canonical_master,pml.products_name,url_text FROM ".TABLE_PRODUCTS .
                        " LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pml USING(products_id)" .
                        " LEFT JOIN " . TABLE_SEO_URL . " ON (" . TABLE_PRODUCTS . ".products_id=" . TABLE_SEO_URL . ".link_id and link_type=1 AND store_id=?)" .
                        " WHERE products_model=? AND
                		pml.language_code=? AND
                		pml.products_store_id=?
                		LIMIT 0,1";
                    $arr = array($store_handler->shop_id,$p_info->data['products_master_model'],$language->code,$store_handler->shop_id);
                }
                $rs = $db->Execute($sql,$arr);
                if ($rs->RecordCount()!=1) return false;
                if (XT_CANONICAL_APPLY_TO_ALL_SLAVES=='true' or $rs->fields['products_canonical_master']=='1') {
                    $link_array = array('page'=> 'product', 'type'=>'product', 'name'=>$rs->fields['products_name'], 'id'=>$rs->fields['products_id'],'seo_url'=>$rs->fields['url_text']);
                    $link_url = $xtLink->_link($link_array,'',true);
                    return '<link rel="canonical" href="'.$link_url.'" />';
                }
            }

            $link_array = array('page'=> 'product', 'type'=>'product', 'name'=>$p_info->data['products_name'], 'id'=>$p_info->data['products_id'],'seo_url'=>$p_info->data['url_text']);
            $link_url = $xtLink->_link($link_array,'',true);
            return '<link rel="canonical" href="'.$link_url.'" />';
            
        }
        /**
 		 * get Canonical Tag for Content
 		 * 
 		 * @return string
 		 * 
 		 * */
        public function _getContentUrl() {
            global $shop_content_data,$xtLink; 
            if(_SYSTEM_MOD_REWRITE == 'true' && $shop_content_data['url_text']!=''){
           	 	$link_array = array('page'=>'content', 'seo_url' => $shop_content_data['url_text']);
            }
            else {
            	$link_array = array('page'=>'content', 'params'=>'coID='.$shop_content_data['content_id'],'seo_url' => $shop_content_data['url_text']);
            }
            $link_url = $xtLink->_link($link_array,'',true);
            return '<link rel="canonical" href="'.$link_url.'" />';
        }
   		/**
 		 * get Canonical Tag for Manufacturer
 		 * 
 		 * @return string
 		 * 
 		 * */
 		public function _getManufacturersUrl() {
            global $manufacturer,$current_manufacturer_id,$xtLink; 
            $man = array('manufacturers_id' => $current_manufacturer_id);
			$man_data = $manufacturer->buildData($man);
            if((_SYSTEM_MOD_REWRITE == 'true') && (_SYSTEM_MOD_REWRITE_DEFAULT == 'true')){ 
				$link_array = array('page'=>'manufacturers', '','seo_url' => $man_data['url_text']);
            }
            else{
            	$link_array = array('page'=>'manufacturers', 'params'=>'mnf='.$current_manufacturer_id,'seo_url' => $man_data['url_text']);
            }
            $link_url = $xtLink->_link($link_array,'',true);
            return '<link rel="canonical" href="'.$link_url.'" />';
        }
        /**
 		 * get Canonical Tag for Categorie
 		 * 
 		 * @return string
 		 * 
 		 * */
 		public function _getCategorieUrl() {
            global $category,$current_category_id,$xtLink;
            if((_SYSTEM_MOD_REWRITE == 'true') && (_SYSTEM_MOD_REWRITE_DEFAULT == 'true')){ 
            	$link_array = array('page'=>'categorie', '','seo_url' => $category->current_category_data['url_text']);
            }
            else {
            	$link_array = array('page'=>'categorie', 'params'=>'cat='.$current_category_id,'seo_url' => $category->current_category_data['url_text']);
            }
            $link_url = $xtLink->_link($link_array,'',true);
            return '<link rel="canonical" href="'.$link_url.'" />';
        }
        
        /**
         * get Canonical Tag for 404
         *
         * @return string
         *
         * */
        public function _get404Url() {
        	global $xtLink,$language;
        	return '';//'<link rel="canonical" href="'.$xtLink->_link(array('page'=>'404','seo_url'=>strtolower($language->code).'/404',true),'',true).'" />';
        }
        /**
         * get Canonical Tag for 404
         *
         * @return string
         *
         * */
        public function _getIndexUrl() {
        	global $xtLink,$language,$page;
            if($language->code == $language->default_language)
                return '<link rel="canonical" href="'.$xtLink->_link(array('page'=>$page->page_name,'paction'=>$page->page_action,'',true),'',true).'" />';
            else
                return '<link rel="canonical" href="'.$xtLink->_link(array('page'=>$page->page_name,'paction'=>$page->page_action, 'seo_url'=>strtolower($language->code).'/index',true),'',true).'" />';
        }
        
        /**
         * get Canonical Tag for 404
         *
         * @return string
         *
         * */
        public function _getOtherUrl() {
        	global $xtLink,$language,$page;
        	return '<link rel="canonical" href="'.$xtLink->_link(array('page'=>$page->page_name,'paction'=>$page->page_action,true),'',true).'" />';
        }

  }
?>