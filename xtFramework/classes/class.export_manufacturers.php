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

class export_manufacturers
{
    protected $_table = TABLE_FEED;
    protected $_table_lang = null;
    protected $_table_seo = null;
    protected $_master_key = 'feed_id';
    protected $nodeUrl = 'adminHandler.php?load_section=export_manufacturers&pg=getNode&';
    protected $saveUrl = 'adminHandler.php?load_section=export_manufacturers&pg=setData&';
    protected $_icons_path = 'images/icons/';

    function __construct ()
    {
        $this->indexID = time() . '-export2cat';
        $this->nodeUrl = 'adminHandler.php?load_section=export_manufacturers&pg=getNode&';
        $this->saveUrl = 'adminHandler.php?load_section=export_manufacturers&pg=setData&';
    }

    function setPosition ($position)
    {
        $this->position = $position;
    }

    function _getParams ()
    {
        $params = array();

        $header['feed_id'] = array('type' => 'hidden');
        $params['header'] = $header;
        $params['exclude'] = array(
            'feed_language_code',
            'feed_store_id',
            'feed_title',
            'feed_type',
            'feed_header',
            'feed_body',
            'feed_footer',
            'feed_mail',
            'feed_mail_flag',
            'feed_mail_header',
            'feed_mail_body',
            'feed_ftp_flag',
            'feed_ftp_server',
            'feed_ftp_user',
            'feed_ftp_password',
            'feed_ftp_dir',
            'feed_ftp_passiv',
            'feed_filename',
            'feed_filetype',
            'feed_encoding',
            'feed_save',
            'feed_export_limit',
            'feed_linereturn_deactivated',
            'feed_p_currency_code',
            'feed_p_customers_status',
            'feed_p_campaign',
            'feed_o_customers_status',
            'feed_o_orders_status',
            'feed_date_range_orders',
            'feed_date_from_orders',
            'feed_date_to_orders',
            'feed_post_flag',
            'feed_post_server',
            'feed_post_field',
            'feed_pw_flag',
            'feed_pw_user',
            'feed_pw_pass',
            'feed_p_slave',
            'feed_categories'
        );
        $params['master_key'] = $this->_master_key;

        return $params;
    }

    function _get ($ID = 0)
    {
    }

    function setExportID ($id)
    {
        $this->exportID = $id;
    }

    function getExportID ()
    {
        return $this->exportID;
    }

    function getTreePanel ()
    {
        if ($this->url_data['export_id'])
            $this->setExportID($this->url_data['export_id']);

        $root = new PhpExt_Tree_AsyncTreeNode();
        $root->setText("Manufacturer")
            ->setId('mroot');

        $tl = new PhpExt_Tree_TreeLoader();
        $tl->setDataUrl($this->nodeUrl);

        if ($this->getExportID())
            $tl->setBaseParams(array('export_id' => $this->getExportID()));

        $tp = new PhpExt_Tree_TreePanel();
        $tp->setTitle(__define('FEED_MANUFACTURER'))
            ->setRoot($root)
            ->setLoader($tl)
            ->setAutoScroll(true)
            ->setAutoWidth(true);

        $tb = $tp->getBottomToolbar();

        $tb->addButton(1, __define('TEXT_SAVE'), $this->_icons_path . 'disk.png', new PhpExt_Handler(PhpExt_Javascript::stm("
                 var checked = Ext.encode(tree.getChecked('id'));
                 var conn = new Ext.data.Connection();
                 conn.request({
                 url: '" . $this->saveUrl . "',
                 method:'POST',
                 params: {'export_id': " . $this->getExportID() . ", manIds: checked},
                 error: function(responseObject) {
                            Ext.Msg.alert('" . __define('TEXT_ALERT') . "', '" . __define('TEXT_NO_SUCCESS') . "');
                          },
                 waitMsg: 'SAVED..',
                 success: function(responseObject) {
                            Ext.Msg.alert('" . __define('TEXT_ALERT') . "','" . __define('TEXT_SUCCESS') . "');
                          }
                 });")));

        $tp->setRenderTo(PhpExt_Javascript::variable("Ext.get('" . $this->indexID . "')"));

        $js = PhpExt_Ext::OnReady(
            PhpExt_Javascript::stm(PhpExt_QuickTips::init()),
            $root->getJavascript(false, "mroot"),
            $tp->getJavascript(false, "tree")
        );

        return '<script type="text/javascript">' . $js . '</script><div id="' . $this->indexID . '"></div>';
    }

    function getNode ()
    {
        global $db;

        if ($this->url_data['node'] == 'mroot') {
            if ($this->url_data['export_id'])
                $this->setExportID($this->url_data['export_id']);

            $m = new manufacturer();
            $data = $m->getManufacturerList('admin');

            $table_data = $db->GetOne("SELECT feed_manufacturers FROM " . TABLE_FEED . " WHERE feed_id=" . (int)$this->getExportID());
            $manufacturersData = unserialize($table_data);

            if (is_array($manufacturersData)) {
                foreach ($manufacturersData as $mdata) {
                    $man_ids[] = $mdata['manufacturers_id'];
                }
            }

            if (is_array($data)) {
                foreach ($data as $man_data) {
                    $checked = false;

                    if (is_array($man_data) && is_array($man_ids)) {
                        if (in_array($man_data['manufacturers_id'], $man_ids)) {
                            $checked = true;
                        }
                    }

                    $new_mans[] = array('id' => $man_data['manufacturers_id'], 'text' => $man_data['manufacturers_name'], 'checked' => $checked, 'expanded' => false);
                }
            }
        }

        header('Content-Type: application/json; charset=' . _SYSTEM_CHARSET);
        return json_encode($new_mans);
    }

    function setData ($data, $dont_die = false)
    {
        global $db;

        $obj = new stdClass();

        if ($this->url_data['export_id']) {
            if ($this->url_data['manIds']) {
                $this->url_data['manIds'] = str_replace(array('[', ']', '"', '\\'), '', $this->url_data['manIds']);
                $man_ids = preg_split('/,/', $this->url_data['manIds']);

                if ($man_ids[0] != "")
                    $save_man_ids = serialize($man_ids);
                else
                    $save_man_ids = "";
            } else {
                $save_man_ids = "";
            }

            $db->Execute(
                "UPDATE " . TABLE_FEED . " SET feed_manufacturers=? WHERE feed_id=?",
                array($save_man_ids, $this->url_data['export_id'])
            );
        }

        if ($dont_die)
            return $obj;

        header('Content-Type: application/json; charset=' . _SYSTEM_CHARSET);
        echo json_encode($obj);
        die;
    }
}