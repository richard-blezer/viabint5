var authRequest;
var intervalTimer = 200;


function isAmazonLoged()
{
    TOKEN = getAmazonToken();
    //alert(TOKEN);
    if (TOKEN != undefined)
    {
            return true;
    }

    return false;
}

var tfm_amazon_payment_product_button_failLoginData;

function tfm_amazon_payment_product_button_clearFailLogins()
{
	tfm_amazon_payment_product_button_failLoginData = [];
}

function tfm_amazon_payment_product_button_failLogin(fail_result, plugin_caller)
{
	if (typeof plugin_caller === 'undefined')plugin_caller = 'na';
	if(fail_result)tfm_amazon_payment_product_button_failLoginData.push(plugin_caller);
}

function tfm_amazon_payment_product_button_checkFailLogins()
{
	if(tfm_amazon_payment_product_button_failLoginData.length>0)return false; else return true;
}

$(document).ready(function(){

    amazon_l = isAmazonLoged();

    if (typeof page === 'undefined' || page === null)page = "not_in_list";
    

    if(page == "login")
    {
		
		var initAmazonLoginInterval = setInterval(function(){if(AMAZONLOGSLOADED){clearInterval(initAmazonLoginInterval);	
				
				initAmazonLogin("login", "normal");
		
		}}, intervalTimer);
	}
	
    if(page == "account")
    {
        initAmazonAccount();
    }

    if(page == "cart")
    {
        var initAmazonCartInterval = setInterval(function(){if(AMAZONLOGSLOADED){clearInterval(initAmazonCartInterval);
        
        if(sub_action == 'logoff')initAmazonLogoff();
		initAmazonLogin("cart", "normal");
		
		}}, intervalTimer);
        
    }
    
    
    
   
   
    if(page == "shipping")
    {
            if ( amazon_l )
            {
                if ( xtLoged==3 )
                {
                    if(account_mode_checkout=="amazon")initAmazonShipping();

                }
                else
                {
                    initAmazonShipping();
                }
            }
                
            if ( xtLoged==3 )
            {
				
				 var initAmazonLoginInterval = setInterval(function(){if(AMAZONLOGSLOADED){clearInterval(initAmazonLoginInterval);
    
    
					initAmazonLogin("shipping", "normal");
				
				}}, intervalTimer);
			}
            
    }   
 
    if(page == "logoff")
    {
		var initAmazonLogoffInterval = setInterval(function(){if(AMAZONLOGSLOADED){clearInterval(initAmazonLogoffInterval);
        
			initAmazonLogoff();
		
		}}, intervalTimer);
	}
    if(page == "payment")
    { 
        initTogglePayments();
        toggleAmazonWallet();
        

            if( amazon_l )
            {
                if ( xtLoged==3 )
                {
                    if(account_mode_checkout=="amazon")initAmazonPayment();
                }
                else
                {
                    initAmazonPayment();
                }
            }
                
            //not in use// if ( xtLoged==3 )initAmazonLogin("payment", "normal");
    }
    
    if(page == "confirmation")initAmazonConfirmation();



    if( $("#AmazonPayButton_leftbox").length )
    {
		
		var initAmazonLeftBoxInterval = setInterval(function(){if(AMAZONLOGSLOADED){clearInterval(initAmazonLeftBoxInterval);
        
			itr=0;
			$('.AmazonPaymentBox_leftbox').each(function(){
					
					 $($(this).find('div')[0]).attr('id', 'AmazonPayButton_leftbox'+itr);
					 $(this).addClass('box'+itr);
					 
					 initAmazonLogin(page, 'leftbox'+itr);
					 itr++;
			});
        
		
		}}, intervalTimer);
	}
	
	if( $("#AmazonPayButton_productbox").length )
    {
			var initAmazonListprInterval = setInterval(function(){if(AMAZONLOGSLOADED){clearInterval(initAmazonListprInterval);
			
				itr=0;
				$('.AmazonPaymentBox_productbox').each(function(){ 
						
						 $($(this).find('div')[0]).attr('id', 'AmazonPayButton_productbox'+itr);
						 $(this).addClass('box'+itr);
						 
						 initAmazonLogin(page, 'productbox'+itr);
						 itr++;
				});
				
						
			}}, intervalTimer);
	}
	

	if( $("#AmazonPayButton_productinfo").length )
    {
        var initAmazonInfoprInterval = setInterval(function(){if(AMAZONLOGSLOADED){clearInterval(initAmazonInfoprInterval);
			
				itr=0;
				$('.AmazonPaymentBox_productinfo').each(function(){ 
						
						 $($(this).find('div')[0]).attr('id', 'AmazonPayButton_productinfo'+itr);
						 $(this).addClass('box'+itr);
						 
						 initAmazonLogin(page, 'productinfo'+itr);
						 itr++;
				});
				
				
		
		}}, intervalTimer);
        
    }
	
	
    try {amazonCustomFixesInit();}
    catch(err) {console.log("Amazon plugin: Custom JS fixes, not exist!");}
    
    
    
    
});

