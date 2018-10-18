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


class order_edit_edit_item {

    public $position = null;
    public $_master_key = 'orders_products_id';

	protected $_table_xsell = TABLE_PRODUCTS_CROSS_SELL;

    function setPosition($position)
    {
        $this->position = $position;
    }

	function _getParams() {
		global $language;

        if (_LIC_TYPE=='free') die('not available in free license');

		$params = array();
		$header['orders_id'] = array('type'=>'hidden');
        $header['products_name'] = array('type' => 'textfield', 'readonly'=>true);
        $header['products_model'] = array('type' => 'textfield', 'readonly'=>true);
        $header['products_tax_class'] = array('type' => 'dropdown', 'url' => 'DropdownData.php?get=tax_classes');
        $header['products_additional_info'] = array('type' => 'textarea', 'width'=>308, 'height'=>80);
        $params['header']         = $header;

        $params['display_checkCol']  = false;
        $params['display_editBtn']  = false;
        $params['display_deleteBtn']  = false;
        $params['display_newBtn']  = false;
        $params['display_GetSelectedBtn'] = false;
        $params['display_resetBtn']  = false;
		$params['master_key']     = $this->_master_key;

        if ($_REQUEST['pg'] == 'edit')
        {
		    $params['include'] = array (
                'orders_products_id',
                'orders_id',
                'products_name',
                'products_model',
                'products_quantity',
                'products_price',
                'products_tax_class',
                'products_additional_info'
            );
        }
        else
        {
            $params['include'] = array (
                'orders_products_id',
                'orders_id',
                'products_name',
                'products_model',
                'products_quantity',
                'products_price',
                'products_tax',
                'products_additional_info'
            );
        }

		return $params;
	}



	function _getIDs($id) {
		global $xtPlugin, $db, $language, $seo;

		$query = "select products_id_cross_sell from ".$this->_table_xsell." where products_id = ? ";

		$record = $db->Execute($query, array((int)$id));
		if ($record->RecordCount() > 0) {

			while(!$record->EOF){
				$records = $record->fields;
				$data[] = $records['products_id_cross_sell'];
				$record->MoveNext();
			} $record->Close();
		}

		return $data;
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language, $system_status;

		if ($this->position != 'admin' || !$ID) return false;

        $table_data = new adminDB_DataRead(TABLE_ORDERS_PRODUCTS, '', '', $this->_master_key, '', '', '');

        $data = $table_data->getData($ID);

		if($table_data->_total_count!=0 || !$table_data->_total_count)
		$count_data = $table_data->_total_count;
		else
		$count_data = count($data);

        $obj = new stdClass();
		$obj->totalCount = $count_data;
		$obj->data = $data;

		return $obj;
	}

    function _getSearchIDs($search_data) {
        global $xtPlugin, $db, $language, $seo,$filter;

        $sql_tablecols = array(
            'p.products_ean','p.products_id',
            'p.products_model',
            'pd.products_name'
        );

        ($plugin_code = $xtPlugin->PluginCode('class.product.php:_getSearchIDs_array')) ? eval($plugin_code) : false;

        $this->sql_Product = new getProductSQL_query();
        $this->sql_Product->setPosition('admin');
        $this->sql_Product->setFilter('Language');
        foreach ($sql_tablecols as $tablecol) {
            $sql_where[]= "(".$tablecol." LIKE '%".$filter->_filter($search_data)."%')";
        }
        $this->sql_Product->setSQL_WHERE(" and (".implode(' or ', $sql_where).")");
        $this->sql_Product->setSQL_GROUP(" p.products_id");
        ($plugin_code = $xtPlugin->PluginCode('class.product.php:_getSearchIDs_querry')) ? eval($plugin_code) : false;
        $query = "".$this->sql_Product->getSQL_query()."";

        $record = $db->Execute($query);
        if ($record->RecordCount() > 0) {

            while(!$record->EOF){
                $records = $record->fields;
                $data[] = $records['products_id'];
                $record->MoveNext();
            } $record->Close();
        }

        ($plugin_code = $xtPlugin->PluginCode('class.product.php:_getSearchIDs_bottom')) ? eval($plugin_code) : false;
        return $data;
    }

	function _set($id, $set_type = 'edit') {

	}	
	
	function _unset($id = 0) {

     }

    public function updateOrderItem($data)
    {
        $r = new stdClass();
        $r->success = false;

        $oId = (int) $data['orders_id'];
        $opId = (int) $data['orders_products_id'];
        if ($oId && $opId)
        {
            global $db, $language, $system_status;
            $cId = $db->GetOne("SELECT customers_id FROM " . TABLE_ORDERS . " WHERE orders_id = ?", array($oId));
            $order = new order($oId, $cId);

            $oldCount = $newCount = $data['products_quantity'];
            $editLine = null;
            for ($i=0; $i<count($order->order_products); $i++)
            {
                if ($order->order_products[$i]['orders_products_id'] == $opId)
                {
                    $editLine = $order->order_products[$i];
                    $oldCount = $editLine['products_quantity'];

                    $taxRate = $db->GetOne("SELECT `tax_rate` FROM " . TABLE_TAX_RATES . " WHERE `tax_class_id` = ?", array($data['products_tax_class']));

                    $dataOverride = array(
                        'products_quantity'     => $data['products_quantity'],
                        'products_price'        => $data['products_price'],
                        'products_tax_class_id' => $editLine['products_tax_class'],
                        'products_tax'          => $taxRate,
                        'products_additional_info'     => $data['products_additional_info'],
                    );

                    $editLine = array_merge($editLine, $dataOverride);
                }
            }
            $order->_saveProductData($editLine,'update',false);

            // fix order stats
            $this->_setStats($order);
            // fix stock
            if ($oldCount != $newCount)
            {
                $stockUpdate = $newCount - $oldCount;
                $stock = new stock();
                $stock->removeStock($editLine['products_id'],$stockUpdate);
            }

            $r->success = true;
        }
        return json_encode($r);
    }

    private function _setStats($order){
        global $db;

        $tmp_order = $order; // fix no csutomer issue // new order($oID);

        $data_array = array('products_count'=>$tmp_order->order_count,
            'orders_stats_price'=>$tmp_order->order_total['total']['plain']
        );

        $check_sql = "SELECT orders_id from ".TABLE_ORDERS_STATS." where orders_id = ?";
        $rs = $db->Execute($check_sql, array((int)$tmp_order->oID));
        if ($rs->RecordCount()>0) {

            $db->AutoExecute(TABLE_ORDERS_STATS, $data_array, 'UPDATE', "orders_id=".(int)$tmp_order->oID."");

        }else{

            $insert_array = array('orders_id'=>$tmp_order->oID);
            $data_array = array_merge($data_array, $insert_array);
            $db->AutoExecute(TABLE_ORDERS_STATS, $data_array, 'INSERT');

        }
    }

    public function hook_order_buildProductData_bottom(&$product_array, $orders_id)
    {
    }
}