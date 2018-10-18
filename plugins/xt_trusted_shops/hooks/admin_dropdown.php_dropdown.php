<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. '/xt_trusted_shops/classes/constants.php';

switch ($request['get'])
{
    // trusted shops badge layer
    case 'ts-show-badge':
        $result = array();
        //text, default, small, reviews
        $result[] =  array('id' => '0',
            'name' => TEXT_EMPTY_SELECTION);
        $result[] =  array('id' => constant('TS_BADGE_SIZE_TEXT'),
            'name' => TEXT_TS_BADGE_SIZE_TEXT);
        $result[] =  array('id' => constant('TS_BADGE_SIZE_DEFAULT'),
            'name' => TEXT_TS_BADGE_SIZE_DEFAULT);
        $result[] =  array('id' => constant('TS_BADGE_SIZE_SMALL'),
            'name' => TEXT_TS_BADGE_SIZE_SMALL);
        $result[] =  array('id' => constant('TS_BADGE_SIZE_REVIEWS'),
            'name' => TEXT_TS_BADGE_SIZE_REVIEWS);
        break;

    // trusted shops seal of approvement
    case 'ts-show-seal':
        $result = array();
        $dir = _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_trusted_shops/images/seal/';
        $d = dir($dir);
        $result[] =  array('id' => '',
            'name' => TEXT_EMPTY_SELECTION);

        while($name = $d->read()){
            if(!preg_match('/\.(gif|jpg|png)$/', $name)) continue;
            $size = filesize($dir.$name);
            $lastmod = filemtime($dir.$name);
            $result[] = array('id' => $name,
                'name'=>$name);
        }
        break;

    // trusted shops video ad
    case 'ts-show-video':
        $result = array();
        $dir = _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_trusted_shops/images/video/';
        $d = dir($dir);
        $result[] =  array('id' => '',
            'name' => TEXT_EMPTY_SELECTION);

        while($name = $d->read()){
            if(!preg_match('/\.(gif|jpg|png)$/', $name)) continue;
            $size = filesize($dir.$name);
            $lastmod = filemtime($dir.$name);
            $result[] = array('id' => $name,
                'name'=>$name);
        }
        break;


}