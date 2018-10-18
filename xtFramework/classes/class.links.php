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

class xtLink
{

    var $xtLink;
    var $params;
    var $GET_PARAMS = array();
    var $link_url;
    var $secure_link_url;

    function xtLink ()
    {
        global $xtPlugin;

        $this->amp = '&amp;';
        $this->show_session_id = _RMV_SESSION;

        ($plugin_code = $xtPlugin->PluginCode('class.link.php:link_top')) ? eval($plugin_code) : false;
        if (isset($plugin_return_value))
            return $plugin_return_value;

    }

    function showSessionID ($val)
    {
        $this->show_session_id = $val;
    }

    function setLinkURL ($url = '')
    {
        $this->link_url = $url;
    }

    function setSecureLinkURL ($surl = '')
    {
        $this->secure_link_url = $surl;
    }

    function unsetLinkURL ()
    {
        unset($this->link_url);
    }

    function unsetSecureLinkURL ()
    {
        unset($this->secure_link_url);
    }

    /**
     * generate link
     *
     * @param mixed $data    array with link data
     * @param mixed $remove_dir  directory which should be removed from link (str_replace)
     * @param mixed $block_session   set to true if no session ID should be added to the generated link
     * @return string
     */
    function _link ($data, $remove_dir = '', $block_session = false)
    {
        global $xtPlugin, $page, $remove_session, $language;
        ($plugin_code = $xtPlugin->PluginCode('class.link.php:_link_top')) ? eval($plugin_code) : false;
        if (isset($plugin_return_value))
            return $plugin_return_value;

        if (empty($data['default_page'])) {
            $default_page = 'index.php';
        } else {
            $default_page = $data['default_page'];
        }

        if (empty($this->link_url)) {
            $system_http_link = _SYSTEM_BASE_HTTP;
        } else {
            $system_http_link = $this->link_url;
        }

        if (empty($this->secure_link_url)) {
            $system_https_link = _SYSTEM_BASE_HTTPS;
        } else {
            $system_https_link = $this->secure_link_url;
        }

        if (!isset($data['conn'])) $data['conn'] = 'NOSSL';
        if (($data['conn'] == 'SSL' && _SYSTEM_SSL == true) or (_SYSTEM_FULL_SSL == true)) {
            if (_SYSTEM_SSL_PROXY=='true') $link_data = $system_https_link.'/';
            else $link_data = $system_https_link . _SRV_WEB;
        } else {
            $link_data = $system_http_link . _SRV_WEB;
        }

        if ($data['seo_url']=='') $seo_url = $this->_getSeoUrl($data); // changed from ------ $seo_url = $this->_getSeoUrl($data); 
        else $seo_url = $data['seo_url']; // this is to skip the double check for seo_url
        if ($seo_url != false && $data['seo_url'] == '') {
            $data['seo_url'] = $seo_url;
        }
        // pagination links
        if (stripos($data['params'], "next_page") !== false) {
            parse_str($data['params'], $output);
            if ($output['next_page']==1) {
                $toRemove = array('next_page');
                if (stripos($data['params'], "cat=") !== false && ($data['seo_url'] != '') && (_SYSTEM_MOD_REWRITE=='true') ) {
                    $toRemove[] = 'cat';
                }
                $data['params'] = $this->clean_url_qs($data['params'], $toRemove);

            }
        }

        if ($remove_dir != '') $link_data = str_replace($remove_dir, '', $link_data);

        if ((_SYSTEM_MOD_REWRITE == 'true') && ($data['seo_url'] != '')) { // seo_url verwenden
            $link_data .= $data['seo_url'];

            if (_SYSTEM_SEO_FILE_TYPE != '') {
                $link_data = $link_data . '.' . _SYSTEM_SEO_FILE_TYPE;
            }

            if (($data['conn'] == 'SSL' && _SYSTEM_SSL == true) or (_SYSTEM_FULL_SSL == true)){

                if (!strpos($link_data, session_name())) {

                    preg_match('@^(?:https://)?([^/]+)@i', _SYSTEM_BASE_HTTPS, $treffer);
                    $https = $treffer[1];

                    preg_match('@^(?:http://)?([^/]+)@i', _SYSTEM_BASE_HTTP, $treffer);
                    $http = $treffer[1];
                    if ($https != $http) {
                        if (!preg_match('/\?/', $link_data))
                            $link_data .= "?" . session_name() . '=' . session_id();
                        else
                            $link_data .= $this->amp . session_name() . '=' . session_id();
                    }
                }

            }

            if (_RMV_SESSION == 'false' && $block_session == false) {
                if (!isset($_COOKIE[session_name()]) && !strpos($link_data, session_name())) {
                    if (!preg_match('/\?/', $link_data))
                        $link_data .= "?" . session_name() . '=' . session_id();
                    else
                        $link_data .= $this->amp . session_name() . '=' . session_id();
                }
            }
            // fix: params for seo links
            if (!empty($data['params'])) {
                if (!preg_match('/\?/', $link_data))
                    $data['params'] = '?' . $data['params'];
                else
                    $data['params'] = $this->amp . $data['params'];
            }

            $link_data .= $data['params'];
            return $link_data;

        } else if ((_SYSTEM_MOD_REWRITE == 'true') && (_SYSTEM_MOD_REWRITE_DEFAULT == 'true') && $data['page'] != 'callback') { // default SEO-URL zusammenbauen
            if ((empty($data['dl_media'])) && (empty($data['default_page'])) && (_SYSTEM_SEO_URL_LANG_BASED == 'true')) {
                if (trim($data['lang_code']) != '') {
                    $link_data .= $data['lang_code'] . '/';
                } else {
                    $link_data .= $language->code . '/';
                }
            }
            // page
            if ($data['page'] == 'dynamic') {
                $link_data .= $page->page_name;
            } else {
                if ((empty($data['page'])) && (!empty($default_page))) {
                    $link_data .= $default_page;
                } else {
                    $link_data .= $data['page'];
                }
            }

            // page_action
            if ($data['page'] == 'dynamic') {
                if (!empty($page->page_action))
                    $link_data .= '/' . $page->page_action;
            } else {
                if (!empty($data['paction']))
                    $link_data .= '/' . $data['paction'];
            }
            if ((empty($data['default_page'])) && (_SYSTEM_SEO_FILE_TYPE != '')) {
                if ($data['page']!='index')
                    $link_data .= '.' . _SYSTEM_SEO_FILE_TYPE;
            } else {
//                  $link_data .= '.html';
            }

            // wegen m?glicher weiterer Parameter
            $link_data .= '?';


//            }
        } else { // No SEO
            if (!empty($data['pos']))
                $link_data .= $data['pos'];

            // page
            if ($data['page'] == 'dynamic') {
                $link_data .= $default_page . '?page=' . $page->page_name;
            } else {
                $link_data .= $default_page . '?page=' . $data['page'];
            }

            // page_action
            if ($data['page'] == 'dynamic') {

                if (!empty($page->page_action))
                    $link_data .= $this->amp . 'page_action=' . $page->page_action;

            } else {

                if (!empty($data['paction']))
                    $link_data .= $this->amp . 'page_action=' . $data['paction'];

            }
        }

        $exclude_array = array();
        $data_exclude = array();

        if (!empty($data['params'])) {

            $data['params'] = str_replace($this->amp, '&', $data['params']);

            $data['params'] = str_replace('&', $this->amp, $data['params']);
            $data['params'] = $this->amp . $data['params'];

        }

        $link_data .= $data['params'];

        if (!empty($data['name']) && !empty($data['type']) && !empty($data['id']))
            $link_data .= $this->amp . $this->_linkTypes($data['type'], $data['id'], $data['name']);

        // add session ?
        if ($this->show_session_id == 'false' && $block_session == false) {
            if (!isset($_COOKIE[session_name()])) {
                if (!preg_match('/\?/', $link_data))
                    $link_data .= "?" . session_name() . '=' . session_id();
                else
                    $link_data .= $this->amp . session_name() . '=' . session_id();
            }
        }

        // add to SSL Link (for ssl proxies)
        if (($data['conn'] == 'SSL' && _SYSTEM_SSL == true) or (_SYSTEM_FULL_SSL == true)){
            if (!strpos($link_data, session_name())) {

                preg_match('@^(?:https://)?([^/]+)@i', _SYSTEM_BASE_HTTPS, $treffer);
                $https = $treffer[1];

                preg_match('@^(?:http://)?([^/]+)@i', _SYSTEM_BASE_HTTP, $treffer);
                $http = $treffer[1];
                if ($https != $http) {
                    if (!preg_match('/\?/', $link_data))
                        $link_data .= "?" . session_name() . '=' . session_id();
                    else
                        $link_data .= $this->amp . session_name() . '=' . session_id();
                }
            }

        }

        ($plugin_code = $xtPlugin->PluginCode('class.link.php:_link_bottom')) ? eval($plugin_code) : false;
        $link_data = preg_replace('/' . $this->amp . $this->amp . '/', $this->amp, $link_data);
        //$link_data = ereg_replace('&&', '&',$link_data);
        //__debug($link_data, 'LINK Data:');


        // UBo++
        $link_data = str_replace('?' . $this->amp, '?', $link_data);
        //$link_data = rtrim($link_data, '?'.$this->amp);
        // check for last chars in url, if ?, & or $this->amp
        if (substr($link_data, -1) == '?' or substr($link_data, -1) == '&') {
            $link_data = substr($link_data, 0, -1);
        } elseif (substr($link_data, -4) == $this->amp) {
            $link_data = substr($link_data, 0, -4);
        }
        // UBo--

        //default language -> '/'
        if(substr($link_data, -8, 8) == $language->default_language.'/index')
        {
            if(substr($link_data, -9, 1)=='.' && _SYSTEM_SEO_URL_LANG_BASED != 'true') {
                return str_replace(substr($link_data, -6, 6),'',$link_data);
            }
            else{
                return str_replace(substr($link_data, -8, 8),'',$link_data);
            }
        }
        //language box
        if(stripos($link_data, $language->default_language.'/index?action=change_lang') !== false)
        {
            return str_replace('/'.$language->default_language.'/index','/index',$link_data);
        }

        return $link_data;
    }

