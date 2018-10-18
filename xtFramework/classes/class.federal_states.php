<?php
class federal_states extends default_table{

    
    public $_table = TABLE_FEDERAL_STATES;    
    public $_table_lang = TABLE_FEDERAL_STATES_DESCRIPTION;
    public $_master_key = 'states_id';
    public $_display_key = 'state_name';

    function  __construct() {
		
    }
    
    function _getParams(){
        global $language;
        $params = array();
        $header = array();        
        $header['country_iso_code_2'] = array(
			'type' => 'dropdown',
			'url'  => 'DropdownData.php?get=countries'
		);
        foreach ($language->_getLanguageList() as $key => $val) { 
			$header['state_name_'.$val['code']] = array('type' => 'text');
		}
		
		$params['GroupField']     = 'country_iso_code_2';
		$params['SortField']      = 'states_code';
		$params['SortDir']        = 'ASC';
		$params['GroupStartCollapsed']=false;
		$params['PageSize']=25; 
        $params['header']=$header;
        $params['master_key']=$this->_master_key;
        return $params;
    }
    
    function _set($data, $set_type = 'edit') {
		global $db,$language,$filter,$seo, $xtPlugin;

		$obj = new stdClass;

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_set_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;
				 
		if($data['status'] == 'true')
	  		{$data['status']=1;}		
	  				
		$oC = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		$oC->setExcludeFields(isset($exclude_fields) ? $exclude_fields : array());
		$objC = $oC->saveDataSet();

		if ($set_type=='new') {	// edit existing
		 	 $obj->new_id = $objC->new_id;
			 $data = array_merge($data, array($this->_master_key=>$objC->new_id));
		}

		$oCD = new adminDB_DataSave($this->_table_lang, $data, true, __CLASS__);
		$objCD = $oCD->saveDataSet();

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_set_bottom')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;
				
		if ($objC->success && $objCD->success) {
		    $obj->success = true;
		} else {
		    $obj->failed = true;
		}

		return $obj;
	}
}