function checkJSelm(key)
{	
	JSe = JSelm(key);


	if( $(JSe).length || JSe=='')
	{
		return true;
	}else
	{ 
		console.log("tfm_amazon_payments: error displaying `"+key+"`: `"+JSe+"` doesn't exists");
		return false;
	}
	
}

function checkJSsel(key)
{
	if( $(key).length )
	{
		return true;
	}else
	{ 
		console.log("tfm_amazon_payments: error displaying html element: element with selector `"+key+"` doesn't exists");
		return false;
	}
}

function JSelm(key)
{
    var result = false;
    
	version_keys.mob =
    [
        false,
        true,
        "true or false depending of the version (mobile or desctop)"
    ];


    if(isMobileTfmAmazon)ism = 1;else ism = 0;
    if(version_keys[key])result = version_keys[key][ism];
   
    if(result && key!='mob')
    {
        if(result.substr(0, 6)=='debug:')
        {
            result = result.replace("debug:", "");
            console.log($(result));
        } 
    }

    return result;
}

function initAmazonLogoff(){  Lout(); }


function initAmazonConfirmation()
{
    if(selected_payment=="tfm_amazon_payments")
    {
		if(checkJSelm("confirmation_billing_address_edit"))
		$( JSelm("confirmation_billing_address_edit") ).hide();
        
        if(checkJSelm("confirmation_shipping_address_edit"))
        $( JSelm("confirmation_shipping_address_edit") ).hide();
	}
}


var amazonShipping_loadFlag;

function initAmazonShippingVisual()
{
	if(checkJSelm("shipping_hide_data"))
   $( JSelm("shipping_hide_data") ).hide();

    if( JSelm("mob") )
    {
		
		if(checkJSelm("shipping_widget_holder"))
		$( JSelm("shipping_widget_holder") ).append( AMAZON_SHIPPING.replace("xxxx", "") );
        
        if(packstation)
        {
				if(checkJSelm("shipping_form_reload_area_container"))
				{
					methods_selector_container = JSelm("shipping_form_reload_area_container");
					html_packstation  = '<input name="action" value="shipping" type="hidden">';
					html_packstation += (packstationMessage);

					
					$( methods_selector_container ).html(html_packstation);
				}
        }
    }
    else
    {   

		if(checkJSelm("shipping_widget_holder"))
		$( JSelm("shipping_widget_holder") ).after( AMAZON_SHIPPING.replace("xxxx", "") ); 
       
		if(checkJSelm("shipping_class_for_resize_right"))
		$( JSelm("shipping_class_for_resize_right") ).attr('class', JSelm("shipping_class_for_resize_new") );
		
		if(checkJSelm("shipping_class_for_resize_left"))
		$( JSelm("shipping_class_for_resize_left") ).attr('class', JSelm("shipping_class_for_resize_new") );
    }

        bwd_class = JSelm("shipping_widget_class");
        if(bwd_class != false)$( "#addressBookWidgetDiv" ).attr('class', bwd_class);
}


function initAmazonShipping()
{
   initAmazonShippingVisual();

WW = "460px";
HH = "260px";
WW_selctors = JSelm('shipping_widget_w');
HH_selctors = JSelm('shipping_widget_h');

if(WW_selctors != false)WW = WW_selctors;
if(HH_selctors != false)HH = HH_selctors;

//
design_obj = {size:{width: WW, height: HH}}

if( WW=='responsive' || HH=='responsive' )
design_obj = {designMode: 'responsive'};
//

if( checkJSsel("#addressBookWidgetDiv") )
{
	
var initAmazonShippingInterval = setInterval(function(){if(AMAZONLIBSLOADED){clearInterval(initAmazonShippingInterval);
	
		new OffAmazonPayments.Widgets.AddressBook({
				sellerId: SELLER_ID,
				design: design_obj,


				onOrderReferenceCreate: function(orderReference)
				{
				
					$.cookie("amazon_Order_id", orderReference.getAmazonOrderReferenceId(), { path: '/' }) ;
				
				},
				onAddressSelect: function(aa) {

						 //alert( $.cookie("amazon_Order_id") );

					 updateSelectedAddress();
				},
				onError: function (error) {
					   alert("ERROR! - The error object is tracked into the debug console!");
						console.log(error.getErrorCode());
						console.log(error.getErrorMessage());
						console.log(SELLER_ID);
						console.log(login_id);
						console.log(buton_picture);
						console.log(buton_size);
				}
		
		}).bind("addressBookWidgetDiv");
	
	
}}, intervalTimer);

}


}


