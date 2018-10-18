    $(".add2cart").submit(function () {  
        console.log('DEDEDD');
        var url = $(this).attr("action")+'?';
        $(':input', this).each(function(index) {
     
            url = url +  '&' + $(this).attr('name') + '=' + $(this).attr('value');
    
        })
        $.fancybox({
            'href'        : url,
            'width'				: '50%',
            'height'			: '50%',
            'type'				: 'iframe'
        });
        return false;
    });
