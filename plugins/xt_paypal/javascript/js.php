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
 
if(XT_PAYPAL_EXPRESS=='true' && $_SESSION['paypalExpressCheckout']==true && $page->page_action!='success' && $page->page_action!='process'){
 
 ?>

<script type="text/javascript"><!--
<?php 

echo "
jQuery(document).ready(function() {
	
	var error_message_agb;
	var error_message_res;
	var error_message;

	$('#buttonConfirm').click(function() {
		$('.checkPPExpressError').removeClass('checkPPExpressError');

   $('#rescission_accepted_paypal, #conditions_accepted_paypal').not(':checked').parent().addClass('checkPPExpressError');   

   	if($('.checkPPExpressError #conditions_accepted_paypal').length !== 0){
		error_message_agb = '".ERROR_CONDITIONS_ACCEPTED." \\n \\n';
	}else{
		error_message_agb = '';
	}

   	if($('.checkPPExpressError #rescission_accepted_paypal').length !== 0){
		error_message_res = '".ERROR_RESCISSION_ACCEPTED." \\n \\n';
	}else{
		error_message_res = '';
	}
	
	if(error_message_agb!='' || error_message_res!=''){
	
		error_message = '';
		
		if(error_message_agb!='')
			error_message = error_message + error_message_agb;
			
		if(error_message_res!='')
			error_message = error_message + error_message_res;
	
		alert(error_message);
	}

   if ($('.checkPPExpressError').length !== 0)
   	      	return false;
	  return true;
	});

});
";
?>
//-->
</script>

<?php  } ?>