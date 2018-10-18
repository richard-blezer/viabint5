<?php
	/* Direkten aufruf verhindern */
	defined('_VALID_CALL') or die('Direct Access is not allowed.');

	class nw_extra_tab
	{
		public $product_id = ''; /* ProduktID als Eigenschaft definieren */
		function __construct($current_product_id)
		{
			/* Beim Erzeugen des Objekts die ueberegeben ID als Eigenschaft zuweisen */
			$this->product_id = $current_product_id;
		}

		function _showHead($tab)
		{
			/* pruefen welches Tab ausgegeben werden soll */
			if ($tab == 'versandkosten')
			{
				$tabName = NW_EXTRA_TAB_HEAD_SHIPPING; /* Head aus XMLDatei fuer den VersandTab */
			}

			/* TabInhalt abfragen */
			$tabBody = $this->_showBody($tab);
			/* Wenn ein TabName vergeben und ein TabInhalt vorhanden ist, erzeuge einen Tab - Kopf */
	if ((!empty($tabName)) && (!empty($tabBody)))
	{
		/* Formatierung des TabKopfes */
		$tabHead = '<div id="versand-info" class="breaking-headline hidden-xs">'. $tabName . '</div>'; // angepast an ew_viabiona template
	}
	// Wert zurueckgeben
	return $tabHead;
	}

		function _showBody($tab)
		{
			global $_content;
			/* TabBody formatieren */
			$class = 'versand_kosten';
			/* Erzeuge Produkt-Objekt */
			$product = new product($this->product_id);
			if ($tab == 'versandkosten')
			{
				/* Versandkosten-Tab? */
				/* Zuweisen des Inhalts der Variable fuer Versandkosten */
				$contentBody = $product->data['products_versandkosten_html'];

				/* Ist fuer Produkt ein Content definiert? */
				if ((!empty($product->data['products_tabinfo'])) && ($product->data['products_tabinfo'] > 0))
				{
					$tabContent = $_content->getHookContent($product->data['products_tabinfo'], true);
				}
				else
				{ /* sonst nimm den Wert aus der Plugin-Konfiguration */
					$tabContent = $_content->getHookContent(NW_EXTRA_TAB_CONTENT_ID, true);
				}
			/* Erzeuge Überschrift */
				$contentHead = '<p class="headline">' . $tabContent['content_heading'] . '</p>';
				/* Erzeuge Text */
				$contentBody = '<p>' . $tabContent['content_body'] . '</p>';
			}
			/* Nur wenn Informationen vohanden sind, erzeuge den Tab-Body */
			if (!empty($contentBody))
			{
				// angepast an ew_viabiona template
				$tabBody = '<div id="pcontent-part-versand" class="pcontent-part hidden-xs"
                     data-orientation-nav-url="#pcontent-part-versand"
                     data-orientation-nav-classes="move"
                     data-orientation-nav-icon="<i class=\'fa fa-truck\'></i>"
                     data-orientation-nav-label="Versandkosten info"
                >'. $contentHead . $contentBody . '</div>';
			}

			return $tabBody;
		}
	}

?>