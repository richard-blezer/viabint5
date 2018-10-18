<?php
/**
	Javascripte mit freundlicher Genehmigung von ShopHostX.
*/

defined('_VALID_CALL') or die('Direct Access is not allowed.');

class google_analytics_async {

	function _getHeaderCode()
	{
		$js='<script type="text/javascript">
		     var _gaq = _gaq || [];';
		  if(XT_GOOGLE_ANALYTICS_ASYNC_ANON=='true')
		  {
			$js.='_gaq.push ([\'_gat._anonymizeIp\']);'."\n";
		  }
		$js.='
			    (function() {
			      var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
			      ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
			      var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
			      })();
		    </script>
		    ';
		
		return $js;
	}
	
	function _getCode() {

		if (XT_GOOGLE_ANALYTICS_ASYNC_UA!='') {
			if ($_GET['page']=='checkout' && $_GET['page_action']=='success') {
				if (XT_GOOGLE_ANALYTICS_ASYNC_ECOM=='true') {
					global $success_order;
					echo $this->_getEcommerceCode();
				} else {
					echo $this->_getStandardCode();
				}

			} else {
				echo $this->_getStandardCode();
			}
		}

	}

	function _getStandardCode()
	{
		$js= '<script type="text/javascript">
			if($.mobile)
			{
				$(\'[data-role=page]\').live(\'pageshow\', function (event, ui) {
					try 
					{
						_gaq.push([\'_setAccount\', \''.XT_GOOGLE_ANALYTICS_ASYNC_UA.'\']);
				
						hash = location.hash;

						if (hash)
						{
						    _gaq.push([\'_trackPageview\', hash.substr(1)]);
						}
						else
						{
						    _gaq.push([\'_trackPageview\']);
						}
					} 
					catch(err)
					{
					}
				});
			}
			
			else
			{
				_gaq.push([\'_setAccount\', \''.XT_GOOGLE_ANALYTICS_ASYNC_UA.'\']);
				_gaq.push([\'_trackPageview\']);
			}
		</script>';
		
		return $js;
	}


	function _getEcommerceCode() {
		global $db, $language, $success_order;

		$js= '<script type="text/javascript">
			if($.mobile)
			{
				$(\'[data-role=page]\').live(\'pageshow\', function (event, ui) {
					try 
					{
						_gaq.push([\'_setAccount\', \''.XT_GOOGLE_ANALYTICS_UA.'\']);
						_gaq.push([\'_trackPageview\']);';
						
		$query = "SELECT shop_title FROM xt_stores WHERE shop_id='" . $success_order->order_data['shop_id'] . "' LIMIT 0,1";
		$rs = $db->Execute($query);
		$tax = $success_order->order_total['total']['plain']-$success_order->order_total['total_otax']['plain'];
		
		$js.='				_gaq.push([\'_addTrans\',
						\''.$success_order->order_data['orders_id'].'\',
						\''.$rs->fields['shop_title'].'\',
						\''.$success_order->order_total['total']['plain'].'\',
						\''.round($tax, 2).'\',
						\''.round($success_order->order_total_data[0]['orders_total_price']['plain'], 2) .'\',
						\''.$success_order->order_data['delivery_city'].'\',
						\''.$success_order->order_data['delivery_postcode'].'\',
						\''.$success_order->order_data['delivery_country_code'].'\']);
		';
		 
		// add products
		foreach ($success_order->order_products as $key => $arr)
		{
			$query = "SELECT categories_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE products_id='" . $arr['products_id'] . "' LIMIT 0,1";
			$rs = $db->Execute($query);
		 
			$query = "SELECT categories_name FROM ".TABLE_CATEGORIES_DESCRIPTION." WHERE categories_id='" . $rs->fields['categories_id'] . "' and language_code = '" . $language->code . "' LIMIT 0,1";
			$rs = $db->Execute($query);
		 
			$js.='			_gaq.push([\'_addItem\',
						\''.$success_order->order_data['orders_id'].'\',
						\''.$arr['products_id'].'\',
						\''.addslashes($arr['products_name']).'\',
						\''.$rs->fields['categories_name'].'\',
						\''.$arr['products_price']['plain'].'\',
						\''.$arr['products_quantity'].'\']);
			';
		}
		 
		$js.='				_gaq.push([\'_trackTrans\']);';
		
		$js.='			}
					catch(err)
					{
					}
				});
			}
			else
			{
				_gaq.push([\'_setAccount\', \''.XT_GOOGLE_ANALYTICS_UA.'\']);
						_gaq.push([\'_trackPageview\']);';
						
		$query = "SELECT shop_title FROM xt_stores WHERE shop_id='" . $success_order->order_data['shop_id'] . "' LIMIT 0,1";
		$rs = $db->Execute($query);
		$tax = $success_order->order_total['total']['plain']-$success_order->order_total['total_otax']['plain'];
		
		$js.='				_gaq.push([\'_addTrans\',
						\''.$success_order->order_data['orders_id'].'\',
						\''.$rs->fields['shop_title'].'\',
						\''.$success_order->order_total['total']['plain'].'\',
						\''.round($tax, 2).'\',
						\''.round($success_order->order_total_data[0]['orders_total_price']['plain'], 2) .'\',
						\''.$success_order->order_data['delivery_city'].'\',
						\''.$success_order->order_data['delivery_postcode'].'\',
						\''.$success_order->order_data['delivery_country_code'].'\']);
		';
		 
		// add products
		foreach ($success_order->order_products as $key => $arr)
		{
			$query = "SELECT categories_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE products_id='" . $arr['products_id'] . "' LIMIT 0,1";
			$rs = $db->Execute($query);
		 
			$query = "SELECT categories_name FROM ".TABLE_CATEGORIES_DESCRIPTION." WHERE categories_id='" . $rs->fields['categories_id'] . "' and language_code = '" . $language->code . "' LIMIT 0,1";
			$rs = $db->Execute($query);
		 
			$js.='			_gaq.push([\'_addItem\',
						\''.$success_order->order_data['orders_id'].'\',
						\''.$arr['products_id'].'\',
						\''.addslashes($arr['products_name']).'\',
						\''.$rs->fields['categories_name'].'\',
						\''.$arr['products_price']['plain'].'\',
						\''.$arr['products_quantity'].'\']);
			';
		}
		 
		$js.='				_gaq.push([\'_trackTrans\']);';
		$js.='	}
			';
		$js.='</script>';
		
		return $js;
	}
}
?>