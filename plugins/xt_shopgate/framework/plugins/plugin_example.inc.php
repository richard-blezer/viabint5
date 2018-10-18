<?php

/**
 * <p>
 * Dieses Plugin für das Shopgate-Framework zeigt die grundlegende
 * Funktionsweise der Implementierungen von Shopping-Systemen bei Shopgate.
 * </p>
 * <p>
 * Für jedes Shopping-System wird eine Plugin-Datei benötigt. Diese Datei muss
 * plugin_<PLUGINNAME>.inc.php heissen und die Klasse ShopgatePlugin enthalten.
 * Die Klasse ShopgatePlugin erbt von ShopgatePluginCore, welche grundlegende Funktionen
 * zur Verfügung stellt.
 * </p>
 * <p>
 * Diese Datei ist nicht für den Produktiven Einsatz geeignet und soll lediglich als Vorlage für neue Plugins dienen und die Integrationsweise veranschaulichen.
 * </p>
 * @author Martin Weber
 * @version 1.0.0
 * @package plugins
 *
 */
class ShopgatePlugin extends ShopgatePluginCore {
	
	/**
	 * <p>
	 * Bevor das Shopgate Framework die einzelnen Funktionen in dem Plugin aufruft
	 * wird die Funktion startup() aufgerufen. Hier können Sie z.B. andere Dateien
	 * aus dem Shopping System einbinden (mit include_once() oder require_once()) oder z.B. eine
	 * Verbindung zur Datenbank herstellen.
	 * </p>
	 * <p>
	 * Wichtig hierbei ist, dass die Funktion am Ende den Wert "true" zurückgibt.
	 * </p>
	 * 
	 * @return bool
	 * @see lib/ShopgatePluginCore::startup()
	 */
	public function startup() {

		return true;
	}
	