    function _storelink ($data)
    {
        global $xtPlugin;

        ($plugin_code = $xtPlugin->PluginCode('class.link.php:_adminlink_top')) ? eval($plugin_code) : false;
        if (isset($plugin_return_value))
            return $plugin_return_value;

        $data['pos'] = _SRV_WEB_ADMIN;

        return $this->_link($data);

    }

    function _adminlink ($data)
    {
        global $xtPlugin;

        ($plugin_code = $xtPlugin->PluginCode('class.link.php:_adminlink_top')) ? eval($plugin_code) : false;
        if (isset($plugin_return_value))
            return $plugin_return_value;

        $link = $this->_link($data, 'xtAdmin/');

        if (_SYSTEM_ADMIN_SSL) return str_replace("http://","https://",$link);
        else return $link;

    }


    function _redirect ($url,$http_response_code='')
    {
        global $xtPlugin;

        ($plugin_code = $xtPlugin->PluginCode('class.link.php:_redirect_top')) ? eval($plugin_code) : false;
        if (isset($plugin_return_value))
            return $plugin_return_value;


        $url = preg_replace('/[\r\n]+(.*)$/im', '', $url);

        $url = html_entity_decode($url);

        ($plugin_code = $xtPlugin->PluginCode('class.link.php:_redirect_bottom')) ? eval($plugin_code) : false;
        if ($http_response_code!='') header('Location: ' . $url,TRUE,$http_response_code);
        else header('Location: ' . $url);
        session_write_close();
        exit;
        // close session and exit
        //session_close();
        //exit();

    }


