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

class csv_export extends csv_api {


	function run_export($data) {

		if (isset($data['limit_lower'])) {
			$this->limit_lower = (int)$data['limit_lower'];
		} 

		if (isset($data['limit_upper'])) {
			$this->limit_upper = (int)$data['limit_upper'];
		}

		if (isset($data['primary'])) $this->primary = $data['primary'];
		if (isset($data['secondary'])) $this->secondary = $data['secondary'];

		$id = $_GET['id'];
		if (!$this->getDetails($id)) die ('- id not found -');

		//$this->_checkCredentials($data);
		
		if (!isset($data['limit_lower'])) {
			$this->_startExport($id);
		}

		$this->password = $data['password'];
		$this->user = $data['user'];
		$this->delimiter = $this->_recordData['ei_delimiter'];
		$this->enclosure = $this->_recordData['ei_enclosure'];
        $this->price_type = $this->_recordData['ei_price_type']; 
		$this->id=$id;
		$this->ei_id = $this->_recordData['ei_id'];

		$data['type']=$this->_recordData['ei_type_spec'];
		$this->limit=$this->_recordData['ei_limit'];
		$this->type=$data['type'];
		$this->store_id = $this->_recordData['ei_store_id']; 
        
		switch ($this->type) {
			case 'products':
				$this->_export_products();
				break;
					
		}

	}

