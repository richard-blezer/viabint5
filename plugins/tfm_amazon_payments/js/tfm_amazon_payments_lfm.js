function fillLoginFormWithAmazonData(F, amazon_login_data)
{

        fields = ["customers_gender", "customers_firstname", "customers_lastname", "customers_company", "customers_company_2", "customers_company_3", "customers_street_address", "customers_suburb]", "customers_postcode", "customers_city", "customers_phone", "customers_mobile_phone", "customers_fax"];

        for(var i=0; i<fields.length; i++)
        {
                    $('<input>').attr({
                        type: 'hidden',
                        value: '',
                        name: 'default_address['+fields[i]+']'
                    }).appendTo(F);
        }

                    $('<input>').attr({
                        type: 'hidden',
                        value: 'default',
                        name: 'default_address[address_class]'
                    }).appendTo(F);

                    $('<input>').attr({
                        type: 'hidden',
                        value: 'DE',
                        name: 'default_address[customers_country_code]'
                    }).appendTo(F);


            $('<input>').attr({
                type: 'hidden',
                value: 'add_customer',
                name: 'action'
            }).appendTo(F);

            $('<input>').attr({
                type: 'hidden',
                value: 'customer',
                name: 'page'
            }).appendTo(F);

            $('<input>').attr({
                type: 'hidden',
                value: 'default',
                name: 'cust_info[customers_email_address]'
            }).appendTo(F);

            $('<input>').attr({
                type: 'hidden',
                value: 'EUR',
                name: 'customers_default_currency'
            }).appendTo(F);

            $('<input>').attr({
                type: 'hidden',
                value: 'en',
                name: 'customers_default_language'
            }).appendTo(F);

            $('<input>').attr({
                type: 'hidden',
                value: 'on',
                name: 'guest-account'
            }).appendTo(F);


            $('<input>').attr({
                type: 'hidden',
                value: amazon_login_data,
                name: 'amazon_login_data'
            }).appendTo(F);

            $('<input>').attr({
                type: 'hidden',
                value: '1',
                name: 'privacy'
            }).appendTo(F);


}
