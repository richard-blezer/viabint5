<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**************************************
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 **************************************/

$prodpl = new sunrise();

if ($prodpl->is_old_ie6())
	$smarty->assign('is_old_ie6',1);

?>