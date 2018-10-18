<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');


class orderCouponInfo extends stdClass
{
    public $xt_coupon = null;
    public $xt_coupon_token = null;
    public $isToken = false;
    public $tokenId = 0;
    public $tokenCode = null;
    public $lastRedeemAmount = 0.0;
}