    function _cleanData ($data)
    {
        global $xtPlugin;

        ($plugin_code = $xtPlugin->PluginCode('class.link.php:_cleanData_top')) ? eval($plugin_code) : false;
        if (isset($plugin_return_value))
            return $plugin_return_value;

        $search_array = array('ä', 'Ä', 'ö', 'Ö', 'ü', 'Ü', '&auml;', '&Auml;', '&ouml;', '&Ouml;', '&uuml;', '&Uuml;');
        $replace_array = array('ae', 'Ae', 'oe', 'Oe', 'ue', 'Ue', 'ae', 'Ae', 'oe', 'Oe', 'ue', 'Ue');

        ($plugin_code = $xtPlugin->PluginCode('class.link.php:_cleanData_arrays')) ? eval($plugin_code) : false;

        $data = str_replace($search_array, $replace_array, $data);

        $replace_param = '/[^a-zA-Z0-9]/';
        $data = preg_replace($replace_param, '-', $data);

        ($plugin_code = $xtPlugin->PluginCode('class.link.php:_cleanData_bottom')) ? eval($plugin_code) : false;
        return $data;

    }

    function _linkTypes ($type, $id, $name)
    {
        global $xtPlugin;

        ($plugin_code = $xtPlugin->PluginCode('class.link.php:_linkTypes_top')) ? eval($plugin_code) : false;
        if (isset($plugin_return_value))
            return $plugin_return_value;

        if ($type == 'category' || $type == 'categorie' ) {
            //$name = $this->_cleanData($name);
            $link_data = 'cat=' . $id;
        }

        if ($type == 'product') {
            //$name = $this->_cleanData($name);
            $link_data = 'info=' . $id;
        }

        if ($type == 'manufacturer') {
            //$name = $this->_cleanData($name);
            $link_data = 'mnf=' . $id;
        }

        if ($type == 'content') {
            //$name = $this->_cleanData($name);
            $link_data = 'coID=' . $id;
        }


        ($plugin_code = $xtPlugin->PluginCode('class.link.php:_linkTypes_bottom')) ? eval($plugin_code) : false;
        return $link_data;
    }

