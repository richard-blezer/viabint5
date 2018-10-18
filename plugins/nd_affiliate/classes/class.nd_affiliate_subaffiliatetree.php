<?php
/*------------------------------------------------------------------------------
	$Id: class.nd_affiliate_subaffiliatetree.php 61 2011-10-06 09:43:59Z Standard $
	copyright (c) 2008 by Andreas Oberzier
	http://www.netz-designer.de
	projects@netz-designer.de
	---------------------------------------
	project: Affiliate-Plugin für xt:Commerce Enterprise
	
	This file may not be redistributed in whole or significant part.
------------------------------------------------------------------------------*/

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if(!class_exists('nd_affiliate_affiliate')) {
	require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/classes/class.nd_affiliate_affiliate.php';
}

class nd_affiliate_subaffiliatetree extends nd_affiliate_affiliate {
	
	public $_table = TABLE_AFFILIATE;
	public $_table_lang = null;
	public $_table_seo = null;
	public $_master_key = 'affiliate_id';
	
	function nd_affiliate_subaffiliatetree($affiliate_id = '') {
		if(!empty($affiliate_id)) {
			$this->affiliateID = $affiliate_id;
			$this->affiliateData = $this->_loadAffiliate();
		} else {
			$this->affiliateData = $_POST;
		}
	}
    
	function _appendNode($array, $node, $i) {
  		if(is_numeric($node[$i]) && $i<=AFFILIATE_TIER_LEVELS) {
  			$array[$node[$i]] = $this->_appendNode($array[$node[$i]], $node, ++$i);
  		} else {
  			//$array[$node[$i]] = 1;
  		}
  		return $array;  		
  	}
  	
	function getSubaffiliateTree() {
		global $db;
		
		if($this->url_data['aID'] > 0) {
			$this->affiliateID = (int)$this->url_data['aID'];
			$this->affiliateData = $this->_loadAffiliate();
		}
		
		$subaffiliates = array();
		
		$root = $db->Execute("SELECT affiliate_root, affiliate_lft, affiliate_rgt
	  					      FROM " . TABLE_AFFILIATE . "
	  					      WHERE affiliate_id = '" . $this->affiliateID . "'");
	  	
	  	$node = $db->Execute("SELECT affiliate_id 
	  						  FROM " . TABLE_AFFILIATE . "
	  						  WHERE affiliate_root = '" . $root->fields['affiliate_root'] . "'
	  						  AND (affiliate_rgt-affiliate_lft) = 1
	  						  AND affiliate_lft > '" . $root->fields['affiliate_lft'] . "'
	  						  AND affiliate_rgt < '" . $root->fields['affiliate_rgt'] . "'");
	  	
	  	while(!$node->EOF) {
	  		$tree = $db->Execute("SELECT aa2.affiliate_id, (aa2.affiliate_rgt - aa2.affiliate_lft) as height
	  							  FROM  " . TABLE_AFFILIATE . "  AS aa1, " . TABLE_AFFILIATE . "  AS aa2
	  							  WHERE  aa1.affiliate_root = aa2.affiliate_root
	  							  AND aa1.affiliate_lft BETWEEN aa2.affiliate_lft AND aa2.affiliate_rgt
	  							  AND aa1.affiliate_rgt BETWEEN aa2.affiliate_lft AND aa2.affiliate_rgt
	  							  AND aa1.affiliate_id =  '" . $node->fields['affiliate_id'] . "'
	  							  AND aa2.affiliate_id >= '" . $this->affiliateID . "'
	  							  ORDER by height desc");
	  		$temp_array = array();
	  		while(!$tree->EOF) {
	  			$temp_array[] = $tree->fields['affiliate_id'];
	  			$tree->MoveNext();
	  		}
	  		$subaffiliates = $this->_appendNode($subaffiliates, $temp_array, 0);
	  		$node->MoveNext();
	  	}

	  	$html = '<table border="0" cellpadding="5" cellspacing="5">';
	  	$html .= '<tr><td>' . $this->affiliateData['affiliate_firstname'] . ' ' . $this->affiliateData['affiliate_lastname'] . '</td></tr>';
	  	$this->_renderSubaffiliates($subaffiliates[$this->affiliateID], 0, $html);
	  	$html .= '</table>';
	  	
	  	return $html;
	}
	
	function _renderSubaffiliates($subaffiliates, $i, &$html) {
		global $db;
		
		if(USER_POSITION == 'admin') {
			$shoproot = _SRV_WEB_UPLOAD;
		} elseif(USER_POSITION == 'store') {
			$shoproot = _SRV_WEB;
		}

		if(is_array($subaffiliates)) {
			arsort($subaffiliates);
		} else {
			$subaffiliates = array();
		}
		$k = sizeof($subaffiliates);
		foreach($subaffiliates as $key => $value) {
			$k--;
			$affiliate = $db->Execute("SELECT affiliate_firstname, affiliate_lastname
  									   FROM " . TABLE_AFFILIATE . "
  									   WHERE affiliate_id = '" . $key . "'");
			$html .= '<tr><td>';
			
			for($j=0; $j<=$i; $j++) {
				if($j > 0) {
					$html .= '<img src="'.$shoproot.'plugins/nd_affiliate/images/dotted.gif" width="21" height="21" alt=""  align="middle">';
				}
				$html .= '<img src="'.$shoproot.'plugins/nd_affiliate/images/trans.gif" width="21" height="1" alt="">';
			}
			if($k == 0 && $i > 0) {
				$html .= '<img src="'.$shoproot.'plugins/nd_affiliate/images/corner.gif" width="21" height="21" alt=""  align="middle">';
			} else {
				$html .= '<img src="'.$shoproot.'plugins/nd_affiliate/images/crossing.gif" width="21" height="21" alt=""  align="middle">';
			}
			$html .= '<img src="'.$shoproot.'plugins/nd_affiliate/images/straight.gif" width="21" height="21" alt=""  align="middle"><img src="'.$shoproot.'plugins/nd_affiliate/images/arrow.gif" width="21" height="21" alt="" align="middle"><img src="'.$shoproot.'plugins/nd_affiliate/images/trans.gif" width="10" height="1" alt="">' . $affiliate->fields['affiliate_firstname'] . ' ' . $affiliate->fields['affiliate_lastname'] . ' (' . $key . ')</td></tr>' . "\n";

			if(is_array($value)) {
				$this->_renderSubaffiliates($value, ++$i, $html);
				$i--;
			}
		}
	}
}
?>