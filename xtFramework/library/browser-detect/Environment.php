<?php

/**
 *
 * Modified by xt:Commerce International Ldt.
 *
 */

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * @package Library
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

/**
 * Reads the environment variables
 *
 * The class returns the environment variables (which are stored in the PHP
 * $_SERVER array) independent of the operating system.
 *
 * Usage:
 *
 *     echo Environment::get('scriptName');
 *     echo Environment::get('requestUri');
 *
 * @package   Library
 * @author    Leo Feyer <https://github.com/leofeyer>
 * @copyright Leo Feyer 2005-2013
 */
class Environment
{

    /**
     * Object instance (Singleton)
     * @var \Environment
     */
    static $objInstance;

    /**
     * Cache
     * @var array
     */
    static $arrCache = array();


    /**
     * Return an environment variable
     *
     * @param string $strKey The variable name
     *
     * @return mixed The variable value
     */
    public static function get($strKey)
    {
        if (isset(self::$arrCache[$strKey]))
        {
            return self::$arrCache[$strKey];
        }

        if (in_array($strKey, get_class_methods('Environment')))
        {
            self::$arrCache[$strKey] = self::$strKey();
        }
        else
        {
            $arrChunks = preg_split('/([A-Z][a-z]*)/', $strKey, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
            $strServerKey = strtoupper(implode('_', $arrChunks));
            self::$arrCache[$strKey] = $_SERVER[$strServerKey];
        }

        return self::$arrCache[$strKey];
    }


    /**
     * Set an environment variable
     *
     * @param string $strKey   The variable name
     * @param mixed  $varValue The variable value
     */
    public static function set($strKey, $varValue)
    {
        self::$arrCache[$strKey] = $varValue;
    }


    /**
     * Return the absolute path to the script (e.g. /home/www/html/website/index.php)
     *
     * @return string The absolute path to the script
     */
    protected static function scriptFilename()
    {
        return str_replace('//', '/', str_replace('\\', '/', (PHP_SAPI == 'cgi' || PHP_SAPI == 'isapi' || PHP_SAPI == 'cgi-fcgi' || PHP_SAPI == 'fpm-fcgi') && ($_SERVER['ORIG_PATH_TRANSLATED'] ? $_SERVER['ORIG_PATH_TRANSLATED'] : $_SERVER['PATH_TRANSLATED']) ? ($_SERVER['ORIG_PATH_TRANSLATED'] ? $_SERVER['ORIG_PATH_TRANSLATED'] : $_SERVER['PATH_TRANSLATED']) : ($_SERVER['ORIG_SCRIPT_FILENAME'] ? $_SERVER['ORIG_SCRIPT_FILENAME'] : $_SERVER['SCRIPT_FILENAME'])));
    }


    /**
     * Return the relative path to the script (e.g. /website/index.php)
     *
     * @return string The relative path to the script
     */
    protected static function scriptName()
    {
        return (PHP_SAPI == 'cgi' || PHP_SAPI == 'isapi' || PHP_SAPI == 'cgi-fcgi' || PHP_SAPI == 'fpm-fcgi') && ($_SERVER['ORIG_PATH_INFO'] ? $_SERVER['ORIG_PATH_INFO'] : $_SERVER['PATH_INFO']) ? ($_SERVER['ORIG_PATH_INFO'] ? $_SERVER['ORIG_PATH_INFO'] : $_SERVER['PATH_INFO']) : ($_SERVER['ORIG_SCRIPT_NAME'] ? $_SERVER['ORIG_SCRIPT_NAME'] : $_SERVER['SCRIPT_NAME']);
    }


    /**
     * Alias for scriptName()
     *
     * @return string The script name
     */
    protected static function phpSelf()
    {
        return self::scriptName();
    }


    /**
     * Return the document root (e.g. /home/www/user/)
     *
     * Calculated as SCRIPT_FILENAME minus SCRIPT_NAME as some CGI versions
     * and mod-rewrite rules might return an incorrect DOCUMENT_ROOT.
     *
     * @return string The document root
     */
    protected static function documentRoot()
    {
        $strDocumentRoot = '';
        $arrUriSegments = array();
        $scriptName = self::get('scriptName');
        $scriptFilename = self::get('scriptFilename');

        // Fallback to DOCUMENT_ROOT if SCRIPT_FILENAME and SCRIPT_NAME point to different files
        if (basename($scriptName) != basename($scriptFilename))
        {
            return str_replace('//', '/', str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])));
        }

        if (substr($scriptFilename, 0, 1) == '/')
        {
            $strDocumentRoot = '/';
        }

        $arrSnSegments = explode('/', strrev($scriptName));
        $arrSfnSegments = explode('/', strrev($scriptFilename));

        foreach ($arrSfnSegments as $k=>$v)
        {
            if ($arrSnSegments[$k] != $v)
            {
                $arrUriSegments[] = $v;
            }
        }

        $strDocumentRoot .= strrev(implode('/', $arrUriSegments));

        if (strlen($strDocumentRoot) < 2)
        {
            $strDocumentRoot = substr($scriptFilename, 0, -(strlen($strDocumentRoot) + 1));
        }

        return str_replace('//', '/', str_replace('\\', '/', realpath($strDocumentRoot)));
    }


    /**
     * Return the request URI [path]?[query] (e.g. /contao/index.php?id=2)
     *
     * @return string The request URI
     */
    protected static function requestUri()
    {
        if (!empty($_SERVER['REQUEST_URI']))
        {
            return $_SERVER['REQUEST_URI'];
        }
        else
        {
            return '/' . preg_replace('/^\//', '', self::get('scriptName')) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
        }
    }


    /**
     * Return the first eight user languages as array
     *
     * @return array The languages array
     */
    protected static function httpAcceptLanguage()
    {
        $arrAccepted = array();
        $arrLanguages = explode(',', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']));

        foreach ($arrLanguages as $strLanguage)
        {
            $strTag = substr($strLanguage, 0, 2);

            if ($strTag != '' && preg_match('/^[a-z]{2}$/', $strTag))
            {
                $arrAccepted[] = $strTag;
            }
        }

        return array_slice(array_unique($arrAccepted), 0, 8);
    }


    /**
     * Return accepted encoding types as array
     *
     * @return array The encoding types array
     */
    protected static function httpAcceptEncoding()
    {
        return array_values(array_unique(explode(',', strtolower($_SERVER['HTTP_ACCEPT_ENCODING']))));
    }


    /**
     * Return the user agent as string
     *
     * @return string The user agent string
     */
    protected static function httpUserAgent()
    {
        $ua = strip_tags($_SERVER['HTTP_USER_AGENT']);
        $ua = preg_replace('/javascript|vbscri?pt|script|applet|alert|document|write|cookie/i', '', $ua);

        return substr($ua, 0, 255);
    }


    /**
     * Return the HTTP Host
     *
     * @return string The host name
     */
    protected static function httpHost()
    {
        $host = $_SERVER['HTTP_HOST'];

        if (empty($host))
        {
            $host = $_SERVER['SERVER_NAME'];

            if ($_SERVER['SERVER_PORT'] != 80)
            {
                $host .= ':' . $_SERVER['SERVER_PORT'];
            }
        }

        return preg_replace('/[^A-Za-z0-9\[\]\.:-]/', '', $host);
    }


    /**
     * Return the HTTP X-Forwarded-Host
     *
     * @return string The name of the X-Forwarded-Host
     */
    protected static function httpXForwardedHost()
    {
        return preg_replace('/[^A-Za-z0-9\[\]\.:-]/', '', $_SERVER['HTTP_X_FORWARDED_HOST']);
    }


    /**
     * Return true if the current page was requested via an SSL connection
     *
     * @return boolean True if SSL is enabled
     */
    protected static function ssl()
    {
        return ($_SERVER['SSL_SESSION_ID'] || $_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1);
    }


    /**
     * Return the current URL without path or query string
     *
     * @return string The URL
     */
    protected static function url()
    {
        $xhost = self::get('httpXForwardedHost');
        $protocol = self::get('ssl') ? 'https://' : 'http://';

        return $protocol . (($xhost != '') ? $xhost . '/' : '') . self::get('httpHost');
    }


    /**
     * Return the real REMOTE_ADDR even if a proxy server is used
     *
     * @return string The IP address of the client
     */
    protected static function ip()
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match('/^[A-Fa-f0-9, \.\:]+$/', $_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return substr($_SERVER['REMOTE_ADDR'], 0, 64);
    }


    /**
     * Return the SERVER_ADDR
     *
     * @return string The IP address of the server
     */
    protected static function server()
    {
        $strServer = !empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];

        // Special workaround for Strato users
        if (empty($strServer))
        {
            $strServer = @gethostbyname($_SERVER['SERVER_NAME']);
        }

        return $strServer;
    }


    /**
     * Return the relative path to the base directory (e.g. /path)
     *
     * @return string The relative path to the installation
     */
    protected static function path()
    {
        return TL_PATH;
    }


    /**
     * Return the relativ path to the script (e.g. index.php)
     *
     * @return string The relative path to the script
     */
    protected static function script()
    {
        return preg_replace('/^' . preg_quote(TL_PATH, '/') . '\/?/', '', self::get('scriptName'));
    }


    /**
     * Return the relativ path to the script and include the request (e.g. index.php?id=2)
     *
     * @return string The relative path to the script including the request string
     */
    protected static function request()
    {
        $strRequest = preg_replace('/^' . preg_quote(TL_PATH, '/') . '\/?/', '', self::get('requestUri'));

        // From version 2.9, do not fallback to $this->script
        // anymore if the request string is empty (see #1844).

        // IE security fix (thanks to Michiel Leideman)
        $strRequest = str_replace(array('<', '>', '"'), array('%3C', '%3E', '%22'), $strRequest);

        // Do not urldecode() here (thanks to Russ McRee)!
        return $strRequest;
    }


    /**
     * Return the URL and path that can be used in a <base> tag
     *
     * @return string The base URL
     */
    protected static function base()
    {
        return self::get('url') . TL_PATH . '/';
    }


    /**
     * Return the host name
     *
     * @return string The host name
     */
    protected static function host()
    {
        if(!self::get('httpXForwardedHost') )
            return self::get('httpHost');
        return '';
    }


    /**
     * Return true on Ajax requests
     *
     * @return boolean True if it is an Ajax request
     */
    protected static function isAjaxRequest()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    }


    /**
     * Return the operating system and the browser name and version
     *
     * @return object The agent information
     */
    public static function agent()
    {
        global $_environment;

        $ua = self::get('httpUserAgent');

        $return = new stdClass();
        $return->string = $ua;

        $os = 'unknown';
        $mobile = false;
        $browser = 'other';
        $shorty = '';
        $version = '';
        $engine = '';

        // Operating system
        foreach ($_environment['os'] as $k=>$v)
        {
            if (stripos($ua, $k) !== false)
            {
                $os = $v['os'];
                $mobile = $v['mobile'];
                break;
            }
        }

        $return->os = $os;

        // Browser and version
        foreach ($_environment['browser'] as $k=>$v)
        {
            if (stripos($ua, $k) !== false)
            {
                $browser = $v['browser'];
                $shorty  = $v['shorty'];
                $version = preg_replace($v['version'], '$1', $ua);
                $engine  = $v['engine'];
                break;
            }
        }
		
		//fix for IE 11 
		if ($browser=='other')
		{
				$matches = array();
				preg_match('/Trident\/\d{1,2}.\d{1,2}; rv:([11]*)/', $ua, $matches);
				if (count($matches)>1)
				{
					$v = $_environment['browser']['MSIE_NEW'];
					$browser = $v['browser'];
	                $shorty  = $v['shorty'];
	                $version = preg_replace($v['version'], '$1', $ua);
	                $engine  = $v['engine'];
				}
		}
		
        $versions = explode('.', $version);
        $version  = $versions[0];

        $return->class = $os . ' ' . $browser . ' ' . $engine;

        // Add the version number if available
        if ($version != '')
        {
            $return->class .= ' ' . $shorty . $version;
        }

        // Mark mobile devices
        if ($mobile)
        {
            $return->class .= ' mobile';
        }

        $return->browser  = $browser;
        $return->shorty   = $shorty;
        $return->version  = $version;
        $return->engine   = $engine;
        $return->versions = $versions;
        $return->mobile   = $mobile;

        return $return;
    }


    /**
     * Prevent direct instantiation (Singleton)
     *
     * @deprecated Environment is now a static class
     */
    function __construct() {}


    /**
     * Prevent cloning of the object (Singleton)
     *
     * @deprecated Environment is now a static class
     */
    final public function __clone() {}


    /**
     * Return an environment variable
     *
     * @param string $strKey The variable name
     *
     * @return string The variable value
     *
     * @deprecated Use Environment::get() instead
     */
    public function __get($strKey)
    {
        return self::get($strKey);
    }


    /**
     * Set an environment variable
     *
     * @param string $strKey   The variable name
     * @param mixed  $varValue The variable value
     *
     * @deprecated Use Environment::set() instead
     */
    public function __set($strKey, $varValue)
    {
        self::set($strKey, $varValue);
    }


    /**
     * Return the object instance (Singleton)
     *
     * @return \Environment The object instance
     *
     * @deprecated Environment is now a static class
     */
    public static function getInstance()
    {
        if (self::$objInstance === null)
        {
            self::$objInstance = new Envionment();
        }

        return self::$objInstance;
    }
}
?>