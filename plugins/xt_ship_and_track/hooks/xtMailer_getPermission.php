<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

// überschreiben des (fehlerhaften?) standartverhaltens des xtmailer siehe Ticket WEN-660798

$this->perm_array['shop_perm']['status'] = $this->shop_id;
