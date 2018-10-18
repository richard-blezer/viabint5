<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if (isset($_GET['api'])) {
    if ($_GET['api']=='csv_export') {
        include 'plugins/xt_im_export/classes/class.csvapi.php';
        include 'plugins/xt_im_export/classes/class.export.php';
        $csv_export = new csv_export();
        $csv_export->run_export($_GET);
    }
    if ($_GET['api']=='csv_import') {
        include 'plugins/xt_im_export/classes/class.csvapi.php';
        include 'plugins/xt_im_export/classes/class.import.php';
        $csv_export = new csv_import();
        $csv_export->run_import($_GET);
    }

}