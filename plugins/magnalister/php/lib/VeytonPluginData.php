<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id: VeytonPluginData.php 193 2013-02-28 17:30:35Z tim.neumann $
 *
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class VeytonPluginData {
    public function __construct() {
    }
    
    public function process($case, $data) {
        switch ($case) {
            case 'updatePluginProductData':
                $this->updatePluginProductData($data);
                break;
        }
        
        return $this;
    }
    
    private function updatePluginProductData($data = array()) {
        return MagnaDB::gi()->update(
            TABLE_PLUGIN_PRODUCTS,
            $data,
            array(
                'name' => 'magnalister',
                'code' => 'magnalister'
            )
        );
    }
}