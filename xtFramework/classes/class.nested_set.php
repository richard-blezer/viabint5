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

class nested_set extends getCategorySQL_query {
	
	/**
	 * Position before
	 * @var string
	 */
	const POSITION_BEFORE = 'above';
	
	/**
	 * Position first child
	 * @var string
	 */
	const POSITION_FIRST_CHILD = 'first_child';
	
	/**
	 * Position last child
	 * @var string
	 */
	const POSITION_LAST_CHILD = 'last_child';
	
	/**
	 * Position after
	 * @var string
	 */
	const POSITION_AFTER = 'below';
	
	/**
	 * Database adapter
	 * @var object
	 */
	protected static $_db = null;
	
	/**
	 * Table name
	 * @var string
	 */
	protected $_table = TABLE_CATEGORIES;
	
	/**
	 * Table description name
	 * @var string
	 */
	protected $_table_description = TABLE_CATEGORIES_DESCRIPTION;
	
	/**
	 * Primary table column
	 * @var string
	 */
	protected $_primary_column = 'categories_id';
	
	/**
	 * Categories left column name
	 * @var string
	 */
	protected $_column_left = 'categories_left';
	
	/**
	 * Categories right column name
	 * @var string
	 */
	protected $_column_right = 'categories_right';
	
	/**
	 * Categories parent_id
	 * @var string
	 */
	protected $_column_parent = 'parent_id';
	
	/**
	 * @access private
	 */
	protected $_nodeLevelName = 'level';
	
	/**
	 * @access private
	 */
	protected $_subNodesCountName = 'SubNodesCount';
	
	/**
	 * @var array
	 */
	protected $_joinConditions = array();
	
	/**
	 * @var array
	 */
	protected $_whereConditions = array();

    /**
     * @var array
     */
    protected $_nestedLevel = array();
	
	/**
	 * Set database adapter
	 * @param object $db
	 */
	public static function setDbAdapter($db) 
	{
		self::$_db = $db;
	}
	
	/**
	 * Get database adapter
	 * @return object
	 */
	public static function getDbAdapter() 
	{
		return self::$_db;
	}

    public function setNestedLevel($level)
    {
        $this->_nestedLevel = $level;
    }
	
	/**
	 * Class constructor. If there is no defult database set it will initiliaze global
	 * $db object.
	 */
	public function __construct() 
	{
		global $db;
		
		if (null == self::$_db) {
			self::$_db = $db;
		}
		
		$this->setSQL_TABLE(TABLE_CATEGORIES . " AS c CROSS JOIN " . TABLE_CATEGORIES . " AS parent");
		$this->setSQL_WHERE(" c.categories_status = '1'");
	}
	
	/**
	 * Sets table name
	 * @param string $tableName
	 * @return nested_set
	 */
	public function setTable($tableName) 
	{
		$this->_table = (string)$tableName;
		return $this;
	}
	
	/**
	 * Get table name
	 * @return string
	 */
	public function getTable() 
	{
		return $this->_table;
	}
	
	/**
	 * Sets table description name
	 * @param string $tableDescriptionName
	 * @return nested_set
	 */
	public function setTableDescription($tableDescriptionName) 
	{
		$this->_table_description = (string)$tableDescriptionName;
		return $this;
	}
	
	/**
	 * Get table description name
	 * @return string
	 */
	public function getTableDescription() 
	{
		return $this->_table_description;
	}
	
