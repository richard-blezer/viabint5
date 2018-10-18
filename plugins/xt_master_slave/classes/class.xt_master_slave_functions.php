<?php
if(DB_PREFIX!=''){
 $DB_PREFIX = DB_PREFIX . '_';
}else{
 define('DB_PREFIX','xt');
 $DB_PREFIX = DB_PREFIX . '_';
}
define('TABLE_PRODUCTS_TO_ATTRIBUTES', $DB_PREFIX . 'plg_products_to_attributes');
define('TABLE_PRODUCTS_ATTRIBUTES', $DB_PREFIX . 'plg_products_attributes');
define('TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION', $DB_PREFIX . 'plg_products_attributes_description');
define('TABLE_PRODUCTS_ATTRIBUTES_TEMPLATES', $DB_PREFIX.'plg_products_attributes_templates');
class xt_master_slave_functions
{
    protected static $masterPriceViewFlags = array('mp' => XT_MASTER_SLAVE_MASTERPRICE, 'ap' => XT_MASTER_SLAVE_AUTOPRICE, 'rp' => XT_MASTER_SLAVE_RANGEPRICE, 'np' => XT_MASTER_SLAVE_NOPRICE);

    public static function get_master_price_view_flags ()
    {
        $ret = array();
        $i = 0;
        foreach (self::$masterPriceViewFlags as $pvfKey => $pvfVal) {
            $ret[$i]['id'] = $pvfKey;
            $ret[$i]['name'] = $pvfVal;
            $i++;
        }
        return $ret;
    }
	
	protected static function get_Current_product_id ()
	{
		  global $current_product_id;
		  return  $current_product_id;
	}
	
    static function get_slave_from_master ($productsModel_)
    {
        global $db;

        $sql_where = " AND p.products_master_model=? ";

        $sdata_sql_products = new getProductSQL_query();
        $sdata_sql_products->setPosition('plugin_ms_sdata_sql_products_sdata');
        $sdata_sql_products->setSQL_WHERE($sql_where);

        $sql = $sdata_sql_products->getSQL_query();
        $record = $db->Execute($sql,array( $productsModel_));
        return $record;
    }

    /*
   * Auto Price
   * get the smallest slave price
   * if no slave exist return the master price
   * */
    protected static function ap ($productsModel_, $masterPriceContainer_)
    {
        global $price;
        $priceContainer = array();
        $slavesR = self::get_slave_from_master($productsModel_);
        if ($slavesR->RecordCount() > 0) {
            while (!$slavesR->EOF) {
                $p = new product($slavesR->fields['products_id']);
                $priceContainer[] = $p->data['products_price']['plain'];
                $slavesR->MoveNext();
            }
            $masterPriceContainer_['formated'] = XT_MASTER_SLAVE_FROM . ' ' . $price->_StyleFormat(min($priceContainer));
        }
        $slavesR->Close();
        return $masterPriceContainer_;
    }

    /*
   * Range Price
   * get the price range from all slaves
   * if only 1 slave or no slave exist return master price
   * */
    protected static function rp ($productsModel_, $masterPriceContainer_)
    {
        global $price;
        $priceContainer = array();
        $slavesR = self::get_slave_from_master($productsModel_);
        if ($slavesR->RecordCount() > 1) {
            while (!$slavesR->EOF) {
                $p = new product($slavesR->fields['products_id']);
                $priceContainer[] = $p->data['products_price']['plain'];
                $slavesR->MoveNext();
            }
            $masterPriceContainer_['formated'] = $price->_StyleFormat(min($priceContainer)) . ' - '. $price->_StyleFormat(max($priceContainer));
        }
        $slavesR->Close();
        return $masterPriceContainer_;
    }
	
	 /*
   * Slave Price
   * get the slave price 
   * if more than one slave price found return master price 
   * */
    protected static function sp ($productsModel_, $masterPriceContainer_,$productsID)
    {
        global $price;
        $priceContainer = array();
		
		$slavesR = self::get_slave_session($productsModel_,$productsID);
		
        if ($slavesR->RecordCount() == 1) 
		{
			$p = new product($slavesR->fields['products_id']);
			$priceContainer = $p->data['products_price']['plain'];
            $masterPriceContainer_['formated'] = $price->_StyleFormat($priceContainer);
			
        }
		
		
        $slavesR->Close();
        return $masterPriceContainer_;
    }

 /*
   * Slave Products_id
   * get the slave products_id 
   * if more than one slave found return master products_id 
   * */
    public static function slave_products_id ($productsModel_,$productsID)
    {
       
		$slavesR = self::get_slave_session($productsModel_,$productsID);
		$res='';
	
        if ($slavesR->RecordCount() == 1) 
		{
			$res = new product($slavesR->fields['products_id'], 'full', '', '', 'product_info');
			
        }
		
        $slavesR->Close();
        return $res;
    }

