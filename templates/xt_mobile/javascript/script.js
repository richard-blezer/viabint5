$(document).ready(function(){

    /******************************************************************************/
    /*  Product Page                                                              */
    /******************************************************************************/

    $('.xtm-qty-plus').click(function(){
        var qty = $(this).parents('.xtm-qty').find('input');
        qty.val(qty.val()*1+1);

        return false;
    });
    $('.xtm-qty-minus').click(function(){
        var qty = $(this).parents('.xtm-qty').find('input');
        if(qty.val()*1>1){
            qty.val(qty.val()*1-1);
        }

        return false;
    });

    /******************************************************************************/
    /*  Cart Page                                                                 */
    /******************************************************************************/

    $('.xtm-cart-qty-plus').click(function(){
        var qty = $(this).parents('.xtm-cart-item').find('input[name="qty[]"]');
        qty.val(qty.val()*1+1);
        $(this).parents('form').submit();
    });
    $('.xtm-cart-qty-minus').click(function(){
        var qty = $(this).parents('.xtm-cart-item').find('input[name="qty[]"]');
        if(qty.val()*1>1){
            qty.val(qty.val()*1-1);
            $(this).parents('form').submit();
        }
    });

    $('.xtm-cart-delete').click(function(){
        var del = $(this).parents('.xtm-cart-item').find('input[name="cart_delete[]"]');
        del.click();
        $(this).parents('form').submit();

    });

    /******************************************************************************/
    /*  Footer                                                                    */
    /******************************************************************************/

    function xtm_footer_scroller(){

        if($('#xtm-footer')[0] != undefined){
            var hbh = $('#xtm-header-bar').outerHeight();
            var fbh = $('#xtm-footer-bar').outerHeight();
            var ch = $('#xtm-content').outerHeight();
            var cm = parseFloat($('#xtm-content').css('margin-bottom'));
            var fh = $('#xtm-footer').outerHeight();
            var bh = $('body').outerHeight();

            var max = ch + fh + hbh + fbh;

            if(bh > max) {
                $('#xtm-footer').addClass('fixed');
                $('#xtm-footer').css({'bottom':0});
                //$('#content').css({'margin-bottom':fbh});
                $('#xtm-footer').css({'margin-bottom':fbh});
            } else {
                $('#xtm-footer').removeClass('fixed');
                $('#xtm-footer').css({'margin-bottom':0});
            }
        }
    }


    setTimeout(function(){
        xtm_footer_scroller();
    }, 500);

    $(window).resize(function (event) {
        xtm_footer_scroller();
    });

    $(document).mouseup(function(){
        setTimeout(function(){xtm_footer_scroller()}, 200);
    });

});

/******************************************************************************/
/*  POPUP                                                                     */
/******************************************************************************/
$( document ).on( "pageinit", function() {
    $( ".xtm-popup-image" ).on({
        popupbeforeposition: function() {
            var maxHeight = $( window ).height() - 60 + "px";
            $( ".xtm-popup-image img" ).css( "max-height", maxHeight );
        }
    });

    $( "#popupPanel" ).on({
        //Because the popup container is positioned absolute,
        //you can't make the panel full height with height:100%;.
        //This small script sets the height of the popup
        //to the actual screen height.
        popupbeforeposition: function() {
            var h = $( window ).height();
            console.log('adadasd');
            $( "#popupPanel" ).css( "height", h );
        }
    });
});

/******************************************************************************/
/*  SLIDER                                                                    */
/******************************************************************************/

$(document).ready(function() {
    $('.flexslider').flexslider({
        animation: "slide",
        animationLoop: true
    });
});