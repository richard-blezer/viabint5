$(document).ready(function() {
    $(".add2cart").bind("submit", function() {
        var params = $(this).serializeArray();
        params.push({
            "name": "ajax",
            "value": "true"
        });
        $.fancybox.showActivity();
        $.ajax({
            type: "POST",
            cache: false,
            url: $(this).attr('action'),
            data: params,
            success: function(data) {
                $.fancybox.hideActivity();
                $.fancybox(data, {
                    'width': 300,
                    'height': 175,
                    'showCloseButton': false,
                    'scrolling': 'no',
                    'onClosed': function() {
                        getCart();
                    }
                });
            }
        });

        return false;
    });

    function getCart() {
        $('#bcb').load('index.php?action=getCart');
        return false;
    }
});
