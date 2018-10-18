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
 # @version $Id: xt_etracker.php 4547 2011-05-02 09:13:47Z dev_tunxa $
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

class xt_etracker{
	
	private $etracker_code_version = '3.0';
	private $etracker_taghost = 'code.etracker.com';
	private $etracker_cnthost = 'www.etracker.de';
	private $tval;
	private $basket;
	private $tonr;
	private $order;
	
	function _getCode($page_section) {
		
		if(XT_ETRACKER_ACTIVATED == 'false')
		return '';

		$cryptId = XT_ETRACKER_SECURECODE;
		
		if(!preg_match("/^[0-9a-zA-Z]+$/", $cryptId))
		return '';
		
		// parameter check
		$ssl			= XT_ETRACKER_SSL=='true' ? 1 : 0;
		$pagename		= rawurlencode( $this->_getPagename($page_section) );
		$areas			= rawurlencode( $this->getAreas() );
		if(XT_ETRACKER_AKTIVATE_CART == 'true'){
			$ilevel			= 1;
			$targets		= rawurlencode( XT_ETRACKER_TARGET );
			$tval 			= str_replace(',', '.', $this->tval);
			$tval 			= is_numeric($this->tval) ? $this->tval : 0;
			$tsale			= 0;
			$tonr 			= str_replace('"', '', $this->tonr);
			$customer	= $this->isNewcustomer() ? 1 : 0;
			
			$basket			= rawurlencode( $this->basket );
			
		}else{
			$ilevel			= 0;
			$targets		= '';
			$tval 			= 0;
			$tsale			= 0;
			$tonr 			= '';
			$customer		= 0;
			$basket			= '';
		}
		
		$lpage 			= is_numeric($lpage) ? $lpage : 0;
		// trigger
		$trigger		= preg_replace("/\s{1,}/", '', $trigger); // remove all \s*
		$trigger		= preg_match("/^[0-9,]+$/", $trigger) ? $trigger : ''; // comma separated list of integers
		
		
		$se				= 0;
		$url			= str_replace('"', '', $this->getURL());
		$tag			= str_replace('"', '', $tag);
		$organisation 	= rawurlencode('XTC');
		$demographic  	= rawurlencode('XTC');
		$noScript		= XT_ETRACKER_NOSCRIPT == 'true' ? true : false;
		
		$code  = "<!-- Copyright (c) 2000-".date("Y")." etracker GmbH. All rights reserved. -->\n";
		$code .= "<!-- This material may not be reproduced, displayed, modified or distributed -->\n";
		$code .= "<!-- without the express prior written permission of the copyright holder. -->\n\n";
		$code .= "<!-- BEGIN etracker Tracklet ".$this->etracker_code_version." -->\n";
		$code .= "<script type=\"text/javascript\">document.write(String.fromCharCode(60)+\"script type=\\\"text/javascript\\\" src=\\\"http\"+(\"https:\"==document.location.protocol?\"s\":\"\")+\"://".$this->etracker_taghost."/t.js?et=".$cryptId."\\\">\"+String.fromCharCode(60)+\"/script>\");</script>\n";
		//$code .= "<p style=\"display:none;\" id=\"et_count\"></p>";
		$code .= $this->getParameters( $showAll, $easy, $pagename, $areas, $ilevel,
		$targets, $tval, $tsale, $tonr, $lpage, $trigger,
		$customer, $basket, $free, $se, $url, $tag,
		$organisation,  $demographic );
		
		$code .= "<script type=\"text/javascript\">_etc();</script>\n";
		$code .= "<noscript><p><a href=\"http://www.etracker.com\"><img style=\"border:0px;\" alt=\"\" src=\"https://www.etracker.com/nscnt.php?et=".$cryptId."\" /></a></p></noscript>\n";
		
		if($noScript)
		$code .= $this->getNoScriptTag( $cryptId, $easy, $ssl, $pagename, $areas, $ilevel,
		$targets, $tval, $tsale, $tonr, $lpage, $trigger,
		$customer, $basket, $free, $se, $url, $tag,
		$organisation,  $demographic );
		
		$code .= "<!-- etracker CODE END -->\n\n";
		
		return $code;
	}
	
	
	function isNewcustomer(){
		if($SESSION['customer']->customers_id > 0){
			$date_added = strtotime($SESSION['customer']->customer_info['date_added']);
			$yesterday = date('Y-m-d H:i:s', mktime(0, 0, 0, date("m") , date("d") - 1, date("Y")));
			if($date_added> $yesterday)
				return true;
			else
				return false;
		}
		else{
			return false;
		}
	}
	
	function getTval(){

		$this->tval  = $this->order->order_total['total']['plain'];
	}
	
	function getTonr(){
		if(!isset($_SESSION['success_order_id'])){
			$this->tonr = 0;
			return false;
		}else{
			$this->tonr = $_SESSION['success_order_id'];
			return true;
		}
	}
	
