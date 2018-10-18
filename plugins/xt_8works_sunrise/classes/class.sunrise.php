<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**************************************
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 **************************************/

class sunrise {
	
    
    /**
    * ADD JAVASCRIPT
    */
    function javascript_include($tpl_name, $plugin_name) 
    {
        global $xtMinify;

        if (_STORE_TEMPLATE != $tpl_name)
            return false;

        if (!is_object($xtMinify))
            return false;

        $inlineJS = $this->inlineJS();
        $code = '';

        if ($inlineJS)
            $code .= $this->inlineJS();

//        if (XT_8WORKS_SUNRISE_JQUERY == 'true')
//            $xtMinify->add_resource(_SRV_WEB_TEMPLATES._STORE_TEMPLATE.'/javascript/jQuery.min.js', 90);

        if (XT_8WORKS_SUNRISE_JQUERY_PLUGINS == 'true')
            $xtMinify->add_resource(_SRV_WEB_TEMPLATES._STORE_TEMPLATE.'/javascript/jQuery.plugins.js', 100);

        if (XT_8WORKS_SUNRISE_JSS == 'true')
            $xtMinify->add_resource(_SRV_WEB_TEMPLATES._STORE_TEMPLATE.'/javascript/init.js', 110);
            
        $xtMinify->add_resource( _SRV_WEB_TEMPLATES._STORE_TEMPLATE. '/javascript/jquery.colorbox-min.js', 100);
        $xtMinify->add_resource( _SRV_WEB_TEMPLATES._STORE_TEMPLATE. '/javascript/jquery-ui-1.9.2.custom.min.js', 100);

        $xtMinify->add_resource('xtFramework/library/jquery/pikaday.css',100);
        $xtMinify->add_resource('xtFramework/library/jquery/pikaday.jquery.min.js',120);
        $xtMinify->add_resource('xtFramework/library/jquery/moments.js',140);

        echo $code;
    }
    
    
	/**
	 * ADD JAVASCRIPTinclude("viabint3.int] /plugins/xt_8works_sunrise/inc/jQuery.plugins.js");
	 */
//	function javascript_include($tpl_name,$plugin_name) {
//		
//		if (_STORE_TEMPLATE != $tpl_name)
//			return false;
//		
//		$js_path = _SYSTEM_BASE_URL._SRV_WEB.'plugins/'.$plugin_name.'/inc/';
//		$inlineJS = $this->inlineJS();
//		$code = '';
//		
//		if ($inlineJS)
//			$code .= $this->inlineJS();
//		
//		if (XT_8WORKS_SUNRISE_JQUERY == 'true')
//			$code .= '<script type="text/javascript" src="'.$js_path.'jQuery.min.js"></script>'."\n";
//		
//		if (XT_8WORKS_SUNRISE_JQUERY_PLUGINS == 'true')
//			$code .= '<script type="text/javascript" src="'.$js_path.'jQuery.plugins.js"></script>'."\n";
//		
//		if (XT_8WORKS_SUNRISE_JSS == 'true')
//			$code .= '<script type="text/javascript" src="'._SYSTEM_BASE_URL._SRV_WEB.'templates/'.$tpl_name.'/javascript/init.js"></script>'."\n";
//		
//		echo $code;
//	}

	/**
	 * INLINE JAVASCRIPT
	 */
	function inlineJS() {
		$out = '';
		$out .= '<script type="text/javascript">'."\n".'// <!-- '."\n";
		
		//cache mode
		if (defined('XT_8WORKS_SUNRISE_CACHEMODE') && XT_8WORKS_SUNRISE_CACHEMODE == 'true') {
			$out .= '	var ajaxCache = false;'."\n";
		} else {
			$out .= '	var ajaxCache = true;'."\n";
		}
		
		//lang var for startpage slider
		if (defined('TEXT_8WORKS_SUNRISE_NO_RESULT'))
			$out .= '	var SPnoResult = "'.TEXT_8WORKS_SUNRISE_NO_RESULT.'";'."\n";
		else
			$out .= '	var SPnoResult = "For this filter, there are no results.";'."\n";
		
		$out .= "\n".'// -->'."\n".'</script>'."\n";
		
		return $out;
	}
	
	/**
	 * GET COMPLETE CATEGORY ARRAY
	 */
	function get_categories($rekursiv=false, $id=0, $level=0) {
		global $category;
		
		return $category->getCategoryBox($id, $rekursiv, $level);
	}
	
	/**
	 * GET THE MAIN CATEGORIES
	 */
	function get_main_cats() {
		$get = $this->get_categories(FALSE);
		$out = array();
		
		foreach ($get as $i) {
			if ($i['level'] == 1)
				$out[] = $i;
		}
		
		return $out;
	}

