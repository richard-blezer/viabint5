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
  
  
  class janolaw_agb_hosting {
      
      
      var $_file_agb = 'agb_include.html';
      var $_file_privacy = 'datenschutzerklaerung_include.html';  
      var $_file_impressum = 'impressum_include.html';  
      var $_file_widerruf = 'widerrufsbelehrung_include.html'; 
      var $_cache_time = 7200; 
      
      var $_kundennummer = JANOLAW_AGB_KUNDENNUMMER;
      var $_shop_id = JANOLAW_AGB_SHOPID;
     
     function  janolaw_agb_hosting() {
         
         $this->dir = 'http://www.janolaw.de/agb-service/shops/'.$this->_kundennummer.'/'.$this->_shop_id.'/';
         $this->local_dir = 'media/content/';
     }
      
     public function runFetch() {
         if (strlen($this->_kundennummer)>3) {
            $this->_fetchFile($this->_file_agb,JANOLAW_AGB_CONTENT_AGB);
            if (JANOLAW_AGB_PRIVACY_USE=='true') $this->_fetchFile($this->_file_privacy,JANOLAW_AGB_CONTENT_PRIVACY);
            if (JANOLAW_AGB_IMPRESSUM_USE=='true') $this->_fetchFile($this->_file_impressum,JANOLAW_AGB_CONTENT_IMPRESSUM);
            $this->_fetchFile($this->_file_widerruf,JANOLAW_AGB_CONTENT_WIDERRUF);             
         }

         
     } 
      
      private function _fetchFile($file,$coID) {
          global $db;
          
          if (filectime($this->local_dir.$file)+$this->_cache_time<=time()) {
          
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->dir.$file);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1) ;
            $res = curl_exec ($ch) ;
            curl_close ($ch) ;
          
            $fp = fopen($this->local_dir.$file, "w+");
            fputs($fp,$res);
            fclose($fp);    
          
            // zuweisen zu content
			$db->Execute("UPDATE ".TABLE_CONTENT_ELEMENTS." SET content_body = ? WHERE content_id=? and language_code='de'", array($res,$coID));
          }
      }
      
      
      
  }
?>