	/**
	 * <p>
	 * Diese Funktion wird aufgerufen, wenn eine CSV-Datei mit den Produkten erstellt
	 * werden soll.
	 * </p>
	 * <p>Gestartet wird der Prozess über die Datei cronjobs/update_items_csv.php
	 * </p>
	 * <p>
	 * Die CSV-Datei wird vom Framework verwaltet und von diesem geschrieben. Das einzige
	 * was Sie machen müssen ist die Daten aus Ihrem Shopping-System zu lesen, für jedes
	 * Produkt ein bestimmtest Array zu generieren und dieses Array an die Funktion $this->addItem($itemArray)
	 * übergeben. Die Funktion $this->addItem($itemArray) verwaltet das Schreiben in die CSV-Datei.
	 * </p>
	 * <p>
	 * In diesem Beispiel haben wir ein Array ($items) mit Beispiel-Produktdaten vorbereitet. Normalerweise
	 * sollen dann hier Ihre Produktdaten aus Ihrer Datenbank stehen.
	 * </p>
	 * <p>
	 * Die Datensätze der CSV werden nur von unserem Server beim Import validiert. Jedes Produkt muss hierbei
	 * mindestens eine einzigartige Artikelnummer, einen Namen, einen Hersteller, einen Preis und eine Mehrwertsteuerangabe
	 * besitzen. Zudem müssen sie mindestens einer Produktkategorie zugeordnet sein.
	 * </p>
	 * <p>
	 *  Weitere Informationen über den Aufbau der CSV-Datei finden Sie unter
	 *  https://www.shopgate.com/csvdoc
	 * </p>
	 *
	 * @see lib/ShopgatePluginCore::startCreateItemsCSV()
	 */
	protected function createItemsCsv() {
		
		// Array mit Beispieldaten zum Testen definieren. 
		// Hier sollten Sie bei dem richtigen Plugin die Daten
		// z.B. aus der Datenbank lesen.
		
		$items = array(
			array(
				'number'			=> 'iphone004',
				'name'				=> 'iPhone 3GS 16GB Black',
				'desc'				=> 'iPhone 3GS mit 16GB Speicher in Schwarz',
				'netto_price'		=> '800,00',
				'tax'				=> 19.00,
				'manufacturers_name'=> 'Apple',
				'cat'				=> 'Hardware=>Apple=>iPhones',
				'key_words'			=> 'apple, iphone, 3gs',
				'ean_number'		=> '',
				'last_modified'		=> '16-02-2010'
			),
			array(
				'number'			=> 'htc001',
				'name'				=> 'HTC Desire',
				'desc'				=> 'HTCA8181 Desire mit Android 2.2',
				'netto_price'		=> '540,00',
				'tax'				=> 19.00,
				'manufacturers_name'=> 'HTC',
				'cat'				=> 'Hardware=>Smartphones=>HTC||Handys=>HTC',
				'key_words'			=> 'htc, smartphone, desire, android',
				'ean_number'		=> '',
				'last_modified'		=> '16-02-2010'
			),
			array(
				'number'			=> 'tp01125',
				'name'				=> 'ThikPad T500',
				'desc'				=> 'Lenovo ThinkPad T500 mit <strong>8GB</strong> DDR3 RAM',
				'netto_price'		=> '1200,00',
				'tax'				=> 19.00,
				'manufacturers_name'=> 'Lenovo',
				'cat'				=> 'Hardware=>Laptop=>lenovo',
				'key_words'			=> 'lenovo, laptop, thnikpad',
				'ean_number'		=> '',
				'last_modified'		=> '16-02-2010'
			),
		);
		
		// Die Artikel nacheinander in unser Format bringen und der CSV hinzufügen
		
		foreach($items as $item) {
			
			// In der Schleife erfolgt die Zuordnung zu "unserer" Array Struktur.
			// Dieses Array muss am Ende an die Funktion $this->addItem($itemArray) übergeben werden
			
			// Die Artikelnummer des Produktes
			// Diese muss in einer CSV-Datei einmalig sein
			$itemArr['item_number'] 	= $item["number"];
			// Name des Herstellers
			$itemArr['manufacturer']    = $item["manufacturers_name"];
			// Produktname
			$itemArr['item_name']		= $item["name"];
			// Die Produktbeschreibung
			// Diese kann auch HTML-Formatierungen enthalten
			$itemArr['description']		= $item["desc"];
			// Der Preis inkl. MwSt.
			$itemArr['unit_amount'] 	= ($item["netto_price"]*(1+($item["tax"]/100)));
			// Produktverfügbarkeit
			$itemArr['is_available']	= true;
			// Verfügbarkeitstext
			$itemArr['available_text']	= "Versandvertig in 1-2 Tagen";
			// Die Url zum Artikel
			$itemArr['url_deeplink']	= "http://www.testshop.de/product/".$item["number"];
			// Die Url zum Bild
			$itemArr['urls_images']		= "http://www.testshop.de/product/".$item["number"].".jpg";
			// Der Kategoriepfad
			$itemArr['categories']		= $item["cat"];
			// Das Gewicht
			$itemArr['weight']			= 0;
			// Suchwörter/Key-Words
			$itemArr['tags' ]			= $item["key_words"];
			// Hersteller Artikelnummer
			$itemArr['manufacturer_item_number']	= "";
			// Die Währung
			$itemArr['currency']		= "EUR"; // TODO eventuell umrechnen
			// Die Steuern (19/7/0/...)
			$itemArr['tax_percent']		= $item["tax"];
			// Die Preisempfehlung des Herstellers
			$itemArr['msrp']			= "";
			// Die Versandkosten für eine Bestellung dieses Produktes
			$itemArr['shipping_costs_per_order'] = 0;
			// Die Versandkosten fr jedes weitere Produkt
			$itemArr['additional_shipping_costs_per_unit'] = 0;
			// Die EAN-Nummer
			$itemArr['ean']				= $item["ean_number"];
			// Wann wurde der Artikel zuletzt geändert
			$itemArr['last_update']		= $item["last_modified"];
			// Informationen, die bei der Bestellung wieder zurück gegeben werden
			// -- Der kunde Sieht diese Info nicht --
			$itemArr['internal_order_info'] = "";
			// Die Artikelnummer des Elternelementes
			$itemArr['parent_item_number'] 	= "";
			// Gibt es Vartiationen dieses Elementes
			$itemArr['has_children']		= false;
			// Im Elternelement der Name der Variation
			// In den Kindelementen ist es der Wert
			$itemArr['attribute_1' ]	= "";
			$itemArr['attribute_2' ]	= "";
			$itemArr['attribute_3' ]	= "";
			$itemArr['attribute_4' ]	= "";
			$itemArr['attribute_5' ]	= "";
			
			// Füge das Produkt der CSV-Datei hinzu
			// Die Funktion addItem() schreibt das Produkt in dem richtigen
			// Format in die CSV-Datei. Darum müssen Sie sich nicht mehr kümmern.
			$this->addItem($itemArr);
		}
	}