	public static function returnSlavesAttributes ($productsModel_)
    { 	global $db,$language;
       	
    	static $_slavesAttrCache = array();
    	
    	if (isset($_slavesAttrCache[$productsModel_])) {
    		return $_slavesAttrCache[$productsModel_];
    	}
		//$slavesR = self::get_slave_session($productsModel_,$productsID);
		$slavesR = self::get_slave_from_master($productsModel_);
		$res=array();
		
        if ($slavesR->RecordCount() >0) 
		{
			while (!$slavesR->EOF) {
				$sql = " SELECT pta.*,pa.*, pd.*,pt.* FROM   " . TABLE_PRODUCTS_TO_ATTRIBUTES . " pta INNER JOIN 
							".TABLE_PRODUCTS_ATTRIBUTES." pa ON pa.attributes_id = pta.attributes_id LEFT JOIN 
							".TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION." pd ON pd.attributes_id = pa.attributes_id LEFT JOIN
							".TABLE_PRODUCTS_ATTRIBUTES_TEMPLATES." pt ON pt.attributes_templates_id = pa.attributes_templates_id
	          				WHERE pta.products_id=? and pd.language_code=?";
						
	       		$record = $db->Execute($sql,array((int)$slavesR->fields['products_id'],$language->code));
				while (!$record->EOF){
                    array_push($res,$record->fields);
                    $record->MoveNext();
                }
				$slavesR->MoveNext();
            }
        }
        $slavesR->Close();
        $_slavesAttrCache[$productsModel_] = $res;
        return $res;
    }
	
	public static function returnSingleSlaveAttributes ($id)
    { 	global $db,$language;

        static $_singleAttributesCache = array();

        if (isset($_singleAttributesCache[$id])) {
            return $_singleAttributesCache[$id];
        }

		$res=array();
		$sql = " SELECT pta.* FROM   " . TABLE_PRODUCTS_TO_ATTRIBUTES . " pta 
      				WHERE pta.products_id=? ";
				
   		$record = $db->Execute($sql,array((int)$id));
        if ($record->RecordCount() > 0) {
            while (!$record->EOF) {
                array_push($res,array($record->fields['attributes_parent_id']=>$record->fields['attributes_id']));
                $record->MoveNext();
            }
            $record->Close();
        }

        $_singleAttributesCache[$id] = $res;

        return $res;
    }

    public static function returnSelectedSlaveAttributes ($id)
    { 	global $db,$language;

        static $_selectedAttributesCache = array();

        if (isset($_selectedAttributesCache[$id])) {
            return $_selectedAttributesCache[$id];
        }

        $res=array();
        $sql = " SELECT ptad.attributes_name AS option_name, ptad_parent.attributes_name AS group_name FROM   " . TABLE_PRODUCTS_TO_ATTRIBUTES . " pta
                LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION ." ptad ON (pta.attributes_id=ptad.attributes_id AND ptad.language_code='" . $language->code . "')
                LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION. " ptad_parent ON (pta.attributes_parent_id=ptad_parent.attributes_id AND ptad_parent.language_code='" . $language->code . "')
      			WHERE pta.products_id=? ";

        $record = $db->Execute($sql,array((int)$id));
        if ($record->RecordCount() > 0) {
            while (!$record->EOF) {
                array_push($res,$record->fields);
                $record->MoveNext();
            }
            $record->Close();
        }

        $_selectedAttributesCache[$id] = $res;

        return $res;
    }
    /*
   * No Price
   * clear the formated price value
   * return an empty string
   * */
    protected static function np ($priceContainer_)
    {
        $priceContainer_['formated'] = '';
    }

    public static function get_master_price ($masterPriceViewFlag_ = '', $productsModel_, $masterPriceContainer_,$productsID='')
    {
        switch ($masterPriceViewFlag_) {
            case 'mp':
                $ret = $masterPriceContainer_;
                break;
            case 'ap':
                $ret = self::ap($productsModel_, $masterPriceContainer_);
                break;
            case 'rp':
                $ret = self::rp($productsModel_, $masterPriceContainer_);
                break;
            case 'np':
                $ret = self::np($masterPriceContainer_);
                break;
			case 'sp':
                $ret = self::sp($productsModel_,$masterPriceContainer_,$productsID);
                break;
            default:
                $ret = $masterPriceContainer_;
                break;
        }
        return $ret;
    }
	