	/**
	 * Get new categories_left and categories_right for given parent at given position
	 * @param int $categories_id
	 * @param string $position
	 * @return multitype:number
	 */
	public function getCategoryLeftRight($categories_id, $position = self::POSITION_LAST_CHILD) 
	{
		$left_right = array();
		$lft = null;
		$rgt = null;
		$left = $this->_column_left;
		$right = $this->_column_right;
		
		if (!empty($categories_id)) {
			list($lft, $rgt) = $this->getCategoryBounds($categories_id);
		}
		
		// Existing node id
		if ((null !== $lft) && (null !== $rgt)) {
			$sql1 = '';
			$sql2 = '';
			switch ($position) {
				case self::POSITION_FIRST_CHILD :
                    $sql1 = "UPDATE {$this->_table} SET $right = $right + 2 WHERE $right > $lft";
                    $sql2 = "UPDATE {$this->_table} SET $left = $left + 2 WHERE $left > $lft";
					
                    // Left
                    $lftRgt[] = $lft + 1;
                    // Right
                    $lftRgt[] = $lft + 2;

                    break;
                case self::POSITION_LAST_CHILD :
                    $sql1 = "UPDATE {$this->_table} SET $right = $right + 2 WHERE $right >= $rgt";
                    $sql2 = "UPDATE {$this->_table} SET $left = $left + 2 WHERE $left > $rgt";
					
                    // Left
                    $lftRgt[] = $rgt;
                    // Right
                    $lftRgt[] = $rgt + 1;
					
                    break;
                case self::POSITION_AFTER :
                    $sql1 = "UPDATE {$this->_table} SET $right = $right + 2 WHERE $right > $rgt";
                    $sql2 = "UPDATE {$this->_table} SET $left = $left + 2 WHERE $left > $rgt";

                    // Left
                    $lftRgt[] = $rgt + 1;
                    // Right
                    $lftRgt[] = $rgt + 2;
					
                    break;
                case self::POSITION_BEFORE :
                    $sql1 = "UPDATE {$this->_table} SET $right = $right + 2 WHERE $right > $lft";
                    $sql2 = "UPDATE {$this->_table} SET $left = $left + 2 WHERE $left >= $lft";
					
                    // Left
                    $lftRgt[] = $lft;
                    // Right
                    $lftRgt[] = $lft + 1;

                    break;
			}
			
			self::$_db->Execute($sql1);
			self::$_db->Execute($sql2);
		} else {
			// Add it to the end of set at first level
			$query = "SELECT MAX({$this->_column_right}) AS categories_right FROM {$this->_table}";
			$rs = self::$_db->Execute($query);
			
			if ($rs->RecordCount() == 0) {
				$right = 0;
			} else {
				$right = $rs->fields['categories_right'];
			}
			$lftRgt = array($right + 1, $right + 2);
        }
        
        // Left, Right
        return $lftRgt;
	}
	
	/**
	 * Builds the nested set from the adjacent model and updates the DB
	 *
	 * @param string $table the table name
	 * @param string $primaryKey the primary key
	 * @param string $parentKey the parent key used in the adjacent model
	 * @param string $leftKey the left key for the nested set
	 * @param string $rightKey the right key for the nested set
	 * @return array returns 2 arrays with the left and right values
	 */
	public function buildNestedSet($primaryKey = 'categories_id', $parentKey = 'parent_id', $leftKey = 'categories_left', $rightKey = 'categories_right')
	{

        $rootCategories = $this->getCategoryChildren(0);

        $heirarchy = array();
        $positionLeft = 0;

        foreach ($rootCategories as $category) {
            $heirarchy = array_merge($heirarchy, $this->computePositions($category['categories_id'], $positionLeft));
        }
        //var_dump($heirarchy);die;
			
		// Update the nested set
		foreach ($heirarchy as $row) {
			$key = $row[$primaryKey];
            unset($row[$primaryKey]);
            self::$_db->AutoExecute($this->_table, $row, 'UPDATE', $primaryKey."=".$key."");
		}
	}

    protected function computePositions($category_id, &$positionLeft)
    {
        $positions = array();
        $positionLeft += 1;
        $currentCategoryPositions = array(
            "categories_id" => $category_id,
            "categories_left" => $positionLeft
        );

        $children = $this->getCategoryChildren($category_id);

        foreach ($children as $child) {
            $positions = array_merge($positions, $this->computePositions($child['categories_id'], $positionLeft));
        }

        $positionLeft += 1;
        $currentCategoryPositions["categories_right"] = $positionLeft;
        $positions[] = $currentCategoryPositions;

        return $positions;
    }

    protected function getCategoryChildren($parent_id)
    {
        $childCategories = array();
        $query = sprintf("SELECT * FROM %s WHERE parent_id='%d' ORDER BY categories_left ASC", TABLE_CATEGORIES, $parent_id);
        $rs = self::$_db->Execute($query);

        if ($rs->RecordCount() > 0) {
            while(!$rs->EOF) {
                $childCategories[] = $rs->fields;
                $rs->MoveNext();
            }

            $rs->Close();
        }

        return $childCategories;
    }
	
	/**
	 * Returns the left and right values for the nested set
	 *
	 * @param array $records records
	 * @param string $primaryKey the primary key
	 * @return array returns 2 arrays with the left and right values
	 */
	protected function getNestedSet($records, $primaryKey)
	{
		$left = array();
		$right = array();
			
		$level = 1;
		$current = 0;
		foreach ($records as $row) {
			if ($row['level'] == $level) {
				$current++;
			} elseif ($row['level'] < $level) {
				$current += $level - $row['level'] + 1;
			}
			$level = $row['level'];
	
			$left[$row[$primaryKey]] = $current;
			$current++;
			$right[$row[$primaryKey]] = $current + $row['SubNodesCount'] * 2;
		}
			
		return array($left, $right);
	}
	
