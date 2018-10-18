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

class language_content
{

    var $default_language = _STORE_LANGUAGE;

    protected $_table = TABLE_LANGUAGE_CONTENT;
    protected $_table_lang = null;
    protected $_table_seo = null;
    protected $_master_key = 'language_content_id';

    function _buildData ($id)
    {
        global $db, $xtPlugin, $store_handler;

        ($plugin_code = $xtPlugin->PluginCode('class.language_content.php:_buildData_top')) ? eval($plugin_code) : false;
        if (isset($plugin_return_value))
            return $plugin_return_value;

        $record = $db->CacheExecute(
            _CACHETIME_LANGUAGE_CONTENT, "SELECT * FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_content_id = ?",
            array($id)
        );

        if ($record->RecordCount() > 0) {
            while (!$record->EOF) {
                $data = $record->fields;
                $record->MoveNext();
            }
            $record->Close();
            ($plugin_code = $xtPlugin->PluginCode('class.language_content.php:_buildData_bottom')) ? eval($plugin_code) : false;
            return $data;
        } else {
            return false;
        }
    }


    function _getLanguageContentList ($list_type = 'store')
    {
        global $db, $xtPlugin, $store_handler;
        $qry_where = '';
        ($plugin_code = $xtPlugin->PluginCode('class.language_content.php:_getLanguagelist_top')) ? eval($plugin_code) : false;
        if (isset($plugin_return_value))
            return $plugin_return_value;

        ($plugin_code = $xtPlugin->PluginCode('class.language_content.php:_getLanguagelist_qry')) ? eval($plugin_code) : false;

        $record = $db->CacheExecute("SELECT * FROM " . TABLE_LANGUAGE_CONTENT . " " . $qry_where . "");
        while (!$record->EOF) {
            $data[] = $record->fields;
            $record->MoveNext();
        }
        $record->Close();

        ($plugin_code = $xtPlugin->PluginCode('class.language_content.php:_getLanguagelist_bottom')) ? eval($plugin_code) : false;
        return $data;
    }

    function _getLanguageContent ($class)
    {
        global $db, $xtPlugin;

        ($plugin_code = $xtPlugin->PluginCode('class.language.php:_getLanguageContent_top')) ? eval($plugin_code) : false;
        if (isset($plugin_return_value))
            return $plugin_return_value;

        _buildDefine($db, TABLE_LANGUAGE_CONTENT, 'language_key', 'language_value', 'language_code=\'' . $this->environment_language . '\' and (class = \'' . $class . '\' or class=\'both\')');
    }

