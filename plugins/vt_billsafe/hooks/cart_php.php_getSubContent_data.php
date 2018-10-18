<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if ($_SESSION['selected_payment_sub']=='RAT') {

   if (isset($this->sub_content['payment']) && $this->sub_content['payment']['products_model']=='vt_billsafe') {
       unset ($this->sub_content['payment']);
   }
}

?>