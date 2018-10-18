<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if (isset($_SESSION['cart']->discount)) {
    if ($_SESSION['cart']->discount != 'false') {
        $tpl_data = array_merge($tpl_data,array('discount'=>$_SESSION['cart']->discount));
    }
}
