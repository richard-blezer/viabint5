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

class csv_import extends csv_api {


	function run_import($data) {


		$this->dir = _SRV_WEBROOT.'export/';

		$this->limit_lower = 0;
		$this->limit_upper = 0;
		
		if (isset($data['limit_lower'])) {
			$this->limit_lower = (int)$data['limit_lower'];
		} 

		if (isset($data['limit_upper'])) {
			$this->limit_upper = (int)$data['limit_upper'];
		}

		if (isset($data['counter_new'])) {
			$this->counter_new = (int)$data['counter_new'];
		}

		if (isset($data['counter_update'])) {
			$this->counter_update = (int)$data['counter_update'];
		}
		
		$id = $_GET['id'];
		if (!$this->getDetails($id)) die ('- id not found -');

		//$this->_checkCredentials($data);

		if (!isset($data['limit_lower'])) {
			$this->_startExport($id);
		}

		$this->password = $data['password'];
		$this->user = $data['user'];

		$this->id=$id;
		$this->ei_id = $this->_recordData['ei_id'];
		
		$data['type']=$this->_recordData['ei_type_spec'];
		$this->limit=$this->_recordData['ei_limit'];
		$this->file=$this->_recordData['ei_filename'];
		$this->delimiter = $this->_recordData['ei_delimiter'];
        $this->cat_tree_delimiter = $this->_recordData['ei_cat_tree_delimiter'];
		$this->enclosure = $this->_recordData['ei_enclosure'];
		$this->price_type = $this->_recordData['ei_price_type'];
		$this->store_id = $this->_recordData['ei_store_id'];
		// read header
		$header = $this->readHeader();

		switch ($data['type']) {
			case 'products':
				$this->_import_products();
				break;
		}
	}
	
/**
	 * Import Products Data (products & products_description table)
	 *
	 */
	function _import_products(){
        if (_SYSTEM_SECURITY_KEY!=$_GET['seckey'])
        {
            die(TEXT_WRONG_SYSTEM_SECURITY_KEY);

        }
		global $db,$customers_status,$language,$seo,$filter,$price,$xtPlugin;

		// allowed primary keys
		//$allowed_primary=array('products_id','external_id','products_model','products_ean');
		$allowed_primary=explode(',', EI_ALLOWED_PRIMARY);
		
		$this->_checkKeyImport($allowed_primary);

		if($this->secondary){
			$allowed_secondary=explode(',', EI_ALLOWED_SECONDARY);
			$this->_checkKeyImport($allowed_secondary, 'secondary');		
		}
		
		if ($this->limit_upper==0) $this->limit_upper = $this->limit;
		if ($this->limit_lower==0) {
			$this->clearLog();
			$this->limit_lower=1;
		}

		$lower = $this->limit_lower;

		$this->_language_list = $language->_getLanguageList();

		$product_table_fields = $this->readTabelFields(TABLE_PRODUCTS);
		$product_description_table_fields = $this->readTabelFields(TABLE_PRODUCTS_DESCRIPTION);

        ($plugin_code = $xtPlugin->PluginCode('plugin_xt_im_export_csv_import.php:_import_products_top')) ? eval($plugin_code) : false;
        $line_data = $this->_parseLineData(1); // get first line
         if (!isset($line_data["store_id"])){
            echo TEXT_MISSING_STORE_ID."<br />";
        }else{
           if ($line_data["store_id"]!=$this->store_id){
                echo TEXT_WRONG_STORE_ID; die();
            } 
        }
		for ($i = $lower; $i<$this->limit_upper && $i<$this->line_count;$i++) {
			$line_data = $this->_parseLineData($i);
           
            

            //TODO BOM Dedection

			// check if key exists in importfile

			if (!isset($line_data[$this->primary]) or $line_data[$this->primary]=='') {
				$msg=  '501|'.$i.' missing primary key in line :'.$i.'<br />';
				$this->addLog($msg);
			}elseif ($this->secondary !='' && (!isset($line_data[$this->secondary]) or $line_data[$this->secondary]=='')) {
				$msg=  '501|'.$i.' missing secondary key in line :'.$i.'<br />';
				$this->addLog($msg);	
			} else {
				
				if($this->secondary)
				$qry = " and ".$this->secondary."='".$line_data[$this->secondary]."'";

				// check if update or import
				$rs = $db->Execute("SELECT * FROM ".TABLE_PRODUCTS." WHERE ".$this->primary."='".$line_data[$this->primary]."' ".$qry."");

				if ($rs->RecordCount()==1) {
					$action = 'update';
					$products_id = $rs->fields['products_id'];
				} elseif ($rs->RecordCount()==0) {
					$action = 'import';
				} else {
					$msg= '500|'.$i.' double primary key in line :'.$i.'<br />';
					$this->addLog($msg);
					continue;
				}

				$line_data['products_price'] = str_replace(',', '.', $line_data['products_price']);
				
				if($this->price_type=='true')
				$line_data['products_price'] = $price->_BuildPrice($line_data['products_price'], $line_data['products_tax_class_id'], 'save');
				
				//	$line_data['last_modified'] = $db->DBTimeStamp(time());
				if ($action=='update') {

					$update_id = $line_data[$this->primary];
					
					if($this->secondary){
						$update_second_id = $line_data[$this->secondary];
						$qry = " and ".$this->secondary."='".$update_second_id."'";
						unset($line_data[$this->secondary]);
					}
					
					unset($line_data[$this->primary]);
					unset($line_data['products_id']);
					
					// set last modified date
					$db->AutoExecute(TABLE_PRODUCTS,$line_data,"UPDATE",$this->primary."='".$update_id."' ".$qry." ");
					$this->counter_update++;
				} else {
					unset($line_data['products_id']);
					
					if ($this->_recordData['ei_language']==1) { // only insert if language isset for this article
						$db->AutoExecute(TABLE_PRODUCTS,$line_data);
						$this->counter_new++;
						$products_id = $db->Insert_ID();
					}
				}

                ($plugin_code = $xtPlugin->PluginCode('plugin_xt_im_export_csv_import.php:_import_products_products_tag')) ? eval($plugin_code) : false;

				// categories tag?
				if ($line_data['categories']!='' && $products_id>0) {
					// import categories
					$categories = array();
					$categories = explode('#',$line_data['categories']);
                    $add_sql='';
                    if (($this->store_id) && ($this->_FieldExist('store_id', TABLE_PRODUCTS_TO_CATEGORIES))){
                        $add_sql = " and store_id=".$this->store_id;
                    }
					$db->Execute("DELETE FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE products_id='".$products_id."'". $add_sql);
					$first = true;
					foreach ($categories as $key => $id) {
						$cat_data = array();
						$cat_data['categories_id']=$id;
						$cat_data['products_id']=$products_id;
						$cat_data['master_link']='0';
                        if (($this->store_id) && ($this->_FieldExist('store_id', TABLE_PRODUCTS_TO_CATEGORIES))){
                            $cat_data['store_id']=$this->store_id;
                        }
						if ($first) {
							$cat_data['master_link']='1';
						}
						$db->AutoExecute(TABLE_PRODUCTS_TO_CATEGORIES,$cat_data);
						$first = false;
					}
				}

                if ($line_data['categories_tree']!='' && $products_id>0) {
                    $this->_insertCategory($line_data['categories_tree'],$products_id);
                }

                ($plugin_code = $xtPlugin->PluginCode('plugin_xt_im_export_csv_import.php:_import_products_categories_tag')) ? eval($plugin_code) : false;

				if ($this->_recordData['ei_language']==1) {
					// import language content
					foreach ($this->_language_list as $key => $val) {
						$description = array();
						foreach ($product_description_table_fields as $field => $name) {
							if ($field != 'products_id' && $field !='language_code') {
								if (isset($line_data[$field.'_'.$val['code']])) {
									
									// insert name if not set (to avoid inconsistend databases)
									if ($field=='products_name' && $line_data[$field.'_'.$val['code']]=='') {
										$line_data[$field.'_'.$val['code']]='Artikel-'.time();
									}						
									$description[$field] = $line_data[$field.'_'.$val['code']];
								}
							}
						}
						if (is_array($description)) {
							if ($action=='update') {
								if (count($description)>0){
                                    if (($this->store_id) && ($this->_FieldExist('products_store_id', TABLE_PRODUCTS_DESCRIPTION))){
                                        $add_sql = " and (products_store_id=".$this->store_id." || products_store_id=0)";
                                        $description["products_store_id"] = $this->store_id;
                                    }
    								$db->AutoExecute(TABLE_PRODUCTS_DESCRIPTION,$description,"UPDATE","products_id=".$products_id." and language_code='".$val['code']."'". $add_sql);
                                }  
                            } else {
								$description['products_id']=$products_id;
								$description['language_code']=$val['code'];
                                if (($this->store_id) && ($this->_FieldExist('products_store_id', TABLE_PRODUCTS_DESCRIPTION))){
                                    $description["products_store_id"] = $this->store_id;
                                }
								$db->AutoExecute(TABLE_PRODUCTS_DESCRIPTION,$description);
							}
						}

                        ($plugin_code = $xtPlugin->PluginCode('plugin_xt_im_export_csv_import.php:_import_products_products_description')) ? eval($plugin_code) : false;
                        $add_to_fileds = '';
                        $update_store = '';
                        if (($this->store_id) && ($this->_FieldExist('store_id', TABLE_SEO_URL))){
                            $add_to_fileds = 'store'.$this->store_id."_";
                            $update_store = $this->store_id;
                        }
                        
						// update seo urls
						if($line_data['url_text_'.$val['code']] != ''){
							$auto_generate = false;
							$line_data['url_text_'.$add_to_fileds.$val['code']] = $line_data['url_text_'.$val['code']];
						}else{
							$auto_generate = true;
							$line_data['url_text_'.$add_to_fileds.$val['code']] = $line_data['products_name_'.$val['code']];
						}

						$line_data['meta_keywords_'.$add_to_fileds.$val['code']] = $line_data['meta_keywords_'.$val['code']];
						$line_data['meta_title_'.$add_to_fileds.$val['code']] = $line_data['meta_title_'.$val['code']];
						$line_data['meta_description_'.$add_to_fileds.$val['code']] = $line_data['meta_description_'.$val['code']];

						//if ($action=='update') {
						$seo->_UpdateRecord('product',$products_id, $val['code'], $line_data,$auto_generate,"",$update_store);
						//}
					}
				}
			}// IF
		}

        ($plugin_code = $xtPlugin->PluginCode('plugin_xt_im_export_csv_import.php:_import_products_bottom')) ? eval($plugin_code) : false;

        $this->_redirecting();

	}

    
    /**
     * check if field exists within a table (precheck for plugins)
     *
     * @param string $field
     * @param string $table
     * @return boolean
     */
    function _FieldExist($field,$table) {
        global $db,$filter;
        $table = $filter->_charNum($table);
        $query = "SHOW FIELDS FROM ".$table." ";
        $record = $db->Execute($query);
        $records = array();
        if ($record->RecordCount() > 0) {
            while(!$record->EOF){
                if ($field==$record->fields['Field']) return true;
                $record->MoveNext();
            } $record->Close();
        }
        return false;
    }
    