	/**
	 * <p>Diese Funktion wird aufgerufen, wenn eine CSV-Datei mit Produktbewertungen
	 * von Ihren Produkten erstellt werden soll.</p> 
	 * 
	 * <p>Gestartet wird der Prozess über die Datei cronjobs/update_reviews_csv.php</p>
	 * 
	 * <p>Die CSV-Datei wird vom Framework verwaltet und von diesem geschrieben. Das einzige
	 * was Sie machen müssen ist die Daten aus Ihrem Shopping-System zu lesen, für jedes
	 * Produkt ein bestimmtest Array zu generieren und dieses Array an die Funktion $this->addItem($itemArray)
	 * übergeben. Die Funktion $this->addItem($itemArray) verwaltet das Schreiben in die CSV-Datei.</p>
	 *
	 * <p>In diesem Beispiel haben wir ein Array ($reviews) mit Beispiel-Produktbewertungen vorbereitet. Normalerweise
	 * sollen dann hier Ihre Daten aus Ihrer Datenbank stehen.</p>
	 * 
	 * <p>Jede Produktbewertung benötigt mindestens eine eindeutige Id, eine Artikelnummer, sowie ein Rating zwischen 0 und 10.</p>
	 * 
	 * <p>Weitere Informationen über den Aufbau der CSV-Datei finden Sie unter
	 *  https://www.shopgate.com/csvdoc/csv_docu_reviews/</p>
	 * 
	 * @see lib/ShopgatePluginCore::startCreateReviewsCsv()
	 */
	protected function createReviewsCsv() {
		$reviews = array(
			array(
				"id"		=> "100",
				"item"		=> "iphone004",
				"rate"		=> "6",
				"name"		=> "",
				"date"		=> "2010-08-01",
				"title"		=> "Super aber...",
				"free_text"	=> "... das bekannte antennenproblem"
			),
			array(
				"id"		=> "101",
				"item"		=> "iphone004",
				"rate"		=> "10",
				"name"		=> "Max Mustermann",
				"date"		=> "2010-08-02",
				"title"		=> "Super, bin sehr zufrieden !!TOP!!",
				"free_text"	=> ""
			),
			array(
				"id"		=> "110",
				"item"		=> "htc001",
				"rate"		=> "10",
				"name"		=> "",
				"date"		=> "2010-06-06",
				"title"		=> "",
				"free_text"	=> ""
			),
			array(
				"id"		=> "120",
				"item"		=> "htc001",
				"rate"		=> "9",
				"name"		=> "Anonym",
				"date"		=> "2010-07-10",
				"title"		=> "Mein persönlicher Testsieger",
				"free_text"	=> ""
			),
		);
		$itemArr = array();
		
		foreach($reviews as $rev) {
			// Die Artikelnummer des Produktes, zu dem die Bewertung gehört
			$itemArr["item_number"] 		= $rev["item"];
			// Die BewertungsId in Ihrem System
			$itemArr["update_review_id"] 	= $rev["id"];
			// Die anzahl der vergebenen Punkte.
			// 1=Schlecht ... 10 Sehr Gut
			$itemArr["score"] 				= $rev["rate"];
			// Name des verfassers
			$itemArr["name"] 				= $rev["name"];
			// Datem, wann die Bewertung erfasst wurde
			$itemArr["date"] 				= $rev["date"];
			// Der Titel der Bewertung
			$itemArr["title"]	 			= $rev["title"];
			// Der Text der Bewertung
			$itemArr["text"] 				= $rev["free_text"];
			
			$this->addItem($itemArr);
		}
	}
	