function updateSelectedAddress()
{
        
     ajax_address = fillAddressURL+"="+$.cookie("amazon_Order_id");
        

    if( JSelm("mob") )
    {
           $.ajax({url: ajax_address, success: function(result){
                    data = jQuery.parseJSON(result);
                    if(data.refresh)
                    {
                        location.reload();
                    }else
                    {

                    }
           }});

    }else
    {   
		
	if(checkJSelm("shipping_form_reload_area_container") && checkJSelm("shipping_form_reload_area_result"))
	{
		
		methods_selector_container = JSelm("shipping_form_reload_area_container");
        methods_selector_result    = JSelm("shipping_form_reload_area_result");
		
		
		
        amazonShipping_loadFlag=true;

        if(account_mode_checkout=="amazon")
        $("#CheckoutModeAmazon").fadeTo("fast", 0.33);

        $( methods_selector_container ).fadeTo("fast", 0.33);

        $.ajax({url: ajax_address, success: function(result){
            data = jQuery.parseJSON(result);
            $( methods_selector_container ).fadeTo("fast", 1);

            //console.log("----");
            //console.log(data);
            //console.log(data.refresh);
       

            if(data.refresh || data.mess==1 || data.mess==2)
            {
                    $( methods_selector_container ).html("Loading ...");
                    $( methods_selector_container ).load( fillMehodsURL+" "+methods_selector_result, function( response, status, xhr ) {
                            
                            amazonShipping_loadFlag=false;

                            if(account_mode_checkout=="amazon")
                            $("#CheckoutModeAmazon").fadeTo("fast", 1);


                            if(data.mess==1)
                            {
                                html_packstation  = '<input name="action" value="shipping" type="hidden">';
                                html_packstation += (packstationMessage);
                                
                                $( methods_selector_container ).html(html_packstation);
                                hideShowShippingNavigButtons(true);
                            }
                            else if(data.mess==2)
                            {
                                html_empty  = '<input name="action" value="shipping" type="hidden">';
                                html_empty += (emptyMessage);
                                
                                $( methods_selector_container ).html(html_empty);
                                hideShowShippingNavigButtons(true);
                            }
                            else
                            {
								hideShowShippingNavigButtons(false);
                                $( methods_selector_container ).html( $(methods_selector_container+" form").html() );
                            }
                    });

            }else
            {   
                    $(methods_selector_container).fadeTo( "fast", 1 );
                    
                   amazonShipping_loadFlag=false;
                   if(account_mode_checkout=="amazon")
                   $("#CheckoutModeAmazon").fadeTo("fast", 1);
            }

        }});

	}
    }

       
}

function hideShowShippingNavigButtons(hide)
{
	if(hide)
	{
			//if(checkJSelm("shipping_next_button"))
			//$( JSelm("shipping_next_button") ).hide();
			
			//if(checkJSelm("shipping_prev_button"))
			//$( JSelm("shipping_prev_button") ).hide();
	}else
	{
			//if(checkJSelm("shipping_next_button"))
			//$( JSelm("shipping_next_button") ).show();
			
			//if(checkJSelm("shipping_prev_button"))
			//$( JSelm("shipping_prev_button") ).show();
	}
}

function initTogglePayments()
{
            $( "input[name='selected_payment']:radio" ).change(function() {
                 toggleAmazonWallet();
            });
}

function initAmazonPayment()
{
			if(checkJSelm("payment_hide_backbutton"))
			{
				if(cartType=='virtual')$( JSelm("payment_hide_backbutton") ).hide();
			}
			
			if(checkJSelm("payment_hide_data"))
            $( JSelm("payment_hide_data") ).hide();


			if( checkJSsel("#walletWidgetDiv") )
			{
				var PaymentWalletObject = {
					sellerId: SELLER_ID,
					onOrderReferenceCreate: function(orderReference) {

						orderReferenceId = orderReference.getAmazonOrderReferenceId();
						$.cookie("amazon_Order_id", orderReferenceId, { path: '/' });
		

					},
					onPaymentSelect: function (a) {
					   //alert( $.cookie("amazon_Order_id") );
					},
					design: {
						designMode: 'responsive'
					},
					onError: function (error) {}
				}

				if(cartType!='virtual')PaymentWalletObject.amazonOrderReferenceId = $.cookie("amazon_Order_id");

				var initAmazonPaymentInterval = setInterval(function(){if(AMAZONLIBSLOADED){clearInterval(initAmazonPaymentInterval);	
					
					new OffAmazonPayments.Widgets.Wallet(PaymentWalletObject).bind("walletWidgetDiv");
				
				}}, intervalTimer);
			}
}


