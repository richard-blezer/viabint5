<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Configuration Tab for Contentmanager
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */

$groupingPosition = 'EW_VIABIONA_NAME';

$grouping['ew_viabiona_hyperlink'] = array('position' => $groupingPosition);
$grouping['ew_viabiona_hyperlink_status'] = array('position' => $groupingPosition);
$grouping['ew_viabiona_show_text_status'] = array('position' => $groupingPosition);

$params['grouping'] = $grouping;