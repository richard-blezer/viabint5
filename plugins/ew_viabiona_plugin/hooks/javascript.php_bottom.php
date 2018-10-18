<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * JavaScript Hookpoint before content printing
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */

use ew_viabiona\plugin as ew_viabiona_plugin;

if (class_exists('ew_viabiona\plugin') && ew_viabiona_plugin::status()) {

    // cache watcher link when active
    global $xtLink;
    if (ew_viabiona_plugin::check_conf('CONFIG_EW_VIABIONA_PLUGIN_AJAX_CACHE_WATCHER')) : ?>
        <script type="text/javascript">
            /* <![CDATA[ */
            var EW_VIABIONA_PLUGIN_AJAX_CACHE_WATCHER_LINK = "<?php echo $xtLink->_link(array('page' => 'ew_viabiona_ajax_cache_watcher')); ?>";
            /* ]]> */
        </script>
    <?php endif;

    //include assets
    if (method_exists('ew_viabiona\plugin', 'registerAssets')) {
        global $ew_viabiona_plugin;

        $ew_viabiona_plugin = (is_object($ew_viabiona_plugin) && $ew_viabiona_plugin instanceof ew_viabiona_plugin) ? $ew_viabiona_plugin : new ew_viabiona_plugin();
        echo ($ew_viabiona_plugin->registerAssets() === false) ? '<!-- ew_viabiona_plugin :: Errors in merging the client-side scripts -->' : null;
    }
}