function toggleAmazonWallet()
{
            
                if( 
                    $( "input[value='tfm_amazon_payments']:radio" ).attr('checked')=='checked'
                  ||$( "input[value='tfm_amazon_payments']:radio" ).attr('checked')==1  
                )
                {
                    $( "#walletWidgetDiv" ).show();

                }
                else
                {
                    $( "#walletWidgetDiv" ).hide();

                }
            
}

function initAmazonAccount()
{
    if( account_mode_checkout=="amazon" )
    {
		
		if(checkJSelm("account_message_parent") && checkJSelm("account_address_element"))
		{
			$( JSelm("account_message_parent") ).after( MESSAGE_BOX.replace("xxxx", "") );
			$( JSelm("account_address_element") ).hide();
		}
    }
}


function initAmazonLogin(page, type)
{

    login_id = "AmazonPayButton";
    buton_size = 'medium';
    
    buton_picture = "LwA";
    if( xtLoged!=1 )buton_picture = "PwA";

    var next_page_after_log = cPageURL;
    
    if(type.substring(0, 7)=="leftbox")
    {
        login_id = "AmazonPayButton_"+type;
        buton_size = 'small';
        buton_picture = "PwA";
    }
    
    if(type.substring(0, 10)=="productbox")
    {
        login_id = "AmazonPayButton_"+type;
        buton_size = 'small';
        buton_picture = "PwA";
    }
    
    if(type.substring(0, 11)=="productinfo")
    {
        buton_picture = "PwA";
        login_id = "AmazonPayButton_"+type;
    }
    
    

    if(type=="normal")
    {
            if(page=='login')
            {
				if(checkJSelm("login_login"))
                $( JSelm("login_login") ).after( AMAZON_BOX.replace("xxxx", "") );
                
                $("#AmazonPayButton").css('text-align', JSelm("login_login_css_align") );

                next_page_after_log = 'cart';

            }
            else if(page=='payment')
            {

					if(checkJSelm("payment_add_checkout_mode_button"))
					$( JSelm("payment_add_checkout_mode_button") ).after( AMAZON_CHECKOUT_MODE.replace("xxxx", "") );
                   
                   if(account_mode_checkout=="normal")
                        $("#CheckoutModeAmazon").hide();
                   else
                        $("#CheckoutModeNormal").hide();
              
            }
            else if(page=="cart")
            {
                buton_picture = "PwA";

				if(checkJSelm("cart_login"))
				{
					if( JSelm("cart_login_button_add_type")=="append" )
						 $( JSelm("cart_login") ).append(AMAZON_BOX.replace("xxxx", ""));
					else
						 $( JSelm("cart_login") ).after(AMAZON_BOX.replace("xxxx", ""));
				}

                 $(".AmazonPaymentBox").css('float', JSelm("cart_login_css_button_float") );
                 $("#AmazonPayButton").css('width', '210px');

                 next_page_after_log = 'checkout/shipping';
            }
            else if(page=="shipping")
            {

                   if(account_mode_checkout=="normal")
                   {
                        if(checkJSelm("shipping_add_checkout_mode_button"))
                        $( JSelm("shipping_add_checkout_mode_button") ).after( AMAZON_CHECKOUT_MODE.replace("xxxx", "") );
                        
                        $("#CheckoutModeAmazon").hide();
                   }
                    else
                   {
                        $( "#addressBookWidgetDiv" ).after( AMAZON_CHECKOUT_MODE.replace("xxxx", "") );
                        $("#CheckoutModeNormal").hide();

                          $("#CheckoutModeAmazon>form").submit(function( event )
                          {
                                if(amazonShipping_loadFlag) event.preventDefault();
                          });

                   }

            }
            else
            {
                
            }

    }
    else
    {
             if(page=='login')
             {
                next_page_after_log = 'cart';
             }
    }

	if( checkJSsel("#"+login_id) )
	{
                       OffAmazonPayments.Button(login_id, SELLER_ID, {
                            type: buton_picture,
                            size: buton_size,
                            color: buttonsTheme,
                            login_id: login_id,
                            authorization: function () {
								
								action = true;
								
								if(_SYSTEM_MOD_REWRITE=="true")url_param = '?';else url_param = '&';
								
								if( type.substring(0, 11)=="productinfo" )
								{
									
									QTY_FIELD = false;
									PARENTS_ELM = $("#"+$(this)[0].login_id).parents();
									PARENTS_ELM.each(function(){
										if( $(this).find('[name="qty"]').length )
										 {
											QTY_FIELD = $(this).find('[name="qty"]');
											return false;
										 }
									});
									
									if(QTY_FIELD)
									{
										qty = QTY_FIELD.val();
									}
									else
									{
										console.log("Amazon plugin: 'QTY' field was not found via the relative search. The qty value was set to '1'!");
										qty = 1;
									}
										pid = $($("#"+$(this)[0].login_id).parent()).attr("xt_pid");
										
										tfm_amazon_payment_product_button_clearFailLogins();
										$("body").trigger( "tfm_amazon_payment_product_button", [{qty: qty, pid: pid, type: 'product'}] );
										action = tfm_amazon_payment_product_button_checkFailLogins();
										
										next_page_after_log = 'product'+url_param+'product='+pid+'&qty='+qty+'&action=add_product&tfm_amazon_payment&type=product';
								
								}
								
								if( type.substring(0, 10)=="productbox" )
								{
									pid = $($("#"+$(this)[0].login_id).parent()).attr("xt_pid");
									
									tfm_amazon_payment_product_button_clearFailLogins();
									$("body").trigger( "tfm_amazon_payment_product_button", [{qty: 1, pid: pid, type: 'category'}] );
									action = tfm_amazon_payment_product_button_checkFailLogins();
									
									next_page_after_log = 'product'+url_param+'product='+pid+'&qty=1&action=add_product&tfm_amazon_payment&type=category';
								
								}
								
								if(action)
								{
									loginOptions = { scope: "profile payments:widget payments:shipping_address payments:billing_address", popup: true };
									authRequest = amazon.Login.authorize(loginOptions, function(response) {AmazonLoginDone(next_page_after_log)});
									$.cookie("amazon_shipping_updated", 0, { path: '/' });
								}
								
                            },
                            onError: function (error) {
                                   alert("ERROR! - The error object is tracked into the debug console!");
                                    console.log(error.getErrorCode());
									console.log(error.getErrorMessage());
                                    console.log(SELLER_ID);
                                    console.log(login_id);
                                    console.log(buton_picture);
                                    console.log(buton_size);
                            }
                        });
	}
         
}

