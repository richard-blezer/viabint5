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
 * $Id$
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
require_once(DIR_MAGNALISTER_MODULES.'tradoria/classes/MLProductListTradoriaAbstract.php');

class TradoriaCheckinProductList extends MLProductListTradoriaAbstract {

    public function __construct() {
        $this->aListConfig[] = array(
            'head' => array(
                'attributes'	=> 'class="lowestprice"',
                'content'		=> 'ML_MAGNACOMPAT_LABEL_MP_PRICE_SHORT',
            ),
            'field' => array('tradoriaprice'),
        );
        parent::__construct();

        $this->addDependency('MLProductListDependencyCheckinToSummaryAction');
    }

    protected function getSelectionName() {
        return 'checkin';
    }

    /**
     * adding propertiestable for filter
     */
    protected function buildQuery() {
        $preparedItems = MagnaDB::gi()->fetchArray('
			SELECT DISTINCT '.(
            (getDBConfigValue('general.keytype', '0') == 'artNr')
                ? 'products_model'
                : 'products_id'
            ).'
			  FROM '.TABLE_MAGNA_COMPAT_CATEGORYMATCHING.'
			 WHERE mp_category_id <> \'\'
			   AND mpID = \''.$this->aMagnaSession['mpID'].'\'
		', true);

        parent::buildQuery()->oQuery->where(
            (getDBConfigValue('general.keytype', '0') == 'artNr')
                ?   'p.products_model IN (\''.implode('\', \'', $preparedItems).'\')'
                :   'p.products_id IN (\''.implode('\', \'', $preparedItems).'\')'
        );
        return $this;
    }
}