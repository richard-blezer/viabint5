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

//$db->Execute("DROP TABLE IF EXISTS `xt_plg_customer_bankaccount`");
$db->Execute("
CREATE TABLE IF NOT EXISTS ".DB_PREFIX."_plg_customer_bankaccount (
  account_id int(11) NOT NULL auto_increment,
  customer_id int(11) NOT NULL,
  banktransfer_owner varchar(255) NOT NULL,
  banktransfer_bank_name varchar(255) NOT NULL,
  banktransfer_iban varchar(32) default NULL,
  banktransfer_bic varchar(32) default NULL,
  sepa_mandat int(1) default '0',
  PRIMARY KEY  (account_id)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;
");

$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 24, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 25, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 26, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 27, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 28, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 29, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 30, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 31, '', 0, 10000.00, 0, 1);");

$langs = array('de','en');
$tpls = array('sepa_mandat');
_installMailTemplatesBanktransfer($langs, $tpls);

function _installMailTemplatesBanktransfer($langs, $tpls) {
    global $db;

    $mail_dir = _SRV_WEBROOT.'plugins/xt_banktransfer/installer/template/';

    foreach($tpls as $tpl)
    {
        $data = array(
            'tpl_type' => $tpl,
            'tpl_special' => '-1',
        );
        $c = (int) $db->GetOne("SELECT count(tpl_id) FROM ".TABLE_MAIL_TEMPLATES." where `tpl_type` = ? ", array($data['tpl_type']));
        if ($c>0)
        {
            continue;
        }
        try {
            $db->AutoExecute(TABLE_MAIL_TEMPLATES ,$data);
        } catch (exception $e) {
            return $e->msg;
        }
        $tplId = $db->GetOne("SELECT `tpl_id` FROM `".TABLE_MAIL_TEMPLATES."` WHERE `tpl_type`=? ", array($data['tpl_type']));

        foreach($langs as $lang)
        {
            $html = file_exists($mail_dir.$lang.'/'.$tpl.'_html.txt') ?  _getFileContentBanktransfer($mail_dir.$lang.'/'.$tpl.'_html.txt') : '';

            $data = array(
                'tpl_id' => $tplId,
                'language_code' => $lang,
                'mail_body_html' => $html,
                'mail_subject' => 'SEPA Mandat',
            );
            try {
                $db->AutoExecute(TABLE_MAIL_TEMPLATES_CONTENT ,$data);
            } catch (exception $e) {
                return $e->msg;
            }
        }
    }
}

function _getFileContentBanktransfer($filename) {
    $handle = fopen($filename, 'rb');
    $content = fread($handle, filesize($filename));
    fclose($handle);
    return $content;

}

?>