    function _importYML ($file, $code)
    {
        global $db;

        if (!file_exists($file)) return;

        $lines = file($file);

        // load language definitions
        $definitions = array();
        $rs = $db->Execute(
            "SELECT `language_content_id`, `language_key`, `readonly`, `class` FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_code=?",
            array($code)
        );
        if ($rs->RecordCount() > 0) {
            while (!$rs->EOF) {
                $definitions[$rs->fields['language_key'] . $rs->fields['class']] = $rs->fields;
                $rs->MoveNext();
            }
        }

        foreach ($lines as $line_num => $line) {
        	$delimiterPos = strpos($line, '=', 0);
        	
        	if ($delimiterPos === false) {
        		continue;
        	}
        	$systemPart = substr($line, 0, $delimiterPos);
        	$value = substr($line, $delimiterPos+1);
        	list($plugin, $class, $key) = explode('.', $systemPart);
        	
			$insert_data = array();
			$insert_data['language_key'] = $key;
			$insert_data['language_code'] = $code;
			$insert_data['language_value'] = trim(str_replace("\n", '', $value));
			$insert_data['class'] = $class;
			$insert_data['plugin_key'] = $plugin;
			$insert_data['translated'] = '1';
			$insert_data['readonly'] = '0';
			
			// If there is no translation or translation is not readonly
			if (!isset($definitions[$key . $class])) {
				$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $insert_data);
			} else if (!$definitions[$key . $class]['readonly']) {
				$update = array();
				$update['language_value'] = trim(str_replace("\n", '', $value));
				$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $update, "UPDATE", "`language_content_id`='" . (int)$definitions[$key . $class]['language_content_id']. "'");
			}
        }

        // now get untranslated definitions and insert //TODO check if EN is existing  
        $sql = "SELECT * FROM " . TABLE_LANGUAGE_CONTENT . " a WHERE a.language_code='en' and a.language_key NOT IN (SELECT language_key FROM " . TABLE_LANGUAGE_CONTENT . " b WHERE b.language_code=?)";
        $rs = $db->Execute($sql, array($code));
        if ($rs->RecordCount() > 0) {
            while (!$rs->EOF) {
                $insert_data = array();
                $insert_data['language_key'] = $rs->fields['language_key'];
                $insert_data['language_code'] = $code;
                $insert_data['language_value'] = $rs->fields['language_value'];
                $insert_data['class'] = $rs->fields['class'];
                $insert_data['plugin_key'] = $rs->fields['plugin_key'];
                $insert_data['translated'] = '0';
                $db->AutoExecute(TABLE_LANGUAGE_CONTENT, $insert_data);
                $rs->MoveNext();
            }
        }
    }

    function _importXML ($file, $code, $replace = false)
    {
        global $db;

        if (!file_exists($file)) return;

        $xml = file_get_contents($file);
        $xml_data = XML_unserialize($xml);
        //     debugbreak();

        // load language definitions
        $definitions = array();
        $rs = $db->Execute(
            "SELECT language_key FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_code=?",
            array($code)
        );
        if ($rs->RecordCount() > 0) {
            while (!$rs->EOF) {
                $definitions[$rs->fields['language_key']] = '1';
                $rs->MoveNext();
            }
        }

        foreach ($xml_data['xtcommerce_language']['phrase'] as $key => $val) {
            if (!isset($definitions[$val['language_key']])) { // key not existing
                $insert_data = array();
                $insert_data['language_key'] = $val['language_key'];
                $insert_data['language_code'] = $code;
                $insert_data['language_value'] = trim($val['language_value']);
                $insert_data['class'] = $val['class'];
                $insert_data['plugin_key'] = $val['plugin_key'];
                $insert_data['translated'] = '1';
                $db->AutoExecute(TABLE_LANGUAGE_CONTENT, $insert_data);
            }
        }

        // now get untranslated definitions and insert //TODO check if EN is existing  
        $sql = "SELECT * FROM " . TABLE_LANGUAGE_CONTENT . " a WHERE a.language_code='en' and a.language_key NOT IN (SELECT language_key FROM " . TABLE_LANGUAGE_CONTENT . " b WHERE b.language_code=?)";
        $rs = $db->Execute($sql, array($code));
        if ($rs->RecordCount() > 0) {
            while (!$rs->EOF) {
                $insert_data = array();
                $insert_data['language_key'] = $rs->fields['language_key'];
                $insert_data['language_code'] = $code;
                $insert_data['language_value'] = $rs->fields['language_value'];
                $insert_data['class'] = $rs->fields['class'];
                $insert_data['plugin_key'] = $rs->fields['plugin_key'];
                $insert_data['translated'] = '0';
                $db->AutoExecute(TABLE_LANGUAGE_CONTENT, $insert_data);
                $rs->MoveNext();
            }
        }

        return true;
    }

    function _exportYML ($id, $type = 'all')
    {
        global $db;

        $id = (int)$id;

        $sql = "SELECT * FROM " . TABLE_LANGUAGES . " WHERE languages_id = ?";
        $rs = $db->Execute($sql, array($id));

        $code = $rs->fields['code'];

        if ($rs->RecordCount() == 1) {

            $file = $code . '_content.yml';
            if ($type == 'untranslated') $file = 'untranslated_' . $file;
            $fp = fopen(_SRV_WEBROOT . 'export/' . $file, 'w');

            // phrases
            $data = array();
            $sql = "SELECT * FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_code = ?";
            if ($type == 'untranslated') $sql .= " and translated='0'";
            $rs = $db->Execute($sql, array($code));
            if ($rs->RecordCount() > 0) {
                while (!$rs->EOF) {

                    $string = str_replace(chr(13), " ", $rs->fields['language_value']);
                    $string = preg_replace('/[\r\t\n]/', '', $string);
                    $key = $rs->fields['plugin_key'];
                    if ($key == 'NULL') $key = '';
                    $line = $key . '.' . $rs->fields['class'] . '.' . $rs->fields['language_key'] . '=' . $string . "\n";
                    fputs($fp, $line);

                    $rs->MoveNext();
                }
            }

            fclose($fp);
        } else {
            return;
        }
    }

    /**
     * export language definitions as xml files
     *
     * @param mixed $id
     * @param mixed $type  all,untranslated
     */
    function _exportXML ($id, $type = 'all')
    {
        global $db;

        include_once _SRV_WEBROOT . 'xtFramework/library/phpxml/xml.php';
        $id = (int)$id;

        $sql = "SELECT * FROM " . TABLE_LANGUAGES . " WHERE languages_id = ?";
        $rs = $db->Execute($sql, array($id));

        $code = $rs->fields['code'];

        if ($rs->RecordCount() == 1) {
            $data = array();
            $data['xtcommerce_language']['name'] = $rs->fields['name'];
            $data['xtcommerce_language']['code'] = $rs->fields['code'];
            $data['xtcommerce_language']['image'] = $rs->fields['image'];
            $data['xtcommerce_language']['sort_order'] = $rs->fields['sort_order'];
            $data['xtcommerce_language']['language_charset'] = $rs->fields['language_charset'];
            $data['xtcommerce_language']['default_currency'] = $rs->fields['default_currency'];
            $data['xtcommerce_language']['font'] = $rs->fields['font'];
            $data['xtcommerce_language']['font_size'] = $rs->fields['font_size'];
            $data['xtcommerce_language']['font_position'] = $rs->fields['font_position'];
            $data['xtcommerce_language']['setlocale'] = $rs->fields['setlocale'];
            $data['xtcommerce_language']['translated'] = $rs->fields['translated'];

            $xml = XML_serialize($data);
            $file = $code . '.xml';
            if ($type == 'untranslated') $file = 'untranslated_' . $file;
            $fp = fopen(_SRV_WEBROOT . 'export/' . $file, 'w');
            fputs($fp, $xml);
            fclose($fp);

            // phrases
            $data = array();
            $sql = "SELECT * FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_code = ?";
            if ($type == 'untranslated') $sql .= " and translated='0'";
            $rs = $db->Execute($sql, array($code));
            if ($rs->RecordCount() > 0) {
                while (!$rs->EOF) {
                    $data['xtcommerce_language']['phrase'][] = array('language_key' => $rs->fields['language_key'], 'language_value' => $rs->fields['language_value'], 'class' => $rs->fields['class'], 'plugin_key' => $rs->fields['plugin_key']);
                    $rs->MoveNext();
                }
            }

            $xml = XML_serialize($data);
            $file = $code . '_content.xml';
            if ($type == 'untranslated') $file = 'untranslated_' . $file;
            $fp = fopen(_SRV_WEBROOT . 'export/' . $file, 'w');
            fputs($fp, $xml);
            fclose($fp);
        } else {
            return;
        }

    }

    function setPosition ($position)
    {
        $this->position = $position;
    }

    function _getParams ()
    {
        $params = array();

        $header['language_code'] = array(
            'type' => 'dropdown', // you can modyfy the auto type
            'url' => 'DropdownData.php?get=language_codes'
        );
        $header['class'] = array(
            'type' => 'dropdown', // you can modyfy the auto type
            'url' => 'DropdownData.php?get=language_classes'
        );

        $header['language_value'] = array('type' => 'textarea');

        $header['language_content_id'] = array('type' => 'hidden');
        $header['readonly'] = array('type' => 'status');

        $params['header'] = $header;
        $params['master_key'] = $this->_master_key;
        $params['default_sort'] = 'language_key';
        $params['languageTab'] = 0;
        $params['PageSize'] = 50;

        $params['include'] = array('language_content_id', 'language_code', 'language_key', 'language_value', 'class', 'translated', 'readonly');
        $params['exclude'] = array('');
        $params['display_searchPanel'] = true;

        return $params;
    }

    function _getSearchIDs ($search_data)
    {
        global $filter;

        $sql_tablecols = array('language_key',
            'language_value'
        );

        foreach ($sql_tablecols as $tablecol) {
            $sql_where[] = "(" . $tablecol . " LIKE '%" . $filter->_filter($search_data) . "%')";
        }

        if (is_array($sql_where)) {
            $sql_data_array = " (" . implode(' or ', $sql_where) . ")";
        }

        return $sql_data_array;
    }


    function _get ($ID = 0)
    {
        global $xtPlugin, $db, $language;
		$obj = new stdClass;
        if ($this->position != 'admin') return false;

        $where = '';

        if ($ID === 'new') {
            $obj = $this->_set(array(), 'new');
            $ID = $obj->new_id;
        }

        $ID = (int)$ID;

        if ($this->url_data['query']) {
            $sql_where = $this->_getSearchIDs($this->url_data['query']);
            $where .= $sql_where;
        }

        if (!$ID && !isset($this->sql_limit)) {
            $this->sql_limit = "0,25";
        }

        $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $where, $this->sql_limit);
        if ($this->url_data['get_data']) {
            $data = $table_data->getData();
        } elseif ($ID) {
            $data = $table_data->getData($ID);
        } else {
            $data = $table_data->getHeader();
        }

        if ($table_data->_total_count != 0 || !$table_data->_total_count)
            $count_data = $table_data->_total_count;
        else
            $count_data = count($data);

        $obj->totalCount = $count_data;
        $obj->data = $data;

        return $obj;
    }

    function _set ($data, $set_type = 'edit')
    {
        global $db, $language, $filter;

        $db->Execute("DELETE FROM " . $this->_table . " WHERE language_key = '' and class='' ");

        $obj = new stdClass;
        $o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
        $obj = $o->saveDataSet();

        unset($_SESSION['debug'][$data['language_key']]);

        return $obj;
    }

    function _unset ($id = 0)
    {
        global $db;

        if ($id == 0) return false;
        if ($this->position != 'admin') return false;
        $id = (int)$id;
        if (!is_int($id)) return false;

        $db->Execute("DELETE FROM " . $this->_table . " WHERE " . $this->_master_key . " = ?", array($id));
    }
}