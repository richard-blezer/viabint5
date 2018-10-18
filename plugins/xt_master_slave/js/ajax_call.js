
function getSelected()
{
	var data = '';
	$('#ajax_options_holder input, #ajax_options_holder select').each(
			function(index){  
				var input = $(this);
				if (input.is(':radio') ) 
				{
					var name = input.attr('name').replace("id[","");
					name = name.replace("]","");
					if (input.is(':checked')) data += name+'-'+ input.val() +',';
				}
				if (input.is('select')) 
				{
					var name = input.attr('name').replace("id[","");
					name = name.replace("]","");
					data += name+'-'+ input.val() +',';
				}
			}
		);
		return data;
}

function loadOptions (el,main)
{
	
	if (!$(".add-to-cart").is(':visible')) getOptions (el,main);
	
	$("#tabs").load(window.location.href +" #tabs", function() { 
   	 SetTabs();
	});
	
}

function getOptions (el,main)
{
	$("#latest_clicked").val(el);
	var addTo = $(".addthis_toolbox").html();
	var selected = getSelected();
    $.ajax({
        type: "GET",
        url: "index.php",
        data: "page=xt_master_slave&sected_ids="+selected+"&pID="+$('input[name=ajax_pID]').val()+"&latested_clicked="+el+"&main="+main,
        success: function(data){
        	
        	var d = JSON.parse(data);
        	
        	if (d.num=='1')
        	{
        		if (d.product!='') $("#product").html(d.product);
        		$("#options_div").html(d.content);
        	
           }
            else 
            {
            	if (d.product!='') $("#product").html(d.product);
            	$("#options_div").html(d.content);
            	$("#out_of_stock_holder").html(d.error);
            	$("label[for='"+d.latested_clicked+"']").addClass("default_option_deactivated_selected");
            }
        }
    });
    
    $("#tabs").load(window.location.href +" #tabs", function() { 
   	 SetTabs();
	});
	$("#product-images").load(window.location.href +" #product-images", function() { 
    	$('.cloud-zoom, .cloud-zoom-gallery').CloudZoom();
	});
	
}


function SetTabs()
{
	$('ul.tabs').each(function(){
		
		var $active, $content, $links = $(this).find('a');
	
		$active = $links.first().addClass('active');
		$content = $($active.attr('rel'));
				
		$links.not(':first').each(function () {
			$($(this).attr('rel')).hide();
		});
	
		$(this).on('click', 'a', function(e){
						
			$active.removeClass('active');
			$content.hide();
						
			$active = $(this);
			$content = $($(this).attr('rel'));

			$active.addClass('active');
			$content.show();
			
			e.preventDefault();
		});
	});
	
	  $("#guest").click( function(e){
        $("#cust_info_customers_password").val('');
        $("#cust_info_customers_password_confirm").val('');
		$('#guest-account').slideUp(250);
	});
   
}



