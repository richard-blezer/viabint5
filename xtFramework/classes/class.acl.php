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

class acl{
	
	var $password_min_length = 10;

	function acl() {
		$this->_errorMsg='';
	}
	
	function reset_admin_password($data) {
		global $db,$filter, $xtPlugin;

		require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.bruto_force_protection.php';
		$bruto_force = new bruto_force_protection();

		$reset_email = $filter->_filter($data['reset_email']);
		$captcha = $filter->_filter($data['captcha']);
		if ($reset_email == '' or $captcha == '') {
			$this->_errorMsg=ERROR_LOGIN_EMPTY_PARAMS;	
			
			return false;	
		}
		
		// ip block
		if($_SERVER["HTTP_X_FORWARDED_FOR"]){
			$customers_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}else{
			$customers_ip = $_SERVER["REMOTE_ADDR"];
		}

		// bruto force
		if (!$bruto_force->_isLocked($reset_email) && !$bruto_force->_isLocked($customers_ip)) {

			include _SRV_WEBROOT.'/xtFramework/library/captcha/php-captcha.inc.php';
			if (PhpCaptcha::Validate($captcha)) {
			
						
				// check user
				$query = "SELECT * FROM ".TABLE_ADMIN_ACL_AREA_USER." WHERE email=?  and status='1'";
				$rs = $db->Execute($query, array($reset_email));
				if ($rs->RecordCount()!=1) {
					
					$bruto_force->escalateLoginFail($reset_email,'1','admin_wrong_email');
					$bruto_force->escalateLoginFail($customers_ip,'1','admin_wrong_email');
					
					$this->_errorMsg=_RESET_PASS_WRONG_INPUT;
					return false;
				} else {
					
					$this->ResetPassword($rs->fields['user_id'],$reset_email);
					$this->_successMsg = SUCCESS_CAPTCHA_VALID;
					
					
					return true;				
				}				
							
			} else {
				$bruto_force->escalateLoginFail($reset_email,'1','admin_wrong_captcha');
				$bruto_force->escalateLoginFail($customers_ip,'1','admin_wrong_captcha');
				
				$this->_errorMsg = sprintf(ERROR_LOGIN_COUNT,$bruto_force->failed,$bruto_force->lock_time);
			}
			
			
		} else {
			$this->_errorMsg = sprintf(ERROR_LOGIN_LOCKED,$bruto_force->lock_time);
		}


	}
	
	function checkCode($code)
	{	global $db;
		$ar = explode(":",$code);

		if (isset($ar[1]) && strlen($ar[1])==32) {
			
		$query = "SELECT * FROM ".TABLE_ADMIN_ACL_AREA_USER." WHERE user_id=? and password_request_key=? and password_request_key!=''";
		$rs = $db->Execute($query, array($ar[0], $ar[1]));
		if ($rs->RecordCount()>0)
		{

			$password = $this->generateRandomString($this->password_min_length,3);
			$db->Execute(
				"UPDATE ".TABLE_ADMIN_ACL_AREA_USER." SET password_request_key='',user_password=? WHERE user_id=?",
				array(md5($password), $ar[0])
			);

			$mail = new xtMailer('new_password');
			$mail->_addReceiver($rs->fields['email'],'Admin');
			
			$mail->_assign('NEW_PASSWORD',$password);
			$mail->_sendMail();
			$this->_successMsg = SUCCESS_PASSWORD_SEND;
			header('Location: ' . 'login.php?action=reset_password');
			exit;
		}else{
			$this->_errorMsg= ERROR_REMEMBER_KEY_ERROR;
		}
		} else {
			$this->_errorMsg= ERROR_REMEMBER_KEY_ERROR;
		}
	}