	function getBasket(){
		
		$basket_content = $this->order->order_products;
		$this->basket = '';
		foreach($basket_content as $key=>$value){
			$this->basket .= $value['products_id'].','.$value['products_name'].','.$value['products_model'].','.(int)$value['products_quantity'].','.$value['products_price']['plain'].';';
		}	
		$this->basket = substr($this->basket, 0, -1) ;
		return true;
	}
	
	function getAreas(){
		
		return XT_ETRACKER_AREAS;
	}
	
	function getURL(){
	
		return urlencode($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	}
	
	/**
	* get Pagename
	* @param $page_section string
	* @return string
	*
	* */
	public function _getPagename($page_section)
	{
		switch($page_section)
		{
			case 'product':
				return $this->_getProductPagename();
				break;
			case 'content':
				return $this->_getContentPagename();
				break;
			case 'manufacturers':
				return $this->_getManufacturersPagename();
				break;
			case 'categorie':
				return $this->_getCategoriePagename();
				break;
			case 'customer':
				return $this->_getCustomerPagename();
				break;
			case 'cart':
				return $this->_getCartPagename();
				break;
			case 'checkout':
				return $this->_getCheckoutPagename();
				break;
			default:
				return 'index';
			break;
		}
	}
	
	
	/**
	* get Pagename for Customer
	*
	* @return string
	*
	* */
	public function _getCustomerPagename() {
		global $page;
		if($page->page_action != '')
			return 'customer__'.$page->page_action;
		else
		return 'customer';
	}
	
	/**
	* get Pagename for Cart
	*
	* @return string
	*
	* */
	public function _getCartPagename() {
		global $page;
		if($page->page_action != '')
		return 'cart__'.$page->page_action;
		else
		return 'cart';
	}
	
	/**
	* get Pagename for Checkout
	*
	* @return string
	*
	* */
	public function _getCheckoutPagename() {
		global $page;
		if($page->page_action != ''){
			if($page->page_action=='success'){
				$this->getTonr();
				$this->order = new order($this->tonr,$_SESSION['customer']->customers_id);
				$this->getBasket();
				$this->getTval();
			}
			
			return 'checkout__'.$page->page_action;
		}
		else
			return 'checkout';
	}
	
	/**
	* get Pagename for Product
	*
	* @return string
	*
	* */
	public function _getProductPagename() {
		global $p_info,$xtLink,$db;
	
	
		// check if product is slave product and either master setting is true or product setting is true
		if ($p_info->data['products_master_model']!='') {
			$sql = "SELECT products_id,products_canonical_master FROM ".TABLE_PRODUCTS." WHERE products_model='".$p_info->data['products_master_model']."' LIMIT 0,1";
			$rs = $db->Execute($sql);
			if ($rs->RecordCount()!=1) return false;
			if (XT_CANONICAL_APPLY_TO_ALL_SLAVES=='true' or $rs->fields['products_canonical_master']=='1') {
				$can_product = new product($rs->fields['products_id']);
				return $can_product->data['url_text'];
			}
		}
	
		return 'product__'.str_replace('/', '__', $p_info->data['url_text']);
	}
	
	/**
	* get Pagename for Content
	*
	* @return string
	*
	* */
	public function _getContentPagename() {
		global $shop_content_data,$xtLink;

		return 'content__'.str_replace('/', '__', $shop_content_data['url_text']);
	}
	
	/**
	* get Pagename for Manufacturer
	*
	* @return string
	*
	* */
	public function _getManufacturersPagename() {
		global $manufacturer,$current_manufacturer_id,$xtLink;
		$man = array('manufacturers_id' => $current_manufacturer_id);
		$man_data = $manufacturer->buildData($man);
		return 'manufactures__'.str_replace('/', '__', $man_data['url_text']);
	}
	
	/**
	* get Pagename for Categogie
	*
	* @return string
	*
	* */
	public function _getCategoriePagename() {
		global $category,$current_category_id,$xtLink;
		return 'category__'.str_replace('/', '__', $category->current_category_data['url_text']);
	}
	
	/***********************************************************
	* Note: private function below
	* use only main function 'getCode()'
	/***********************************************************/
	
	/**
	 * \brief Get parameters
	 * gives back the parameter code block
	 *
	 * \param	boolean \a $easy [false]
	 * \param	boolean \a $ssl [false]
	 * \param	string 	\a $pagename ['']
	 * \param	string 	\a $areas ['']
	 * \param	integer \a $ilevel [0]
	 * \param	string 	\a $targets ['']
	 * \param	float 	\a $tval ['']
	 * \param	integer \a $tsale [0]
	 * \param	string 	\a $tonr ['']
	 * \param	integer \a $lpage [0]
	 * \param	string 	\a $trigger ['']
	 * \param	integer \a $customer [0]
	 * \param	string 	\a $basket ['']
	 * \param	boolean \a $free [false]
	 * \param	integer \a $se [0]
	 * \param	string 	\a $url ['']
	 * \return	string
	 * \private
	 */
	function getParameters(	$showAll 		= false,
	$easy 			= 0,
	$pagename 		= '',
	$areas 			= '',
	$ilevel 		= 0,
	$targets 		= '',
	$tval 			= '',
	$tsale 			= 0,
	$tonr 			= 0,
	$lpage 			= 0,
	$trigger 		= 0,
	$customer		= 0,
	$basket 		= '',
	$free 			= false,
	$se 			= 0,
	$url			= '',
	$tag			= '',
	$organisation 	= '',
	$demographic  	= ''
	)
	{
		$code = '';
	
		if($easy)
		$code .= "var et_easy         = $easy;\n";
		if($pagename || $showAll)
		$code .= "var et_pagename     = \"$pagename\";\n";
		if($areas || $showAll)
		$code .= "var et_areas        = \"$areas\";\n";
		if($ilevel || $showAll)
		$code .= "var et_ilevel       = ".$ilevel.";\n";
		if($url || $showAll)
		$code .= "var et_url          = \"$url\";\n";
		if($tag || $showAll)
		$code .= "var et_tag          = \"$tag\";\n";
		if($organisation)
		$code .= "var et_organisation = \"$organisation\";\n";
		if($demographic)
		$code .= "var et_demographic  = \"$demographic\";\n";
		if($targets || $showAll)
		$code .= "var et_target       = \"$targets\";\n";
		if($tval || $showAll)
		$code .= "var et_tval         = \"$tval\";\n";
		if($tonr || $showAll)
		$code .= "var et_tonr         = \"$tonr\";\n";
		if($tsale || $showAll)
		$code .= "var et_tsale        = $tsale;\n";
		if($customer || $showAll)
		$code .= "var et_cust         = $customer;\n";
		if($basket || $showAll)
		$code .= "var et_basket       = \"$basket\";\n";
		if($lpage || $showAll)
		$code .= "var et_lpage        = \"$lpage\";\n";
		if($trigger || $showAll)
		$code .= "var et_trig         = \"$trigger\";\n";
		if($se || $showAll)
		$code .= "var et_se           = \"$se\";\n";
	
		$ret = '';
		if($code)
		{
			$ret .= "\n<!-- etracker PARAMETER ".$this->etracker_code_version." -->\n";
			$ret .= "<script type=\"text/javascript\">\n";
			$ret .= $code;
			$ret .= "</script>\n";
			$ret .= "<!-- etracker PARAMETER END -->\n\n";
		}
		return $ret;
	}
	
	/**
	* \brief Get noscript block
	* gives back the noscript image tag
	*
	* \param	string 	\a $cryptId
	* \param	boolean \a $easy [false]
	* \param	boolean \a $ssl [false]
	* \param	string 	\a $pagename ['']
	* \param	string 	\a $areas ['']
	* \param	integer \a $ilevel [0]
	* \param	string 	\a $targets ['']
	* \param	float 	\a $tval ['']
	* \param	integer \a $tsale [0]
	* \param	string 	\a $tonr ['']
	* \param	integer \a $lpage [0]
	* \param	string 	\a $trigger ['']
	* \param	integer \a $customer [0]
	* \param	string 	\a $basket ['']
	* \param	boolean \a $free [false]
	* \param	integer \a $se [0]
	* \param	string 	\a $url ['']
	* \return	string
	* \private
	*/
	function getNoScriptTag($cryptId,
	$easy 			= false,
	$ssl 			= false,
	$pagename 		= '',
	$areas 			= '',
	$ilevel 		= 0,
	$targets 		= '',
	$tval 			= '',
	$tsale 			= 0,
	$tonr 			= 0,
	$lpage 			= 0,
	$trigger 		= 0,
	$customer		= 0,
	$basket 		= '',
	$free 			= false,
	$se 			= 0,
	$url			= '',
	$tag			= '',
	$organisation 	= '',
	$demographic  	= '')
	{
		$script 		= $free ? 'fcnt' : 'cnt';
	
		$code .= "<!-- etracker CODE NOSCRIPT ".$this->etracker_code_version." -->\n";
		$code .= "<noscript>\n";
		$code .= "<p><a href='http://".$this->etracker_cnthost."/app?et=$cryptId'>\n";
		$code .= "<img style='border:0px;' alt='' src='";
		if($ssl==1) $code .= "https"; else $code .= "http";
		$code .= "://".$this->etracker_cnthost."/$script.php?\n";
		$code .= "et=$cryptId&amp;v=".$this->etracker_code_version."&amp;java=n&amp;et_easy=$easy\n";
		$code .= "&amp;et_pagename=$pagename\n";
		$code .= "&amp;et_areas=$areas&amp;et_ilevel=$ilevel&amp;et_target=$targets,$tval,$tonr,$tsale\n";
		$code .= "&amp;et_lpage=$lpage&amp;et_trig=$trigger&amp;et_se=$se&amp;et_cust=$customer\n";
		$code .= "&amp;et_basket=$basket&amp;et_url=&amp;et_tag=".$tag."\n";
		$code .= "&amp;et_organisation=".$organisation."&amp;et_demographic=".$demographic."' /></a></p>\n";
		$code .= "</noscript>\n";
		$code .= "<!-- etracker CODE NOSCRIPT END-->\n\n";
		return $code;
	}
}