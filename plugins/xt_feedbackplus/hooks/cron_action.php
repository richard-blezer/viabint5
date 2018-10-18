<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');


$fplus = new xt_feedbackplus();

// send reminders
$fplus->sendNotifications();

// send vouchers
$fplus->sendConfirmations();