	/**
	 * <p>Diese Funktion wird aufgerufen, wenn eine CSV-Datei mit
	 * Zusatztexten/-informationen zu Ihren Produkten erstellt
	 * werden soll.</p> 
	 * 
	 * <p>Gestartet wird der Prozess über die Datei cronjobs/update_pages_csv.php</p>
	 *
	 * <p>Die CSV-Datei wird vom Framework verwaltet und von diesem geschrieben. Das einzige
	 * was Sie machen müssen ist die Daten aus Ihrem Shopping-System zu lesen, für jedes
	 * Produkt ein bestimmtes Array zu generieren und dieses Array an die Funktion $this->addItem($itemArray)
	 * übergeben. Die Funktion $this->addItem($itemArray) verwaltet das Schreiben in die CSV-Datei.</p>
	 *
	 * <p>In diesem Beispiel haben wir ein Array ($pages) mit Beispiel-Zusatztexten vorbereitet. Normalerweise
	 * sollen dann hier Ihre Daten aus Ihrer Datenbank stehen.</p>
	 * 
	 * <p>Um die Zusatztexte korrekt in der App darstellen zu können, benötigen wir zu jedem Produkt die Artikelnummer,
	 * eine Überschrift für die Zusatztexte und den Text an sich.</p>
	 * 
	 * <p>Weitere Informationen über den Aufbau der CSV-Datei finden Sie unter
	 *  <a href="https://www.shopgate.com/csvdoc/csv_docu_reviews/">
	 *  https://www.shopgate.com/csvdoc/csv_docu_reviews/
	 *  </a></p>
	 *
	 * @see lib/ShopgatePluginCore::startCreatePagesCsv()
	 */
	protected function createPagesCsv() {
		$pages = array(
			array(
				"item"	=> "iphone004",
				"head"	=> "Das iPhone 4G",
				"text"	=> "<p>Das super iPhone der neusten Generation mit Navigationssoftware</p>",
			),
			array(
				"item"	=> "htc001",
				"head"	=> "Android auf dem Aktuellsten stand",
				"text"	=> "HTC liefert regelmäßig die neusten Updates für seine Android geräte",
			),
			array(
				"item"	=> "tp01125",
				"head"	=> "ThinkPad reihe",
				"text"	=> "Die Traditionsbewußte ThinkPad-Reihe von Lenove. Rubusst, schnell, flexibel! Ideal für Personen, die viel mit Laptops arbeiten.",
			),
		);
		
		foreach($pages as $page) {
			$itemArr = array();
			// Die Artikelnummer des Produktes, zudem der text gehört
			$itemArr["item_number"] = $page["item"];
			// Der Titel des Textes
			$itemArr["title"] 		= $page["head"];
			// Der Text. Dieser kann auch HTML-Formatierungen enthalten
			$itemArr["text"] 		= $page["text"];
			
			$this->addItem($itemArr);
		}
	}
	
