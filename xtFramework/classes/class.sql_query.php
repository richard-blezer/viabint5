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

class SQL_query {
	var $filterFunctions; // sql filter functions array
	/* filter functions
	Manufacturer
	Startpage
	GroupCheck
	Categorie
	*/
	var $a_sql_table, $a_sql_where, $a_sql_sort, $a_sql_group, $a_sql_limit;
    var $user_position;

	function __construct() {
      $this->user_position=USER_POSITION;
	}
    
    function setUserPosition($position='store') {
      $this->user_position=$position;  
    }

	function explodeArray(&$array) {
		$string = implode(",", $array);
		return $string;
	}

	//////////////////////////////////////////////////////
	// manage filter function ($values can be a string or array)
	function setFilter ($function, $values = '', $typ = 'and', $_vtype='string') {
        
        // check if filter was allready set
     //   if (isset($this->filterFunctions[$function])) return;
        
		if (!is_array($values) && !empty($values))
			$_values[] = $values;
		else
			$_values = $values;

		$_values['_vtype'] = $_vtype;

		$this->filterFunctions[$function] = $_values;
	}

	function getFilter () {
		if (count($this->filterFunctions) > 0)
		while (list($function,$values) = each($this->filterFunctions)) {

				if ($values['_vtype']=='string' || empty($values['_vtype'])){
					unset($values['_vtype']);
					call_user_func_array(array(&$this, 'F_'.$function), $values);
				}elseif($values['_vtype']=='array'){
					unset($values['_vtype']);
					call_user_func_array(array(&$this, 'F_'.$function), array($values));
				}

		}

	}
	// manage filter function
	//////////////////////////////////////////////////////



	function setPosition ($position) {
		$this->position = $position;
	}
	function getPosition () {
		return $this->position;
	}

	//////////////////////////////////////////////////////
	// sql arrays start
	function setSQL_COLS ($string, $overwrite = false) {
		if (is_data($string))
			if ($overwrite)
				$this->a_sql_cols = ' '.$string;
			else
				$this->a_sql_cols.= ' '.$string;
	}

	function setSQL_TABLE ($string, $overwrite = false) {
		if (is_data($string))
			if ($overwrite)
				$this->a_sql_table = ' '.$string;
			else
				$this->a_sql_table.= ' '.$string;
	}
	function setSQL_WHERE ($string) {
			$this->a_sql_where.= ' '.$string;
	}
	function setSQL_GROUP ($string) {
			$this->a_sql_group.= ' '.$string;
	}
	function setSQL_SORT ($string) {
			$this->a_sql_sort.= ' '.$string;
	}
	function setSQL_LIMIT ($string) {
			$this->a_sql_limit= ' '.$string;
	}

	// sql arrays end
	//////////////////////////////////////////////////////

	function reset(){

		$this->setSQL_TABLE('');
		$this->setPosition('');
		$this->setSQL_COLS('');
		$this->setSQL_WHERE('');
		$this->setSQL_SORT('');
		$this->setSQL_GROUP ('');
		$this->setSQL_LIMIT('');

	}

	//////////////////////////////////////////////////////
	// sql hook plugins start
	function getHooks () {
		if ($this->position == '') return;
		$this->getHookSQL_COLS();
		$this->getHookSQL_TABLE();
		$this->getHookSQL_WHERE();
		$this->getHookSQL_SORT();
		$this->getHookSQL_GROUP();
		$this->getHookSQL_LIMIT();
	}

	function getHookSQL_COLS () {
	 global $xtPlugin;
	 ($plugin_code = $xtPlugin->PluginCode($this->position.':cols')) ? eval($plugin_code) : false;
	 
	 if(isset($cols))
	 $this->setSQL_COLS($cols);
	}
	function getHookSQL_TABLE () {
	 global $xtPlugin;
	 ($plugin_code = $xtPlugin->PluginCode($this->position.':table')) ? eval($plugin_code) : false;
	 
	 if(isset($table))
	 $this->setSQL_TABLE($table);
	}
	function getHookSQL_WHERE () {
	 global $xtPlugin;
	 ($plugin_code = $xtPlugin->PluginCode($this->position.':where')) ? eval($plugin_code) : false;
	 
	 if(isset($where))
	 $this->setSQL_WHERE($where);
	}
	function getHookSQL_SORT () {
	 global $xtPlugin;
	 ($plugin_code = $xtPlugin->PluginCode($this->position.':sort')) ? eval($plugin_code) : false;
     
	 if(isset($sort))
	 $this->setSQL_SORT($sort);
	}
	function getHookSQL_GROUP () {
	 global $xtPlugin;
	 ($plugin_code = $xtPlugin->PluginCode($this->position.':group')) ? eval($plugin_code) : false;
	 
	 if(isset($group))
	 $this->setSQL_GROUP($group);
	}
	function getHookSQL_LIMIT () {
	 global $xtPlugin;
	 ($plugin_code = $xtPlugin->PluginCode($this->position.':limit')) ? eval($plugin_code) : false;
	 
	 if(isset($limit))
	 $this->setSQL_LIMIT($limit);
	}
	// sql hook plugins end
	//////////////////////////////////////////////////////
}

?>