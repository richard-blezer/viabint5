<?php

    $display_output = false;
	$current_product_id = (int) $_GET["pID"];

    include_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_master_slave/classes/class.xt_master_slave_products.php';
	include_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_master_slave/classes/class.xt_master_slave_functions.php';
	include_once _SRV_WEBROOT . 'xtFramework/classes/class.product.php';
	
    $xt_ms = new master_slave_products();
    $xt_ms->setProductID($current_product_id);
	$data_primary = array();
	$ids = explode(",",$_GET['sected_ids']);
	$t=1;
	
	$master_model = $db->GetOne("SELECT products_master_model FROM ".TABLE_PRODUCTS." WHERE products_id=?",array((int)$current_product_id));
    if ($master_model !=''){
        $master = xt_master_slave_functions::getMasterData($master_model);
        $m_id = $master['products_id'];
    }else{
         $m_id = $current_product_id;
    }
    $m_id = $current_product_id;
	foreach($ids as $i)
	{
		if ($i!='')
		{
			$p = explode("-",$i);
			if (isset($_SESSION['xt_master_slave'][$m_id]['error'][$p[0]])){
                unset($_SESSION['xt_master_slave'][$m_id]['error'][$p[0]]);
            }
			$data[$p[0]]=$p[1];
			if ($_GET['main']==1) $total = count($ids);
			else $total = count($ids)-1;
			//if ($t<$total) $data_primary[$p[0]]=$p[1];
			if ($t==1) $data_primary[$p[0]]=$p[1];
			$t++;
			if ($_GET['main']==1) break;
		}
		
	}
	$line='';
	
	xt_master_slave_functions::unsetFilter();
	unset($_SESSION['select_ms_primary']);
	
	if (count($data)>0) $xt_ms->setFilter($data);
	if (count($data_primary)>0) $xt_ms->setFilter($data_primary,'_primary');
	
	$_GET['action_ms'] = 1;
	
	$xt_ms->getMasterSlave();
	$option_set = $xt_ms->buildOptionSet();
	$optionSet_arr_primary = $xt_ms->buildOptionSet('primary');
	$optionSet_arr = $xt_ms->buildOptionSet('all');
	$mergedOptions_arr = $xt_ms->mergeOptions($option_set, $optionSet_arr,$optionSet_arr_primary);
	
	if ($mergedOptions_arr=='')
	{
		$lat = str_replace("id[","",$_GET["latested_clicked"]);	
		$exp= explode("]",$lat);
		//echo "tuk";
		unset($_SESSION['select_ms'][$current_product_id]['id'][$exp[0]]);
		$xt_ms->getMasterSlave();
	}
	
	$options = $xt_ms->productOptions;
	
	$master_model = $xt_ms->getModel($current_product_id);
	if (isset($_SESSION['select_ms'][$current_product_id]))
	{
		$res = xt_master_slave_functions::slave_products_id($master_model, $current_product_id);
		$new_pr = $res->data;
		//var_dump($res);

if (isset($new_pr['products_id']))
{
	global $slave_product_id;
	$slave_product_id = (int) $new_pr['products_id'];
}

		if ($res=='') {$res = new product($current_product_id,'full'); $new_pr = $res->data;}
		
		if(_STORE_IMAGES_PATH_FULL == 'true'){
                $path_base_url = _SYSTEM_BASE_URL;
            }else{
                $path_base_url = '';
            }
			
		if ($res)
		{
			$p_info = $res;
			if ($new_pr['product_template']!='') {
				$tpl_new_pr=$new_pr['product_template'];
			} else {
				$tpl_new_pr='product.html';
			}
			$sm = new smarty();
	
	        //$sm->force_compile = true;
			$sm->template_dir = _SRV_WEB_TEMPLATES . _STORE_TEMPLATE.'/xtCore/pages/product';
			$sm->compile_dir = _SRV_WEBROOT.'templates_c';
			
			$sm->assign('data', $new_pr);
			foreach($new_pr as $k=>$v)
			{
				$sm->assign($k, $v);
			}
			$master_image = xt_master_slave_functions::productImage($current_product_id);
			
			$tmm= new Template;
			$selected_template = $tmm->selected_template;

            $sm->assign("selected_template", _SRV_WEBROOT.'templates/'.$selected_template);
			$sm->assign('tpl_url_path', $path_base_url._SRV_WEB._SRV_WEB_TEMPLATES.$selected_template.'/');
			if ($new_pr['products_master_flag'] != '1')
			{
				if (XT_MASTER_SLAVE_LOAD_MASTER_IMAGE_IN_SLAVE=='false')
				{
					$im = xt_master_slave_functions::slave_image($master_model, $master_image,$current_product_id,$new_pr['products_image_from_master']); 
				}
				else 
				{
					$im =  $master_image;	
				}
				$sm->assign("products_image", $im);
			}
			else $sm->assign("products_image", $master_image);
			global $xtPlugin;
            ($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave.php:ajax_mode_product_data')) ? eval($plugin_code) : false;
			$line = $sm->fetch('product.html');
		}
		
	}
	
	$a = new stdClass;
	if ($mergedOptions_arr)
	{
		$a->num=1;
		$a->content = $options;
		$a->product = $line;
	}
	else 
	{
		$a->num=0;
		$a->error = TEXT_XT_MASTER_SLAVE_NO_STOCK;
		$a->product = $line;
		$a->content = $options;
		$a->latested_clicked = $_GET["latested_clicked"];
	}
	
    echo json_encode($a);
?>
