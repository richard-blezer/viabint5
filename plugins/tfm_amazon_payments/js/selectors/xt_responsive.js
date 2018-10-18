var version_keys = {

    'login_login':
    [
        "#content  div > h1",
        ".ui-collapsible-content.ui-corner-bottom form",
        "The css selector on the login screen, after which will be placed login button."
    ],
    'login_login_css_align':
    [
        "right",
        "",
        "The css property defining the position of the login button on the login screen."
    ],
    'shipping_widget_holder':
    [
        ".well.shipping-address.address",
        "",
        "The css selector on the shipping screen, where the address widget will be placed."
    ],
    'shipping_widget_holder_add_type':
    [
        "after",
        "",
        "Defines how the widget should be placed according to the sected element."
    ],
    'shipping_hide_data':
    [
        ".well.shipping-address.address",
        "",
        "Selectors for the elements to be hidden on the shipping screen."
    ],
    'shipping_add_checkout_mode_button':
    [
        ".well.shipping-address.address",
        "",
        "Selectors for the elements to be hidden on the shipping screen."
    ],
    'shipping_widget_w':
    [
        "responsive",
        "",
        "The width of the Amazon shipping widget (min. 280)"
    ],
    'shipping_widget_h':
    [
        "",
        "",
        "The height of the Amazon shipping widget"
    ],
    'shipping_class_for_resize_right':
    [
        "",
        "",
        "Selector for right column of the screen."
    ],
    'shipping_class_for_resize_left':
    [
        "",
        "",
        "Selector for left column of the screen."
    ],
    'shipping_class_for_resize_new':
    [
        "",
        "",
        "New class name for the left and right column of the screen."
    ],
    'shipping_form_reload_area_container':
    [
        "form[name^='shipping']:not( form[name^='shipping_address'] )",
        "",
        "Selector for the container of the refreshed area (shipping form)."
    ],
    'shipping_form_reload_area_result':
    [
        "form[name^='shipping']:not( form[name^='shipping_address'] )",
        "",
        "Selector for the result of the refreshed area (shipping form)."
    ],
    'payment_hide_data':
    [
        ".well.payment-address.address",
        "",
        "Selectors for the elements to be hidden on the payment screen."
    ],
    'payment_add_checkout_mode_button':
    [
        ".well.payment-address.address",
        "",
        "Selectors for the elements after which the switch mode button will be added."
    ],
    'payment_hide_backbutton':
    [
        ".btn.btn-default.pull-left",
        "",
        "Selectors for the back button element to be hidden on the payment screen."
    ],

    'confirmation_billing_address_edit':
    [
        ".well.payment-address.address",
        "",
        "Selectors for the button for edition of the address on the confirmation screen."
    ],
    'confirmation_shipping_address_edit':
    [
        "",
        "",
        "Selectors for the button for edition of the payment method on the confirmation screen."
    ],

    'cart_login':
    [
        "p.pull-right:not(.pull-right.pull-none-xs)",
        "",
        "Selectors for the element after which the login button will be placed."
    ],
    'cart_login_button_add_type':
    [
        "append",
        "",
        "Defines how the button should be placed according to the sected element."
    ],
    'cart_login_css_button_float':
    [
        "right",
        "",
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