function AmazonLoginDone(next_page)
{

     if( isAmazonLoged() )getUserDataLogin(authRequest.access_token, xtLoged, next_page);
}

function Lout()
{
    amazon.Login.logout();
}


function getUserDataLogin(TKN, doAfterLog, next_page)
{

    
    amazon.Login.retrieveProfile(TKN, function (profileResponse)
    {
        

        UDT = profileResponse.profile;
     

        $.cookie("amazon_Customer_id", UDT.CustomerId , { path: '/' });

        names = UDT.Name.split(" ");
        //if(names.length==0)names.push("AU-FirstName");
        //if(names.length==1)names.push("AU-LastName");

        mail = UDT.PrimaryEmail;
        if(mail=="")mail="au.mail@amazonuser.com";

        //dob = "01.01.2012";

        //street = "AU-street";
        
        zip = "";
        //zip = UDT.PostalCode;
        //if(zip=="")zip="1111";
        
        //town = "AU-town";

       if(doAfterLog==1 || true)
       {


          var Obj = {
                default_address_customers_firstname: names[0],
                default_address_customers_lastname: names[names.length-1],
                default_address_customers_dob: "",
                cust_info_customers_email_address: mail,
                default_address_customers_street_address: "",
                default_address_customers_postcode: "",
                default_address_customers_city: ""
            };
            

            F = $('<form method="POST"></form>').attr({
                    action: loginURL
            }).appendTo("body");

            var amazon_login_data = JSON.stringify(Obj);


            fillLoginFormWithAmazonData(F, amazon_login_data);

           
            
            $('<input>').attr({
                type: 'hidden',
                value: next_page,
                name: 'next_page_after_log'
            }).appendTo(F);


            $( F ).submit();

            // //$("#registerbox form").submit();
       }
       else if(doAfterLog==3 || doAfterLog==2)
       {
            location.reload();
       }
        
    });

}


function getAmazonToken() {
            return  $.cookie("amazon_Login_accessToken");
}


