<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');


if ($status == 1) {

$review_id = (int)$id;
// check if review is feedback+ review
$sql = "SELECT * FROM ".TABLE_PRODUCTS_REVIEWS." WHERE review_id=?";
$rs = $db->Execute($sql,array($review_id));

if ($rs->fields['review_source']=='feedback+' || $rs->fields['review_source']=='feedback+test') {
    $sql = "UPDATE ".TABLE_FEEDBACKPLUS_LIFE_CIRCLES." SET feedback_send_status=1 WHERE feedback_life_circle_id=?";
    $db->Execute($sql,array($rs->fields['feedbackplus_life_circle_id']));
}
}

?>