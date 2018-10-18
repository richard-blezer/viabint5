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


require_once _SRV_WEBROOT . 'plugins/xt_coupons/classes/class.csvapi_coupons.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'admin/classes/class.adminDB_DataSave.php';


if (!function_exists('error2exception')) {

    function error2exception ($errno, $errstr, $errfile, $errline)

    {
        throw new Exception($errstr);

    }

}

class xt_coupons_token_generator extends csv_api_coupons
{
    protected $_table = TABLE_COUPONS_GENERATOR;

    protected $_table_lang = null;

    protected $_table_seo = null;
    protected $_master_key = 'coupons_generator_id';

    protected $regex = '=^(.*)\[(.):?([\w-.%,]*)\](.*)$=msi';

    public $_table_data = TABLE_COUPONS_GENERATOR;
    public $_module_id = 'xt_coupon_generator';

    public $items_per_call = 100;


    function setPosition ($position)
    {
        $this->position = $position;
    }


    function _getParams ()
    {
        $params = array();
        $header = array();
        $header['coupons_generator_id'] = array('type' => 'hidden');

        $header['coupon_id'] = array('type' => 'dropdown',

            'url' => 'DropdownData.php?get=coupon&plugin_code=xt_coupons');

        $rowActions[] = array('iconCls' => 'start', 'qtipIndex' => 'qtip1', 'tooltip' => 'Run');

        if ($this->url_data['edit_id'])
            $js .= "var edit_id = " . $this->url_data['edit_id'] . ";";
        else
            $js = "var edit_id = record.data.coupons_generator_id;";

        //$js = "var edit_id = record.data.coupons_generator_id;";
        //										$js = "alert(record.data);";
        $js .= "Ext.Msg.show({
            title:'" . TEXT_START . "',
            msg: '" . TEXT_START_ASK . "',
            buttons: Ext.Msg.YESNO,
            animEl: 'elId',
            fn: function(btn){runGenerator(edit_id,btn);},
            icon: Ext.MessageBox.QUESTION
            });";

        $js .= "function runGenerator(edit_id,btn){
            if (btn == 'yes') {
            addTab('row_actions.php?type=coupons_token_generator&id='+edit_id,'... generator ...');  
            }

            };";


        $rowActionsFunctions['start'] = $js;


        $params['header'] = $header;
        $params['display_searchPanel'] = false;

        $params['master_key'] = $this->_master_key;
        $params['default_sort'] = $this->_master_key;
        $params['SortField'] = $this->_master_key;
        $params['SortDir'] = "DESC";

        //								$params['rowActionsJavascript'] = $js;
        $params['rowActions'] = $rowActions;
        $params['rowActionsFunctions'] = $rowActionsFunctions;

        return $params;

    }


    function _get ($ID = 0)
    {

        require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.configuration.php';


        global $xtPlugin, $db, $language;


        if ($this->position != 'admin') return false;


        if ($ID === 'new') {
            $obj = $this->_set(array(), 'new');
            $ID = $obj->new_id;
        }

        $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key);

        if ($this->url_data['get_data'])
            $data = $table_data->getData();
        elseif ($ID)
            $data = $table_data->getData($ID); //		        if($data['mask'] == '' ) {
        //		        		 $data['mask'] = configuration::getValue('_PLUGIN_COUPON_MASK');
        //		        }

        //		        if($data['mask'] == '' ) {
        //		           $data['mask'] = '[r:5]-[r:5]-[r:5]';
        //		        }
        else
            $data = $table_data->getHeader();


        //        $data = array();
        //        $data['count'] = '';
        //        $data['mask'] = '';
        //        $data['coupon_id'] = '';


        $obj = new stdClass();
        $obj->totalCount = count($data);
        $obj->data = $data;

        return $obj;

    }

    function _set ($data, $set_type = 'edit')
    {
        global $db, $language, $filter;

        $obj = new stdClass;
        $obj->success = null;

        //set default value for mask
        if (empty($data['mask']))
            $data['mask'] = '[r:2]-[r:3]-[r:4]';

        $count = (float)1; //use float, it has more bytes than int
        $mask = $data['mask'];
        while (preg_match($this->regex, $mask, $a)) {
            $command = strtolower($a[2]);
            $parameter = explode(',', $a[3]);

            switch ($command) {
                case 'c' : // counter
                    $obj->success = true;
                    break 2;
                case 'r' : // random
                    $random_length = $parameter[0];
                    if ($random_length < 1) {
                        $random_length = 5;
                    }

                    if (count($parameter) < 2 || !$parameter[1]) {
                        $parameter[1] = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    }

                    $symbolsCount = strlen(count_chars($parameter[1], 3));

                    for ($i = 0; $i < $random_length; $i++) {
                        $count *= $symbolsCount;
                    }
                    break;
                default: // unknown
                    $obj->success = false;
                    break;
            }
            $mask = $a[4];
        }

        if ($obj->success === null) {
            if ($count < (int)$data['count']) {
                $obj->success = false;
            } else {
                $obj->success = true;
            }
        }

        if ($obj->success) {
            $o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
            $obj = $o->saveDataSet();
        }
        return $obj;
    }

    function _set_old ($data, $set_type = 'edit')
    {

        require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.configuration.php';

        global $db, $language, $filter;

        $obj = new stdClass;

        $count = $data['count'];

        $mask = $data['mask'];

        $coupon = $data['coupon_id'];

        $trials_max = 10000;


        configuration::setValue('_PLUGIN_COUPON_MASK', $mask);

        $last_code = '';


        $obj->success = true;
        $obj->load_page = 'fffff';
        $obj->message = 'loaded saved';
        return $obj;
    }

    function start_iframe_generator ($count, $coupon_id)
    {
        $params = 'api=coupon_token_generate&count=' . $count . '&coupon_id=' . $id;
        $iframe_target = $xtLink->_adminlink(array('default_page' => 'cronjob.php', 'params' => $params));
        echo '<iframe src="' . $iframe_target . '" frameborder="0" width="100%" height="500"></iframe>';
    }

    function run_generator ($data)
    {
        $this->api = $data['api'];

        if (isset($data['pos'])) {
            $this->pos = $data['pos'];
        } else {
            $this->pos = 0;
        }

        if (isset($data['counter_new'])) {
            $this->counter_new = (int)$data['counter_new'];
        } else {
            $this->counter_new = 0;
        }

        if (isset($data['counter_error'])) {
            $this->counter_error = (int)$data['counter_error'];
        } else {
            $this->counter_error = 0;
        }

        $id = $data['id'];
        if (!$this->getDetails($id)) die ('- id not found -');

        $this->id = $id;

        $this->count = $this->_recordData['count'];
        $this->mask = $this->_recordData['mask'];
        $this->coupon_id = $this->_recordData['coupon_id'];
        $this->items_per_call = $this->_recordData['items_limit'];


        $this->generate_token();

    }

    function generate_token ()
    {
        global $db;

        if (isset($_GET['pos'])) {
            $last_code = '';
            if (($count > 1) && (!preg_match($this->regex, $mask))) {
                //  ?
            }
            $pos = $this->pos;
            $count = $this->count;
            $mask = $this->mask;
            $coupon_id = $this->coupon_id;
            $items_per_call = $this->items_per_call;

            $trials_max = 1000;
            for ($i = $pos; ($i < $count) && ($i < $pos + $items_per_call); ++$i) {
                $new_code = $this->get_new_code($mask, $pos);

                $qry = "SELECT coupon_token_code FROM " . TABLE_COUPONS_TOKEN . " WHERE coupon_token_code = ?";
                $r = $db->Execute($qry,array($new_code));
                if ($r->RecordCount() == 0) {
                    $new_data = Array();
                    $new_data['coupon_id'] = $coupon_id;
                    $new_data['coupon_token_code'] = $new_code;
                    $r = $db->AutoExecute(TABLE_COUPONS_TOKEN, $new_data);
                    $last_code = $new_code;
                } else {
                    --$i;
                    --$trials_max;
                }

                /*  // alte Variante funktioniert nicht mehr :-(
                try {
                $o = new adminDB_DataSave(TABLE_COUPONS_TOKEN, $new_data, false, __CLASS__);
                $obj = @$o->saveDataSet();
                $last_code = $new_code;
                } catch (Exception $e) {
                --$i;
                --$trials_max;
                }
                */
                if ($trials_max <= 0) {
                    break;
                }

            }
            $this->pos = $i;
        }

        $this->_redirecting();

    }


    function _unset ($id = 0)
    {

        global $db;

        if ($id == 0) return false;

        if ($this->position != 'admin') return false;

        $id = (int)$id;

        if (!is_int($id)) return false;

        $db->Execute("DELETE FROM " . $this->_table . " WHERE " . $this->_master_key . " = ?",array($id));
    }


    function init_random ()
    {

        list($usec, $sec) = explode(' ', microtime());

        mt_srand((float)$sec + ((float)$usec * 100000));

        $this->code_counter = Array();

    }


    function get_new_code ($mask, $pos = 0)
    {

        $search_work = $mask;

        while ((preg_match($this->regex, $search_work, $a))) {

            $prefix = $a[1];

            $command = strtolower($a[2]);

            $parameter = explode(',', $a[3]);

            $postfix = $a[4];

            $replace = '';

            switch ($command) {

                case 'c'  : // counter

                    $counter_name = $parameter[1];

                    if ($counter_name == '') $counter_name = 'default';

                    $replace = ($this->code_counter[$parameter[1]]++) + (int)$parameter[0] + (int)$pos;

                    break;

                //        case 'd'  :  // date

                //            $date_format = $parameter[0];

                //            $replace = strftime($date_format);

                //            break;

                //        case 'p'  :  // pr?fsumme

                //            $mode = $parameter[0];

                //            $replace = strftime($date_format);

                //            break;

                case 'r'  : // random

                    $random_length = $parameter[0];

                    if (count($parameter) < 2) $parameter[1] = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

                    if (count($parameter) < 3) $parameter[2] = 'default';

                    $random_base = $parameter[1];

                    $random_name = $parameter[2];

                    if ($random_length < 1) $random_length = 5;

                    if ($random_base == '') $random_base = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

                    if ($random_name == '') $random_name = 'default';

                    for ($random_pos = 0; $random_pos < $random_length; ++$random_pos) {

                        $rand_idx = mt_rand(0, strlen($random_base) - 1);

                        $replace .= substr($random_base, $rand_idx, 1);
                    }

                    break;

                default: // unbekannt !!

                    $replace = '(Uknown Command ' . $command . ')';

                    break;

            }

            $search_work = $prefix . $replace . $postfix;

        }

        return $search_work;

    }

    function _redirecting ()
    {
        global $xtLink;
        if ($this->pos < $this->count) {
            // redirect to next step
            //            $limit_lower = $this->pos +1;
            $limit_lower = $this->pos;
            $limit_upper = $limit_lower + $this->items_per_call;
            $params = 'api=' . $this->api .
                '&id=' . $this->id .
                '&pos=' . $limit_lower .
                '';

            echo $this->_displayHTML($xtLink->_link(array('default_page' => 'cronjob.php', 'params' => $params,'conn'=>'SSL')), $limit_lower, $limit_upper, $this->count);
        } else {
            echo '<br />200 ' . $this->api . ' finished';
            echo '<br />' . $this->pos . ' generated';
        }
    }

    function _displayHTML ($next_target, $lower = 1, $upper = 0, $total = 0)
    {

        $process = $lower / $total * 100;
        if ($process > 100) $process = 100;
        if ($upper > $total) $upper = $total;

        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
            <html>
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <meta http-equiv="refresh" content="2; URL=' . $next_target . '" />
            <title>..generator..</title>
            <style type="text/css">
            <!--
            .process_rating_light .process_rating_dark {
            background:#FF0000;
            height:15px;
            position:relative;
            }

            .process_rating_light {
            height:15px;
            margin-right:5px;
            position:relative;
            width:150px;
            border:1px solid;
            }

            -->
            </style>
            </head>
            <body>
            <div class="process_rating_light"><div class="process_rating_dark" style="width:' . $process . '%">' . round($process, 0) . '%</div></div>
            Processing ' . $lower . ' to ' . $upper . ' of total ' . $total . '
            </body>
            </html>';
        return $html;

    }

    function getDetails ($id)
    {
        global $db, $filter;

        $rs = $db->Execute("SELECT * FROM " . $this->_table_data . " WHERE coupons_generator_id = ?",array($filter->_charNum($id)));
        if ($rs->RecordCount() != 1) return false;

        $this->_recordData = $rs->fields;
        return true;

    }

}

?>