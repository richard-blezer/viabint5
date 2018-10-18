<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

// um mehr als eine box/plugin OHNE tpl- und user-param in {box}
if ($params['name']=='trusted_shops_ratings')
{
    $params['name'] = 'xt_trusted_shops';
    $params['type'] = 'user';
    if (empty($params['tpl'])) $params['tpl'] = 'box_ratings.html';
    $params['box'] = 'ratings';
}
else if ($params['name']=='trusted_shops_seal')
{
    $params['name'] = 'xt_trusted_shops';
    $params['type'] = 'user';
    if (empty($params['tpl'])) $params['tpl'] = 'box_seal.html';
    $params['box'] = 'seal';
}
else if ($params['name']=='trusted_shops_video')
{
    $params['name'] = 'xt_trusted_shops';
    $params['type'] = 'user';
    if (empty($params['tpl'])) $params['tpl'] = 'box_video.html';
    $params['box'] = 'video';
}
else if ($params['name']=='trusted_shops_rich_snippet')
{
    $params['name'] = 'xt_trusted_shops';
    $params['type'] = 'user';
    if (empty($params['tpl'])) $params['tpl'] = 'box_rich_snippet.html';
    $params['box'] = 'rich_snippet';
}