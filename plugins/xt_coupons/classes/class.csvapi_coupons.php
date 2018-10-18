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

require_once _SRV_WEBROOT . 'plugins/xt_im_export/classes/class.csvapi.php';

class csv_api_coupons extends csv_api
{

    var $limit = '1000';
    var $limit_lower = 0;
    var $limit_upper = 0;
    var $delimiter = '|';
    var $counter_new = 0;
    var $counter_update = 0;
    var $version = '1.0';
    var $_table_log = TABLE_EXPORTIMPORT_LOG;
    var $_table_data = TABLE_COUPONS_IM_EXPORT;
    var $_module_id = 'xt_coupon_im_export';
    var $enclosure = '"';

    function csv_api_coupons ()
    {
    }
    
    /**
     * query for export/import dataset
     *
     * @param int $id
     */
    // UBO angepasst an $this->_table_data
    function getDetails ($id)
    {
        global $db, $filter;

        $id = (int)$id;
        if (!is_int($id)) return false;

        $rs = $db->Execute("SELECT * FROM " . $this->_table_data . " WHERE id = ?",array($filter->_charNum($id)));
        if ($rs->RecordCount() != 1) return false;

        if ($this->_recordData['ei_delimiter'] != '') $this->delimiter = $this->_recordData['ei_delimiter'];

        $this->_recordData = $rs->fields;
        return true;

    }
    
    // UBo angepasst an $this->_module_id
    function showLog ($id) {
    	global $logHandler;
    	
    	$logHandler->showLog($this->_module_id, $id);
    }
	
    // UBo angepasst an $this->_module_id
    function _clearLog ($id) {
    	global $logHandler;
    	$logHandler->clearLogMessages($this->_module_id, $id);
    }
}

?>