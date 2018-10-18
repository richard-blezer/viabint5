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
 * (c) 2012 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

# mal zu Testzwecken ausgelagert, gehoert ins ebayFunctions.php

function substitutePictures($tmplStr, $pID, $imagePath) {
	# Tabelle nur bei xtCommerce- und Gambio- Shops vorhanden (nicht OsC)
	if (   defined('TABLE_MEDIA')      && MagnaDB::gi()->tableExists(TABLE_MEDIA)
	    && defined('TABLE_MEDIA_LINK') && MagnaDB::gi()->tableExists(TABLE_MEDIA_LINK)
	) {
		$pics = MagnaDB::gi()->fetchArray('SELECT
            id image_nr, file image_name
			FROM '.TABLE_MEDIA.' m, '.TABLE_MEDIA_LINK.' ml
            WHERE m.type=\'images\' AND ml.class=\'product\' AND m.id=ml.m_id AND ml.link_id='.$pID);
		$i = 2;
		# Ersetze #PICTURE2# usw. (#PICTURE1# ist das Hauptbild und wird vorher ersetzt)
		foreach($pics as $pic) {
			$tmplStr = str_replace('#PICTURE'.$i.'#', "<img src=\"".$imagePath.$pic['image_name']."\" style=\"border:0;\" alt=\"\" title=\"\" />",
				 preg_replace( '/(src|SRC|href|HREF)(\s*=\s*)(\'|")(#PICTURE'.$i.'#)/', '\1\2\3'.$imagePath.$pic['image_name'], $tmplStr));
			$i++;
		}
		# Uebriggebliebene #PICTUREx# loeschen
		$str = preg_replace(	'/#PICTURE\d+#/','', $tmplStr);
		#		str_replace($find, $replace, $tmplStr));
	} else {
		$str = preg_replace(	'/#PICTURE\d+#/','', $tmplStr);
	}
	return $str;
}

