<?php
 /*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id$
 # @copyright xt:Commerce International Ltd., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

define('_TIME_24_HOURS',(24*3600));

/*
 *
 * Performance note: When your database server is much slower than your Web server or the database is very overloaded
 * then ADOdb's caching is good because it reduces the load on your database server.
 * If your database server is lightly loaded or much faster than your Web server, then caching could actually reduce performance.
 *
 */

define('_ACTIVATE_DB_CACHE',0);

define('_CACHETIME_DEFAULT',0);
define('_CACHETIME_LANGUAGE_CONTENT',0);

define('_CACHETIME_MANUFACTURER_LIST',_TIME_24_HOURS);

define('USE_CACHE','true');
define('CACHE_LIFETIME','31536000');
define('CACHE_CHECK',false);
?>