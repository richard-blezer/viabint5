<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');


if (XT_MASTER_SLAVE_SHOP_SEARCH=='master')
	$this->sql_products->setSQL_WHERE(" and ((p.products_master_flag = 1) || ((p.products_master_flag is NULL || p.products_master_flag=0) && (p.products_master_model is NULL || p.products_master_model='') )) ");
else if (XT_MASTER_SLAVE_SHOP_SEARCH=='slave')
	$this->sql_products->setSQL_WHERE(" and (p.products_master_flag = 0 ||  p.products_master_flag is NULL)");

?>