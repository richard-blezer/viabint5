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

class ekomi {


	var $delay = XT_EKOMI_DELAY;
	//var $ws_send_limit = XT_WEBSERVICE_SEND_LIMIT;
	var $ws_send_limit = XT_EKOMI_MAILS_BATCH;
	var $api_id=XT_EKOMI_API_ID; // interface_id
	var $api_key=XT_EKOMI_API_KEY; // interface_pw
	var $ekomi_version='xt-4.1.0.1';
    var $logging = true;
    var $title_length=30;
    var $activate_success_log = true;

	function __contruct() { }


    /**
     * gather orders and send review reminder
     */
    public function sendNotifications() {

		// get relevant orders
		$orders = $this->getOrdersToSend();
		if ($orders==false) return;

		foreach ($orders as $key=>$order) {
			// push products to ekomi

            // are there allowed products in order ?
            if (count($order['products'])>0) {

                $this->pushProducts($order);

                // push order to ekomi
                  $this->pushOrder($order);

                // sent reminder mail to customer
                   $return = $this->sendReminderMail($order);

                // mark order as send
                   if ($return==true) {
                       $this->markOrder($order['order_id']);
                   }

            } else {
                $this->skipOrder($order['order_id']);
            }


		}

	}

    /**
     *
     * download productReviews from webservice
     *
     */
    public function getProductReviews() {
        global $db;

        if (!class_exists('xt_reviews')) return;

        // setup mail templates
        $url = 'http://api.ekomi.de/get_productfeedback.php?interface_id=' . $this->api_id . '&interface_pw=' . $this->api_key . '&version='.$this->ekomi_version.'&type=csv&charset=utf-8&range=1m';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $get_reviews = curl_exec($ch);
        curl_close($ch);

        $temp=fopen("php://memory", "rw");
        fwrite($temp, $get_reviews);
        fseek($temp, 0);

        $review = new xt_reviews();

        while (($data = fgetcsv($temp, 4096, ',', '"')) !== false) {

            // only insert new ones
            if ($data[0]>_SYSTEM_EKOMI_LAST_IMPORT) {

                $sql = "SELECT customers_id FROM ".TABLE_ORDERS." WHERE orders_id=?";
                // TODO get also language from order and save rewiev in correct language
                $arr = $db->getRow($sql, array((int)$data['1']));
                if ($arr['customers_id']>0) {


                    $rev = array();
                    $rev['orders_id']=(int)$data[1];
                    $rev['products_id']=(int)$data[2];
                    $rev['review_rating']=(int)$data[3];
                    $rev['review_text']=$data[4];
                    $title=$data[4];
                    if (strlen($data[4]>$this->title_length)) {
                        $title=substr($data[4],0,$this->title_length).'....';
                    }
                    $rev['review_title']=$title;
                    $rev['review_source']='ekomi';
                    $rev['review_status']='1';
                    $rev['customers_id']=$arr['customers_id'];

                    $review->_addReview($rev,'true');
                    $review->_reCalculate($rev['products_id']);

                 //   echo 'added:'.$rev['review_text'].'</br>';
                    $db->Execute(
                        "UPDATE ".TABLE_CONFIGURATION." SET config_value=? WHERE config_key='_SYSTEM_EKOMI_LAST_IMPORT'",
                        array($data[0])
                    );
                    // set date
                }
            }

     //       $rew[] = $data;
        }
    }