	/**
	 * <p>Diese Funktion wird aufgerufen, wenn das Shoppingsytsem Shopgate-Connect unterstützt.
	 * Dies erlaubt dem Shopbetreiber, dass sich ein Kunde mit seinen Login-Daten
	 * bei Shopgate einloggen kann.</p>
	 * 
	 * <p>Die Login-Daten des Kunden werden übergeben und müssen mit den Daten in der
	 * Datenbank verglichen werden. Die Daten werden im Klartext übermittelt,
	 * weshalb eine HTTPS-Verbindung notwendig ist. Das Passwort muss mit Ihrem
	 * Algorithmus verschlüsselt und verglichen werden.</p>
	 * 
	 * <p>Jeder Benutzer muss hierbei mindestens eine eindeutige Id, eine Email-Adresse, ein Passwort,
	 * einen Vornamen, einen Nachnamen sowie eine gültige Adresse inklusive Länderkürzel haben.</p>
	 *  
	 * <p>Die Daten müssen anhand von $user und $pass aus der Datenbank geladen werden.
	 * Das Passwort ist hierbei verschlüsselt.</p>
	 * 
	 * @see lib/ShopgatePluginCore::getUserData()
	 * 
	 * @param String $user
	 * @param String $pass
	 * @return ShopgateShopCustomer
	 */
	public function getUserData($user, $pass) {
		// Erzeuge Beispieldaten
		$users = array(
			array(
				"id"		=> "12345",
				"password"	=> md5("max"),
				"company"	=> "",
				"name"		=> "Max",
				"surname"	=> "Mustermann",
				"city"		=> "Butzbach",
				"street"	=> "Badborngasse 1a",
				"zipcode"	=> "35510",
				"country"	=> "DE",
				"gender"	=> "m",
				"email"		=> "max.mustermann@shopgate.com",
				"phone"		=> "06033 7470 0",
				"mobile"	=> ""
			),
			array(
				"id"		=> "78556",
				"password"	=> md5("eva"),
				"company"	=> "",
				"name"		=> "Eva",
				"surname"	=> "Mustermann",
				"city"		=> "Butzbach",
				"street"	=> "Badborngasse 1a",
				"zipcode"	=> "35510",
				"country"	=> "DE",
				"gender"	=> "f",
				"email"		=> "eva.mustermann@shopgate.com",
				"phone"		=> "06033 7470 0",
				"mobile"	=> ""
			),
			array(
				"id"		=> "123889",
				"password"	=> md5("eva"),
				"company"	=> "",
				"name"		=> "Monika",
				"surname"	=> "von Wald",
				"city"		=> "Berlin",
				"street"	=> "Hintergasse 40",
				"zipcode"	=> "10115",
				"country"	=> "DE",
				"gender"	=> "f",
				"email"		=> "monika@shopgate.com",
				"phone"		=> "",
				"mobile"	=> ""
			),
		);

		// Suchen nach passendem Benutzer und prüfe die Daten
		// Wenn diese Daten stimmen, geben Sie das ShopgateShopCustomer Objekt zurück
		foreach($users as $_user) {
			if($_user["email"] == $user
			&& $_user["password"] == md5($pass)) {
				
				$userData = new ShopgateShopCustomer();
				$userData->setCustomerNumber($_user["id"]);
				$userData->setFirstName($_user["name"]);
				$userData->setSurname($_user["surname"]);
				$userData->setCompany($_user["company"]);
				$userData->setCity($_user["city"]);
				$userData->setStreet($_user["street"]);
				$userData->setZip($_user["zipcode"]);
				$userData->setCountry($_user["country"]);
				$userData->setGender($_user["gender"]=="m"?ShopgateShopCustomer::MALE:ShopgateShopCustomer::FEMALE);
				$userData->setMail($_user["email"]);
				$userData->setMobile($_user["mobile"]);
				$userData->setPhone($_user["phone"]);
				return $userData;
			}
		}
		
		return false;
	}
	