	/**
	 * Get category right value
	 * @param integer $categories_id
	 * @return number
	 */
	public function getCategoryRight($categories_id) 
	{
		list($left, $right) = $this->getCategoryBounds($categories_id);
		return $right;
	}
	
	/**
	 * Get category left value
	 * @param int $categories_id
	 * @return number
	 */
	public function getCategoryLeft($categories_id)
	{
		list($left, $right) = $this->getCategoryBounds($categories_id);
		return $left;
	}
	
	/**
	 * Get category left/right bounds
	 * @param int $categories_id
	 * @return array
	 */
	public function getCategoryBounds($categories_id) 
	{
		if (!empty($categories_id)) {
			$query = "SELECT {$this->_column_right}, {$this->_column_left} FROM {$this->_table} WHERE {$this->_primary_column} = '{$categories_id}'";
		} else {
			$query = "SELECT MAX({$this->_column_right}) AS {$this->_column_right}, MIN({$this->_column_left}) AS {$this->_column_left} FROM {$this->_table}";
		}
		
		$rs = self::$_db->Execute($query);
		
		if ($rs->RecordCount() == 0) {
			return array(1,2);
		}
		
		return array(
			$rs->fields[$this->_column_left], 
			$rs->fields[$this->_column_right]
		);
	}
	
	function getSQL_query($cols = '') {
		global $xtPlugin;
		
		if (USER_POSITION =='store') {
			$this->setFilter('GroupCheck');
			$this->setFilter('StoreCheck');
		}

        $sqlWhere = "and c.{$this->_column_left} BETWEEN parent.{$this->_column_left} AND parent.{$this->_column_right}";

        if (!empty($this->_nestedLevel)) {
            $level = $this->_nestedLevel;
            $sqlWhere .= " AND (c.{$this->_primary_column} IN (" . join(',', $this->_nestedLevel) . ")";
            $currentCategory = array_pop($level);
            $sqlWhere .= " OR c.{$this->_column_parent} IN (0, '{$currentCategory}'))";
        }

		$this->setSQL_WHERE($sqlWhere);
		
		($plugin_code = $xtPlugin->PluginCode('class.category_sql_query.php:getSQL_query_filter')) ? eval($plugin_code) : false;
		 
		$this->getFilter();
		$this->getHooks();
		$this->a_sql_cols = "," . join(',', $this->_selectTables) . $this->a_sql_cols;

		$sql = "
			SELECT
				COUNT(parent.{$this->_primary_column}) AS level 
				{$this->a_sql_cols} FROM {$this->a_sql_table}";
		if (is_data($this->a_sql_where))
			$sql.=' WHERE '.$this->a_sql_where;
		$sql .= " GROUP BY c.{$this->_primary_column}, c.{$this->_column_left}, c.{$this->_column_right}";
		if (is_data($this->a_sql_sort))
			$sql.=' ORDER BY '.$this->a_sql_sort;
		if (is_data($this->a_sql_limit))
			$sql.=' LIMIT '.$this->a_sql_limit;

		if (USER_POSITION =='admin') {
			$sql = str_replace(" c.categories_status = '1' and ","",$sql);
		}
		return $sql;
	}
	
	function F_Sorting($sort) {
		switch ($sort) {
	
			case 'name' :
				$this->setSQL_SORT(' cd.categories_name');
				break;
	
			case 'name-desc' :
				$this->setSQL_SORT(' cd.categories_name DESC');
				break;
	
			case 'sort_order' :
				$this->setSQL_SORT(' c.categories_left');
				break;
	
			case 'sort_order-desc' :
				$this->setSQL_SORT(' c.categories_left DESC');
				break;
	
			default:
				return false;
		}
	}
	
	/**
	 * Get category three
	 * @param string $categories_id
	 * @param string $cached
	 * @return array
	 */
	public function getTree($cached = true)
	{
		static $tree_cache = array();
		
		if (empty($tree_cache) || !$cached) {
			$query = $this->getSQL_query();
				
			$rs = self::$_db->Execute($query);
						
			$return = array();
			if ($rs->RecordCount() > 0) {
			while(!$rs->EOF){
				$return[] = $rs->fields;
				$rs->MoveNext();
			}
				$rs->Close();
			}
			// Cache the result
			$return = $this->makeTree($return, $this->_primary_column, $this->_column_parent);
			$tree_cache = $return;
		}

		return $tree_cache;
	}
	