    /**
     * create eKomi email Template
     */
    public function setMailTemplate() {
        global $db,$language;

        // check if there is allready a mail template

        $rs = $db->Execute("SELECT * FROM ".TABLE_MAIL_TEMPLATES." WHERE tpl_type='ekomi_reminder'");
        if ($rs->RecordCount()==0) {
            $data = $this->getSettings();

            if (isset($data['mail_html'])) {

                $html_content = $data['mail_html'];
                $txt_content = $data['mail_plain'];
                $subject = $data['mail_subject'];

                $html_content = utf8_encode($html_content);
                $txt_content = utf8_encode($txt_content);
                $subject = utf8_encode($subject);

                // replace fields
                $html_content = str_replace('{vorname}', '{$customers_firstname}', $html_content);
                $html_content = str_replace('{nachname}', '{$customers_lastname}', $html_content);
                $html_content = str_replace('{ekomilink}', '{$ekomilink}', $html_content);

                $txt_content = str_replace('{vorname}', '{$customers_firstname}', $txt_content);
                $txt_content = str_replace('{nachname}', '{$customers_lastname}', $txt_content);
                $txt_content = str_replace('{ekomilink}', '{$ekomilink}', $txt_content);


                $insert_array = array();
                $insert_array['tpl_type'] = 'ekomi_reminder';
                $insert_array['tpl_special'] = 0;


                $db->AutoExecute(TABLE_MAIL_TEMPLATES, $insert_array);
                $mail_tpl_id =  $db->Insert_ID();

                $languages = $language->_getLanguageList('admin');

                foreach ($languages as $key => $val) {

                    // foreach language
                    $insert_array = array();
                    $insert_array['tpl_id'] = $mail_tpl_id;
                    $insert_array['language_code'] = $val['code'];
                    $insert_array['mail_body_html'] = $html_content;
                    $insert_array['mail_body_txt'] = $txt_content;
                    $insert_array['mail_subject'] = $subject;

                    $db->AutoExecute(TABLE_MAIL_TEMPLATES_CONTENT, $insert_array);

                }

                $rs = $db->Execute("SELECT * FROM ".TABLE_MAIL_TEMPLATES." WHERE tpl_type='send_order' LIMIT 0,1");
                if ($rs->RecordCount()>0) {
                    $db->Execute(
                        "UPDATE ".TABLE_MAIL_TEMPLATES." SET email_from=?, email_reply=?",
                        array($rs->fields['email_from'], $rs->fields['email_reply'])
                    );
                }

            }
        }
    }


    /**
     * get settings from webservice
     * @return mixed
     */
    public function getSettings() {


        $url = 'http://api.ekomi.de/v2/getSettings?auth='.$this->api_id.'|'.$this->api_key.'&version='.$this->ekomi_version;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $get_settings  = curl_exec($ch);
        curl_close($ch);

        $settings     = unserialize( $get_settings );

        return $settings;

    }

    /**
     * send reminder email to customer
     *
     * @param $order
     * @return bool
     *
     */
    private function sendReminderMail($order) {
        global $db,$store_handler;

        $sql="SELECT * FROM " . TABLE_ORDERS . " WHERE orders_id='" . $order['order_id']."'";
        $res=$db->getRow($sql);

        $ekomimail = new xtMailer('ekomi_reminder', $res['language_code'],'');
        $ekomimail->_addReceiver($order['customers_email_address'],$order['customer_lastname'].' '.$order['customer_firstname']);
        $ekomimail->_assign('customers_firstname',$order['customer_firstname']);
        $ekomimail->_assign('customers_lastname',$order['customer_lastname']);
        $ekomimail->_assign('ekomilink','<a href="'.$res['ekomi_link'].'" target="_blank">Bewerten</a>');

        $return = $ekomimail->_sendMail();
        return true;
     //   if ($return == false) {
     //       return false;
     //   }
     //   return true;
    }

    /**
     * mark order as notified
     *
     * @param $orders_id
     */
    private function markOrder($orders_id) {
		global $db;

        $sql="UPDATE " . TABLE_ORDERS . " SET ekomi_success='1', ekomi_success_date=now() WHERE orders_id='" . $orders_id."'";
        $db->Execute($sql);

		
	}

    private function skipOrder($orders_id) {
        global $db;
        $sql="UPDATE " . TABLE_ORDERS . " SET ekomi_success='1' WHERE orders_id='" . $orders_id."'";
        $db->Execute($sql);
        if ($this->activate_success_log) {
            $this->ekomiLog('skipped order',$orders_id,'success');
        }
    }

    /**
     * send order information to ekomi webservice
     * @param $order
     * @return array|mixed
     */
    private function pushOrder($order) {
        global $db;
     //   __debug($order);
        $prod = array();
        foreach ($order['products'] as $key => $product) {
            $prod[]=$product['products_id'];
        }

        $prod = implode(',',$prod);

        $url          = 'http://api.ekomi.de/v2/putOrder?auth='.$this->api_id.'|'.$this->api_key.'&version='.$this->ekomi_version.'&order_id='.urlencode($order['order_id']).'&product_ids='.urlencode($prod);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $return  = curl_exec($ch);
        curl_close($ch);
        $data         = unserialize( $return );

        if (is_array($data) && isset($data['link'])) {

            $this->ekomiLog('Transmited Order to eKomi',$order['order_id'],'success');

            $sql = "UPDATE ".TABLE_ORDERS." SET ekomi_link='".$data['link']."', ekomi_hash='".$data['hash']."' WHERE orders_id='".$order['order_id']."'";
            $db->Execute($sql);
        } else {
            $this->ekomiLog('error on pushOrder for Order ID '.$order['order_id']);
        }


        return $data;
		
		
	}

