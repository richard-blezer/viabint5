<?php
include '../../../../admin/main.php';

if (!$xtc_acl->isLoggedIn()) {
    die('login required');
}

$_SESSION["isLoggedIn"] = true;
$_SESSION["imagemanager.filesystem.rootpath"] = $root_dir."/media/images";
$return_url= $_GET['return_url'];
$xtLink->_redirect($return_url);

?>
