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

require_once(_SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.recursive.php');

class export_categories
{
    protected $_table = TABLE_FEED;
    protected $_table_lang = null;
    protected $_table_seo = null;
    protected $_master_key = 'feed_id';
    protected $nodeUrl = 'adminHandler.php?load_section=export_categories&pg=getNode&';
    protected $saveUrl = 'adminHandler.php?load_section=export_categories&pg=setData&';
    protected $_icons_path = 'images/icons/';

    function __construct ()
    {
        $this->indexID = time() . '-export2cat';
        $this->nodeUrl = 'adminHandler.php?load_section=export_categories&pg=getNode&';
        $this->saveUrl = 'adminHandler.php?load_section=export_categories&pg=setData&';
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
            'feed_manufacturer',
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
            'feed_p_slave'
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
        $root->setText("Category")
            ->setId('croot');

        $tl = new PhpExt_Tree_TreeLoader();
        $tl->setDataUrl($this->nodeUrl);

        if ($this->getExportID())
            $tl->setBaseParams(array('export_id' => $this->getExportID()));

        $tp = new PhpExt_Tree_TreePanel();
        $tp->setTitle(__define('TABTEXT_CATEGORY'))
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
                 params: {'export_id': " . $this->getExportID() . ", catIds: checked},
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
            $root->getJavascript(false, "croot"),
            $tp->getJavascript(false, "tree")
        );

        return '<script type="text/javascript">' . $js . '</script><div id="' . $this->indexID . '"></div>';
    }

    function getNode ()
    {
        global $db;

        if ($this->url_data['export_id'])
            $this->setExportID($this->url_data['export_id']);

        $d = new recursive(TABLE_CATEGORIES, 'categories_id');

        $table_data = $db->GetOne("SELECT feed_categories FROM " . TABLE_FEED . " WHERE feed_id=" . (int)$this->getExportID());
        $categoriesData = unserialize($table_data);

        $expand = array();

        if (is_array($categoriesData)) {
            foreach ($categoriesData as $cdata) {
                $path = $d->getPath($cdata);
                $expand = array_merge($expand, $path);
                $cat_ids[] = $cdata;
            }
        }

        $d->setLangTable(TABLE_CATEGORIES_DESCRIPTION);
        $d->setDisplayKey('categories_name');
        $d->setDisplayLang(true);
        $data = $d->_getLevelItems($this->url_data['node']);

        if (is_array($data)) {
            foreach ($data as $cat_data) {
                $checked = false;

                if (is_array($cat_data) && is_array($cat_ids)) {
                    if (in_array($cat_data['categories_id'], $cat_ids)) {
                        $checked = true;
                    }
                }

                $expanded = false;

                if (in_array($cat_data['categories_id'], $expand)) {
                    $expanded = true;
                }

                $new_cats[] = array('id' => $cat_data['categories_id'], 'text' => $cat_data[$d->getDisplayKey()], 'checked' => $checked, 'expanded' => $expanded);
            }
        }

        header('Content-Type: application/json; charset=' . _SYSTEM_CHARSET);
        return json_encode($new_cats);
    }

    function setData ($data, $dont_die = false)
    {
        global $db;

        $obj = new stdClass();

        if ($this->url_data['export_id']) {
            if ($this->url_data['catIds']) {
                $this->url_data['catIds'] = str_replace(array('[', ']', '"', '\\'), '', $this->url_data['catIds']);
                $cat_ids = preg_split('/,/', $this->url_data['catIds']);

                if ($cat_ids[0] != "")
                    $save_cat_ids = serialize($cat_ids);
                else
                    $save_cat_ids = "";
            } else {
                $save_cat_ids = "";
            }

            $db->Execute(
                "UPDATE " . TABLE_FEED . " SET feed_categories=? WHERE feed_id=?",
                array($save_cat_ids, $this->url_data['export_id'])
            );
        }

        if ($dont_die)
            return $obj;

        header('Content-Type: application/json; charset=' . _SYSTEM_CHARSET);
        echo json_encode($obj);
        die;
    }
}