<?php

namespace cakebake;

/**
 * Session Storage
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 * @license   GNU LESSER GENERAL PUBLIC LICENSE Version 3
 * @version   0.2.0
 *
 * @example   Get a value from storage:
 *   cakebake\sessionStorage::get('keyname');
 *   cakebake\ew_session_cache::get('keyname', 'Fallback Value...', 'special-container-1');
 *
 * @example   Set a value to storage:
 *   cakebake\sessionStorage::set('keyname', 'My value to store...');
 *   cakebake\sessionStorage::set('keyname', 'My value to store...', 'special-container-1');
 */
class sessionStorage
{
    /**
     * Session key prefix, for unique session keys
     */
    const SESSION_KEY_PREFIX = 'ss_key::';

    /**
     * Session container prefix, for unique session containers
     */
    const SESSION_CONTAINER_PREFIX = 'ss_container::';

    /**
     * Get a value from storage
     *
     * @example sessionStorage::get('keyname', 'Fallback Value...', 'special-container-1');
     * @param mixed $key           Session key for identification
     * @param mixed $fallbackValue Default fallback value, if nothing is stored
     * @param mixed $container     Session container for key grouping
     * @return mixed Value or if nothing found fallback value (defaults to null)
     */
    public static function get($key, $fallbackValue = null, $container = 'default')
    {
        self::init();
        self::prefixKey($key);
        self::prefixContainer($container);

        return self::exists($key, $container) ? $_SESSION[$container][$key] : $fallbackValue;
    }

    /**
     * Static constructor method
     */
    protected static function init()
    {
        self::setHandler();
    }

    /**
     * Sets user-level session storage functions
     */
    public static function setHandler()
    {
        @session_start();
    }

    /**
     * Modifies the key with its prefix
     *
     * @param mixed $key
     * @return string The key with prefix
     */
    protected static function prefixKey(&$key)
    {
        return $key = (!strstr($key, self::SESSION_KEY_PREFIX)) ? self::SESSION_KEY_PREFIX . "$key" : $key;
    }

    /**
     * Modifies the container with its prefix
     *
     * @param mixed $container
     * @return string The container with prefix
     */
    protected static function prefixContainer(&$container)
    {
        return $container = (!strstr($container, self::SESSION_CONTAINER_PREFIX)) ? self::SESSION_CONTAINER_PREFIX . "$container" : $container;
    }

    /**
     * Lookup for an storage
     *
     * @param mixed $key       Session key for identification
     * @param mixed $container Session container for key grouping
     * @return bool true|false
     */
    public static function exists($key, $container = 'default')
    {
        self::init();
        self::prefixKey($key);
        self::prefixContainer($container);

        return isset($_SESSION[$container][$key]);
    }

    /**
     * Get all storage containers (All stored data)
     *
     * @return array|null
     */
    public static function getAllContainers()
    {
        self::init();
        $data = array();

        if (is_array($_SESSION)) {
            foreach ($_SESSION as $k => $i) {
                if (strstr($k, self::SESSION_CONTAINER_PREFIX)) {
                    $data[self::removePrefix($k, self::SESSION_CONTAINER_PREFIX)] = self::getContainer($k);
                }
            }
        }

        return !empty($data) ? $data : null;
    }

    /**
     * Removes prefix from string
     *
     * @param string $string
     * @param string $prefix
     * @return mixed
     */
    protected static function removePrefix($string, $prefix)
    {
        return str_replace($prefix, '', $string);
    }

    /**
     * Get a complete container array
     *
     * @param string $container
     * @return array|null
     */
    public static function getContainer($container = 'default')
    {
        self::init();
        self::prefixContainer($container);

        if (!self::containerExists($container))
            return null;

        $data = array();
        foreach ($_SESSION[$container] as $key => $item) {
            $data[self::removePrefix($key, self::SESSION_KEY_PREFIX)] = $item;
        }

        return $data;
    }

    /**
     * Lookup for an container
     *
     * @param mixed $container Session container for key grouping
     * @return bool true|false
     */
    public static function containerExists($container = 'default')
    {
        self::init();
        self::prefixContainer($container);

        return isset($_SESSION[$container]);
    }

    /**
     * Stores a value to session cache [Wrapper set()]
     *
     * @param mixed $key       Session key for identification
     * @param mixed $value     Value to be stored
     * @param mixed $container Session container for key grouping
     * @see self::set()
     * @return bool true|false
     */
    public static function update($key, $value, $container = 'default')
    {
        self::init();
        self::prefixKey($key);
        self::prefixContainer($container);

        return (self::exists($key, $container)) ? self::set($key, $value, $container) : false;
    }

    /**
     * Set a value to storage
     *
     * @example sessionStorage::set('keyname', 'My value to store...', 'special-container-1');
     * @param mixed $key       Session key for identification
     * @param mixed $value     Value to be stored
     * @param mixed $container Session container for key grouping
     * @param bool  $overwrite Overwrite if exists
     * @return bool true|false
     */
    public static function set($key, $value, $container = 'default', $overwrite = true)
    {
        self::init();
        self::prefixKey($key);
        self::prefixContainer($container);

        if (self::exists($key, $container) && $overwrite !== true)
            return false;

        if (!self::createContainer($container))
            return false;

        $_SESSION[$container][$key] = $value;

        return self::exists($key, $container);
    }

    /**
     * Creates a new storage container
     *
     * @param mixed $container Session container for key grouping
     * @return bool true|false
     */
    public static function createContainer($container = 'default')
    {
        self::init();
        self::prefixContainer($container);

        if (!self::containerExists($container)) {
            $_SESSION[$container] = array();
        }

        return self::containerExists($container);
    }

    /**
     * Destroys an storage
     *
     * @param mixed $key       Session key for identification
     * @param mixed $container Session container for key grouping
     * @return bool true|false
     */
    public static function destroy($key, $container = 'default')
    {
        self::init();
        self::prefixKey($key);
        self::prefixContainer($container);

        if (self::exists($key, $container)) {
            unset($_SESSION[$container][$key]);
        }

        return self::exists($key, $container) ? false : true;
    }

    /**
     * Destroy all storage containers (All stored data)
     *
     * @param string $additionalMatchString Additional string to match container keys
     * @return bool
     */
    public static function destroyAllContainers($additionalMatchString = null)
    {
        self::init();
        $erg = array();

        if (is_array($_SESSION)) {
            foreach ($_SESSION as $k => $i) {
                if (strstr($k, self::SESSION_CONTAINER_PREFIX)) {
                    if (($additionalMatchString === null) ||
                        (strstr($k, (string)$additionalMatchString))
                    ) {
                        $erg[] = self::destroyContainer($k);
                    }
                }
            }
        }

        return !in_array(false, $erg);
    }

    /**
     * Destroys an storage container
     *
     * @param mixed $container Session container for key grouping
     * @return bool true|false
     */
    public static function destroyContainer($container = 'default')
    {
        self::init();
        self::prefixContainer($container);

        if (self::containerExists($container)) {
            unset($_SESSION[$container]);
        }

        return self::containerExists($container) ? false : true;
    }
}
