<?php
/*
 #########################################################################
 #                       xt:Commerce VEYTON 4.0 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce VEYTON 4.0 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id$
 # @copyright xt:Commerce International Ltd., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');


class socialbookmarks {


	function socialbookmarks() {
		$this->bookmarks = array();
		$this->bookmarks['delicious'] = 'http://del.icio.us/post?url={url}&title={title}';
		$this->bookmarks['Digg'] = 'http://digg.com/submit?phase=2&url={url}&title={title}';
		$this->bookmarks['Furl'] = 'http://furl.net/storeIt.jsp?t={title}&u={url}';
		$this->bookmarks['Blinklist'] = 'http://blinklist.com/index.php?Action=Blink/addblink.php&Name={title}&Description={title}&Url={url}';
		$this->bookmarks['Reddit'] = 'http://reddit.com/submit?url={url}&title={title}';
		$this->bookmarks['Technorati'] = 'http://www.technorati.com/faves?add={url}';
		$this->bookmarks['Yahoo My Web'] = 'http://myweb2.search.yahoo.com/myresults/bookmarklet?u={url}&t={title}';
		$this->bookmarks['Newsvine'] = 'http://www.newsvine.com/_wine/save?u={url}&h={title}';
		$this->bookmarks['Socializer'] = 'http://ekstreme.com/socializer/?url={url}&title={title}';
		$this->bookmarks['Stumbleupon'] = 'http://www.stumbleupon.com/refer.php?url={url}&title={title}';
		$this->bookmarks['Google Bookmarks'] = 'http://www.google.com/bookmarks/mark?op=edit&output=popup&bkmk={url}&title={title}';
		$this->bookmarks['RawSugar'] = 'http://www.rawsugar.com/tagger/?turl={url}&tttl={title}';
		$this->bookmarks['Squidoo'] = 'http://www.squidoo.com/lensmaster/bookmark?{url}';
		$this->bookmarks['Spurl'] = 'http://www.spurl.net/spurl.php?url={url}&title={title}';
		$this->bookmarks['BlinkBits'] = 'http://blinkbits.com/bookmarklets/save.php?v=1&source_url={url}&title={title}';
		$this->bookmarks['Netvouz'] = 'http://netvouz.com/action/submitBookmark?url={url}&title={title}&popup=no';
		$this->bookmarks['Rojo'] = 'http://www.rojo.com/add-subscription/?resource={url}';
		$this->bookmarks['Blogmarks'] = 'http://blogmarks.net/my/new.php?mini=1&simple=1&url={url}&title={title}';
		$this->bookmarks['Scuttle'] = 'http://www.scuttle.org/bookmarks.php/maxpower?action=add&address={url}&title={title}&description={title}';
		$this->bookmarks['Yigg'] = 'http://yigg.de/newpost.php?exturl={url}';
		$this->bookmarks['MrWong'] = 'http://www.mister-wong.de/index.php?action=addurl&bm_url={url}&bm_description={title}';
	}

	function _getSocialBookmarks($url,$title) {
		$html = '';
		
		
		foreach ($this->bookmarks as $key => $val) {


			if (constant(XT_SOCIALBOOKMARKS_.strtoupper(str_replace(' ','',$key)))=='true') {
				$target = str_replace('{url}',urlencode($url),$val);
				$target = str_replace('{title}',urlencode($title),$target);
				$target = str_replace('&','&amp;',$target);
				$html.='<a title="'.$key.'" href="'.$target.'" target="_blank"><img class="socialbookmark" alt="'.$key.'" src="'._SRV_WEB.'plugins/xt_socialbookmarks/images/'.strtolower(str_replace(' ','',$key)).'.jpeg" /></a>';
			}
		}

		return $html;

	}



}
?>