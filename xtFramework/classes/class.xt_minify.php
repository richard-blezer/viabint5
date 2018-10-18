<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Mario Zanier
 * Date: 06.02.13
 * Time: 14:40
 * (c) Mario Zanier, mzanier@xt-commerce.com
 */

require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/Minify/Minify.php';
require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/Minify/Minify/Controller/MinApp.php';

class xt_minify
{

    var $resources;
    var $debugger = true;
    var $debug_flag = true;
    var $css_cache_time = _SYSTEM_CSS_CACHE_TIME;
    var $js_cache_time = _SYSTEM_JS_CACHE_TIME;
    var $css_minify_option = _SYSTEM_CSS_MINIFY_OPTION; // minifymerge, merge, single
    var $js_minify_option = _SYSTEM_JS_MINIFY_OPTION; // minifymerge, merge, single
    var $css_file='style';
    var $js_file='javascript';

    /**
     *
     * add js/css resource to stack
     *
     * @param $file filename including directory
     * @param $sort_order sort order
     */
    public function add_resource($file,$sort_order) {
        $type='js';
        if (substr ( $file, -4 )=='.css') $type='css';
        $this->resources[$type][]=array('sort_order'=>$sort_order,'source'=>$file,'type'=>$type);
    }

    /**
     * read file content
     *
     * @param $file
     * @return string
     */
    private function getFileContent($file) {

        $handle = fopen($file, "r");
        $contents = fread($handle, filesize($file));
	fclose($handle);
        return $contents;
    }

    private function save_cache_file($feed, $filename)
    {
        $feedFile = fopen('cache/'.$filename, "w+");
        if ($feedFile) {
            fputs($feedFile, $feed);
            fclose($feedFile);

        } else {
            echo "<br /><b>Error creating css cache file, please check write permissions for cache.</b><br />";
        }
    }

    /**
     * generate css/js html tags
     *
     */
    public function serveFile() {

        $this->serveCSS();
        $this->serveJS();

    }

	private function checkCacheFile($file,$file_type='css') {
		
		$expires = $this->{$file_type.'_cache_time'};
		if (!isset($expires)) {echo "Not set"; $expires = 3600;}
		
        if (file_exists($file) AND (time() - filemtime($file) < (int)$expires)) {
            return true;
        }
        return false;

    }
	
	function sortResources (&$array, $key) {
	    $sorter=array();
	    $ret=array();
	    reset($array);
	    foreach ($array as $ii => $va) {
	        $sorter[$ii]=$va[$key];
	    }
	    asort($sorter);
	    foreach ($sorter as $ii => $va) {
	        $ret[$ii]=$array[$ii];
	    }
	    $array=$ret;
	}
	
    private function serveCSS() {
        global $store_handler,$client_detect,$xtPlugin;

        $filename = $this->css_file.'_'.$store_handler->shop_id._STORE_TEMPLATE.'.css';
		$this->sortResources($this->resources['css'],'sort_order');
		
		($plugin_code = $xtPlugin->PluginCode('class.xt_minify.php:serveCSS_top')) ? eval($plugin_code) : false;
		
        if ($this->css_minify_option=='minifymerge') {

            if (!$this->checkCacheFile('cache/'.$filename,'css')) {

            require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/Minify/Minify/CSS.php';
            $file_content=array();
            foreach ($this->resources['css'] as $key => $arr) {
                if (file_exists($arr['source'])) {
                    $content = $this->getFileContent($arr['source']);

                    // set current dir
                    $option['currentDir']=dirname ($arr['source']);

                    $content = Minify_CSS::minify($content,$option);
                    $file_content[]=$content;
                }
            }
            $file_content=implode("",$file_content);
            $this->save_cache_file($file_content,$filename);

            }

            $filetime= filemtime ( 'cache/'.$filename);

            $media = '';
            if ($client_detect->mobile == true or $client_detect->tablet==true) $media = 'media="screen"';
            echo '<link rel="stylesheet" type="text/css" href="'._SYSTEM_BASE_URL._SRV_WEB.'cache/'.$filename.'?'.md5($filetime).'" '.$media.'/>'."\n";

        } elseif ($this->css_minify_option=='merge') {

            $file_content='';
            foreach ($this->resources['css'] as $key => $arr) {
                if (file_exists($arr['source'])) {
                    $content = $this->getFileContent($arr['source']);
                    $file_content.=$content;
                }
            }
            $this->save_cache_file($file_content,$filename);

            $filetime= filemtime ( 'cache/'.$filename);

            echo '<link rel="stylesheet" type="text/css" href="'._SYSTEM_BASE_URL._SRV_WEB.'cache/'.$filename.'?'.md5($filetime).'" />'."\n";

        } elseif ($this->css_minify_option=='single') {

            foreach ($this->resources['css'] as $key => $arr) {
                if (file_exists($arr['source'])) {
                    echo '<link rel="stylesheet" type="text/css" href="'._SYSTEM_BASE_URL._SRV_WEB.$arr['source'].'" />'."\n";
                }
            }

        }

    }

    private function serveJS() {
        global $store_handler,$xtPlugin;

        $filename = $this->js_file.'_'.$store_handler->shop_id._STORE_TEMPLATE.'.js';
		$this->sortResources($this->resources['js'],'sort_order');
        // js minify

		$type = '';
		if(_STORE_META_DOCTYPE_HTML != "html5"){
			$type = 'type="text/javascript" ';
		}
		($plugin_code = $xtPlugin->PluginCode('class.xt_minify.php:serveJS_top')) ? eval($plugin_code) : false;
		
        if ($this->js_minify_option=='minifymerge') {

            if (!$this->checkCacheFile('cache/'.$filename,'js')) {


            require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/Minify/JSMinPlus.php';
            $file_content=array();
            foreach ($this->resources['js'] as $key => $arr) {
                if (file_exists($arr['source'])) {
                    $content = $this->getFileContent($arr['source']);

                    // set current dir
                    $option['currentDir']=dirname ($arr['source']);

                    $content = JSMinPlus::minify($content,basename($arr['source']));
                    $file_content[]=$content;
                }
            }
            $file_content = implode("\n;",$file_content);

            $this->save_cache_file($file_content,$filename);
        }
            $filetime= filemtime ( 'cache/'.$filename);

            echo '<script '.$type.'src="'._SYSTEM_BASE_URL._SRV_WEB.'cache/'.$filename.'?'.md5($filetime).'"></script>'."\n";


        } elseif($this->js_minify_option=='merge') {

            $file_content=array();
            foreach ($this->resources['js'] as $key => $arr) {
                if (file_exists($arr['source'])) {
                    $content = $this->getFileContent($arr['source']);
                    $file_content[]=$content;
                }
            }
            $file_content = implode("\n;",$file_content);
            $this->save_cache_file($file_content,$filename);

            $filetime= filemtime ( 'cache/'.$filename);

            echo '<script '.$type.'src="'._SYSTEM_BASE_URL._SRV_WEB.'cache/'.$filename.'?'.md5($filetime).'"></script>'."\n";



        } elseif($this->js_minify_option=='single') {

            foreach ($this->resources['js'] as $key => $arr) {
                if (file_exists($arr['source'])) {
                    echo '<script '.$type.'src="'._SYSTEM_BASE_URL._SRV_WEB.$arr['source'].'"></script>'."\n";

                } else {
                    echo 'dont exists:'.$arr['source'].'<br>';
                }
            }
        }

    }




}

?>