	/**
	 * <p>Diese Funktion wird aufgerufen, um die exakte Version des verwendeten Shopsystems
	 * zu erfragen. 
	 * </p> 
	 *
	 * @see lib/ShopgatePluginCore::startCreateShopInfo()
	 */
	public function createShopInfo(){
		
	}
	/**
	 * <p>Diese Funktion speichert eine Bestellung in Ihre Datenbank. Das Objekt $order enthält alle
	 * relevanten Daten und die bestellten Artikel. Zudem werden auch Lieferanschrift,
	 * Rechnungsanschrift und Kundenanschrift übergeben.</p>
	 *
	 * <p>Die Produkte können über die Funktion $order->getOrderItems() als Array
	 * abgerufen werden. Jedes Element ist ein Objekt vom Typ ShopgateOrderItem,
	 * welches die wichtigsten Informationen zu dem jeweiligen Produkt enthält.</p>
	 * 
	 * <code>
	 * foreach($order->getOrderItems() as $orderItem) {
	 * 
	 * }
	 * </code>
	 * 
	 * <p>Die Addressdaten sind vom Typ ShopgateOrderAddress und enthalten jeweils die
	 * Kunden-, Liefer-, oder Rechnungsanschrift.</p>
	 * - <b>Die Adresse des Kunden:</b><br/>
	 *      $order->getCustomerAddress();
	 * - <b>Die Lieferadresse:</b><br />
	 *      $order->getDeliveryAddress();
	 * - <b>Die Rechungsadresse:</b><br />
	 *      $order->getInvoiceAddress();
	 * 
	 * @see lib/ShopgatePluginCore::saveOrder()
	 * @param ShopgateOrder $order
	 */
	public function saveOrder(ShopgateOrder $order) {
		
		// Allgemeine Daten zur Bestellung
		$shopgateBestellnummer 			= $order->getOrderNumber();
		$kundenNummerInIhremShop 		= $order->getExternalCustomerNumber();
		$artikelPreis 					= $order->getAmountItems();
		$versandkosten 					= $order->getAmountShipping();
		$gesamtkostenDerBestellung 		= $order->getAmountComplete();
		$kundenTelefonNummer 			= $order->getCustomerPhone();
		$kundenEmailAdresse 			= $order->getCustomerMail();
		
		// Kundenadresse
		$customer = $order->getCustomerAddress();
		$kundenVorname 					= $customer->getFirstName();
		$kundenNachname 				= $customer->getSurname();
		$kundenFirma 					= $customer->getCompany();
		$kundenStrasse 					= $customer->getStreet();
		$kundenStadtname 				= $customer->getCity();
		$kundenPostleitzahl 			= $customer->getZipcode();
		$kundenLand 					= $customer->getCountry();
		
		// Lieferadresse 
		$delivery = $order->getDeliveryAddress();
		$lieferAdresseVorname 			= $delivery->getFirstName();
		$lieferAdresseNachname 			= $delivery->getSurname();
		$lieferAdresseFirma 			= $delivery->getCompany();
		$lieferAdresseStrasse 			= $delivery->getStreet();
		$lieferAdresseStadtname 		= $delivery->getCity();
		$lieferAdressePostleitzahl 		= $delivery->getZipcode();
		$lieferAdresseLand 				= $delivery->getCountry();
		
		// Rechnungsadresse
		$invoice = $order->getInvoiceAddress();
		$rechnungsAdresseVorname 		= $invoice->getFirstName();
		$rechnungsAdresseNachname 		= $invoice->getSurname();
		$rechnungsAdresseFirma 			= $invoice->getCompany();
		$rechnungsAdresseStrasse		= $invoice->getStreet();
		$rechnungsAdresseStadtname 		= $invoice->getCity();
		$rechnungsAdressePostleitzahl 	= $invoice->getZipcode();
		$rechnungsAdresseLand 			= $invoice->getCountry();
		
		// Produktliste der Bestellung
		$produktlisteDerBestellung = $order->getOrderItems();
		
		// Die einzelnen Produkte aus der Liste auslesen
		foreach($produktlisteDerBestellung as $produkt) {

			$artikelNummer 				= $produkt->getItemNumber();
			$artikelName 				= $produkt->getName();
			$artikelBruttopreis 		= $produkt->getUnitAmountWithTax();
			$artikelNettopreis 			= $produkt->getUnitAmount();
			$artikelMehrwertsteuer 		= $produkt->getTaxPercent();
			$artikelWährung 			= $produkt->getCurrency();
			$artikelAnzahl 				= $produkt->getQuantity();
			
		}

		/*
		 * 
		 * Ihre Aufgabe:
		 * Speichern bzw. Weiterverarbeiten der Bestell- und Produktdaten
		 * 
		 */
		
	}
}

?>