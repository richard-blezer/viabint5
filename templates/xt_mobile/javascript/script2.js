$(document).bind('mobileinit',function(){
    //need for plus/minus/delete function in cart
    //whithout this lines it sends each call only on time
    $.extend(  $.mobile , {
        ajaxEnabled: false,
        ajaxFormsEnabled: false,
        ajaxLinksEnabled: false
    });
});