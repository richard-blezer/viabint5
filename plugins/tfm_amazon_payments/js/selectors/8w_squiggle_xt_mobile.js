var version_keys = {

    'login_login':
    [
        "#register-login",
        ".ui-collapsible-content.ui-corner-bottom form",
        "The css selector on the login screen, after which will be placed login button."
    ],
    'login_login_css_align':
    [
        "right",
        "center",
        "The css property defining the position of the login button on the login screen."
    ],
    'shipping_widget_holder':
    [
        "#checkout-content .section.address h1",
        "#xtm-content",
        "The css selector on the shipping screen, where the address widget will be placed."
    ],
    'shipping_widget_holder_add_type':
    [
        "after",
        "append",
        "Defines how the widget should be placed according to the sected element."
    ],
    'shipping_hide_data':
    [
        "#checkout-content .section.address table.shipping_address,#checkout-content .checkout-border",
        "#xtm-content>:not( form[name^='shipping'], #xtm-checkout-nav, #xtm-checkout-nav+div+br ), form[name^='shipping_address']",
        "Selectors for the elements to be hidden on the shipping screen."
    ],
    'shipping_add_checkout_mode_button':
    [
        "#checkout-shipping .column.xt-grid-4 .box",
        "#xtm-content",
        "Selectors for the elements to be hidden on the shipping screen."
    ],
    'shipping_class_for_resize_right':
    [
        "#checkout-content .section.address",
        "",
        "Selector for right column of the screen."
    ],
    'shipping_class_for_resize_left':
    [
        "#checkout-content .section.shipping",
        "",
        "Selector for left column of the screen."
    ],
    'shipping_class_for_resize_new':
    [
        "w50p",
        "",
        "New class name for the left and right column of the screen."
    ],
    'shipping_form_reload_area_container':
    [
        "#checkout-content form:nth-child(4)",
        "",
        "Selector for the container of the refreshed area (shipping form)."
    ],
    'shipping_form_reload_area_result':
    [
        "#checkout-content form:nth-child(4)",
        "",
        "Selector for the result of the refreshed area (shipping form)."
    ],
    'payment_hide_data':
    [
        "#checkout-payment>div:nth-child(1)>.box:nth-child(2)",
        "#xtm-content>:not( form[name^='payment'], #xtm-checkout-nav, #xtm-checkout-nav+div+br ), form[name^='payment_address']",
        "Selectors for the elements to be hidden on the payment screen."
    ],
    'payment_add_checkout_mode_button':
    [
        "#checkout-payment>div:nth-child(1)>.box:nth-child(2)",
        "#xtm-content",
        "Selectors for the elements after which the switch mode button will be added."
    ],
    'payment_hide_backbutton':
    [
        ".column.xt-grid-12>p:eq(0)",
        "",
        "Selectors for the back button element to be hidden on the payment screen."
    ],

    'confirmation_billing_address_edit':
    [
        "",
        ".ui-block-b a:eq(2)",
        "Selectors for the button for edition of the address on the confirmation screen."
    ],
    'confirmation_shipping_address_edit':
    [
        "",
        ".ui-block-a a:eq(1)",
        "Selectors for the button for edition of the payment method on the confirmation screen."
    ],

    'cart_login':
    [
        "#cart-buttons .right-buttons",
        "#xtm-content>[name^='cart']",
        "Selectors for the element after which the login button will be placed."
    ],
    'cart_login_button_add_type':
    [
        "append",
        "after",
        "Defines how the button should be placed according to the sected element."
    ],
    'cart_login_css_button_float':
    [
        "right",
        "left",
        "The value for the CSS property `float` for the login button on the cart screen."
    ],
    'account_message_parent':
    [
        "#content h1",
        "",
        "Selector for the parent element after which the message will be placed."
    ],
    'account_address_element':
    [
        "#adress-book>:not(.amazon_orange, h1)",
        "",
        "Selector for the address box."
    ]


};

