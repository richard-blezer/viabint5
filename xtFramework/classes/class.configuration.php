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

class configuration{

	function configuration(){
	}

	function setPosition ($position) {
		$this->position = $position;
	}

	function setGroupID () {
        if($this->url_data['edit_id'])
		  $cgID = $this->url_data['edit_id'];
        if($this->url_data['group_id'])
		  $cgID = $this->url_data['group_id'];
		$this->groupID = $cgID;
	}

	function getGroupID () {
	    return $this->groupID;
	}

	function setStoreID($value) {
	    $this->storeID = $value;
	}
	function isStoreID() {
	    if ($this->storeID) {
	        return true;
	    }
	    if ($this->url_data['store_id']) {
	        $this->setStoreID($this->url_data['store_id']);
	        return true;
	    }
	    return false;
	}

	function getStoreID (){
	    return $this->storeID;
	}

	function getConfigurationTable() {
		$table = TABLE_CONFIGURATION;
		if($this->url_data['store_id']) {
			$store_id = $this->url_data['store_id'];
			$this->setStoreID($store_id);
			$table = TABLE_CONFIGURATION_MULTI.$store_id;
		}elseif($this->storeID){
			$table = TABLE_CONFIGURATION_MULTI.$this->storeID;
		}
		return $table;
	}
	
	function ExcludeInactiveLang()
	{
		global  $db, $language;
		$add_to_query= '';
		$query = "SELECT * FROM " . TABLE_LANGUAGES . " where language_status=0";

		$record = $db->Execute($query);
		if($record->RecordCount() > 0)
		{
			while(!$record->EOF)
			{
				$add_to_query.=" and config_key NOT LIKE '%_".$record->fields['code']."'  ";
				$record->MoveNext();
			}
			$record->Close();
		}
		
		return $add_to_query;
	}
	
    // get edit mode data (configuration)
	function getConfigResetData() {
		global $xtPlugin, $db, $language;

		$cgID = $this->getGroupID ();
		$where = ' group_id = '. $db->Quote((int)$cgID) . ' ';

		// serch query
		if($this->url_data['query'])
			$where .= ' and ' . $db->Quote($this->url_data['query']) . ' ';
		if ($cgID==12) $where.=$this->ExcludeInactiveLang();

    	$query = "SELECT * FROM " . $this->getConfigurationTable() . "  where " . $where." ORDER BY sort_order";
    	$record = $db->Execute($query);

		while(!$record->EOF){
			if ($record->fields['config_value'] == 'true') $record->fields['config_value'] = 1;
			if ($record->fields['config_value'] == 'false') $record->fields['config_value'] = 0;

    		$data[0][$record->fields['config_key']] = $record->fields['config_value'];
    		$record->MoveNext();
    	}$record->Close();
			
		$admin_options_id=25;
		$query = "SELECT group_id FROM " . TABLE_CONFIGURATION_GROUP . "  where group_title = 'TEXT_CONF_ADMIN_OPTIONS_NAV' ";
    	$r = $db->Execute($query);
		if($r->RecordCount() > 0) {
			$admin_options_id = $r->fields['group_id'];
		}
		if ($_GET['edit_id']==$admin_options_id)
			$data[0]['config_info']=_filterText(html_entity_decode(TEXT_RELOAD_INFO), $type='full');

		return $data;
	}

