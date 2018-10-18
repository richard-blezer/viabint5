//######### PREDEFINED VALUES DESKTOP VERSION ##############################################

//p.1
var amazon_shipping_widget_holder_id = "#amazon_shipping_widget_holder";
//FILE: `pages/checkout/subpage_shipping.html`
//The id of the element after which the address widget will be placed.

//p.2
var amazon_shipping_class_for_resize_right_id = "#amazon_shipping_class_for_resize_right";
//FILE: `pages/checkout/subpage_shipping.html`
//The id of the element holding left box of the page

//p.3
var amazon_shipping_class_for_resize_left_id = "#amazon_shipping_class_for_resize_left";
//FILE: `pages/checkout/subpage_shipping.html`
//The id of the element holding right box of the page

//p.4
var amazon_shipping_class_for_resize_new_css = "col-md-6";
//FILE: `pages/checkout/subpage_shipping.html`
//The name of the new css style for p.2 and p.3

//p.5
var amazon_payment_hide_data_id = "#amazon_payment_hide_data";
//FILE:  `pages/checkout/subpage_payment.html`
//The id of the element for the original box, holding the shipping address

//p.6
var amazon_payment_hide_backbutton_id = "#amazon_payment_hide_backbutton";
//FILE:  `pages/checkout/subpage_payment.html`
//The id of the element for the `Back button`

//p.7
var amazon_cart_login_id = "#amazon_cart_login";
//FILE:  `pages/cart.html`
//The id of the element in which, the login button will be placed

//p.8
var amazon_account_message_parent_id = "#amazon_account_message_parent";
//FILE:  `pages/address_book.html`
//The id of the element after which the Amazon message will be displayed

//##########################################################################



var version_keys = {

    'login_login':
    [
        "#loginbox",
        "",
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
        amazon_shipping_widget_holder_id,
        "",
        "The css selector on the shipping screen, where the address widget will be placed.",
        "checkout/subpage_shipping.html",
        ""
    ],
    'shipping_widget_holder_add_type':
    [
        "after",
        "",
        "Defines how the widget should be placed according to the sected element."
    ],
    'shipping_hide_data':
    [
        amazon_shipping_widget_holder_id,
        "",
        "Selectors for the elements to be hidden on the shipping screen.",
        "",
        "checkout/subpage_shipping.html"
    ],
    'shipping_add_checkout_mode_button':
    [
        amazon_shipping_widget_holder_id,
        "",
        "Selectors for the elements to be hidden on the shipping screen."
    ],
    'shipping_class_for_resize_right':
    [
        amazon_shipping_class_for_resize_right_id,
        "",
        "Selector for right column of the screen.",
        "checkout/subpage_shipping.html"
    ],
    'shipping_class_for_resize_left':
    [
        amazon_shipping_class_for_resize_left_id,
        "",
        "Selector for left column of the screen.",
        "checkout/subpage_shipping.html"
    ],
    'shipping_class_for_resize_new':
    [
        amazon_shipping_class_for_resize_new_css,
        "",
        "New class name for the left and right column of the screen.",
        "checkout/subpage_shipping.html"
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
    'shipping_widget_w':
    [
        "460px",
        "",
        "The width of the Amazon shipping widget (min. 280)"
    ],
    'shipping_widget_h':
    [
        "260px",
        "",
        "The height of the Amazon shipping widget"
    ],
    'payment_hide_data':
    [
        amazon_payment_hide_data_id,
        "",
        "Selectors for the elements to be hidden on the payment screen.",
        "checkout/subpage_payment.html"
    ],
    'payment_add_checkout_mode_button':
    [
        "",
        "",
        "Selectors for the elements after which the switch mode button will be added."
    ],
    'payment_hide_backbutton':
    [
        amazon_payment_hide_backbutton_id,
        "",
        "Selectors for the back button element to be hidden on the payment screen.",
        "checkout/subpage_payment.html"
    ],

    'confirmation_billing_address_edit':
    [
        ".payment-address",
        "",
        "Selectors for the button for edition of the address on the confirmation screen.",
        "checkout/subpage_confirmation.html"
    ],
    'confirmation_shipping_address_edit':
    [
        "",
        "",
        "Selectors for the button for edition of the payment method on the confirmation screen.",
        "checkout/subpage_confirmation.html"
    ],

    'cart_login':
    [
        amazon_cart_login_id,
        "",
        "Selectors for the element after which the login button will be placed.",
        "cart.html"
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
        amazon_account_message_parent_id,
        "",
        "Selector for the parent element after which the message will be placed.",
        "address_book.html"
    ],
    'account_address_element':
    [
        "#adress-book>:not(.amazon_orange, "+amazon_account_message_parent_id+")",
        "",
        "Selector for the address box.",
        "address_book.html"
    ]


};

