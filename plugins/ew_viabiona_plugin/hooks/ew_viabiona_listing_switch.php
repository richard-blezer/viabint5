<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Template switch
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 * @example   use in templates like {hook key=ew_viabiona_listing_switch}
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */

use ew_viabiona\ListingSwitch;
use ew_viabiona\plugin as ew_viabiona_plugin;
use ew_viabiona\Template as template;

if (class_exists('ew_viabiona\plugin') && ew_viabiona_plugin::status()) {

    $tpl_object = new template();
    $listingSwitch = new ListingSwitch();
    $listingSwitch->ressource = $smarty->_plugins['function']['hook'];

    if (($listingSwitch->buttons_data = $listingSwitch->get_buttons_data()) !== false && !empty($listingSwitch->buttons_data)) {
        $tpl = 'ew_viabiona_listing_switch.html';
        $tpl_object->getTemplatePath($tpl, 'ew_viabiona_plugin', 'hooks', 'plugin');

        //buttons
        $buttons = $tpl_object->getTemplate(
            'ew_viabiona_listing_switch', $tpl, array(
            'buttons_data'     => $listingSwitch->buttons_data,
            'current_template' => $listingSwitch->current_template
        ));
        $smarty->assign('ew_viabiona_listing_switch_buttons', $buttons);

        //switch
        $listingSwitch_status = $listingSwitch->get_status();
        $smarty->assign('ew_viabiona_listing_switch_status', $listingSwitch_status);

        unset($tpl_object, $tpl, $listingSwitch, $buttons, $listingSwitch_status);
    }

}