    function _getParams ($exclude = '', $include = '')
    {
        global $xtPlugin;

        $data_array = array();
        $data_array = $_GET;
        reset($data_array);

        ($plugin_code = $xtPlugin->PluginCode('class.link.php:_getParams_top')) ? eval($plugin_code) : false;
        if (isset($plugin_return_value))
            return $plugin_return_value;

        // added array $include to overwrite array $default_exclude
        if (!is_array($include)) $include = array();
        if (!is_array($exclude)) $exclude = array();
        $default_exclude = array();
        $default_exclude = array('page', 'x', 'y', 'next_page', 'page_action');
        ($plugin_code = $xtPlugin->PluginCode('class.link.php:_getParams_exclude')) ? eval($plugin_code) : false;

        $exclude = array_merge($exclude, $default_exclude);

        $url = array();
        if (is_array($data_array) && (sizeof($data_array) > 0)) {
            while (list($key, $value) = each($data_array)) {

                if ((strlen($value) > 0) && (!in_array($key, $exclude) || in_array($key, $include))) {
                    $url[] = $key . '=' . urlencode($value);
                }
            }
        }

        $url = implode($this->amp, $url);

        ($plugin_code = $xtPlugin->PluginCode('class.link.php:_getParams_bottom')) ? eval($plugin_code) : false;
        return $url;
    }

    function ClearUPFromDuplicate($org_page)
    {
        global $xtLink,$seo,$db;
        if (stripos($org_page,"xtAdmin") || stripos($org_page,"cron")|| stripos($org_page,"xtInstaller") || stripos($org_page,"xtUpdater") ) return false;

        if ((_SYSTEM_MOD_REWRITE_NO_DUPLICATE_URLS=='true') && (_SYSTEM_MOD_REWRITE=='true'))
        {

            if (strpos($org_page,"sorting=")) return true; // exclude sorting option

            if ((strpos($org_page,"?")) || (strpos($org_page,"&")) )
            {
                $exp = explode("?",$org_page);
                $new_arrr= array();
                $new_ar = explode("&",$exp[1]);
                foreach($new_ar as $key=>$value)
                {
                    $second_exp= explode("=",$value);
                    if ($second_exp[0]=='page') $n=$second_exp[1];
                }

                $new_arrr= array('page'=>$n,'params'=>str_replace('page='.$n.'&',"",$exp[1]));

                $tmp_link  = $this->_getSeoUrl($new_arrr);

                if ($tmp_link!='')
                {
                    if (_SYSTEM_SEO_FILE_TYPE!='')
                    {
                        $exp = explode(".",$tmp_link);
                        if ($exp[count($exp)-1]!=_SYSTEM_SEO_FILE_TYPE) $tmp_link = $tmp_link.'.'._SYSTEM_SEO_FILE_TYPE;
                    }
                    $xtLink->_redirect($tmp_link);
                }

            }
            else if (_SYSTEM_SEO_FILE_TYPE!='')
            {
                $exp = explode(".",$org_page);
                $page_url_data = $seo->_cleanUpUrl($org_page);
                if (($exp[count($exp)-1]!=_SYSTEM_SEO_FILE_TYPE) && ($page_url_data["url_clean"]!='')) $xtLink->_redirect($org_page.'.'._SYSTEM_SEO_FILE_TYPE);
            }
            elseif (substr($org_page, -1)=='/')
            {
                $tmp_link = substr($org_page,0, -1);

                /* first check in seo table for original url with '/'. */
                $page_url_data = $seo->_cleanUpUrl($org_page);
                $clean_page = $page_url_data['url_clean'];
                $url = $seo->_UrlHash($clean_page);
                $query = "SELECT * FROM ".TABLE_SEO_URL." WHERE url_md5='".$url."' LIMIT 0,1";
                $rs = $db->CacheExecute($query);
                //if doesn't exists check for url without '/'. if exists redirect to it
                if ($rs->RecordCount()==0)
                {
                    $page_url_data = $seo->_cleanUpUrl($tmp_link);
                    $clean_page = $page_url_data['url_clean'];
                    $url = $seo->_UrlHash($clean_page);
                    $query = "SELECT * FROM ".TABLE_SEO_URL." WHERE url_md5='".$url."' LIMIT 0,1";

                    $rs = $db->CacheExecute($query);
                    if ($rs->RecordCount()>0)
                        $xtLink->_redirect($tmp_link);
                }
            }

        }
    }

