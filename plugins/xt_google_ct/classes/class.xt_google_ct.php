<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');

class xt_google_ct {
	function _getCode() {
			if ($_GET['page']=='checkout' && $_GET['page_action']=='success' && XT_GCT_ACTIVATE=='true') {
			global $success_order;
			echo $this->_getTrackingCode();
			}                                           
	}

	function _getTrackingCode() {
		global $success_order;
        
if(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === "on")  {
            $http = 'https';
        }else{
            $http = 'http';  
        }          

        if (!is_object($success_order)) return false;
        $total_net=0;                                                                                                          
		foreach ($success_order->order_products as $key => $arr) {                                                                  
		    $total_net+=$arr['products_final_price']['plain_otax'];                                                                 
        }
    $conv_label = XT_GCT_CONVERSION_LABEL;
        if (XT_GCT_CONVERSION_LABEL != ''){
          $conv_label = XT_GCT_CONVERSION_LABEL;
        }else{
          $conv_label = 'default';
        }
        
        
//		$code .= "<!-- Google Code for Conversion Page --> \n";
//		$code .= "<script type='text/javascript'>\n";
//		$code .= "/* <![CDATA[ */\n";
//		$code .= "var google_conversion_id = ".XT_GCT_CUSTOMER_ID.";\n";
//		$code .= "var google_conversion_language = 'de';\n";
//		$code .= "var google_conversion_format = '3';\n";
//		$code .= "var google_conversion_color = 'ffffff';\n";
//		$code .= "var google_conversion_label = '".$conv_label."';\n";
//		$code .= "var google_conversion_value = 0;\n";
//		$code .= "if (".$total_net.") {\n";
//		$code .= "  google_conversion_value = ".$total_net.";\n";
//		$code .= "}\n";
//		$code .= "/* ]]> */\n";
//		$code .= "</script>\n";
//		$code .= "<script type='text/javascript' src='".$http."://www.googleadservices.com/pagead/conversion.js'>\n";
//		$code .= "</script>\n";
//		$code .= "<noscript>\n";
//		$code .= "<div style='display:inline;'>\n";
//		$code .= "<img height='1' width='1' style='border-style:none;' alt='' src='".$http."://www.googleadservices.com/pagead/conversion/".XT_GCT_CUSTOMER_ID."/?value=".$total_net."&amp;label=".$conv_label."&amp;guid=ON&amp;script=0' />\n";
//		$code .= "</div>\n";
//		$code .= "</noscript>\n";

		$code .= "<!-- Google Code for Conversion Page --> \n";
		$code .= "<script type='text/javascript'>\n";
		$code .= "dataLayer.push({";
		$code .= "event: 'transaction',\n";
		$code .= "value: ".$total_net."\n";
		$code .= "});\n";
		$code .= "</script>\n";

		return $code;
	}
}