	function getConfigHeaderData() {
         global $xtPlugin, $db, $language;
        $cgID = $this->getGroupID ();
        // edit mode (configuration)
		$where = '';
		if ($cgID==12) $where=$this->ExcludeInactiveLang();
		$query = "SELECT * FROM " . $this->getConfigurationTable() . " where group_id = ? ".$where." ORDER BY sort_order, id";

		$record = $db->Execute($query, array($cgID));
		while(!$record->EOF){
		    $type = '';
		    //$required = true;
		    //$type = $record->fields['type'];
		    if ($record->fields['config_value'] == 'true' || $record->fields['config_value'] == 'false') {
		      $type = 'truefalse';
		    }

		    if($record->fields['type'])
			   $type = $record->fields['type'];

			if ($record->fields['type'] == 'dropdown') {
				if (strstr($record->fields['url'],'status:')) {
					$record->fields['url'] = str_replace('status:','',$record->fields['url']);
					$url = 'DropdownData.php?systemstatus='.$record->fields['url'].'&skip_empty=true';
				} else {
					$url = 'DropdownData.php?get='.$record->fields['url'].'&skip_empty=true';
				}
			} else {
				$url = $record->fields['url'];
			}
			
			if ($record->fields['config_key']=='_STORE_LOGO') 
			{
				$type='logo';
				$_SESSION['logo_store_id'] = $this->getStoreID();
			}
			
            // set header data
			$header[$record->fields['config_key']] = $tmp_data = array(
				'name' => $record->fields['config_key'],
				'text' => __define('TEXT'.$record->fields['config_key']),
				'masterkey' => false,
				'lang' => false,
				'value' => _filterText($record->fields['config_value']),
				'hidden' => false,
				'min' => null,
				'max' => null,
				'readonly' => false,
				'required' => null,
				'type' => $type,
				'url' => $url,
				'renderer' => null
			);
			$record->MoveNext();
		} $record->Close();
		$header['config_info']=array('type'=>'admininfo');
		return $header;
	}

	function getConfigGroupData() {
        global $xtPlugin, $db, $language;
        $table = ' INNER JOIN '.$this->getConfigurationTable() . ' c  ON cg.group_id = c.group_id ';

		$where = '';

		if($this->url_data['query']) {
		  $where = ' and (cg.group_title like \'%'.$this->url_data['query'].'%\') or (config_key like \'%'.$this->url_data['query'].'%\') or (config_value like \'%'.$this->url_data['query'].'%\') ';
		}
         $query = "SELECT cg.* FROM " . TABLE_CONFIGURATION_GROUP .  " cg ".$table." where cg.visible = 1 " . $where." ";

         echo $query;

    		$record = $db->Execute($query);
    		while(!$record->EOF){
    			$data[] = $record->fields;
    			$record->MoveNext();
    		}$record->Close();
        return $data;
	}

	function getConfigSysNavData($parent=0) {
		global $db;

         $query = "SELECT * FROM " . TABLE_CONFIGURATION_GROUP .  " where visible = 2 ";
    		$record = $db->Execute($query);
    		while(!$record->EOF){
    			$data[] = $record->fields;
    			$record->MoveNext();
    		}$record->Close();

		$erg = array();
		if (is_array($data)) {
			foreach ($data as $key => $val)  {

				$type = '';
				$leaf = '1';
				$type='I';
				$icon='images/icons/'.$val['group_icon'];
				$url_d = 'adminHandler.php?load_section=configuration&edit_id='.$val['group_id'];
				$arrTMP = Array('text' => __define($val['group_title'])
				,'url_i' => ''
				,'url_d' => $url_d
				,'tabtext' => __define($val['group_title'])
				,'id' => 'group_'.$val['group_id']
				,'type'=>$type
				,'leaf'=>$leaf
				,'icon'=>$icon
				);

				$erg[]=$arrTMP;
			}

		}

		return $erg;
	}	
	
	function getConfigNavData($parent=0) {
		global $db;

		if (strstr($parent,'store_')) {
			$tmp = explode('_',$parent);
			$store_id = (int)$tmp[1];

		}

         $query = "SELECT * FROM " . TABLE_CONFIGURATION_GROUP .  " where visible = 1 ";
    		$record = $db->Execute($query);
    		while(!$record->EOF){
    			$data[] = $record->fields;
    			$record->MoveNext();
    		}$record->Close();

		$erg = array();
		if (is_array($data)) {
			foreach ($data as $key => $val)  {

				$type = '';
				$leaf = '1';
				$type='I';
				$icon='images/icons/'.$val['group_icon'];
				$url_d = 'adminHandler.php?load_section=configuration&edit_id='.$val['group_id'].'&store_id='.$store_id;
				$arrTMP = Array('text' => __define($val['group_title'])
				,'url_i' => ''
				,'url_d' => $url_d
				,'tabtext' => __define($val['group_title'])
				,'id' => 'group_'.$val['group_id'].'_'.$store_id
				,'type'=>$type
				,'leaf'=>$leaf
				,'icon'=>$icon
				);

				$erg[]=$arrTMP;
			}

		}

		return $erg;
	}