	/**
	 * WALK THROUGH THE COMPLETE CATEGORY ARRAY
	 */
	function recurse_array_to_html_list($params) {
		$output = '';
		$count = count($params['array']);
		$i = 0;
		
		if (!$params['sub']) {
			if ($params['sf-menu'])
				$ul_class .= ' sf-menu';
			if ($params['sf-vertical'])
				$ul_class .= ' sf-vertical';
			$ul_class .= ' list-depth-'.$params['depth'];
			$ul_class .= ' first-ul';
			$a_class  .= ' master-link';
		} else {
			$ul_class .= ' sub-ul';
			$a_class  .= ' slave-link';
		}
		
		if (is_array($params['array']) && $count > 0) {
		
			$output .= '<ul class="catcount-'.$count.$ul_class.'">';
			
			foreach ($params['array'] as $element) {
			
				$i++;
				//current or not?
				$act = '';
				if ($element['active'] == 1)
					$act = ' current';
				//first or last child in ul list?
				$pos = '';
				if ($i == 1) {
					$pos = ' first-child';
				} elseif ($count == $i) {
					$pos = ' last-child';
				}
				//have subs?
				$sub = ''; $sub_span = '';
				if (isset($element['sub']) && isset($element['level']) && $element['level'] < $params['depth']) {
					$sub = ' sub';
					$sub_span = '<span class="sub-arrow">&nbsp;</span>';
				}
				//title
				$title = '';
				if (strip_tags($element['categories_heading_title']) != '')
					$title = htmlspecialchars(strip_tags($element['categories_heading_title']));
				else
					$title = htmlspecialchars(strip_tags($element['categories_name']));
				
				//if (1){
				if ($element['categories_id'] != 14 && $element['categories_id'] != 19 && $element['categories_id'] != 20 && $element['categories_id'] != 121 && $element['categories_id'] != 123){
				
				/* 
					categories_id != 14 => keine alpfhabetisch 
					categories_id != 19 => keine personengruppen
					categories_id != 20 => keine produktgruppen
				*/
				
    				//item html
    				$output .= '<li id="cat-id-'.$element['categories_id'].'" class="category level-'.$element['level'].' ul-item-'.$i.' lang-'.$element['language_code'].$act.$pos.$sub.'">';
    				$output .= '<a class="level-'.$element['level'].$a_class.'" href="'.$element['categories_link'].'" title="'.$title.'">';
    				$output .= '<span>'.htmlspecialchars(strip_tags($element['categories_name'])).'</span>'.$sub_span;
    				$output .= '</a>';
        }
				//loop
				if (isset($element['sub']) && isset($element['level']) && $element['level'] < $params['depth']) {
					$params['array'] = $element['sub'];
					$params['sub'] = 1;
					$output .= $this->recurse_array_to_html_list($params);
				}
				
				$output .= '</li>';
			}
			
			$output.= '</ul>';
			
			return $output;
			
		} else {
		   return 'No Array';
		}
	}
	
	/**
	 * TEASER CONVERTER (IMAGES,SHORT-DESCRIPTION,LINK,ETC.)
	 */
	function flatten_array_converter($params) {
		$output = array();
		
		foreach ($params['array'] as $element) {
			if ($element['sub'] && $element['level'] && $element['level'] < $params['depth']) {
				$params['array'] = $element['sub'];
				unset($element['sub']);
				$output[] = $element;
				$output = array_merge($output,$this->flatten_array_converter($params));
			} else {
				$output[] = $element;
			}
		}
		
		return $output;
	}
	
	/**
	 * SORT BY SPECIFIC KEY
	 */
	function array_sort($array, $on, $order=SORT_ASC) {
		$new_array = array();
		$sortable_array = array();
		
		if (count($array) > 0) {
			foreach ($array as $k => $v) {
				if (is_array($v)) {
					foreach ($v as $k2 => $v2) {
						if ($k2 == $on) {
							$sortable_array[$k] = $v2;
						}
					}
				} else {
					$sortable_array[$k] = $v;
				}
			}
			switch ($order) {
				case SORT_ASC:
					asort($sortable_array);
				break;
				case SORT_DESC:
					arsort($sortable_array);
				break;
			}
			foreach ($sortable_array as $k => $v) {
				$new_array[$k] = $array[$k];
			}
		}
		return $new_array;
	}
	
	//check plugin status
	function check_plugin_status($plugin_code) {
		global $xtPlugin;
		
		if (isset($xtPlugin->active_modules[$plugin_code])) {
			return true;
		} else {
			return false;
		}
	}
		
	//teaser
	function get_teaser() {
		$cats_data = $this->flatten_array_converter(array(
			'array' => $this->get_categories(TRUE),
			'depth' => XT_8WORKS_SUNRISE_CATEGORY_LEVEL
		));
		
		$teaser_data = array();
		foreach ($cats_data as $item_data) {
			$teaser_data[] = array(
				'id' => $item_data['categories_id'],
				'status' => $item_data['teaser_status'],
				'sort' => $item_data['teaser_sort'],
				'language' => $item_data['language_code'],
				'name' => strip_tags($item_data['categories_name']),
				'title' => strip_tags($item_data['categories_heading_title']),
				'description' => strip_tags($item_data['categories_description']),
				'image' => $item_data['categories_image'],
				'link' => $item_data['categories_link']
			);
		}
		$output = $this->array_sort($teaser_data,'sort',SORT_ASC);
		
		return $output;
	}
	
