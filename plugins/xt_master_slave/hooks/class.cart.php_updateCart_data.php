<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if (($data['qty']>1) && ($data['products_digital']==1)) $data['qty']=1; 

?>