	/**
	 * exportfunction for products + products_description + seo
	 *
	 */
	function _export_products() {
        if (_SYSTEM_SECURITY_KEY!=$_GET['seckey'])
        {
            die(TEXT_WRONG_SYSTEM_SECURITY_KEY);

        }

		global $db,$customers_status,$language,$price;

		if ($this->limit_upper==0) $this->limit_upper = $this->limit;
        
		$query = "SELECT * FROM ".TABLE_PRODUCTS." LIMIT ".$this->limit_lower.",".$this->limit;

		$count = $db->Execute("SELECT COUNT(*) as count FROM ".TABLE_PRODUCTS);

		$this->count = $count->fields['count'];

		$_customers_status = $customers_status->_getStatusList('admin');
		$_language_list = $language->_getLanguageList();

		$rs = $db->Execute($query);

		if ($rs->RecordCount()>0) {
			// delete if limit = 0
			$fp = $this->_openFile();
			$header_added = false;
			while (!$rs->EOF) {
				unset($rs->fields['price_flag_graduated_all']);
				unset($rs->fields['price_flag_special_all']);
				unset($rs->fields['flag_has_specials']);
				foreach ($_customers_status as $key => $val) {
					unset($rs->fields['price_flag_graduated_'.$val['id']]);
					unset($rs->fields['price_flag_special_'.$val['id']]);
				}
				$records = $rs->fields;
                
                // price net/gros
                if($this->price_type=='true')
                $records['products_price'] = $price->_BuildPrice($records['products_price'], $records['products_tax_class_id'], 'show');
                $add_sql='';
                if ($this->store_id){
                    $add_sql = " and store_id=".$this->store_id;
                }
				// add categories info
				$cs = $db->Execute("SELECT * FROM ".TABLE_PRODUCTS_TO_CATEGORIES." 
				                    WHERE products_id='".$rs->fields['products_id']."' ".$add_sql."
				                    ORDER BY master_link DESC");
				
				if ($cs->RecordCount()>1) {
					$cats = array();
					while (!$cs->EOF) {
						$cats[]=$cs->fields['categories_id'];
						$cs->MoveNext();
					}
					$records['categories'] = implode('#',$cats);
				} else {
					$records['categories'] = $cs->fields['categories_id'];
				}
                $records['store_id'] = $this->store_id;
				$_lang_data = array();
				// export languages ? ei_language
				if ($this->_recordData['ei_language']==1) {
				foreach ($_language_list as $key => $val) {
				    $add_sql='';
                    if ($this->store_id){
                        $add_sql = " and pd.products_store_id=".$this->store_id." and su.store_id=".$this->store_id;
                    }
					$lang_sql_Product = "SELECT pd.*, su.url_text,su.meta_title,su.meta_keywords,su.meta_description 
					                     FROM ".TABLE_PRODUCTS_DESCRIPTION." pd, ".TABLE_SEO_URL." su 
					                     WHERE su.link_id='".$rs->fields['products_id']."' and pd.products_id='".$rs->fields['products_id']."' 
					                       and su.language_code='".$val['code']."' and pd.language_code='".$val['code']."' and su.link_type='1' ".$add_sql ;
					$lang_record = $db->Execute($lang_sql_Product);
					if($lang_record->RecordCount() > 0){
						unset($lang_record->fields['products_id']);
						$_lang_data['products_description_'.$val['code']] = _filterText($lang_record->fields['products_description']);
						$_lang_data['products_name_'.$val['code']] = _filterText($lang_record->fields['products_name']);
						$_lang_data['products_short_description_'.$val['code']] = _filterText($lang_record->fields['products_short_description']);
						$_lang_data['url_text_'.$val['code']] = $lang_record->fields['url_text'];
						$_lang_data['meta_title_'.$val['code']] = $lang_record->fields['meta_title'];
						$_lang_data['meta_keywords_'.$val['code']] = $lang_record->fields['meta_keywords'];
						$_lang_data['meta_description_'.$val['code']] = $lang_record->fields['meta_description'];
						$records = array_merge($records,$_lang_data);
					}
				}
				}

				if (!$header_added) {
					$this->_writeHeader($records,$fp,$this->codecs[$this->type]);
					$header_added = true;
				}

				/* ouch 
					$line = implode($this->delimiter,$records);
					fputs($fp, $line."\n");
				 */
				fputcsv ($fp, $records, $this->delimiter, $this->enclosure);
				$rs->MoveNext();
			}$rs->Close();
			fclose($fp);

		}

		$this->_redirecting();
	}

	/**
	 * write export header to file
	 *
	 * @param fields $records
	 * @param handle $fp
	 * @param string $commandline
	 */
	function _writeHeader($records,&$fp,$commandline) {
		if ($this->limit_lower==0) {
		$header = array();
		foreach ($records as $key => $val) {
			$header[]=$key;
		}
		$header = implode($this->delimiter,$header);
	//	fputs($fp, $commandline."\n");
		fputs($fp, $header."\n");
		}
	}


	function _openFile() {
		
		$file = _SRV_WEBROOT.'export/'.$this->_recordData['ei_filename'];
		if ($this->limit_lower==0) {
			if (file_exists($file)) unlink($file);
		}
		$fp = fopen($file, "a");
		return $fp;
	}

	function _redirecting() {
		global $xtLink;

		if ($this->limit_upper<$this->count) {
			// redirect to next step
			global $xtLink;
			$limit_lower =$this->limit_upper;
			$limit_upper =$this->limit_upper+$this->limit;
			$params = 'api=csv_export&id='.$this->ei_id.
						'&limit_lower='.$limit_lower.
						'&limit_upper='.$limit_upper.
						'&timer_start='.$this->timer_start.
                        '&seckey='.$_GET['seckey'];;
				
			if (isset($this->primary)) $params.='&primary='.$this->primary;
			if (isset($this->secondary)) $params.='&secondary='.$this->secondary;
			//$xtLink->_redirect($xtLink->_link(array('default_page'=>'cronjob.php', 'params'=>$params)));
			echo $this->_displayHTML($xtLink->_link(array('default_page'=>'cronjob.php', 'params'=>$params)),$limit_lower,$limit_upper,$this->count);
		} else {
				
			// insert into log
			$this->_stopExport($this->id);
			echo $this->_htmlHeader();	
			echo '- export finished -<br />';
			echo '- exported datasets '.$this->count.'<br />';
			
			$this->showLog($this->id);
			echo $this->_htmlFooter();
			$this->_clearLog($this->id);	
		}
	}
}
?>