	//special nav
	function get_category_nav() {
		return $this->recurse_array_to_html_list(array(
			'array' => $this->get_main_cats(),
			'depth' => XT_8WORKS_SUNRISE_CATEGORY_LEVEL,
			'sf-menu' => TRUE,
			'sf-vertical' => FALSE
		));
	}
	function get_all_category_level_nav() {
		return $this->recurse_array_to_html_list(array(
			'array' => $this->get_categories(TRUE),
			'depth' => XT_8WORKS_SUNRISE_CATEGORY_LEVEL,
			'sf-menu' => TRUE,
			'sf-vertical' => FALSE
		));
	}
	
	//overview
	function get_categories_page_list() {
		return $this->recurse_array_to_html_list(array(
			'array' => $this->get_categories(TRUE),
			'depth' => XT_8WORKS_SUNRISE_CATEGORY_LEVEL,
			'sf-menu' => FALSE,
			'sf-vertical' => FALSE
		));
	}
	
	//copyright
	function get_design() {
		$output = '';
		
		$output .= '<div class="copyright designer-note">';
		$output .= '<a href="http://www.8works.de/webdesign/veyton-templates.html" title="Veyton Templates" target="_blank">Premium Veyton Templates</a>';
		$output .= ' by ';
		$output .= '<a href="http://www.8works.de" title="Internetagentur" target="_blank">Internetagentur 8works</a>';
		$output .= '</div>';
		
		return $output;
	}
	
	//content
	function get_content_id($name) {
		global $_content;
		
		$blocks = $_content->BLOCKS;
		if (in_array($name,$blocks)) {
			$id = array_keys($blocks, $name);
			return $id[0];
		} else {
			return false;
		}
	}
	
	//ie checker
	function is_old_ie() {
		$browser = $_SERVER['HTTP_USER_AGENT'];
		if (false !== strpos($browser, 'MSIE 5') || false !== strpos($browser, 'MSIE 6') || false !== strpos($browser, 'MSIE 7')) {
			return true;
		} else {
			return false;
		}
	}
	
	//ie checker (V6)
	function is_old_ie6() {
		$browser = $_SERVER['HTTP_USER_AGENT'];
		if (false !== strpos($browser, 'MSIE 5') || false !== strpos($browser, 'MSIE 6')) {
			return true;
		} else {
			return false;
		}
	}
	
	//ie checker output
	function check_ie_version() {
		$output = false;
		$cookie = $_COOKIE['MSIE_V_ALERT'];
		$browser = $_SERVER['HTTP_USER_AGENT'];
		
		if (!$cookie && XT_8WORKS_SUNRISE_SHOW_MSIE_ALERT == 'true' && $this->is_old_ie()) {
			$output .= '<div id="msie-alert">';
			$output .= '<span class="info-head"><span>' . TEXT_8WORKS_SUNRISE_INFO . '</span></span> ';
			$output .= '<span class="update-txt">';
			$output .= TEXT_8WORKS_SUNRISE_MSIE_ALERT_1;
			$output .= '<a class="msie" href="http://www.microsoft.com/windows/internet-explorer/" target="_blank">Internet Explorer</a> ';
			$output .= TEXT_8WORKS_SUNRISE_MSIE_ALERT_2;
			$output .= '<a href="http://browsehappy.com/" target="_blank">Browse Happy</a>.';
			$output .= '</span>';
			$output .= '<br /><button onclick="this.blur();">' . TEXT_8WORKS_SUNRISE_REJECT . '</button>';
			$output .= '</div>';
		}
		
		return $output;
	}
	
	/**
	 * CLEAR SMARTY CACHE
	 */
	function clearCache() {
		$smarty = new smarty();
		
		return $smarty->clear_all_cache();
	}
	
	/**
	 * DELETE CACHE WITH LINK
	 */
	function reloadCacheOnce() {		
		if (!isset($_GET['admin_delete_cache']))
			return false;
			
		global $xtLink;
		
		$err = 'Pr&uuml;fen Sie Ihren Browser und die Berechtigungen! Cache wurde nicht gel&ouml;scht!';
		
		if (!isset($_SERVER['HTTP_REFERER']) && !isset($_SERVER["SERVER_NAME"])) {
			echo 'ERROR(1): '.$err;
			return false;
		}
		
		$ref = parse_url(htmlspecialchars($_SERVER['HTTP_REFERER']));
		$host = htmlspecialchars($_SERVER["SERVER_NAME"]);
		
		if ($ref['host'] == $host && preg_match('~xtAdmin/(.*).php~', $ref['path'])) {
			$man = $this->clearCache();
			if ($man == 1) {
				//$xtLink->_redirect($xtLink->_link(array('page'=>'index','cache_success'=>'true')));
				echo '<div id="cache-success"><ul class="info_success"><li class="infoSuccess">'.TEXT_XT_8WORKS_CACHE_RELOAD_SUCCESS.'</li></ul></div>';
			} else {
				echo 'ERROR(3): '.$err;
			}
		} else {
			echo 'ERROR: '.$err;
		}
	}

}

?>