	function _getParams() {
        global $xtPlugin, $db, $language;
		$params = array();

		$this->setGroupID (); // check url params and set groupID

        $cgID = $this->getGroupID ();

		if($cgID) {
			$params['header']         = $this->getConfigHeaderData();
			$params['master_key']     = 'id';
			$params['default_sort']   = 'sort_order';
			//$params['languageTab']    = false;
			$params['edit_masterkey'] = true;
		} else {
            if($this->url_data['store_id'])
				$cgID = $this->url_data['store_id'];

			$params['master_key']     = 'group_id';
			$params['default_sort']   = 'sort_order';
			$params['languageTab']    = false;
			$params['edit_masterkey'] = true;
			$params['exclude'] = array('sort_order', 'visible', 'multistore');
		}
			return $params;

	}

	function _get($cgID = 0) {
		global $xtPlugin, $db, $language;
		if ($this->position != 'admin') return false;
        $this->setGroupID ();

        $cgID = $this->getGroupID ();

		if ($cgID === 'new') {
			echo 'error no new configs!';
			die;
		}

		if($cgID) {
		    // edit mode (configuration) reset data
    		$data = $this->getConfigResetData();
		} else {
		    // show configuration groups in grid
            $data = $this->getConfigGroupData();
		}

		$obj = new stdClass;
        $obj->data = $data;
  		if (!$cgID) {
            $obj->totalCount = count($data);
        } else {
            $obj->totalCount = 1;
        }

		return $obj;
	}

	function _set($data, $set_type = 'edit'){
		global $db,$language,$filter,$xtPlugin;

		$obj = new stdClass;

		$tmp_data = $data;
		unset($data);

		foreach ($tmp_data as $key => $val) {

			if($key!=''){
				$content = array();
				$content['config_value']=$val;
				
				if($key=='_SYSTEM_USE_DB_HOOKS' && $content['config_value']=='true'){
					$ptable_data = new adminDB_DataRead(TABLE_PLUGIN_PRODUCTS, NULL, NULL, 'plugin_id');
					$pdata = $ptable_data->getData();					
					foreach ($pdata as $pkey => $pval) {
						$xtPlugin->_MultiDeleteHookFiles($pval['plugin_id']);
					}
				}
				
				if($this->url_data['store_id']){
					
					// default stuff
					$insert=true;
					if ($key=='_STORE_LANGUAGE' && $val=='') $insert=false;
					
					if ($insert) {
						$content[config_value] = _stripslashes ($content[config_value]);
						if ($key!='_STORE_LOGO')
						$db->AutoExecute(TABLE_CONFIGURATION_MULTI.$this->url_data['store_id'], $content, 'UPDATE', "config_key='".$key."'");
					}
				}else{
					$db->AutoExecute(TABLE_CONFIGURATION, $content, 'UPDATE', "config_key='".$key."'");
				}
			}
		}

		$obj->success = true;

		return $obj;
	}
    
    /**
    * Wert des $config_key lesen
    * 
    * @param mixed $config_key 
    * @param mixed $group_id  OPTIONAL, falls ein key in mehreren Gruppen verwendet wird
    */
    function getValue($config_key, $group_id = '') {
      global $db;
      $erg = '';
      $sql = "select config_value from ".TABLE_CONFIGURATION." where config_key = '".$config_key."'";
      if ($group_id != '') {
         $sql .= ' and group_id = '.$group_id;   
      }
      $sql .= ';';
      $res = $db->execute($sql);
      
      $arr = $res->FetchRow();
      $res->close();
      $erg = $arr['config_value'];
      return $erg;
    }  
    
    /**
    * Wert in die Tabelle configuration schreiben
    * 
    * @param mixed $config_key
    * @param mixed $config_value
    * @param mixed $group_id
    */
    function setValue($config_key, $config_value, $group_id = '') {
      global $db;
      $erg = false;
      $sql = "update ".TABLE_CONFIGURATION." set config_value = '".$config_value."' where config_key = '".$config_key."'";
      if ($group_id != '') {
         $sql .= " and group_id = '$group_id'";   
      }
      $sql .= ';';
      $res = $db->execute($sql);
      if ($db->ErrorNo == 0) {
        $erg = true;   
      } else {
        $erg = false;   
      }
      return $erg;
    }    
}