    /**
     * transmit order information to eKomi
     *
     * @param $order order object
     */
    private function pushProducts($order) {


		foreach ($order['products'] as $key => $product) {
				
			$url= 'http://api.ekomi.de/v2/putProduct?auth='.$this->api_id.'|'.$this->api_key.'&version='.$this->ekomi_version.'&product_id='.urlencode($product['products_id']).'&product_name='.urlencode($product['products_name']);


					$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$return = curl_exec($ch);
			curl_close($ch);
			$data          = unserialize( $return );

				
		}
		return;

	}

	/**
	 * get relevant orders from database
	 */
	private function getOrdersToSend() {
		global $db, $store_handler;
		
		$enabledStores = array();
		
		$checkEnabledStores = $db->Execute("SELECT shop_id FROM " . TABLE_PLUGIN_CONFIGURATION . " WHERE config_key='XT_EKOMI_ENABLED' AND config_value='true'");
		
		if ($checkEnabledStores->RecordCount() > 0) {
			while(!$checkEnabledStores->EOF){
				$enabledStores[] = $checkEnabledStores->fields['shop_id'];
				$checkEnabledStores->MoveNext();
			}
			$checkEnabledStores->Close();
		}
		
		// No stores enabled
		if (empty($enabledStores) || !in_array($store_handler->shop_id, $enabledStores)) {
			return false;
		}
		
		$data = array();

        $install_time=_SYSTEM_EKOMI_INSTALL_TIME;
        $install_time = date('Y-m-d',_SYSTEM_EKOMI_INSTALL_TIME);

        $sql = "SELECT * FROM ".TABLE_ORDERS." o, ".TABLE_ORDERS_STATUS_HISTORY." osh WHERE
                        osh.date_added > DATE_SUB('".$install_time."', INTERVAL ".(int)$this->delay." DAY)
                        AND osh.date_added <= DATE_SUB(CURDATE(),INTERVAL ".(int)$this->delay." DAY)
                        AND osh.orders_id=o.orders_id
                       	AND o.shop_id = '{$store_handler->shop_id}' 
                        AND osh.orders_status_id = '".XT_EKOMI_ORDER_STATUS."' AND o.ekomi_success!='1' LIMIT 0,".$this->ws_send_limit;


        $rs = $db->Execute($sql);

		if ($rs->RecordCount()==0) {

            echo 'no orders';
            return false;
        }
		// Daten in Array speichern
		while(!$rs->EOF){

			$line_data = array();
			$line_data['order_id']              = $rs->fields['orders_id'];
			$line_data['customer_firstname']    = $rs->fields['delivery_firstname'];
			$line_data['customer_lastname']     = $rs->fields['delivery_lastname'];
			$line_data['customers_email_address'] = $rs->fields['customers_email_address'];

			// get products
			$products = array();
			$psql = "SELECT op.products_id, op.products_name, p.ekomi_allow FROM ".TABLE_ORDERS_PRODUCTS." op, ".TABLE_PRODUCTS." p WHERE op.orders_id='".$rs->fields['orders_id']."' and op.products_id=p.products_id";
			$prs = $db->Execute($psql);
			while (!$prs->EOF) {

                // check if products are allowed for ekomi
                if ($prs->fields['ekomi_allow']=='1')
				    $products[]=array('products_id'=>$prs->fields['products_id'],'products_name'=>$prs->fields['products_name']);
				$prs->MoveNext();
			}
			$line_data['products']=$products;
			$data[]=$line_data;
			$rs->MoveNext();

		}

		return $data;

	}

    /**
     * ekomi logging
     *
     * @param $message
     */
    private function ekomiLog($message,$id=0,$type='info') {
        global $logHandler;

        if ($this->logging==false) return;

        $log_data = array();
        $log_data['message'] = $message;
        $logHandler->_addLog($type,'xt_ekomi',$id,$log_data);
    }


}
?>