	function ResetPassword($id,$reset_email)
	{
		global $db,$filter,$xtLink;
		
		$request_key = $this->generateRandomString(32,0);

		$db->Execute(
			"UPDATE ".TABLE_ADMIN_ACL_AREA_USER." SET password_request_key=? WHERE user_id=?",
			array($request_key, $id)
		);

		$mail = new xtMailer('password_optin');
		$mail->_addReceiver($reset_email,'Admin');

		$remember_link = $xtLink->_adminlink(array('default_page'=>'xtAdmin/reset_admin_password.php', 'params'=>'action=check_code&remember='.$id.':'.$request_key));
		
		$mail->_assign('remember_link',$remember_link);
		$mail->_sendMail();
		
	}
	
	function checkAdminKey()
	{
		global $logHandler,$xtPlugin;
		
		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':checkAdminKey')) ? eval($plugin_code) : false;	
		
		if (isset($_SESSION['admin_user']['admin_key']) && ($_SESSION['admin_user']['admin_key']==$_GET['sec']) && isset($_GET['sec']))
		{
			return true;
		}
		
		if (CSRF_PROTECTION=='debug')
		{
			if (!isset($_GET['sec'])) $log_data = 'Get parameter Sec not set';
			else $log_data ='Admin key doesn\'t match ';
			if ($_GET['edit_id']) $log_data .=' Edit_id = '.$_GET['edit_id'];
			
			if (!isset($_GET['load_section'])) $section = $_SERVER['PHP_SELF'];
			else $section = $_GET['load_section'];
			
			$identification ='1000'; // default value to filter Admin Key failure 
			$logHandler->_addLog('error',$section,$identification,$log_data);
		}
		else if (CSRF_PROTECTION=='true')  die(TEXT_WRONG_ADMIN_SESSION_SECURITY_KEY);
		
	}
	
	function login($data) {
		global $db,$filter, $xtPlugin;

		require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.bruto_force_protection.php';
		$bruto_force = new bruto_force_protection();


		$handle = $filter->_filter($data['handle']);
		$passwd = $filter->_filter($data['passwd']);
		if ($handle == '' or $passwd == '') {
			$this->_errorMsg=ERROR_LOGIN_EMPTY_PARAMS;
			return false;
		}

		// bruto force
		if (!$bruto_force->_isLocked($handle)) {

		// query for user
		$passwd = md5($passwd);
		$query = "SELECT user_id,name,u.group_id,handle,default_language_code FROM ".TABLE_ADMIN_ACL_AREA_USER." u, ".TABLE_ADMIN_ACL_AREA_GROUPS." g WHERE u.group_id = g.group_id and u.handle=? and u.user_password=? and g.status='1' and u.status='1'";
		$rs = $db->Execute($query, array($handle, $passwd));
		if ($rs->RecordCount()!=1) {
			// TODO implement bruto force check
			$bruto_force->escalateLoginFail($handle,'1','admin_login');
			$this->_errorMsg = sprintf(ERROR_LOGIN_COUNT,$bruto_force->failed,$bruto_force->lock_time);
			//$this->_errorMsg=_LOGIN_WRONG_INPUT;
			return false;
		} else {
			$_SESSION['admin_user']['user_id'] = $rs->fields['user_id'];
			$_SESSION['admin_user']['user_name'] = $rs->fields['handle'];
			$_SESSION['admin_user']['group_id'] = $rs->fields['group_id'];
			$_SESSION['admin_user']['group_name'] = $rs->fields['name'];
            $_SESSION['admin_user']['login_time'] = time();
			$_SESSION['admin_user']['admin_key'] = md5($_SESSION['admin_user']['login_time'].$rs->fields['user_id']);
			// Sets admin default language code
			if (!empty($rs->fields['default_language_code'])) {
				$_SESSION['selected_language'] = $rs->fields['default_language_code'];
			}
			if(USER_RIGHTS=='session'){
				$this->loadPermissions();
			}
			
			($plugin_code = $xtPlugin->PluginCode(__CLASS__.':login_success_bottom')) ? eval($plugin_code) : false;			

			header('Location: ' . 'ejsadmin.php');
			//return true;
		}
		} else {
			$this->_errorMsg = sprintf(ERROR_LOGIN_LOCKED,$bruto_force->lock_time);
		}


	}

	function getLoginTime() {
	    return $_SESSION['admin_user']['login_time'];
	}
	function getRefrechTime() {
	    return time() + SESSION_REFRESH_TIME;
	}

	function getUsername() {
		return $_SESSION['admin_user']['user_name'];
	}


	function logout() {
		unset($_SESSION['admin_user']);
	}

	function isLoggedIn() {
		if (isset($_SESSION['admin_user']['user_id'])) {
			return true;
		}
		return false;
	}

	function loadPermissions(){
		global $db;

		$sql = "SELECT * FROM ".TABLE_ADMIN_ACL_AREA_PERMISSIONS." ap, ".TABLE_ADMIN_ACL_AREA." a WHERE a.area_id = ap.area_id and ap.group_id=?";
		$record = $db->Execute($sql, array((int)$_SESSION['admin_user']['group_id']));
			if($record->RecordCount() > 0){
				while(!$record->EOF){

					$_SESSION['admin_user']['permissions'][$record->fields['area_name']] = $record->fields;

					$record->MoveNext();
				}$record->Close();
			}else{
				return false;
			}
	}

	function check_area_name($area){
		global $db;

		$sql = "SELECT * FROM ".TABLE_ADMIN_ACL_AREA." WHERE area_name =?";
		$record = $db->Execute($sql, array($area));
		if($record->RecordCount() == 0){
			$record = array('area_name'=>$area, 'category'=>'default');
			$db->AutoExecute(TABLE_ADMIN_ACL_AREA, $record, 'INSERT');
		}
	}
	
	/**
	 * generate more secure token/passwords
	 * @param number $length
	 * @param number $specialSigns
	 * @return string
	 */
	function generateRandomString($length=32,$specialSigns = 0) {
		
		$newpass = "";
		$laenge=$length;
		$laengeS = $specialSigns;
		$string="ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz123456789";
		$stringS = "!#$%&()*+,-./";
	
		mt_srand();
	
		for ($i=1; $i <= $laenge; $i++) {
			$newpass .= substr($string, mt_rand(0,strlen($string)-1), 1);
		}
		for ($i = 1; $i <= $laengeS; $i++) {
			$newpass .= substr($stringS, mt_rand(0, strlen($stringS) - 1), 1);
		}
		$newpass_split = str_split($newpass);
		shuffle($newpass_split);
		$newpass = implode($newpass_split);
		return $newpass;
	}
		


	function checkPermission($area,$type) {
		global $db;

		$this->check_area_name($area);

		if(_SYSTEM_ADMIN_RIGHTS=='session'){
			$rights = $_SESSION['admin_user']['permissions'][$area];
		}elseif(_SYSTEM_ADMIN_RIGHTS=='db'){
			$sql = "SELECT * FROM ".TABLE_ADMIN_ACL_AREA_PERMISSIONS." ap, ".TABLE_ADMIN_ACL_AREA." a WHERE a.area_id = ap.area_id and a.area_name=? and ap.group_id=?";
			$rs = $db->Execute($sql, array($area, (int)$_SESSION['admin_user']['group_id']));
			$rights = $rs->fields;
		}

		//fix:
		if($type=='view')
		$type = 'read';
		
		$type = 'acl_'.$type;
		
		if(_SYSTEM_ADMIN_PERMISSIONS=='whitelist'){

			if($_SESSION['admin_user']['user_id']==1){
				return true;
			}
			
			if(!is_array($rights)){
				return false;
			}
			
			if ($rights[$type]=='1') {
				return true;
			} else {
				return false;
			}			

		}elseif(_SYSTEM_ADMIN_PERMISSIONS=='blacklist'){

			if($_SESSION['admin_user']['user_id']==1){
				return true;
			}
			
			if(!is_array($rights)){
				return true;
			}
			
			if ($rights[$type]=='1') {
				return false;
			} else {
				return true;
			}			

		}

	}
}