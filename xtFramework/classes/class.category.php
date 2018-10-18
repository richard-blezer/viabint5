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

class category {
	public $current_category_id;
	public $current_category_data;
	public $level;
	public $admin = false;

	public $_master_key = 'categories_id';
	public $_image_key = 'categories_image';
	public $_table = TABLE_CATEGORIES;
	public $_display_key = 'categories_name';
	protected $_table_lang = TABLE_CATEGORIES_DESCRIPTION;
	protected $_table_seo = TABLE_SEO_URL;
	public $store_field_exists = false;
	public $_store_field = 'categories_store_id';


	function category($catID = 0) {
		$this->getPermission();
		$this->current_category_id = $catID;
		$this->current_category_data = $this->getCategoryData($this->current_category_id);
		$this->data = $this->current_category_data;
		$this->getLevel($this->current_category_id);
	}

	function _setAdmin() {
		$this->admin = true;
	}

	function getPermission(){
		global $store_handler, $customers_status, $xtPlugin;
	
		$this->perm_array = array(
			'shop_perm' => array(
				'type'=>'shop',
				'table'=>TABLE_CATEGORIES_PERMISSION,
				'key'=>$this->_master_key,
				'simple_permissions' => 'true',
				'simple_permissions_key' => 'permission_id',
				'pref'=>'c'
			),
			'group_perm' => array('type'=>'group_permission',
				'table'=>TABLE_CATEGORIES_PERMISSION,
				'key'=>$this->_master_key,
				'simple_permissions' => 'true',
				'simple_permissions_key' => 'permission_id',
				'pref'=>'c'
			)
		);

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':getPermission')) ? eval($plugin_code) : false;

		$this->permission = new item_permission($this->perm_array);

		return $this->perm_array;
	}

	function getCategoryData ($catID = 0) {
		global $xtPlugin, $db, $language, $current_manufacturer_id;

		($plugin_code = $xtPlugin->PluginCode('category:getCategoryData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$tpl = new Template();
		if ($catID == 0 && empty($current_manufacturer_id)) {
			$data['listing_template'] = _STORE_TEMPLATE_PRODUCT_LISTING_STARTPAGE;
			return $data;
		} elseif ($catID == 0 && !empty($current_manufacturer_id)) {
			$data['listing_template'] = _STORE_TEMPLATE_PRODUCT_LISTING_MANUFACTURERS;
			return $data;
		}

		if (USER_POSITION =='store')
		$sql_tablecols = "cd.*, c.*, su.*";
		else
		$sql_tablecols = "cd.*, c.*";
		
		($plugin_code = $xtPlugin->PluginCode('category:getCategoryData_sql_tablecols')) ? eval($plugin_code) : false;

		$this->create_getCategorySQL_query();
		$this->sql_categories->setPosition('getCategoryData');
		$this->sql_categories->setFilter('Language');
		
		if (USER_POSITION =='store')
		$this->sql_categories->setFilter('Seo');
		
		$this->sql_categories->setSQL_COLS(", " . $sql_tablecols);
		$this->sql_categories->setSQL_WHERE("and c.categories_id = '" . $catID . "'");
		($plugin_code = $xtPlugin->PluginCode('class.category.php:getCategoryData_SQL')) ? eval($plugin_code) : false;

		$query = "".$this->sql_categories->getSQL_query()."";

		if ($this->admin) $query = str_replace(" c.categories_status = '1' and ","",$query);

		$record = $db->Execute($query);
		if($record->RecordCount() > 0){

			$data = $record->fields;
			
			if($data['categories_image'])
			$data['categories_image']= __CLASS__.':'.$data['categories_image'];
			
			global $mediaImages;
			$media_images = $mediaImages->get_media_images($data['categories_id'], __CLASS__);
			$data['more_images'] = $media_images['images'];					

			if ($catID == $this->current_category_id) {
				$data['listing_template'] = $tpl->getDefaultTemplate($record->fields['listing_template'], 'product_listing/');
				$data['categories_template'] = $tpl->getDefaultTemplate($record->fields['categories_template'], 'categorie_listing/');
			}
			($plugin_code = $xtPlugin->PluginCode('category:getCategoryData_bottom')) ? eval($plugin_code) : false;
			return $data;
		}else{
			return false;
		}
	}
    
    function getCategoryListingAdmin ($catID = '', $store='', $unassigned=false) { 
        global $xtPlugin, $db, $language;
        $catID = (int)$catID;
        $add_table='';
		$add_where='';
		$add_store_f='';
		$grouping='';
		if ($unassigned)
		{
			$st = new multistore();
			$stores = $st->getStores();
			$grouping=' GROUP BY cd.categories_id '; 
			foreach($stores as $stm)
			{
				
				$add_table .= " left JOIN " . TABLE_CATEGORIES_PERMISSION . " pm".$stm['id']."
					ON (pm".$stm['id'].".pid = c.categories_id and pm".$stm['id'].".pgroup = 'shop_".$stm['id']."') ";
				
				if(_SYSTEM_GROUP_PERMISSIONS=='blacklist'){
					$add_where .= " and pm".$stm['id'].".permission = 1";
				}elseif(_SYSTEM_GROUP_PERMISSIONS=='whitelist'){
					$add_where .= " and pm".$stm['id'].".permission IS NULL";
				}
			}
		}
		if ($store!='')
		{
			$add_table = " left JOIN " . TABLE_CATEGORIES_PERMISSION . " pm 
					ON (pm.pid = c.categories_id and pm.pgroup = 'shop_".$store."') ";
			$add_store_f = " and cd.categories_store_id='".$store."'";
				
			if(_SYSTEM_GROUP_PERMISSIONS=='blacklist'){
				$add_where = " and pm.permission IS NULL";
			}elseif(_SYSTEM_GROUP_PERMISSIONS=='whitelist'){
				$add_where = " and pm.permission = 1";
			}
		}
		
        $query = "SELECT c.categories_id,c.categories_status, cd.categories_name, c.category_custom_link FROM  ".TABLE_CATEGORIES." c 
        		LEFT JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd ON cd.categories_id = c.categories_id AND cd.language_code='".$language->code."' ".$add_store_f.$add_table."
        		WHERE  c.parent_id =? ".$add_where.$add_store_f.$grouping."  ORDER BY  c.categories_left,c.categories_right, cd.categories_name";
		
       $record = $db->Execute($query, array($catID));
        if($record->RecordCount() > 0){
            while(!$record->EOF){
                
                $cat_data = array();
                $cat_data['categories_status']=$record->fields['categories_status'];
                $cat_data['categories_id']=$record->fields['categories_id'];
                $cat_data['category_custom_link']=$record->fields['category_custom_link'];
				
                if (!empty($record->fields['categories_name'])) {
                  $cat_data['categories_name']=$record->fields['categories_name']; 
                } else {
                  $cat_data['categories_name'] = ' - EMPTY - ';  
                }
                
                $data[] = $cat_data;

                $record->MoveNext();
            }
            $record->Close();
			
            for($h=0; $data[$h]; $h++) {
				$data[$h]["categories_name"] = $data[$h]["categories_name"]." (id:".$data[$h]["categories_id"].")";
			}
      
            return $data;
        }else{
            return false;
        }
    }
		
	function buildCustomLinkURL($data)
	{	global $db, $language,$xtLink;	
		$url='';
		
		if ($data['category_custom_link']==1)
		{
			switch ($data['category_custom_link_type'])
			{
				
				case 'custom':
				case 'plugin':
					$query = "SELECT * FROM ".TABLE_CATEGORIES_CUSTOM_LINK_URL." WHERE categories_id = ?
					and language_code = ?";
					$record = $db->Execute($query, array($data['categories_id'], $language->code));
					if($record->RecordCount() > 0){
						$url = $record->fields['link_url'];
					}
				break;
				
				case 'product':
					$p_info = new product($data['category_custom_link_id']);
					$link_array = array(
						'page'=> 'products',
						'type'=> 'product',
						'name'=>$p_info->data['products_name'],
						'text'=>$p_info->data['products_name'],
						'id'=>$p_info->data['products_id'],
						'seo_url'=>$p_info->data['url_text'],
					);
					
					$url = $xtLink->_link($link_array);
				break;
				
				case 'category':
					$p_info = new category($data['category_custom_link_id']);
					$link_array = array(
						'page'=> 'categorie',
						'type'=> 'category',
						'name'=>$p_info->data['categories_name'],
						'text'=>$p_info->data['categories_name'],
						'id'=>$p_info->data['categories_id'],
						'seo_url'=>$p_info->data['url_text'],
					);
					
					$url = $xtLink->_link($link_array);
				break;
				
				case 'content':
					$p_info = new content($data['category_custom_link_id']);
					$conn = 'NOSSL';
					
                	if ($p_info->data['link_ssl']=='1') $conn='SSL';
					$link_array = array(
						'page'=>'content',
						'type'=>'content',
						'name'=>$p_info->data['content_title'],
						'id'=>$p_info->data['content_id'],
						'seo_url' => $p_info->data['url_text'],
						'conn'=>$conn
					);

					$url = $xtLink->_link($link_array);
				break;
			}
		}

	return $url;
	}	
		
	function buildData ($data) {
		global $xtPlugin, $xtLink, $mediaImages;

		($plugin_code = $xtPlugin->PluginCode('category:buildData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if (is_array($data) && is_data($data['categories_id'])){
			$cat_data = $this->getCategoryData($data['categories_id']);
            
			if (is_array($cat_data) && count($cat_data) != 0)
				$data = array_merge($data, $cat_data);

			$media_images = $mediaImages->get_media_images($data['categories_id'], __CLASS__);
			$data['more_images'] = $media_images['images'];

			($plugin_code = $xtPlugin->PluginCode('category:buildDataArray_beforeData')) ? eval($plugin_code) : false;
		
			if ($data['category_custom_link']==1) {
				$url = $this->buildCustomLinkURL($data);
				$data['categories_link'] = $url;
			} else {
				$link_array = array(
					'page'=> 'categorie',
					'type'=> 'category',
					'name'=>$data['categories_name'],
					'text'=>$data['categories_name'],
					'id'=>$data['categories_id'],
					'seo_url'=>$data['url_text'],
				);
	
				$data['categories_link'] = $xtLink->_link($link_array);
			}
			($plugin_code = $xtPlugin->PluginCode('category:buildData_bottom')) ? eval($plugin_code) : false;
			return $data;
        }
	}

	function getCategoriesDropDown ($manID) {
		global $_GET, $PHP_SELF, $xtPlugin, $language,$db;

		($plugin_code = $xtPlugin->PluginCode('category:getCategoriesDropDown_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$sql_products = new getProductSQL_query();
		$sql_products->setSQL_COLS(', c.categories_id as id, cd.categories_name as text, parent_id', true);
		$sql_products->setSQL_TABLE("INNER JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id = p.products_id LEFT JOIN ".TABLE_CATEGORIES." c ON p2c.categories_id = c.categories_id LEFT JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd ON c.categories_id = cd.categories_id");
		$sql_products->setSQL_WHERE("and cd.language_code = '" . $language->code . "'");
		$sql_products->setFilter('Manufacturer', $manID);

		($plugin_code = $xtPlugin->PluginCode('category:getCategoriesDropDown_filter')) ? eval($plugin_code) : false;

		if (is_data($_GET['sorting'])) {
			$sql_products->setFilter('Sorting', $_GET['sorting']);
		} else {
			$sql_products->setSQL_SORT("p.products_ordered");
		}

		$sql = $sql_products->getSQL_query();

		$record = $db->Execute($sql);


		if($record->RecordCount() > 0){
			while(!$record->EOF){
				($plugin_code = $xtPlugin->PluginCode('category:getCategoriesDropDown_data')) ? eval($plugin_code) : false;
				$options[] = $record->fields;
				$record->MoveNext();
			}$record->Close();
		}else{
			return false;
		}

		($plugin_code = $xtPlugin->PluginCode('category:getCategoriesDropDown_bottom')) ? eval($plugin_code) : false;
		return $options;
	}

	function create_getCategorySQL_query() {
		global $xtPlugin;
		$this->sql_categories = new getCategorySQL_query();
		($plugin_code = $xtPlugin->PluginCode('category:create_getCategorySQL_query')) ? eval($plugin_code) : false;
	}

	function getAllCategoriesList ($data_array = '', $parent_id = '0', $spacer = '') {
		global $xtPlugin, $db;
		
		($plugin_code = $xtPlugin->PluginCode('category:getAllCategoriesList_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$nested_set = $this->getNestedSet();
		
		($plugin_code = $xtPlugin->PluginCode('category:getAllCategoriesList_data')) ? eval($plugin_code) : false;
	
		$tree = $nested_set->getTree();
		$data_array = $nested_set->buildHierarchy($tree, 0, false);
		
		foreach ($data_array as &$category) {
			$category['categories_name'] = str_repeat($spacer . '-', (int)$category['level'] - 1) . $category['categories_name'];
			$category['text'] = $category['categories_name'];
			$category['id'] = $category['categories_id'];
		}
		
		($plugin_code = $xtPlugin->PluginCode('category:getAllCategoriesList_bottom')) ? eval($plugin_code) : false;
		return $data_array;
	}

	/**
	 * Check if category has sub categories
	 *
	 * @param int $id
	 * @return boolean
	 */
	function category_has_subcategories($id,$store='') {
		global $db, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('category:category_has_subcategories_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;
		
		if ($store!=''){
            $add_table = " left JOIN " . TABLE_CATEGORIES_PERMISSION . " pm 
                    ON (pm.pid = c.categories_id and pm.pgroup = 'shop_".$store."') ";
                
            if(_SYSTEM_GROUP_PERMISSIONS=='blacklist'){
                $add_where = " and pm.permission IS NULL";
            }elseif(_SYSTEM_GROUP_PERMISSIONS=='whitelist'){
                $add_where = " and pm.permission = 1";
            }
        }
		
		$record = $db->Execute(
			"select count(*) as count from " . TABLE_CATEGORIES . " c ".$add_table." where c.parent_id = ? ".$add_where,
			array((int) $id)
		);
		if($record->fields['count']>0){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns a category heirarchy
	 * @param number $catID
	 * @param string $nested
	 * @param number $level
	 * @param string $filter
	 * @return array
	 */
	function getCategoryBox ($catID = 0, $nested = false, $level = 0,$filter='') {
		global $xtPlugin;
        $this->deepest_level_display = 0;
        $this->categories_filter = $filter;
        // Instantiate new nested set object
        $nested_set = $this->getNestedSet();
        
        ($plugin_code = $xtPlugin->PluginCode('class.category.php:getCategoryBox_tree')) ? eval($plugin_code) : false;

       /*
         // If $nested = false retrieve only main and expanded categories
        if (!$nested) {
            $nested_set->setNestedLevel($this->level);
        }
        */
        
        $tree = $nested_set->getTree();
        if ($catID > 0) {
       		$this->level[] = $catID;
        }
		
        if (!$nested) {
            $tree = $nested_set->getExpandedNodesOnly($tree, $this->level);
        }

        if (!empty($tree)) {
        	$return = $nested_set->buildHierarchy($tree, $catID, $nested, $this->level);
        }
        
        ($plugin_code = $xtPlugin->PluginCode('class.category.php:getCategoryBox_bottom')) ? eval($plugin_code) : false;
        if (isset($plugin_return_value)) 
        	return $plugin_return_value;
        
        return $return;
	}

	/**
	 * Get category listing
	 * @param string $catID
	 * @return unknown|boolean|Ambigous <Ambigous, multitype:, multitype:unknown Ambigous >
	 */
	function getCategoryListing ($catID = '',$direct_children_only=0) {
		global $xtPlugin;
	
		if (strlen($catID) == 0)
			$catID = $this->current_category_id;
		$catID = (int)$catID;
	
		($plugin_code = $xtPlugin->PluginCode('category:getCategoryListing_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
			return $plugin_return_value;
	
		$nested_set = $this->getNestedSet();
		
		($plugin_code = $xtPlugin->PluginCode('class.category.php:getCategoryListing_SQL')) ? eval($plugin_code) : false;
	
		$tree = $nested_set->getTree();
		$categories = $nested_set->buildHierarchy($tree, $catID, false,'',$direct_children_only);
		
		if(empty($categories)){
			return false;
		}
		
		return $categories;
	}
	
	/**
	 * Creates new nested set instance
	 * @return nested_set
	 */
	protected function getNestedSet() {
		global $xtPlugin;
		
		$nested_set = new nested_set();
		$nested_set->setPosition('getCategoryListing');
		$nested_set->setFilter('Language');
		
		if (USER_POSITION =='store') {
			$nested_set->setFilter('Seo');
		}

		$nested_set->setSQL_SORT("c.sort_order,c.categories_left,cd.categories_name");
		
		($plugin_code = $xtPlugin->PluginCode('category:getNestedSet_bottom')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
			return $plugin_return_value;
		
		return $nested_set;
	}
	
	/**
	 * get child categories of category
	 *
	 * @param int $catID
	 * @param int $level
	 * @return array
	 */
	function getChildCategories ($catID = 0, $level = 0, $nested = false) {
		$nested_set = $this->getNestedSet();
		$tree = $nested_set->getTree();
		return $nested_set->buildHierarchy($tree, $catID, $nested);
	}


	/**
	 * get parent id of category
	 *
	 * @param int $catID
	 * @return int
	 */
	function getParentID ($catID) {
		$array = array('categories_id' => $catID);
		$data = $this->buildData($array);
		if (is_data($data))
		return $data['parent_id'];
		else
		return 0;
	}

	function _getParentID($catID) {
		global $db;

		$catID=(int)$catID;

		$rs = $db->Execute(
			"SELECT parent_id FROM ".TABLE_CATEGORIES." WHERE categories_id=?",
			array((int)$catID)
		);
		if ($rs->RecordCount()==1) {
			return $rs->fields['parent_id'];
		}
	}

	/**
	 * get parent data of category
	 *
	 * @param int $catID
	 * @return array
	 */
	function getParentData ($catID) {
		$array = array('categories_id' => $catID);
		$data = $this->buildData($array);
		if (is_array($data))
		return $data;
		else
		return 0;
	}

	/**
	 * Move category to target category
	 *
	 * @param int $categories_id
	 * @param int $target_id
	 */
	function moveCategory($categories_id, $target_id) {
		global $db;

		$categories_id = (int)$categories_id;
		$target_id = (int)$target_id;

		if(!$target_id){
			$target_id = 0;
		}

		$nested_set = $this->getNestedSet();
		list($left, $right) = $nested_set->getCategoryLeftRight($target_id, nested_set::POSITION_LAST_CHILD);
		
		$update = array(
			'categories_left' => $left,
			'categories_right' => $right,
			'parent_id' => $target_id,
		);
		$db->AutoExecute(TABLE_CATEGORIES, $update, 'UPDATE', "categories_id=".(int)$categories_id."");
		$nested_set->buildNestedSet();

	}

    /**
     * sort categorie within tree
     *
     * @param $source
     * @param $target
     * @param $position
     */
    function sortCategory($source,$target,$position) {
        global $db;
		
        $nested_set = $this->getNestedSet();
        list($left, $right) = $nested_set->getCategoryLeftRight($target, $position);
		
		$update = array(
			'categories_left' => $left,
			'categories_right' => $right,
		);
		
		$db->AutoExecute(TABLE_CATEGORIES, $update, 'UPDATE', "categories_id=".(int)$source."");
		$nested_set->buildNestedSet();
    }

	/**
	 * remove category from database, move products into other category if selected
	 *
	 * @param int $catID
	 * @param boolean $move_products
	 * @param int $target_catID
	 */
	function removeCategory($categories_id) {
		global $db;

		$category_ids = array();
		$nested_set = new nested_set();
		list($left, $right) = $nested_set->getCategoryBounds($categories_id);
		$width = (($right - $left) + 1);
		$category_ids = $this->getChildCategoriesIDs($categories_id);
		$category_ids[] = $categories_id;

		// now delete categories
		foreach ($category_ids as $key => $id) {
			$this->_delete($id);
		}
		
		$db->Execute("UPDATE " . TABLE_CATEGORIES . " SET categories_left=categories_left-" . $width . " WHERE categories_left>" . $left);
		$db->Execute("UPDATE " . TABLE_CATEGORIES . " SET categories_right=categories_right-" . $width . " WHERE categories_right>" . $right);
		$nested_set->buildNestedSet();
	}

	/**
	 * delete category and products from database
	 *
	 * @param int $id categories id
	 */
	function _delete($id) {
		global $db,$xtPlugin;

		$id = (int)$id;

		($plugin_code = $xtPlugin->PluginCode('class.category.php:_delete_top')) ? eval($plugin_code) : false;

		if (is_int($id)) {

			$rs = $db->Execute(
				"UPDATE ".TABLE_PRODUCTS_TO_CATEGORIES." SET  categories_id = 0 WHERE categories_id = ? AND master_link=1",
				array($id)
			);

            $set_perm = new item_permission($this->perm_array);
            $set_perm->_deleteData($id);


			$db->Execute("DELETE FROM " . TABLE_CATEGORIES . " WHERE categories_id = ?", array($id));
			$db->Execute("DELETE FROM " . TABLE_CATEGORIES_DESCRIPTION . " WHERE categories_id = ?", array($id));
			saveDeletedUrl($id,2);
			$db->Execute("DELETE FROM " . TABLE_SEO_URL . " WHERE link_id = ? and link_type='2'", array($id));
			$db->Execute("DELETE FROM " . TABLE_MEDIA_LINK . " WHERE link_id = ? and class='category'", array($id));
            $db->Execute("DELETE FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE categories_id = ?", array($id));

			($plugin_code = $xtPlugin->PluginCode('class.category.php:_delete_bottom')) ? eval($plugin_code) : false;
		}
	}

	/**
	 * get array with child category ids
	 *
	 * @param int $categories_id
	 * @return array
	 */
	function getChildCategoriesIDs($categories_id) {
		$nested_set = new nested_set();
		return $nested_set->getChildCategoryIds($categories_id);
	}
	/**
	 * get level of categories id
	 *
	 * @param int $catID
	 */
	function getLevel ($catID) {
        $arr = $this->getPath($catID);
		$this->level = array_reverse(array_merge($arr, array (0)));
	}
	
	/**
	 * Get category path
	 * @param int $categories_id
	 * @return array
	 */
	function getPath ($categories_id) {
		$nested_set = new nested_set();
		return $nested_set->getCategoryPath($categories_id);
	}

	/**
	 * Get navigation path
	 * @param int $categories_id
	 * @return array
	 */
	function getNavigationPath ($categories_id) {
		static $branch_cache = array();
		
		if (isset($branch_cache[$categories_id])) {
			return $branch_cache[$categories_id];
		}
		
		$path = array();
		$branch = $this->getPath($categories_id);
		foreach ($branch as $category_id) {
			$path[] = $this->buildData(array('categories_id' => $category_id));
		}
		
		$branch_cache[$categories_id] = $path;
		return $path;
	}

	/**
	 * Get breadcrumb navigation
	 * @param int $categories_id
	 */
	function getBreadCrumbNavigation($categories_id) {
		global $db,$brotkrumen;
		$path = $this->getNavigationPath($categories_id);
		$path = array_reverse($path);
		foreach ($path as $key => $arr) {
			$brotkrumen->_addItem($arr['categories_link'],$arr['categories_name']);
		}
	}

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		global $language, $xtPlugin;
		$params = array();

		($plugin_code = $xtPlugin->PluginCode('class.category.php:_getParams_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;
		
		if (StoreIdExists($this->_table_lang,$this->_store_field)) {
			$this->store_field_exists=true;
		}
		
		if ($this->store_field_exists)
			$params['languageStoreTab'] = true;
		
		$header['categories_id'] = array('type' => 'hidden');
		$header['parent_id'] = array('type' => 'hidden');
		$header['permission_id'] = array('type' => 'hidden');
		$header['categories_owner'] = array('type' => 'hidden');
		$header['top_category'] = array('type' => 'status');
		$header['category_custom_link'] = array('type' => 'hidden');
		$st = new multistore();
		$stores = $st->getStores();
		
		foreach ($stores as $store) {
			foreach ($language->_getLanguageList() as $key => $val) {
				$add_to_f='';
				if ($this->store_field_exists) $add_to_f = 'store'.$store['id'].'_';
				$header['categories_description_'.$add_to_f.$val['code']] = array('type' => 'htmleditor');
	            $header['categories_description_bottom_'.$add_to_f.$val['code']] = array('type' => 'htmleditor');
	
				if(_SYSTEM_HIDE_SUMAURL=='true'){
					$header['url_text_'.$add_to_f.$val['code']] = array('type'=>'hidden');
				}else{
					$header['url_text_'.$add_to_f.$val['code']] = array('width'=>400);
				}
	
				//$header['url_text_'.$val['code']] = array('width'=>400);
				$header['meta_keywords_'.$add_to_f.$val['code']] = array('width'=>400);
				$header['meta_title_'.$add_to_f.$val['code']] = array('width'=>400);
				$header['meta_description_'.$add_to_f.$val['code']] = array('type' => 'textarea','width'=>400,'height'=>60);
				$header['categories_store_id_'.$add_to_f.$val['code']] = array('type' => 'hidden');
				$header['store_id_'.$add_to_f.$val['code']] = array('type' => 'hidden');
			}
		}
		$header['products_sorting2'] = array(
			'type' => 'dropdown',
			'url'  => 'DropdownData.php?get=status_ascdesc'
		);

		$header['products_sorting'] = array(
			'type' => 'dropdown',
			'url'  => 'DropdownData.php?get=category_sort'
		);


		($plugin_code = $xtPlugin->PluginCode('class.category.php:_getParams_header')) ? eval($plugin_code) : false;

		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;

		$params['exclude'] = array('external_id', 'date_added', 'last_modified','category_custom_link_type','category_custom_link_id', 'categories_left', 'categories_right');

		foreach ($language->_getLanguageList() as $key => $val) {
			$tmp_array = array('external_id_'.$val['code']);
			$params['exclude'] = array_merge($params['exclude'], $tmp_array);
		}

		$check_id = str_replace('subcat_','',$this->url_data['edit_id']);
		$tmp = explode("__",$check_id);
		$check_id=$tmp[0];
		
		if(_SYSTEM_SIMPLE_GROUP_PERMISSIONS=='true' &&  $this->_getParentID($check_id)!=0){
			$set_perm = new item_permission($this->perm_array);
			$params['exclude'] = $set_perm->_excludeFields($params['exclude']);
		}

		($plugin_code = $xtPlugin->PluginCode('class.category.php:_getParams_bottom')) ? eval($plugin_code) : false;

		return $params;
	}

	function _get($catID = 0) {
		global $db,$language, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.category.php:_get_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;
		$org_cat = $catID;
		$catID = str_replace('subcat_','',$catID);
		$tmp = explode("__",$catID);
		$catID=$tmp[0];
		
		if ($_GET['pg']=='CheckItem') 
		{
			$this->CheckItem($catID,false);
			die();
		}
		
		$res = $this->checkCategoryCustomLink($catID);
		if ($res==1) 
		{
			echo '<script>';
			echo 'contentTabs.remove(contentTabs.getActiveTab());';
			echo "addTab('adminHandler.php?load_section=custom_link&edit_id=".$org_cat."','".TEXT_EDIT_CUSTOM_LINK."');";
			echo '</script>';
		}
		
		if ($catID === 'new' && isset($_GET['master_node'])) {

			$master = $_GET['master_node'];
			$master = str_replace('subcat_','',$_GET['master_node']);
			$tmp = explode("__",$master);
			$master=$tmp[0];
			$obj = $this->_set(array('parent_id'=>$master), 'new');
			$catID = $obj->new_id;
		}

		if(_SYSTEM_SIMPLE_GROUP_PERMISSIONS == 'false' || $this->_getParentID($catID)==0){
			$permissions = $this->perm_array;
		}else{
			$permissions = '';
		}

		($plugin_code = $xtPlugin->PluginCode('class.category.php:_get_data')) ? eval($plugin_code) : false;
		
		$store_field='';
		if ($this->store_field_exists) {
			$store_field= $this->_store_field;
		}
		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', '', $permissions,'','',$store_field);

		if ($this->url_data['get_data']){
			$data = $table_data->getData();
		}elseif($catID){
			$data = $table_data->getData($catID);
			$data[0]['group_permission_info']=_getPermissionInfo();
            $data[0]['shop_permission_info']=_getPermissionInfo();
			$data[0]['category_custom_link'] = 0;
            
		}else{
			$data = $table_data->getHeader();
		}

		($plugin_code = $xtPlugin->PluginCode('class.category.php:_get_bottom')) ? eval($plugin_code) : false;

		$obj = new stdClass;
		$obj->totalCount = count($data);
		$obj->data = $data;

		return $obj;
	}

	function _set($data, $set_type = 'edit'){
		global $db, $language, $filter, $seo, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.category.php:_set_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$obj = new stdClass;

		unset($data['categories_image']);
		// Very stupidly parent_id can be "node_cat_store[store_id]" or "[categories_parent_id]_catst_[store_id]"
		// So ....
		if (substr($data['parent_id'], 0, strlen('node_cat_store')) == 'node_cat_store') {
			$data['parent_id'] = 0;
		} else {
			list($target_category, $store_id) = explode('_catst_', $data['parent_id']);
			$data['parent_id'] = $target_category;
		}
		
		if ($set_type=='new') {
			$nested_set = new nested_set();
			$nested_set->setTable(TABLE_CATEGORIES);
			$nested_set->setTableDescription(TABLE_CATEGORIES_DESCRIPTION);
			$nested_set->buildNestedSet();
			
			list($left, $right) = $nested_set->getCategoryLeftRight($data['parent_id']);
			$data['categories_left'] = $left;
			$data['categories_right'] = $right;
		}
		$oC = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		$objC = $oC->saveDataSet();

		if ($set_type=='new') {	// edit existing
			$obj->new_id = $objC->new_id;
			$data = array_merge($data, array($this->_master_key=>$objC->new_id));
		}

		$oCD = new adminDB_DataSave($this->_table_lang, $data, true, __CLASS__,$this->store_field_exists);
		$objCD = $oCD->saveDataSet();

		// Build Seo URLS
		$st = new multistore();
		$stores = $st->getStores();
		foreach ($stores as $store) {
			foreach ($language->_getLanguageList() as $key => $val) {
				$stor_f='';
				$store_f_update='';
				if ($this->store_field_exists) {
					$stor_f='store'.$store['id'].'_';
					$store_f_update = $store['id'];
				}
				if($data['url_text_'.$stor_f.$val['code']] != '' && $data['url_text_'.$stor_f.$val['code']]!='Suma URL'){
					$auto_generate = false;
				}else{
					$auto_generate = true;
					$data['url_text_'.$stor_f.$val['code']] = $data['categories_name_'.$stor_f.$val['code']];
				}
	
				if ($set_type=='new') {	// edit existing
					//	 $seo->_InsertRecord('category',$obj->new_id, $val['code'], $data,$auto_generate);
				}else{
					$seo->_UpdateRecord('category',$data['categories_id'], $val['code'], $data,$auto_generate,'',$store_f_update);
				}
			}
		}
		$set_perm = new item_permission($this->perm_array);
		$set_perm->_saveData($data, $data[$this->_master_key]);

		$catdata = $this->getChildCategoriesIDs($data['categories_id']);


		($plugin_code = $xtPlugin->PluginCode('class.category.php:_set_bottom')) ? eval($plugin_code) : false;

		if ($objC->success && $objCD->success) {
			$obj->success = true;
		} else {
			$obj->failed = true;
		}

		return $obj;
	}

	function _setImage($id, $file) {
		global $xtPlugin,$db,$language,$filter,$seo;
		if ($this->position != 'admin') return false;

		($plugin_code = $xtPlugin->PluginCode('class.category.php:_setImage_top')) ? eval($plugin_code) : false;

		$obj = new stdClass;

		$data[$this->_master_key] = $id;
		$data['categories_image'] = $file;

		$o = new adminDB_DataSave($this->_table, $data);
		$obj = $o->saveDataSet();

		$obj->totalCount = 1;
		if ($obj->success) {
			$obj->success = true;
		} else {
			$obj->failed = true;
		}

		($plugin_code = $xtPlugin->PluginCode('class.category.php:_setImage_bottom')) ? eval($plugin_code) : false;
		return $obj;
	}	
	
	function _rebuildSeo($id, $params){
		global $xtPlugin,$db,$language,$filter,$seo;
		if ($this->position != 'admin') return false;

		$obj = new stdClass;
		
		$rs=$db->Execute(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema=? AND table_name=? AND COLUMN_NAME = ? ",
			array(_SYSTEM_DATABASE_DATABASE, $this->_table_lang, $this->_store_field)
		);
		$s_id ='';
		if ($rs->RecordCount()>0){
			$s_id = $this->_store_field;
		}
		$store_id='';
		if (isset($_GET['edit_id']))
		{
			$exp = explode('node_cat_store',$_GET['edit_id']); // rebuild seo for whole store ID
			if ($exp[1]){
				$store_id = $exp[1];
			}
			$exp = explode('_catst_',$_GET['edit_id']); // rebuild seo for category ID and store ID
			if ($exp[1]){
				$store_id = $exp[1];
			}
		}
		
		$seo->_rebuildSeo($this->_table, $this->_table_lang, $this->_table_seo, '2', 'category', 'categories_name', $this->_master_key, $id,$s_id,$store_id);

		$obj->success = true;
		return $obj;

	}

	function _unset($id = 0) {
		global $db,$link_params, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.category.php:_unset_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if ($id==0) {
			if (is_array($link_params) && isset($link_params['edit_id'])) {
				$id = str_replace('subcat_','',$link_params['edit_id']);
				$tmp = explode("__",$id);
				$id=$tmp[0];
				$id = (int)$id;
			} else {
				return false;
			}
		} else {
			$id = (int)$id;
		}


		if (!is_int($id)) return false;
		if ($id == 0) return false;

		$this->removeCategory($id);

		($plugin_code = $xtPlugin->PluginCode('class.category.php:_unset_bottom')) ? eval($plugin_code) : false;

		$obj = new stdClass;
		$obj->success = true;
		return $obj;

	}
	/*
	media images
	*/
	function get_media_images($id){
		global $mediaImages, $xtPlugin;
		
		if(USER_POSITION=='admin') return false;
		
		if ($data['tmp_images'] = $mediaImages->_getMediaFiles($id, __CLASS__, 'images', 'free')) { 
			
			foreach ($data['tmp_images'] as $key => $val){
				$data['images'][$key]['file'] = __CLASS__.':'.$val['file'];
				$data['images'][$key]['data'] = $val;
			}
			
			($plugin_code = $xtPlugin->PluginCode(__CLASS__.':get_media_images')) ? eval($plugin_code) : false;
			
			return $data;
		} else {
			return false;
		}
	}
	
	function checkCategoryCustomLink($catID)
	{
		global $db,$language;	
		
		$query = "select category_custom_link from ".TABLE_CATEGORIES." WHERE categories_id = '".$catID."' ";
		$record = $db->Execute($query);
		if ($record->RecordCount() > 0) {
			return $record->fields['category_custom_link'];
		}
		return -1;
	}
	
	function CheckItem($id,$custom_link=true)
	{
		$obj = new stdClass;
		$id = str_replace('subcat_','',$id);
		$tmp = explode("__",$id);
		$id=$tmp[0];
		$res = $this->checkCategoryCustomLink($id);
		
		if ($custom_link) // checking if custom_link action are available 
		{
			if ($res==0)  // $res =0 - category
				echo 'failure';
			else echo 'success';
		}
		else //checking if category action are available 
		{
			if ($res==1)  // $res =1 - custom_link
				echo 'failure';
			else echo 'success';
		}
		die();
	}
}