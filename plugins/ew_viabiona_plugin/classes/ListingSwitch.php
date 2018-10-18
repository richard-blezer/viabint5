<?php

namespace ew_viabiona;

/**
 * Listing Switch for xt:C product listings
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */
class ListingSwitch
{
    /**
     * @var string $request_key Request Key for $_GET and $_SESSION | defaults to ew_viabiona_listing_switch
     */
    public $request_key = 'ew_viabiona_listing_switch';

    public $buttons_data;
    public $current_switch;
    public $current_template;
    public $new_template;
    public $ressource = array();

    /**
     * GET SWITCH STATUS AND NEW TEMPLATE
     */
    public function get_status()
    {
        $this->new_template = false;
        $this->current_switch = $this->get_to_session($this->request_key);

        if ((int)$this->current_switch === 0)
            return false;

        if (isset($this->buttons_data[$this->current_switch]['template']) && !empty($this->buttons_data[$this->current_switch]['template']))
            $this->new_template = $this->buttons_data[$this->current_switch]['template'];

        if ($this->current_template == $this->new_template)
            return false;

        $path = $this->slashit($this->trailingslashit(_STORE_TEMPLATE) . 'xtCore/pages/product_listing/' . $this->new_template);

        return $path;
    }

    /**
     * SET GET PARAM TO SESSION
     *
     * @param $key
     * @return null
     */
    public function get_to_session($key)
    {
        $out = null;

        if (isset($_GET[$key])) {
            $_SESSION[$key] = $_GET[$key];
            $out = $_GET[$key];
        } elseif ($_SESSION[$key]) {
            $out = $_SESSION[$key];
        }

        return $out;
    }

    /**
     * removes unwanted slashes
     *
     * @param string $string
     * @return string
     */
    public function slashit($string)
    {
        if (!is_string($string))
            return false;

        $string = trim($string);

        if (empty($string))
            return false;

        if (DIRECTORY_SEPARATOR == "\\") {
            $string = str_replace('/', '\\', $string);
            $string = str_replace('\\\\', '\\', $string);
        } else {
            $string = str_replace('\\', '/', $string);
            $string = str_replace('\/\/', '/', $string);
        }

        return $string;
    }

    /**
     * adds a trailing slash
     *
     * @uses untrailingslashit
     * @param string $string
     * @return string
     */
    public function trailingslashit($string)
    {
        return $this->untrailingslashit($string) . DIRECTORY_SEPARATOR;
    }

    /**
     * removes a trailing slash
     *
     * @param string $string
     * @return string
     */
    public function untrailingslashit($string)
    {
        return is_string($string) ? rtrim(trim($string), DIRECTORY_SEPARATOR) : false;
    }

    /**
     * GET THE BUTTONS ARRAY
     */
    public function get_buttons_data()
    {
        $buttons = array();
        $this->current_template = $this->get_current_template();
        $this->current_switch = (int)$this->get_to_session($this->request_key);

        if (defined('CONFIG_EW_VIABIONA_PLUGIN_LISTING_SWITCH_TEMPLATE1') && CONFIG_EW_VIABIONA_PLUGIN_LISTING_SWITCH_TEMPLATE1 != '') {
            $buttons[1] = array(
                'template' => CONFIG_EW_VIABIONA_PLUGIN_LISTING_SWITCH_TEMPLATE1,
                'title'    => TEXT_EW_VIABIONA_LISTING_SWITCH_TITLE_VIEW1,
                'active'   => ($this->current_switch === 1) ? true : ($this->current_switch === 0 && $this->current_template == CONFIG_EW_VIABIONA_PLUGIN_LISTING_SWITCH_TEMPLATE1) ? true : false,
                'link'     => $this->url_add_param($this->request_key, 1),
            );
        }

        if (defined('CONFIG_EW_VIABIONA_PLUGIN_LISTING_SWITCH_TEMPLATE2') && CONFIG_EW_VIABIONA_PLUGIN_LISTING_SWITCH_TEMPLATE2 != '') {
            $buttons[2] = array(
                'template' => CONFIG_EW_VIABIONA_PLUGIN_LISTING_SWITCH_TEMPLATE2,
                'title'    => TEXT_EW_VIABIONA_LISTING_SWITCH_TITLE_VIEW2,
                'active'   => ($this->current_switch === 2) ? true : ($this->current_switch === 0 && $this->current_template == CONFIG_EW_VIABIONA_PLUGIN_LISTING_SWITCH_TEMPLATE2) ? true : false,
                'link'     => $this->url_add_param($this->request_key, 2),
            );
        }

        if (count($buttons) === 0)
            return false;

        return $buttons;
    }

    /**
     * GET CURRENT TEMPLATE
     */
    public function get_current_template()
    {
        if (!isset($this->ressource[1]) || empty($this->ressource[1]))
            return false;

        $path = $this->slashit($this->ressource[1]);
        $filename = basename($path);

        if (empty($filename))
            return false;

        return $filename;
    }

    /**
     * ADD URL QUERY PARAM
     *
     * @param string $key
     * @param string $value
     * @param string $url
     * @return string URL with new Parameter
     */
    public function url_add_param($key, $value, $url = 'current')
    {
        $key = trim((string)$key);
        $value = trim((string)$value);

        if ($url == 'current')
            $url = $_SERVER['REQUEST_URI'];

        $query = parse_url($url, PHP_URL_QUERY);

        if ($query) {
            if (strpos($query, $key) !== false) {
                $url_path = parse_url($url, PHP_URL_PATH);
                parse_str($query, $url_querys);
                $url_querys[$key] = $value;
                $url = $url_path . '?' . http_build_query($url_querys);
            } else {
                $url .= '&' . $key . '=' . $value;
            }

        } else {
            $url .= '?' . $key . '=' . $value;
        }

        return $url;
    }

    /**
     * GET URL QUERY PARAM
     *
     * @param        $key
     * @param string $url
     * @return bool
     */
    public function url_get_param($key, $url = 'current')
    {
        if ($url == 'current')
            $url = $_SERVER['REQUEST_URI'];

        if (strpos($url, $key) !== false)
            parse_str(parse_url($url, PHP_URL_QUERY), $params);

        if (!isset($params[$key]))
            return false;

        return $params[$key];
    }
}