	/**
	 * Build a heirarchy from tree
	 * @param array $tree
	 * @param number $parent_id
	 * @param string $nested
	 * @param array $current_path
	 * @param string $class The class name of current class
	 * @return Ambigous array
	 */
	public function buildHierarchy($tree, $parent_id = 0, $nested = true, $current_path = array(),$direct_children_only=0, $class = __CLASS__)  
	{
		global $mediaImages, $xtLink;
		$return = array();
		$categories = $this->getTopTree($tree, $parent_id);
		
		foreach ($categories as &$category) {
			$category['active'] = '0';
			
			$media_images = $mediaImages->get_media_images($category['categories_id'], __CLASS__);
			$category['more_images'] = $media_images['images'];
			
			// Check only last element. Last element is the current category in the current path.
			if ($category['categories_id'] == $current_path[count($current_path)-1])
				$category['active'] = '1';
			
			if ($category['category_custom_link']==1) // custom_link not a category
			{
				$url = $this->buildCustomLinkURL($category);
				$category['categories_link'] = $url;
			}
			else {
				$link_array = array('page'=> 'categorie',
						'type'=> 'category',
						'name'=>$category['categories_name'],
						'text'=>$category['categories_name'],
						'id'=>$category['categories_id'],
						'seo_url'=>$category['url_text'],
				);
			
				$category['categories_link'] = $xtLink->_link($link_array);
			}
			
			$tpl = new Template();
			$category['listing_template'] = $tpl->getDefaultTemplate($category['listing_template'], 'product_listing/');
			$category['categories_template'] = $tpl->getDefaultTemplate($category['categories_template'], 'categorie_listing/');
			
			if ($nested) {
				$category['sub'] = $this->buildHierarchy($tree, $category['categories_id'], $nested, $current_path,$direct_children_only);
				$return[] = $category;
			} else {
				$return[] = $category;
				if ($direct_children_only!=1){
                    $children = $this->buildHierarchy($tree, $category['categories_id'], $nested, $current_path,$direct_children_only);
                    $return = array_merge($return, $children); 
                }
			}
		}
		
		return $return;
	}
	