    public function _getSeoUrl($data)
    {
        global $db, $page, $xtPlugin, $filter;

        $link_type = null;
        if ( ! empty($data['params']))
        {
            $params = explode('&', str_replace('&amp;', '&', $data['params']));
            $params_value = explode('=', $params[0]);
        }

        switch ($data['page'])
        {
            case 'content':
                $link_type = 3;
                if ($params_value[0] === 'coID')
                    $data['id'] = $params_value[1];
                break;
            case 'product':
                $link_type = 1;
                if ($params_value[0] === 'info')
                    $data['id'] = $params_value[1];
                break;
            case 'categorie':
                $link_type = 2;
                if ($params_value[0] === 'cat')
                    $data['id'] = $params_value[1];
                break;
            case 'manufacturers':
                if ($params_value[0] === 'mnf')
                    $data['id'] = $params_value[1];
                $link_type = 4;
                break;
            default:
                ($plugin_code = $xtPlugin->PluginCode('class.link.php:_getSeoUrl')) ? eval($plugin_code) : false;
                break;
        }

        if ( ! isset($data['id'], $link_type))
        {
            if (isset($data['plugin']))
            {
                $data['id'] = $data['plugin'];
                $link_type  = 1000;
            }
            elseif (isset($data['page'])&& !isset($link_type))
            {
                require_once _SRV_WEBROOT.'/xtFramework/classes/class.seo_plugins.php';
                $seo_plugins = new seo_plugins();
                $res = $seo_plugins->getPluginByPluginCode($data['page']);
                if ($res['plugin_id'])
                {
                    $data['id'] = $res['plugin_id'];
                    $link_type  = 1000;
                }
            }
        }

        if (empty($link_type) OR empty($data['id']))
            return false;

        $lang_code = (empty($data['lang_code']) OR ! ctype_alpha($data['lang_code']))
            ? $_SESSION['selected_language']
            : $data['lang_code'];
        $add_store_id='';
        $rs=$db->Execute("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema='"._SYSTEM_DATABASE_DATABASE."' AND table_name='".TABLE_SEO_URL."' AND COLUMN_NAME = 'store_id' ");
        if ($rs->RecordCount()>0)
        {
            $store= new multistore();
            $add_store_id=" and store_id='".$store->shop_id."'";
        }

        $record = $db->Execute('SELECT url_text FROM '.TABLE_SEO_URL." WHERE language_code = '". $filter->_char($lang_code)."' AND link_type = '".$filter->_int($link_type)."' AND link_id = '".$filter->_int($data['id'])."'".$add_store_id);
        return $record->RecordCount()
            ? $record->fields['url_text']
            : false;
    }

    public function clean_url_qs ($url_params, $qs_key)
    {
        $params_str = '';

        $params = explode('&', $url_params);
        $out_params = array();

        foreach ($params as $key => &$param) {
            list($name, $value) = explode('=', $param);
            if (in_array($name, $qs_key)) {
                unset($params[$key]);
            } else {
                $out_params[$name] = urldecode($value);
            }
        }
        $params_str = trim(http_build_query($out_params));
        // no change required
        return $params_str;
    }
    public function getCurrentUrl()
    {
        $pageURL = 'http';
        if ($_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }

        $pageURL .= "://";

        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }

        return $pageURL;
    }
}