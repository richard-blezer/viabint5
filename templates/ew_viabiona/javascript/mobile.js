$(document).ready(function () {

  //
  // cart / checkout
  //

  (function () {
    $('.cart-btn-update').remove();
    $('.show-payment-address').click(function () {
      $('.payment-address').removeClass('hidden-xs');
      $('.different-payment-address').hide();
    });
    $('#checkout-confirmation-order-btn').click(function () {
      var agb = $('input[name=conditions_accepted]');
      if (agb.length && !agb.is(':checked')) {
        var msg = $(this).data('agb-error-msg');
        if (msg) {
          alert(msg);
        }
        $('html, body').animate({
            scrollTop: agb.offset().top
        }, 1000);
        return false;
      }
      return true;
    });
  })();

  //
  // startpage mobile nav
  //

  (function () {
    var nav = $('.box-categories[data-highlight]');
    var id = nav.data('highlight');
    var cat = nav.find('#cid-'+id);

    cat.addClass('active');
    cat.closest('.collapse')
      .collapse('show');
  })();

  //
  // teaser logic
  //

  (function () {
    var teaser = $('#ew_viabiona_teaser'),
        keks = { key: 'ew_viabiona_teaser_show_mobile', value: 'false' };

    if (esseKeks(keks.key) === false) {
      teaser.hide().removeClass('hidden-xs').fadeIn();
      backeKeks(keks.key, keks.value, 365);
    }
  })();

  //
  // show/hide password field values
  //

  (function () {
    var msg = 'Passwort ein-/ausblenden';

    $('input[type=password]').each(function () {
      var pwf = $(this);

      pwf.password({
        eyeClass: 'fa',
        eyeOpenClass: 'fa-eye',
        eyeCloseClass: 'fa-eye-slash',
        message: msg
      });

      var parent = pwf.parent();
      var text = $('<p></p>', {
        class: 'pw-show-hide-text help-block cursor-pointer text-right',
        text: msg,
        click: function () {
          parent.find('.input-group-addon').click();
        }
      });

      parent
        .addClass('pw-show-hide')
        .after(text);
    });
  })();

});