	/*Building custom link in category tree
     * @param int $category
     * @return string
     * */
    public function buildCustomLinkURL($category){
         global $xtLink, $db,$store_handler, $language;
        
        $url = ''; 
        switch($category['category_custom_link_type']){
            case 'product':
            case 'category':
            case 'content':
                 // create an instance of the respective custom link class (product,category or content)
                $info =  new $category['category_custom_link_type']($category['category_custom_link_id']);
                $link_arr = array('page'=> $category['category_custom_link_type'], 
                                  'type'=>$category['category_custom_link_type'],
                                  'id'=>$category['category_custom_link_id'], 
                                  'seo_url'=>$info->data['url_text']);
                $url = $xtLink->_link($link_arr);  
            break;
         
            case 'plugin':
                  $rs = $db->Execute("SELECT url_text, code FROM ".TABLE_SEO_URL." s
                                  INNER JOIN ".TABLE_PLUGIN_PRODUCTS." p ON p.plugin_id = s.link_id
                                  WHERE s.link_type=1000 and s.link_id = '".$category['category_custom_link_id']."' 
                                  and s.store_id = ".$store_handler->shop_id." and s.language_code = '".$language->code."'");
                  if ($rs->RecordCount()>0)
                  {
                      $link_arr = array('page'=> $rs->fields['code'], 
                                      'type'=>$rs->fields['code'],
                                      'id'=>$category['category_custom_link_id'], 
                                      'seo_url'=>$rs->fields['url_text']);
                      $url = $xtLink->_link($link_arr); 
                  }
            break;
            case 'custom': 
           
                $url = $category['link_url']; 
            break; 
        }
        
        return $url;
    }
	
	/**
	 * Get all child categories of category no matter the depth
	 * @param int $categories_id
	 * @return array
	 */
	public function getChildCategoryIds($categories_id) {
		
		$category_ids = array();
		$categories_id = (int)$categories_id;
		list($left, $right) = $this->getCategoryBounds($categories_id);
		
		$rs = self::$_db->Execute("SELECT categories_id FROM {$this->_table} WHERE categories_left BETWEEN {$left} AND {$right}");
		
		if ($rs->RecordCount() > 0) {
			while (!$rs->EOF) {
				$category_ids[] = $rs->fields['categories_id'];
				$rs->MoveNext();
			}
			$rs->Close();
		}
		
		return $category_ids;
	}
	
	/**
	 * Get category parent path
	 * @param int $categories_id
	 */
	public function getCategoryPath($categories_id) {
		$path = array();
		
		$query = "
			SELECT {$this->_primary_column}
			FROM {$this->_table}
			WHERE
				{$this->_column_left} <= (SELECT {$this->_column_left} FROM {$this->_table} WHERE {$this->_primary_column} = '{$categories_id}') AND
				{$this->_column_right} >= (SELECT {$this->_column_right} FROM {$this->_table} WHERE {$this->_primary_column} = '{$categories_id}')
			ORDER BY {$this->_column_left}		
		";
		$rs = self::$_db->Execute($query);
		
		if ($rs->RecordCount() > 0) {
			while (!$rs->EOF) {
				$path[] = $rs->fields[$this->_primary_column];
				$rs->MoveNext();
			}
			$rs->Close();
			$path = array_reverse($path);
		}
		
		return $path;
	}
	
	/**
	 * Retrieves subtree starting with $id
	 *
	 * @access public
	 */
	function getSubTree(&$rows, $id, $idName, $returnTopNode = true)
	{
		if (empty($id)) {
			return $rows;
		}
		$skipLevel = -1;
		$resultRows = array();
		foreach ($rows as $key => $row) {
			if (($skipLevel != -1) and ($row[$this->_nodeLevelName] > $skipLevel)) {
				$resultRows[] = $row;
				continue;
			}
	
			if ($row[$idName] == $id) {
				$skipLevel = $row[$this->_nodeLevelName];
				if ($returnTopNode)
					$resultRows[] = $row;
			} else
				$skipLevel = -1;
		}
		return $resultRows;
	}
	
	function getTopTree(&$rows, $id)
	{
		$resultRows = array();
		$idName = 'parent_id';
		foreach ($rows as $key => $row) {
			if (($row[$idName] == $id)) {
				$resultRows[] = $row;
				continue;
			}
		}
		return $resultRows;
	}
	
	/**
	 * Returns only the nodes that are expanded, preserving the deep tree traversal order
	 *
	 * @param $rows the nodes in deep tree traversal order
	 * @param $expandedIds an array of ids that are expanded
	 * @param $parentIdName the name of the key of the parentId in each node
	 * @access public
	 */
	function getExpandedNodesOnly(&$rows, $expandedIds)
	{
		$skipLevel = -1;
		$expandedIds = array_map('intval', $expandedIds);
		$parentIdName = 'parent_id';
		$resultRows = array();
		foreach ($rows as $key => $row) {
			if (($skipLevel != -1) and ($row[$this->_nodeLevelName] > $skipLevel))	// Skip hidden sublevels
				continue;
			if (($row[$parentIdName] == 0) or in_array((int)$row[$parentIdName], $expandedIds)) {
				$resultRows[] = $row;
				$skipLevel = -1;
			} else
				$skipLevel = $row[$this->_nodeLevelName];
		}
		return $resultRows;
	}
	
	/**
	 * Returns the rows with all subnodes of $id removed
	 */
	function removeSubTree(&$rows, $id, $idName, $removeTopNode = false)
	{
		$skipLevel = -1;
		$resultRows = array();
		foreach ($rows as $key => $row) {
			if (($skipLevel != -1) and ($row[$this->_nodeLevelName] > $skipLevel))	// Skip hidden sublevels
				continue;
				
			if ($row[$idName] == $id) {
				$skipLevel = $row[$this->_nodeLevelName];
				if ($removeTopNode) continue;
			} else
				$skipLevel = -1;
			$resultRows[] = $row;
		}
		return $resultRows;
	}
	
	protected function makeTree($rows, $idName, $parentIdName, $parent = 0, $level = 0)
	{
		$parentMap = array();
		foreach ($rows as $key => $row) {
			$parentId = (int)$row[$parentIdName];
			if (!isset($parentMap[$parentId]))
				$parentMap[$parentId] = array($key);
			else
				$parentMap[$parentId][] = $key;
		}
		
		return $this->recursiveMakeTree($rows, $parentMap, $idName, $parentIdName, 0, 1);
	}
	
	protected function recursiveMakeTree($rows, $parentMap, $idName, $parentIdName, $parent = 0, $level = 1)
	{
		$treeRows = array();
		
		if (!isset($parentMap[$parent]))
			return $treeRows;
			
		foreach ($parentMap[$parent] as $key) {
			$row = $rows[$key];
			
			//$row[$this->_nodeLevelName] = $level;
			if (isset($parentMap[$row[$idName]])) {
				$subTreeRows = $this->recursiveMakeTree($rows, $parentMap, $idName, $parentIdName, $row[$idName], $level+1);
				
				$row[$this->_subNodesCountName] = count($subTreeRows);
				$treeRows[] = $row;
				$treeRows = array_merge($treeRows, $subTreeRows);
			} else {
				$row[$this->_subNodesCountName] = 0;
				$treeRows[] = $row;
			}
		}
		return $treeRows;
	}
}

