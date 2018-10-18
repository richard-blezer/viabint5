
 
function openConfigurationPage(payment_configuration_id, payment_configuration_text){

	addTab('adminHandler.php?load_section=payment&pg=overview&parentNode=node_payment&gridHandle=paymentgridForm&edit_id='+payment_configuration_id, payment_configuration_text);
}

function openCPanel(payment_cpanel_text, payment_cpanel_url){
	if(payment_cpanel_url.length==0){
		alert('Configuration URL is missing. Please contact our support.');
	}
	else{
		$.ajax({
			type: 'POST',
			url: '../plugins/xt_payments/pages/xtpayments_cpanel_login.php',
			success: function(data) {
				params = JSON.parse(data);
				addITab(payment_cpanel_url+'?token='+params.token+'&XT_SHOP_SESSION='+params.session+'', payment_cpanel_text);
			},
			error: function(data) {
				
			}
		});
	}
}