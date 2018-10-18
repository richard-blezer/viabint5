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
class plugin_uninstalled extends plugin{

	var $master_id = 'code';

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		global $language;

		$params = array();

		$params['header']         = array();
		$params['master_key']     = $this->master_id;
		$params['default_sort']   = 'type';

		$params['GroupField']     = 'type';
		$params['SortField']      = 'type';
		$params['SortDir']        = 'ASC';

		if($this->url_data['pg']=='overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true){
			$params['include'] = array ('payment_icon','title', 'version', 'code', 'url','type');
		}else{
			$params['exclude'] = array ('plugin_code', 'configuration', 'file');
		}

		$rowActions[] = array('iconCls' => 'install_plugin', 'qtipIndex' => 'qtip1', 'tooltip' => 'Run');
		if ($this->url_data['edit_id'])
		  $js = "var edit_id = ".$this->url_data['edit_id'].";";
		else
          $js = "var edit_id = record.id;";
		$js.= "Ext.Msg.confirm('Pluginsystem','Plugin installieren ?',function(btn){doInstall(edit_id,btn);})";
		$rowActionsFunctions['install_plugin'] = $js;

		$extF = new ExtFunctions();

		$js = "function doInstall(edit_id,btn){
	  		var edit_id = edit_id;
	  		if (btn == 'yes') {
	  		".$extF->_RemoteWindow("TEXT_INSTALL_PLUGIN","TEXT_INSTALL_PLUGIN","plugin_install.php?plugin_id='+edit_id+'", '', array(), 800, 600).' new_window.show();'."
			}
		};";

		$params['rowActionsJavascript'] = $js;

        $params['display_searchPanel']  = true;
		$params['display_newBtn'] = false;
		$params['display_editBtn'] = false;
		$params['display_deleteBtn'] = false;

		$params['rowActions']             = $rowActions;
		$params['rowActionsFunctions']    = $rowActionsFunctions;

		return $params;
	}

	function _get($pID=0){

		if ($this->position != 'admin') return false;

		if ($pID === 'new') {
               $obj = $this->_set(array(), 'new');
               $pID = $obj->new_id;
		}

		if($pID){
			$data = $this->getFilePlugins($pID);
		}else{
			$data = $this->getFilePlugins();
		}
        
        $tmp_data = array();
        //build search filter
        if(isset($this->url_data['query']) && strlen(trim($this->url_data['query']))>1){
            $search_key = strtolower($this->url_data['query']);
            foreach($data as $k=>$v){
                if(stripos(strtolower($v['title']),$search_key)!==false || stripos(strtolower($v['code']),$search_key)!==false || stripos(strtolower($v['url']),$search_key)!==false  || stripos(strtolower($v['version']),$search_key)!==false){
                    $tmp_data[] = $v;
                }
            }
            $data = $tmp_data;
        }

		$obj = new stdClass;
		$obj->totalCount = count($data);
		
		if($obj->totalCount==0){
		
			$data[] =  array('icon'=>'', 'title'=>'', 'version'=>'', 'code'=>'', 'url'=>'', 'type'=>'');
		
		}else{
            foreach($data as $k=>$v){
                if(empty($v['title']) && empty($v['version']) && empty($v['code'])){
                    $v['title'] = 'ERROR: installer.xml';
                    $v['code'] = $v['file'];
                    $data[$k] = $v;
                }
            }
        }

		$obj->data = $data;
		return $obj;

	}

	function _set($data){

		$obj = new stdClass;
		$param ='/[^a-zA-Z0-9_-]/';
		$data['code']=preg_replace($param,'',$data['code']);
		$file = _SRV_WEBROOT.'plugins/'.$data['code'].'/installer/'.$data['code'].'.xml';
		if (is_file($file)) {
			$this->InstallPlugin($file);
			$obj->success = true;
		} else {
			$obj->failed = true;
		}

		return $obj;
	}
}