	/**
	 * read first line and return header data.
	 *
	 */
	function readHeader() {
		global $filter;
		
		$this->filedata = array();
		$this->filedata = file($this->dir.$this->file);
		$this->line_count = count($this->filedata);
		$this->count = $this->line_count;
		$this->price_type = $this->_recordData['ei_price_type'];

		$this->primary = $this->_recordData['ei_type_match'];
		$this->secondary = $this->_recordData['ei_type_match_2'];
		
		$this->mapping = explode($this->delimiter, $this->filedata[0]);
		foreach ($this->mapping as $key => $val) {
			$val = trim(str_replace(array("\n", "\r", "\n\r"), '', $val),'"');
			$this->mapping[$key]=str_replace('"','',$val);
			
		}
		
		if (!is_array($this->mapping)) die ('- no columns - ');
		if (count($this->mapping)<2) die ('- no columns - ');
	
	}

	function _redirecting() {
		global $xtLink;

		if ($this->limit_upper<$this->count) {
			// redirect to next step
			$limit_lower =$this->limit_upper;
			$limit_upper =$this->limit_upper+$this->limit;
		
			$params = 'api=csv_import&id='.$this->ei_id.
						'&limit_lower='.$limit_lower.
						'&limit_upper='.$limit_upper.
						'&timer_start='.$this->timer_start.
						'&counter_new='.$this->counter_new.'&counter_update='.$this->counter_update.
                        '&seckey='.$_GET['seckey'];;

			
			echo $this->_displayHTML($xtLink->_link(array('default_page'=>'cronjob.php', 'params'=>$params)),$limit_lower,$limit_upper,$this->count - 1);
			
		} else {
			echo '<br />200 import finished';
			echo '<br />New:'.$this->counter_new;
			echo '<br />Update:'.$this->counter_update;
		}

	}

}

?>