	/*
		Returns slaves based on selected master options (based on session)
	*/
	public static function get_slave_session($products_model,$productsID)
	{	global $db;
		$tt = self::get_slave_from_master($products_model);
		
		if ($tt->RecordCount()>0)
		{
			$add_more = '';
			while (!$tt->EOF) {
					$add_more  .= (($add_more=='')?'':', '). (int)$tt->fields['products_id'];
				$tt->MoveNext();
			}
			$tt->Close(); 
			$add_more = ' and p.products_id in ('.$add_more.')';
		}
		
		$add_to_where ='';
		$add_to_table='';
		$i=1;
		
		if (isset($_SESSION['select_ms'][$productsID]["id"]))
		{
		foreach ($_SESSION['select_ms'][$productsID]["id"] as $key => $val) {
				$add_to_where .=  (($add_to_where=='')?' ': " and ")." pa".$i.".attributes_id = ". (int)$val;
				$add_to_table .= " LEFT JOIN ". TABLE_PRODUCTS_TO_ATTRIBUTES." pa".$i." ON pa".$i.".products_id = p.products_id "; 
				$i++;
			}
			if ($add_to_where!='') $add_to_where =' and('.$add_to_where.')';	
		}
		
		
		$sql_where = "";
        $sql_where .= " WHERE p.products_status = '1'";
        if (_STORE_STOCK_CHECK_DISPLAY == 'false' && _SYSTEM_STOCK_HANDLING == 'true') {
            $sql_where .= " AND p.products_quantity > 0";
        }
		
        $sql = "
          SELECT DISTINCT p.products_id, p.products_price, p.products_image, p.products_master_model, p.products_model
          FROM   " . TABLE_PRODUCTS . " p  ".$add_to_table." ". $sql_where .$add_to_where.$add_more. ";";
		 
        $record = $db->Execute($sql);	
        return $record;
	}
	
	/* returns master data by products_model*/
    public static function getMasterData($productsModel)
    {
        global $db;
        $sql = "SELECT products_image,products_model,products_id FROM   " . TABLE_PRODUCTS . " where     products_model=?";
      
       $record = $db->Execute($sql,array($productsModel));
       return $record->fields;
    }
	
	/* returns image by ID*/
	public static function productImage($productsID)
	{
		global $db;
		$sql = "
          SELECT products_image FROM   " . TABLE_PRODUCTS . " where 	products_id=?";
		 
        $record = $db->Execute($sql,array((int)$productsID));
		if ($record->fields["products_image"]!='')
			return 'product:'.$record->fields["products_image"];
		else return 'product:'._STORE_PRODUCT_NO_PICTURE;
	}
	 /*
   * Slave Image
   * get the slave image 
   * if no slave image found return master image 
   * */
    public static function slave_image($productsModel_, $master_image,$productsID,$load_master='1',$current_item_image='')
    {
		if (strpos($master_image, 'product:')===false){
            $master_image = 'product:'.$master_image;
        }
		if (_PLUGIN_MASTER_SLAVE_REDIRECT_TO_SLAVE!='true') { // mode is either load slave in master and ajax
         
            if ($current_item_image==''){
                $slavesR = self::get_slave_session($productsModel_,$productsID);
                if ($slavesR->RecordCount() ==1) 
                {	
                	while (!$slavesR->EOF) {
                	   if ($slavesR->fields['products_image']!='') $master_image2 = 'product:'.$slavesR->fields['products_image'];
                	   else $master_image2 ='product:'._STORE_PRODUCT_NO_PICTURE;
                	   $slavesR->MoveNext();
                	}
                	$slavesR->Close();
                }else $master_image2 = $master_image; // set master iamge
            }else {
                $master_image2 = $current_item_image; // set currenct image
            }
            
        }else { // mode is redirect to slave
             if ($current_item_image=='') // still in master product 
                $master_image2 = $master_image; 
             else $master_image2 = $current_item_image; 
        }

		if (empty($load_master))
			return $master_image2;
		else return $master_image;
    }
	
	/**
	 * 
	 * unset filter in SESSION
	 */
	public static function unsetFilter() {
		
		if (($_SESSION['select_ms']['action'] != 1 and $_GET['action_ms'] != 1) or $_GET['reset_ms'] == 1) {
			unset($_SESSION['select_ms']/*[$this->pID]*/);
		}
	}

	
	/**
	 * 
	 * set filter in SESSION
	 * 
	 * @param array $data option and its value
	 */
	public static function setFilter($data,$pid) {
		
		foreach ($data as $key => $val) {
			if ($val != 0) {
				$_SESSION['select_ms'][$pid]['id'][$key] = $val;
			} else {
				unset($_SESSION['select_ms'][$pid]['id'][$key]);
				//$this->unset = true;
			}
		}
	}
	
	/*
     * 
     * returns array of not selected options for a master 
     * 
     * @param string $products_model  - master model products' model
     */
    public static function getNotSelectedOptions($products_model) {
        $attributes = array();
        $res  = xt_master_slave_functions::returnSlavesAttributes($products_model);
        foreach($res as $att){
            if (!in_array($att["attributes_parent_id"], $attributes)){
                array_push($attributes,$att["attributes_parent_id"]);
            }
        } 
        $selected = array();
        foreach($_SESSION['select_ms'] as $selected){
            foreach($selected['id'] as $k=>$val){
                if (!in_array($k, $selected)){
                    array_push($selected,$k);
                }
            }
        }
        $not_selected = array();
        foreach($attributes as $a){
            if (!in_array($a, $selected)){
                array_push($not_selected,$a);
            }
        }
        return  $not_selected;
    }
	
	 
}

?>