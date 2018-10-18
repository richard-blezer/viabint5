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


class xt_im_export{

	protected $_table = TABLE_EXPORTIMPORT;
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

		$header['ei_type_spec'] = array('type' => 'dropdown',
									'url'  => 'DropdownData.php?get=imexport_typesspec&plugin_code=xt_im_export');
		
		$header['ei_type_match'] = array('type' => 'dropdown',
									'url'  => 'DropdownData.php?get=imexport_matching&plugin_code=xt_im_export');
		
		$header['ei_type_match_2'] = array('type' => 'dropdown',
									'url'  => 'DropdownData.php?get=imexport_matching_2&plugin_code=xt_im_export');		
		
		$header['ei_language'] = array('type' => 'status');
		
		$header['ei_price_type'] = array('type' => 'dropdown','url'  => 'DropdownData.php?get=conf_truefalse');
		$header['ei_store_id'] = array('type' => 'dropdown','url'  => 'DropdownData.php?get=stores');
		/* freitext lt. wunsch von mh
		$header['ei_delimiter'] = array('type' => 'dropdown',
									'url'  => 'DropdownData.php?get=imexport_delimiter');		
		$header['ei_enclosure'] = array('type' => 'dropdown',
									'url'  => 'DropdownData.php?get=imexport_enclosure');		
		*/
		$rowActions[] = array('iconCls' => 'start', 'qtipIndex' => 'qtip1', 'tooltip' => 'Run');

        if(!$this->url_data['edit_id'] && $this->url_data['new'] != true){
			$js = "var edit_id = record.data.ei_id;";

		$js .= "Ext.Msg.show({
			   title:'".TEXT_START."',
			   msg: '".TEXT_START_ASK."',
			   buttons: Ext.Msg.YESNO,
			   animEl: 'elId',
				 // fn: function(btn){runImport(edit_id,btn);},
				 fn: function(btn) {if (btn == 'yes') {addTab('row_actions.php?type=api_csv_export&seckey="._SYSTEM_SECURITY_KEY."&id='+edit_id,'... import / export ...');}},
			   icon: Ext.MessageBox.QUESTION
			});";

		$rowActionsFunctions['start'] = $js;
        }

        if(!$this->url_data['edit_id'] && $this->url_data['new'] != true){
            $rowActions[] = array('iconCls' => 'cron_log', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_XT_IMPORT_EXPORT_LOG);
            $js = "var edit_id = record.data.ei_id;";
            $extF = new ExtFunctions();
            $js.= $extF->_RemoteWindow("TEXT_XT_IMPORT_EXPORT_LOG","TEXT_XT_IMPORT_EXPORT_LOG","adminHandler.php?plugin=xt_im_export&load_section=xt_im_export_log&pg=overview&ei_id='+edit_id+'", '', array(), 800, 600).' new_window.show();';

            $rowActionsFunctions['cron_log'] = $js;
        }

		$js = "function runImport(edit_id,btn){
	  		var edit_id = edit_id;
	  		if (btn == 'yes') {
	  			addTab('row_actions.php?type=api_csv_export&seckey="._SYSTEM_SECURITY_KEY."&id='+edit_id,'... import / export ...');  
			}

		};";




		if (! $_GET['new']) {
			$params['rowActionsJavascript'] = $js;

			$params['rowActions']             = $rowActions;
			$params['rowActionsFunctions']    = $rowActionsFunctions;
		}


		if (!$this->url_data['edit_id'] && $this->url_data['new'] != true) {
			$params['include'] = array ('id','ei_id','ei_type','ei_type_spec','ei_title', 'ei_delimiter', 'ei_limit','ei_filename');
		}

		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;
		$params['SortField']      = $this->_master_key;
		$params['SortDir']        = "DESC";

		/* grouping params */
		$params['GroupField']     = "ei_type";
		/* grouping params end */

		return $params;
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language;

		if ($this->position != 'admin') return false;

		if ($ID === 'new') {
			$obj = $this->_set(array('ei_delimiter' => ';', 'ei_enclosure' => '"'), 'new');
			$ID = $obj->new_id;
		}

		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key);

		if ($this->url_data['get_data'])
		$data = $table_data->getData();
		elseif($ID) {
			$data = $table_data->getData($ID);
			if ($data[0][ei_enclosure] == '&quot;') {
				$data[0][ei_enclosure] = '\"';
			}
		}
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

		$db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = '".$id."'");

	}


}
?>