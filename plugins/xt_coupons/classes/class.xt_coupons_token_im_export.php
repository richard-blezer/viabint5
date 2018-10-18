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


require_once _SRV_WEBROOT.'plugins/xt_coupons/classes/class.csvapi_coupons.php';   


class xt_coupons_token_im_export extends csv_api_coupons {

	protected $_table = TABLE_COUPONS_IM_EXPORT;
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'id';
   

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		global $language;

		$header['id'] = array('type' => 'hidden');

		$header['ei_filename'] = array('type' => 'textfield');


		$header['ei_type'] = array('type' => 'dropdown',
									'url'  => 'DropdownData.php?get=imexport_types&plugin_code=xt_im_export');
		$header['ei_coupon'] = array('type' => 'dropdown',
									'url'  => 'DropdownData.php?get=coupon&plugin_code=xt_coupons');
									
		$rowActions[] = array('iconCls' => 'start', 'qtipIndex' => 'qtip1', 'tooltip' => 'Run');

		if ($this->url_data['edit_id'])
          $js = "var edit_id = ".$this->url_data['edit_id'].";";
        else    
//          $js = "var edit_id = record.data.ei_id;";
          $js = "var edit_id = record.data.id;";
          
		$js .= "Ext.Msg.show({
                title:'".TEXT_START."',
                msg: '".TEXT_START_ASK."',
                buttons: Ext.Msg.YESNO,
                animEl: 'elId',
                fn: function(btn){runImport(edit_id,btn);},
                icon: Ext.MessageBox.QUESTION
                });";

		$rowActionsFunctions['start'] = $js;


		$js = "function runImport(edit_id,btn){
	  		var edit_id = edit_id;
	  		if (btn == 'yes') {
	  			addTab('row_actions.php?type=coupons_token_im_export&id='+edit_id,'... import / export ...');  
			}
		};";

		$params['rowActionsJavascript'] = $js;
	
		$params['rowActions']             = $rowActions;
		$params['rowActionsFunctions']    = $rowActionsFunctions;

		if (!$this->url_data['edit_id'] && $this->url_data['new'] != true) {
			$params['include'] = array ('id','ei_id','ei_type','ei_title', 'ei_delimiter', 'ei_limit','ei_filename');
		}

		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;
		$params['SortField']      = $this->_master_key;
		$params['SortDir']        = "DESC";

		return $params;
	}

    function _get($ID = 0) {
		global $xtPlugin, $db, $language;

		if ($this->position != 'admin') return false;

		if ($ID === 'new') {
			$obj = $this->_set(array(), 'new');
			$ID = $obj->new_id;
		}

		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key);

		if ($this->url_data['get_data'])
		$data = $table_data->getData();
		elseif($ID)
		$data = $table_data->getData($ID);
		else
		$data = $table_data->getHeader();

		$obj = new stdClass;
		$obj->totalCount = count($data);
		$obj->data = $data;

		return $obj;
	}

	function _set($data, $set_type='edit'){
		global $db,$language,$filter;


		if($data['ei_id']=='') {
			$data['ei_id'] = md5(rand(5, 15).time());
		}
        
        if($data['ei_filename'] == '') {
           $data['ei_filename'] = $data['ei_type'].date('YmdHis').'.csv';   
        }
        if($data['ei_delimiter'] == '') {
           $data['ei_delimiter'] = ';';   
        }
        if($data['ei_limit'] <= 0) {
           $data['ei_limit'] = 20;   
        }
        if($data['ei_title'] == '') {
           $data['ei_title'] = $data['ei_type'].date('YmdHis');   
        }
		
		$obj = new stdClass;
		$o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		$obj = $o->saveDataSet();
			
		return $obj;
	}


	function _unset($id = 0) {
		global $db;
		if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		$id=(int)$id;
		if (!is_int($id)) return false;

		$db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ?",array($id));

	}
    
    function run_import($data) {
        $this->dir = _SRV_WEBROOT.'export/';
        $this->api = $data['api'];

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
        } else {
            $this->counter_new = 0;
        }

        if (isset($data['counter_update'])) {
            $this->counter_update = (int)$data['counter_update'];
        } else {
            $this->counter_update = 0;
        }
        
        if (isset($data['counter_error'])) {
            $this->counter_error = (int)$data['counter_error'];
        } else {
            $this->counter_error = 0;
        }
        
        $id = $_GET['id'];
        if (!$this->getDetails($id)) die ('- id not found -');

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
        
        // read header
        $header = $this->readHeader();

        $this->_import_coupon_codes();
    }
    
    function run_export($data) {
    $this->dir = _SRV_WEBROOT.'export/';
    $this->api = $data['api'];
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
    } else {
        $this->counter_new = 0;
    }

    if (isset($data['counter_update'])) {
        $this->counter_update = (int)$data['counter_update'];
    } else {
        $this->counter_update = 0;
    }
    
    if (isset($data['counter_error'])) {
        $this->counter_error = (int)$data['counter_error'];
    } else {
        $this->counter_error = 0;
    }
    
    $id = $_GET['id'];
    if (!$this->getDetails($id)) die ('- id not found -');

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
    $this->coupon_id = $this->_recordData['ei_coupon'];
	
    $this->_export_coupon_codes();
}
        
    
    function _import_coupon_codes(){
        global $db,$language,$filter;
        
        if ($this->limit_upper==0) $this->limit_upper = $this->limit;
        if ($this->limit_lower==0) {
            $this->clearLog();
            $this->limit_lower=1;
        }

        $lower = $this->limit_lower;
       
        set_error_handler('error2exception', E_ERROR | E_USER_ERROR | E_CORE_ERROR | E_COMPILE_ERROR );
        
        if (($count > 1) && (!preg_match($this->regex, $mask))) {
            // 
        }

        for ($i = $lower; $i<$this->limit_upper && $i<$this->line_count;$i++) { 
          $line_data = $this->_parseLineData($i);  
          
          $new_data = Array();  
          $new_data['coupon_id'] = $line_data['coupon_id'];
          $new_data['coupon_token_code'] = $line_data['coupon_token_code'];          
          try {
              $db_erg = @$db->AutoExecute(TABLE_COUPONS_TOKEN, $new_data, 'INSERT');
              if($db_erg == true) {
                  ++$this->counter_new;
              } else {
                  $msg = '500|'.$i.' Error insert Coupon_Code '.$new_data['coupon_code'];
                  $this->addLog($msg);
                  ++$this->counter_error;                  
              }
          } catch (Exception $e) {
              $msg = '500|'.$i.' Error insert Coupon_Code '.$new_data['coupon_code'];
             $this->addLog($msg);
              ++$this->counter_error;
          }            
        } 
        restore_error_handler();

        $this->_redirecting();
    }
    
    function _export_coupon_codes(){
        global $db,$customers_status,$language;
        if ($this->limit_upper==0) $this->limit_upper = $this->limit;
	
		$where=''; $where_tokens = '';
        $sec_key= array();
		if (($this->coupon_id!='') && ($this->coupon_id>0)) 
		{
			 $where = ' WHERE c.coupon_id=? ';
			 $where_tokens = ' WHERE coupon_id=? ';
             array_push($sec_key,$this->coupon_id);
		}
		
        $query = "Select  ";
        $query .= " cd.coupon_name";
        $query .= ", cd.coupon_id";
        $query .= ", ct.coupon_token_code";
//        $query .= ", cr.order_id";
        $query .= " From";
        $query .= " ".TABLE_COUPONS." as c";
        $query .= " Right Join ".TABLE_COUPONS_TOKEN." as ct On c.coupon_id = ct.coupon_id";
        $query .= " Left Join ".TABLE_COUPONS_REDEEM." as cr On ct.coupons_token_id = cr.coupon_token_id";
        $query .= " Left Join ".TABLE_COUPONS_DESCRIPTION." as cd On cd.coupon_id = c.coupon_id AND cd.language_code = '".$language->code."'"; 
		$query .=  $where; 		
        $query .= " LIMIT ".$this->limit_lower.",".$this->limit;
        $query .= ";";
        
        $count = $db->Execute("SELECT COUNT(coupons_token_id) as count FROM ".TABLE_COUPONS_TOKEN.$where_tokens,$sec_key);

        $this->count = $count->fields['count'];

        $rs = $db->Execute($query,$sec_key);

        if ($rs->RecordCount()>0) {
            $fp = $this->_openFile($rs->fields);
            $header_added = false;
            while (!$rs->EOF) {
                $records = $rs->fields;
                $line = '"'.implode('"'.$this->delimiter.'"',$records).'"';
                fputs($fp, $line."\n");
                ++$this->counter_new;
                $rs->MoveNext();
            }$rs->Close();
            fclose($fp);
        }

        $this->_redirecting();
    }

    
    function readHeader() {
        global $filter;
        $this->filedata = array();
        $this->filedata = file($this->dir.$this->file);
        $this->line_count = count($this->filedata);
        $this->count = $this->line_count;

        $this->primary = $this->_recordData['ei_type_match'];
        
        $this->mapping = explode($this->delimiter, $this->filedata[0]);
        foreach ($this->mapping as $key => $val) {
            $val = trim(str_replace(array("\n", "\r", "\n\r"), '', $val),'"');
            $this->mapping[$key]=str_replace('"','',$val);
            
        }

        if (!is_array($this->mapping)) die ('- no columns - ');
        if (count($this->mapping)<2) die ('- no columns - ');
    }
     
    function _openFile($fields) {
        $file = _SRV_WEBROOT.'export/'.$this->_recordData['ei_filename'];
        if ($this->limit_lower==0) {
            if (file_exists($file)) unlink($file);
            $fp = fopen($file, "w");
            $header = '"'.implode('"'.$this->delimiter.'"',array_keys($fields)).'"'; 
            fputs($fp, $header."\n");       
        } else {
            $fp = fopen($file, "a");            
        }
        return $fp;
    }       

    function _redirecting() {
        global $xtLink;
        if ($this->limit_upper < $this->count) {
            // redirect to next step
            $limit_lower =$this->limit_upper;
            $limit_upper =$this->limit_upper + $this->limit;
            if($limit_upper > $this->count) $limit_upper = $this->count;
        
//            $params = 'api='.$this->api.'&id='.$this->ei_id.
            $params = 'api='.$this->api.'&id='.$this->id.
                        '&limit_lower='.$limit_lower.
                        '&limit_upper='.$limit_upper.
                        '&timer_start='.$this->timer_start.
                        '&counter_new='.$this->counter_new.'&counter_update='.$this->counter_update.'&counter_error='.$this->counter_error;

            echo $this->_displayHTML($xtLink->_link(array('default_page'=>'cronjob.php', 'params'=>$params,'conn'=>'SSL')),$limit_lower,$limit_upper,$this->count);
            
        } else {
                echo '<br />200 '.$this->api.' finished';
            if ($this->api == 'coupon_import') {
                echo '<br />New:'.$this->counter_new;
                echo '<br />Update:'.$this->counter_update;
                echo '<br />Error:'.$this->counter_error;
            } else if ($this->api == 'coupon_export') {
                echo '<br />New:'.$this->counter_new;
            }
        }

    }     
 
}

if (!function_exists('error2exception')) {
    function error2exception($errno, $errstr, $errfile, $errline)
    {
       throw new Exception($errstr);
    }
}

?>