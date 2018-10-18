<?php

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/classes/class.paypal.php';
 
if(XT_PAYMENTS_PAYPAL_EXPRESS_ENABLED=='true' && $_SESSION[XT_PAYMENTS_PARAM_PPEXPRESS_CHECKOUT]==true && $page->page_action!='success' && $page->page_action!='process'){
 
 ?><script type="text/javascript"><!--
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
</script><?php  } ?>