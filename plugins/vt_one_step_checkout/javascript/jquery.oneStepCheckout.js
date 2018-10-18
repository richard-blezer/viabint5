	    var options = { 
	        /*target:        '#'+target, */
	        dataType:  		'json',   
	        success:       processJson
	    }; 

;(function($){
	$.fn.oneStepCheckout = function(op){

/************************************************************************************/
/***   OneStepCheckout Default Functions - START                                  ***/
/************************************************************************************/

            var sf = $.fn.oneStepCheckout;
            var ths = $(this);
            $('.checkError').removeClass('checkError');
            ths.resetActiveNav();
            $('input[type=radio]').unbind();
            $('#buttonConfirm').unbind();			

            var target = 'confirmationTable';


            ths.getSelectedShipping();
            ths.getSelectedPayment();

            $('#checkoutnavigation li:eq(0)').not('.success').removeClass('inactive').addClass('active');


		
	    $('#subpage_shippingBlock input[type=radio]').change(function() { 
			ths.resetActiveNav();
			

	    			
	        ths.getSelectedShipping();
	        ths.getSelectedPayment();
	        $(this).parents('form').ajaxSubmit(options); 
	        // !!! Important !!! 
	        return false; 
	    }); 
	    $('#subpage_paymentBlock [type=radio]').change(function() { 
	    	//$('#'+target).html('loading');
		ths.resetActiveNav();
	        ths.getSelectedPayment();
	        $(this).parents('form').ajaxSubmit(options); 
	        // !!! Important !!! 
	        return false; 
	    });

	    $('#buttonConfirm').click(function() {
	    	ths.conditionCheck();
	    	var check = $('.checkError');
	    	if (check.length == 0) 
	    		return true;
	    	return false;
	    });

/************************************************************************************/
/***   OneStepCheckout Default Functions - ENDE                                   ***/
/************************************************************************************/


/************************************************************************************/
/***   xt_banktransfer - START                                                    ***/
/************************************************************************************/

            //BEIM W€HLEN EINER GESPEICHERTEN VORLAGE
            $('#subpage_paymentBlock [name=acID]').change(function() {
                $('#subpage_paymentBlock [id=banktransfer]').attr("checked","checked");
                ths.resetActiveNav();
	        ths.getSelectedPayment();
                $(this).parents('form').ajaxSubmit(options);
                // !!! Important !!!
                return false;
	    });

            //BEIM EINTRAGEN NEUER VERBINDUNGEN
            $('#subpage_paymentBlock [name^=banktransfer]').change(function() {
                $('#subpage_paymentBlock [id=banktransfer]').attr("checked","checked");
                ths.resetActiveNav();
	        ths.getSelectedPayment();
                $(this).parents('form').ajaxSubmit(options);
                // !!! Important !!!
                return false;
	    });

/************************************************************************************/
/***   xt_banktransfer - ENDE                                                     ***/
/************************************************************************************/

/************************************************************************************/
/***   xt_billpay - START                                                         ***/
/************************************************************************************/
            $('#subpage_paymentBlock [name=billpay_eula]').change(function() {
                $('#subpage_paymentBlock [id=billpay_invoice]').attr("checked","checked");
                ths.resetActiveNav();
	        ths.getSelectedPayment();
                $(this).parents('form').ajaxSubmit(options);
                // !!! Important !!!
                return false;
	    });
            $('#subpage_paymentBlock [name^=billpay[]').change(function() {
                $('#subpage_paymentBlock [id=billpay_invoice]').attr("checked","checked");
                ths.resetActiveNav();
	        ths.getSelectedPayment();
                
                var birthday = 0;
                $('#subpage_paymentBlock [name^=billpay[]').each(function(index) {
                   console.log($(this).val());
                    if($(this).val() != '00'){
                        birthday++;
                    }
                });

                if(birthday == 3){
                    $(this).parents('form').ajaxSubmit(options);
                }    
                console.log(birthday);
                // !!! Important !!!
                return false;
	    });

            // billpay - did
            $('#subpage_paymentBlock [name=billpay_eula_did]').change(function() {
                $('#subpage_paymentBlock [id=billpay_did]').attr("checked","checked");
                ths.resetActiveNav();
	        ths.getSelectedPayment();
                $(this).parents('form').ajaxSubmit(options);
                // !!! Important !!!
                return false;
	    });
            $('#subpage_paymentBlock [name^=billpay_did[]').change(function() {
                $('#subpage_paymentBlock [id=billpay_did]').attr("checked","checked");
                ths.resetActiveNav();
	        ths.getSelectedPayment();
                $(this).parents('form').ajaxSubmit(options);

                // !!! Important !!!
                return false;
	    });
            $('#subpage_paymentBlock [name^=billpay_account]').change(function() {
                $('#subpage_paymentBlock [id=billpay_did]').attr("checked","checked");
                ths.resetActiveNav();
	        ths.getSelectedPayment();
                $(this).parents('form').ajaxSubmit(options);
                // !!! Important !!!
                return false;
	    });
/************************************************************************************/
/***   xt_billpay - ENDE                                                          ***/
/************************************************************************************/
	    

    	$('input.check').click(function() {
	    	ths.conditionCheck();
    	});
	};
	
	
	$.fn.extend({
		conditionCheck:function(){
			var check = $('input.check:checked');
			var checkCount = $('input.check');
			
			$('input.check').parent().removeClass('checkError');
			if (check.length != checkCount.length) {
				$('input.check').not(':checked').parent().addClass('checkError');
	    		$('#selectedConfirmation').parents('li').removeClass('inactive success').addClass('active checkError');
			} else {
	    		$('#selectedConfirmation').parents('li').removeClass('inactive checkError active').addClass('success');
			}
			
			var active = $('input[name="selected_shipping"]:checked');
			if (active.length == 0){
				var active = $('input[name="selected_shipping"]:hidden');
			}

			if (active.length == 0){
				$('#selectedShipping').parents('li').addClass('checkError');
				$('#subpage_shippingBlock').addClass('checkError');
			} else {
				$('#selectedShipping').parents('li').removeClass('checkError');
				$('#subpage_shippingBlock').removeClass('checkError');
			}
			
			var active = $('input[name="selected_payment"]:checked');
			if (active.length == 0){
				var active = $('input[name="selected_payment"]:hidden');
			}
			if (active.length == 0){
				$('#selectedPayment').parents('li').addClass('checkError');
				$('#subpage_paymentBlock').addClass('checkError');
			
			} else {
				$('#selectedPayment').parents('li').removeClass('checkError');
				$('#subpage_paymentBlock').removeClass('checkError');
			}			
			
		},
		getSelectedShipping:function(){
			// for 1 default selection
			var active = $('input[name="selected_shipping"]:checked');
			if (active.length == 0){
				var active = $('input[name="selected_shipping"]:hidden');
			}
			if (active.length > 0){
				var selectedShipping = active.parents('table').find('.header strong').html();
				
				if (selectedShipping != $('#selectedShipping').html()) {
					$(active).parents('form').ajaxSubmit(options); 					
				} 
				
				$('#selectedShipping').html(selectedShipping);
				$('#selectedShipping').parents('li').removeClass('inactive active').addClass('success');
				//$('#selectedPayment').parents('li').removeClass('inactive').addClass('active');
				return selectedShipping;
			}
			return false;
		},
		resetActiveNav:function(){
			//$('#checkoutnavigation li.active').not('.success').removeClass('active').addClass('inactive');
			var active = $('#checkoutnavigation li.active');
			if (active.length == 0){
			}
		},
		getSelectedPayment:function(){
			var active = $('input[name="selected_payment"]:checked');
			if (active.length == 0){
				var active = $('input[name="selected_payment"]:hidden');
			}
			
			if (active.length > 0){
				var selectedPayment = active.parents('table').find('.header strong').html();
				$('#selectedPayment').html(selectedPayment);
				$('#selectedPayment').parents('li').removeClass('active inactive').addClass('success');
			//	$('#selectedConfirmation').parents('li').removeClass('inactive').addClass('active');
				return selectedPayment;
			} else {
                            // needed, so that the selected payment is removed from the left navigation, in case that the
                            // selected payment ist not allowed for the selected shipping
                            $('#selectedPayment').html('');
                        }
			return false;
		}
	});
	
})(jQuery);



function processJson(data) { 
    // 'data' is the json object returned from the server 
	$.each(data, ( function( ident, template ) {
		var decoded = $('<div />').html(template).text();
  		$('#'+ident).html(template);
	}));	
    $("#oneStepCheckout").oneStepCheckout();
}