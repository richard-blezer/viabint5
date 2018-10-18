<?php
defined('_VALID_CALL') OR die('Direct Access is not allowed.');

if ($_GET['type']=='PS_getDocumentInfo') {

    include _SRV_WEBROOT.'plugins/xt_protected_shops/classes/class.xt_protected_shops.php';

    $protected = new xt_protected_shops();
    $protected->loadDocumentTypes();

    echo '<style>
						ul.success {border:solid 2px #4DAA30; background-color:#BDFFA9; padding:8px}
						ul.success li {}
						ul.success li.Success {list-style:none; padding:5px 0px 2px 20px; background-image:url(xtAdmin/images/icons/accept.png); background-repeat:no-repeat; background-position:0px 4px; background-color:#BDFFA9}
					</style>
			';


    echo $info->info_content;

}

if ($_GET['type']=='PS_getDocuments') {

    include _SRV_WEBROOT.'plugins/xt_protected_shops/classes/class.xt_protected_shops.php';

    $protected = new xt_protected_shops();
    $protected->getDocuments(true);

    echo '<style>
						ul.success {border:solid 2px #4DAA30; background-color:#BDFFA9; padding:8px}
						ul.success li {}
						ul.success li.Success {list-style:none; padding:5px 0px 2px 20px; background-image:url(xtAdmin/images/icons/accept.png); background-repeat:no-repeat; background-position:0px 4px; background-color:#BDFFA9}
					</style>
			';


    echo $info->info_content;

}