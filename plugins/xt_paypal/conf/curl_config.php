<?php
if (XT_PAYPAL_SSL_VERSION=='autodetect'){
   $curlVersion = curl_version();
    $curlSslBackend = $curlVersion['ssl_version'];
    if (substr_compare($curlSslBackend, "NSS/", 0, strlen("NSS/")) === 0) {
       return array(
            'SSL_VERSION' => 1,
            'CIPHER_LIST' => "",
        );
    }
    else {
        return array(
            'SSL_VERSION' => CURL_SSLVERSION_TLSv1,
            'CIPHER_LIST' => "TLSv1",
        );
    } 
}else{
     return array(
            'SSL_VERSION' => XT_PAYPAL_SSL_VERSION,
            'CIPHER_LIST' => (XT_PAYPAL_CIPHER_LIST=='autodetect')? '':XT_PAYPAL_CIPHER_LIST,
        );
}
