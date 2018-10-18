<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');


/**
 * Class notify_on_restock_tools
 */
class order_edit_tools
{

    /** Parsed/Entschlüsselt einen mit createSignedRequest erzeugten string
     *  Liefert den payload zurück
     *
     * @param $signed_request - zu entschlüsselnder string
     * @param $secretKey - der zu verwendende key
     * @return bool|mixed
     */
    public static function parseSignedRequest($signed_request, $secretKey)
    {
        list($encoded_sig, $payload) = explode('.', $signed_request, 2);

        // decode the data
        $sig = self::base64UrlDecode($encoded_sig);
        $data = json_decode(self::base64UrlDecode($payload), true);

        // check sig
        $expected_sig = hash_hmac('sha256', $payload, $secretKey, $raw = false);
        if ($sig !== $expected_sig) {
            $msg = 'Bad signed request.';
            error_log($msg);
            return false;
        }

        return $data;
    }

    /** Verschlüsselt Daten zu einem string
     *
     * @param $payload - die zu verschlüsselnden daten
     * @param $secretKey - der zu verwendende key
     * @return string
     */
    public static function createSignedRequest($payload, $secretKey)
    {
        $payload = json_encode($payload);
        $payload = self::base64UrlEncode($payload);
        $sig = hash_hmac("sha256", $payload, $secretKey, $raw = false);
        $sig = self::base64UrlEncode($sig);
        return $sig.'.'. $payload;
    }

    /** wie facebook
     *
     * @param $input
     * @return string
     */
    public static function base64UrlDecode($input) {
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /** wie facebook
     *
     * @param $input
     * @return mixed
     */
    public static function base64UrlEncode($input) {
        $str = strtr(base64_encode($input), '+/', '-_');
        $str = str_replace('=', '', $str);
        return $str;
    }

    /** ermittelt die email-adresse eines im system eingeloggten nutzers
     *
     * @param $customer
     * @return email des angemeldeten nutzers, sonst null ( wenn nicht eingeloggt)
     */
    public static function signedInCustomersEmail($customer)
    {
        if ($customer != null
            && $customer->customer_info['customers_id']
            && array_key_exists('customers_email_address', $customer->customer_info)
            && !is_null( $customer->customer_info['customers_email_address']))
        {
            return $customer->customer_info['customers_email_address'];
        }
        return null;
    }

    public static function makeAsyncCall($host, $path)
    {
        $timeout = 30;
        try {
            $socket = stream_socket_client("$host:80",
                $errno, $errstr, $timeout, STREAM_CLIENT_ASYNC_CONNECT|STREAM_CLIENT_CONNECT);

            //socket_set_nonblock($socket);

            $packet  = "GET {$path} HTTP/1.0\r\n";
            $packet .= "Host: {$host}\r\n";
            $packet .= "Connection: close\r\n\r\n";
            //error_log(4);
            fwrite($socket, $packet);
            //error_log(7);
            fclose($socket);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    /** Sendet eine 'async' request
     *
     * @param $url
     * @param array $params
     */
    function makeAsyncCall2($url, $params = array()){

        $post_params = array();

        foreach ($params as $key => &$val) {
            if (is_array($val)) $val = implode(',', $val);
            $post_params[] = $key.'='.urlencode($val);
        }
        $post_string = implode('&', $post_params);

        $parts=parse_url($url);

        $fp = fsockopen($parts['host'],
            isset($parts['port'])?$parts['port']:80,
            $errno, $errstr, 30);

        stream_set_blocking($fp, 0);

        $out = "POST ".$parts['path']." HTTP/1.1\r\n";
        $out.= "Host: ".$parts['host']."\r\n";
        $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out.= "Content-Length: ".strlen($post_string)."\r\n";
        $out.= "Connection: Close\r\n\r\n";
        if (isset($post_string)) $out.= $post_string;

        fwrite($fp, $out);
        fclose($fp);

    }

}

class notify_on_restock_exception extends Exception
{
    // nur ein test ob beim commit die jira issue aus dem commit kommentar übernaommen wird
    // nochmal
}