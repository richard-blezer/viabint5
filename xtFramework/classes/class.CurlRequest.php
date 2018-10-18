<?php

class CurlRequest
{

    protected $_url;
    protected $_curlOpts = array();
    protected $_args;
    protected $_result;
    protected $_certFile;
    protected $_userAgent = 'Mozilla/5.0 (Windows; I; Windows NT 5.1; ru; rv:1.9.2.13) Gecko/20100101 Firefox/4.0';

    public function __construct($url)
    {
        $this->_certFile = _SRV_WEBROOT . 'media/certificate/cacert.pem';
        $this->url($url);
        $this->_curlOpts['RETURNTRANSFER'] = true;
        $this->_curlOpts['HEADER'] = false;
      //  $this->_curlOpts['FOLLOWLOCATION'] = true;
        $this->_curlOpts['USERAGENT'] = $this->_userAgent;
    }

    public function url($url)
    {
        if (strpos($url, 'https://') === 0) {
            $this->certificate();
        }
        $this->_url = $url;
    }

    public function data($data = null)
    {
        if (is_null($data)) {
            return $this->_args;
        }
        $data = is_array($data) ? $data : array();
        $this->_args = http_build_query($data);
        return $this;
    }

    public function get()
    {
        if (!empty($this->_args)) {
            $url = $this->_url . '?' . $this->_args;
        } else {
            $url = $this->_url;
        }
        $opts = array();
        foreach ($this->_curlOpts as $key => $value) {
            $opts[constant('CURLOPT_' . strtoupper($key))] = $value;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, $opts);
        $this->_result = curl_exec($ch);
        if (!$this->_result) {
            $this->_error = curl_error($ch);
        }
        curl_close($ch);
        return $this;
    }

    public function post()
    {
        $opts = array();
        foreach ($this->_curlOpts as $key => $value) {
            $opts[constant('CURLOPT_' . strtoupper($key))] = $value;
        }
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = $this->_args;
        $ch = curl_init($this->_url);
        curl_setopt_array($ch, $opts);
        $this->_result = curl_exec($ch);
        if (!$this->_result) {
            $this->_error = curl_error($ch);
        }
        curl_close($ch);
        return $this;
    }

    public function certificate($check = true, $code = 2)
    {
        if ($check) {
            $code = intval($code);
            if ($code !== 1 || $code !== 2) {
                $code = 2;
            }
            $this->_curlOpts['SSL_VERIFYPEER'] = true;
            $this->_curlOpts['SSL_VERIFYHOST'] = $code;
            $this->_curlOpts['CAINFO'] = $this->_certFile;
        } else {
            $this->_curlOpts['SSL_VERIFYPEER'] = false;
        }
        return $this;
    }

    public function result()
    {
        return $this->_result;
    }

    public function error()
    {
        return $this->_error;
    }

}

?>