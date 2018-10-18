<?php

/**
 * Die API für Bestellungen zur Kommunikation mit Shopgate
 * 
 * @author Martin Weber
 * @version 1.0.0
 * @package shopgate_framework
 */
class ShopgateOrderApi extends ShopgateCoreApi {
	
	/**
	 * Die Details einer Bestellung von Shopgate mit der Bestellnummer abholen. 
	 * Es wird ein Objekt mit den kompletten Bestellinformationen zurückgegeben.
	 * 
	 * @tutorial <a href="https://www.shopgate.com/apidoc/function_details/9">
	 * https://www.shopgate.com/apidoc/function_details/9</a>
	 * 
	 * @param string $orderNumber	Die Bestellnummer.
	 * @throws ShopgateFrameworkException
	 * @return ShopgateOrder 	Die Bestellung in einem ShopgateOrder-Objekt
	 */
	public function getOrderDetails($orderNumber) {
		$result = $this->_execute("orders_get_details", array("order_number"=>$orderNumber));
		if(empty($result["shop_order"])) throw new ShopgateFrameworkException("Das Format entspricht nicht der ShopgateAPI.\n".print_r($result,true));
		
		$order = new ShopgateOrder($result["shop_order"]);
		
		$customer = new ShopgateOrderAddress($result["shop_order"]["customer"]);
		$order->setCustomerAddress($customer);
		
		$invoice_address = new ShopgateOrderAddress($result["shop_order"]["invoice_address"]);
		$order->setInvoiceAddress($invoice_address);
		
		$delivery_address = new ShopgateOrderAddress($result["shop_order"]["delivery_address"]);
		$order->setDeliveryAddress($delivery_address);
		
		$notes = array();
		foreach($result["delivery_notes"] as $note)
			$notes[] = new ShopgateDeliveryNote($node);
		$order->setDeliversNotes($notes);
		
		$items = array();
		foreach($result["items"] as $item)
			$items[] = new ShopgateOrderItem($item);

		$order->setOrderItems($items);
		
		return $order;
	}

	/**
	 * Ein Array mit einer Liste aller Bestellunegn bei Shopgate abholen.
	 * 
	 * Parameter werden über ein Array übergeben:
	 * <ul>
	 * <li><b>limit</b><br/>Maximale Anzahl an Objekten pro Anfrage auf einer Seite. Das absolute Maximum liegt bei 100.</li>
	 * <li><b>page</b><br/>Die Seite. See mySQL limit/page</li>
	 * <li><b>date_from</b></li>
	 * <li><b>date_to</b></li>
	 * </ul>
	 * 
	 * @tutorial <a href="https://www.shopgate.com/apidoc/function_details/7">
	 * https://www.shopgate.com/apidoc/function_details/7</a>
	 * 
	 * @param array $params			
	 * @return array 	Eine Liste der Bestellungen
	 */
	public function getOrderList($params=array()) {
		$result = $this->_execute("orders_get_list", $params);
		$orders = array();
		
		foreach($result["orders"] as $order) {
			$orders[] = new ShopgateOrder($order);
		}
		
		return $orders;
	}
	
	/**
	 * Gibt ein Array mit nicht abgeschlossen Bestellungen zurück.
	 * 
	 * Parameter werden über ein Array übergeben:
	 * <ul>
	 * <li><b>limit</b><br/>Maximale Anzahl an Objekten pro Anfrage auf einer Seite. Das absolute Maximum liegt bei 100.</li>
	 * <li><b>page</b><br/>Die Seite. See mySQL limit/page</li>
	 * <li><b>date_from</b></li>
	 * <li><b>date_to</b></li>
	 * 
	 * @tutorial <a href="https://www.shopgate.com/apidoc/function_details/7">
	 * https://www.shopgate.com/apidoc/function_details/7</a>
	 * 
	 * @param array $params
	 * @return array	Eine Liste der nicht abgeschlossenen Bestellungen
	 */
	public function getNotCompletedOrderList($params=array()) {
		$result = $this->_execute("orders_get_not_completed_list", $params);
		$orders = array();
		
		foreach($result["orders"] as $order) {
			$orders[] = new ShopgateOrder($order);
		}
		
		return $orders;
	}
	
	/**
	 * Gibt ein Array mit nicht bestätigten Bestellungen zurück.
	 * 
	 * Parameter werden über ein Array übergeben:
	 * <ul>
	 * <li><b>limit</b><br/>Maximale Anzahl an Objekten pro Anfrage auf einer Seite. Das absolute Maximum liegt bei 100.</li>
	 * <li><b>page</b><br/>Die Seite. See mySQL limit/page</li>
	 * <li><b>date_from</b></li>
	 * <li><b>date_to</b></li>
	 * </ul>
	 * 
	 * @tutorial <a href="https://www.shopgate.com/apidoc/function_details/7">
	 * https://www.shopgate.com/apidoc/function_details/7</a>
	 * 
	 * @param array $params
	 * @return array	Eine Liste der nicht bestätigten Bestellungen
	 */
	public function getNotApprovedOrderList($params=array()) {
		$result = $this->_execute("orders_get_not_approved_list", $params);
		$orders = array();
		
		foreach($result["orders"] as $order) {
			$orders[] = new ShopgateOrder($order);
		}
		
		return $orders;
	}
	
	/**
	 * EIne Bestellung als abgeschlossen markieren
	 *
	 * @param ShogateOrder $order
	 */
	public function setShippingComplete($order) {
		$params = array(
			"order_number" => $order->getOrderNumber(),
		);
		
		$this->_execute("orders_set_shipping_status_completed", $params);
	}

	public function setShippingCompletelist($orderList) {
		$params = array();
		for($i=0;$i<count($orderList);$i++)
			$params["orders[$i]"]=$orderList[$i]->getOrderNumber();
			
		$this->_execute("orders_set_shipping_status_completed", $params);
	}
	
	/**
	 * Eine Nachricht an den Kunden der Bestellung schicken.
	 * 
	 * @param ShopgateOrder $order	Die Bestellung in einem ShopgateOrder-Objekt
	 * @param string $message	Die Nachricht an den Kunden
	 */
	public function sendMessage($order, $message) {
		$params = array(
			"order_number"=>$order->getOrderNumber(),
			"message"=>$message,
		);
		
		$this->_execute("orders_send_message", $params);
	}
}


/**
 * Die ShopgateOrder-Klasse in der die einzelnen Details einer Bestellung gespeichert werden.
 * 
 * @author Martin Weber
 * @version 1.0.0
 */
class ShopgateOrder {
	private $order_number;
	private $order_received_status;
	private $order_received_status_time;
	private $shipping_completed;
	private $shipping_completed_time;
	private $manual_confirm_shipping_url;
	private $amount_items;
	private $amount_shipping;
	private $amount_complete;
	private $order_currency;
	private $is_test;
	
	private $customer_number;
	private $customer_mail;
	private $customer_phone;
	private $customer_mobile;
	private $external_customer_number;
	
	private $customer_address;
	private $invoice_address;
	private $delivery_address;
	
	private $order_items;
	private $delivery_notes;
	
	
	
	/**
	 * Der Konstruktor der ShopgateOrder-Klasse
	 * 
	 * @param array $data  Ein Array mit allen Informationen der Bestellung
	 */
	public function __construct($data=null) {
		if(!empty($data)) {
			$this->setOrderNumber($data["order_number"]);
			$this->setShippingApproved($data["order_received_status"]);
			$this->setShippingApprovedTime($data["order_received_status_time"]);
			$this->setShippingCompleted($data["shipping_completed"]);
			$this->setShippingCompletedTime($data["shipping_completed_time"]);
			$this->setAmountItems($data["amount_items"]);
			$this->setAmountShipping($data["amount_shipping"]);
			$this->setAmountComplete($data["amount_complete"]);
			$this->setCustomerNumber($data["customer_number"]);
			$this->setExternalCustomerNumber($data["external_customer_number"]);
			$this->setIsTest($data["is_test"]);
			$this->setCustomerMail($data["customer_mail"]);
			$this->setCustomerPhone($data["customer_phone"]);
			$this->setCustomerMobile($data["customer_mobile"]);
			$this->setOrderCurrency($data["order_currency"]);
			$this->setManualConfirmShippingUrl($data["manual_confirm_shipping_url"]);
		}
	}
	
	/**
	 * Sets the order_number value
	 *
	 * @param string $order_number
	 * @return void
	 */
	public function setOrderNumber($order_number) {
		$this->order_number = $order_number;
	}
	
	/**
	 * Returns the order_number value
	 *
	 * @return string
	 */
	public function getOrderNumber() {
		return $this->order_number;
	}
	
	/**
	 * Sets the order_received_status value
	 *
	 * @param bool $order_received_status
	 * @return void
	 */
	public function setShippingApproved($order_received_status) {
		$this->order_received_status = $order_received_status;
	}
	
	/**
	 * Returns the order_received_status value
	 *
	 * @return bool
	 */
	public function getShippingApproved() {
		return $this->order_received_status;
	}
	
	/**
	 * Sets the order_received_status_time value
	 *
	 * @param string $order_received_status_time
	 * @return void
	 */
	public function setShippingApprovedTime($order_received_status_time) {
		$this->order_received_status_time = $order_received_status_time;
	}
	
	/**
	 * Returns the order_received_status_time value
	 *
	 * @return string
	 */
	public function getShippingApprovedTime() {
		return $this->order_received_status_time;
	}
	
	/**
	 * Sets the shipping_completed value
	 *
	 * @param bool $shipping_completed
	 * @return void
	 */
	public function setShippingCompleted($shipping_completed) {
		$this->shipping_completed = $shipping_completed;
	}
	
	/**
	 * Returns the shipping_completed value
	 *
	 * @return bool
	 */
	public function getShippingCompleted() {
		return $this->shipping_completed;
	}
	
	/**
	 * Sets the shipping_completed_time value
	 *
	 * @param string $shipping_completed_time
	 * @return void
	 */
	public function setShippingCompletedTime($shipping_completed_time) {
		$this->shipping_completed_time = $shipping_completed_time;
	}
	
	/**
	 * Returns the shipping_completed_time value
	 *
	 * @return string
	 */
	public function getShippingCompletedTime() {
		return $this->shipping_completed_time;
	}
	
	/**
	 * Sets the amount_items value
	 *
	 * @param int $amount_items
	 * @return void
	 */
	public function setAmountItems($amount_items) {
		$this->amount_items = $amount_items;
	}
	
	/**
	 * Returns the amount_items value
	 *
	 * @return int
	 */
	public function getAmountItems() {
		return $this->amount_items;
	}
	
/**
	 * Sets the amount_shipping value
	 *
	 * @param int $amount_shipping
	 * @return void
	 */
	public function setAmountShipping($amount_shipping) {
		$this->amount_shipping = $amount_shipping;
	}
	
	/**
	 * Returns the amount_shipping value
	 *
	 * @return int
	 */
	public function getAmountShipping() {
		return $this->amount_shipping;
	}
	
	/**
	 * Sets the amount_complete value
	 *
	 * @param int $amount_complete
	 * @return void
	 */
	public function setAmountComplete($amount_complete) {
		$this->amount_complete = $amount_complete;
	}
	
	/**
	 * Returns the amount_complete value
	 *
	 * @return int
	 */
	public function getAmountComplete() {
		return $this->amount_complete;
	}
	
	/**
	 * Sets the is_test value
	 *
	 * @param bool $is_test
	 * @return void
	 */
	public function setIsTest($is_test) {
		$this->is_test = $is_test;
	}
	
	/**
	 * Returns the is_test value
	 *
	 * @return bool
	 */
	public function getIsTest() {
		return $this->is_test;
	}
	
	/**
	 * Sets the customer value
	 *
	 * @param ShopgateOrderAddress $customer
	 * @return void
	 */
	public function setCustomerAddress(ShopgateOrderAddress $customer) {
		$this->customer_address = $customer;
	}
	
	/**
	 * Returns the customer value
	 *
	 * @return ShopgateOrderAddress
	 */
	public function getCustomerAddress() {
		return $this->customer_address;
	}
	
	/**
	 * Sets the invoice_address value
	 *
	 * @param ShopgateOrderAddress $invoice_address
	 * @return void
	 */
	public function setInvoiceAddress(ShopgateOrderAddress $invoice_address) {
		$this->invoice_address = $invoice_address;
	}
	
	/**
	 * Returns the invoice_address value
	 *
	 * @return ShopgateOrderAddress
	 */
	public function getInvoiceAddress() {
		return $this->invoice_address;
	}
	
	/**
	 * Sets the delivery_address value
	 *
	 * @param ShopgateOrderAddress $delivery_address
	 * @return void
	 */
	public function setDeliveryAddress(ShopgateOrderAddress $delivery_address) {
		$this->delivery_address = $delivery_address;
	}
	
	/**
	 * Returns the delivery_address value
	 *
	 * @return ShopgateOrderAddress
	 */
	public function getDeliveryAddress() {
		return $this->delivery_address;
	}
	
	/**
	 * Sets the delivery_notes value
	 *
	 * @param array $delivery_notes
	 * @return void
	 */
	public function setDeliversNotes($delivery_notes) {
		$this->delivery_notes = $delivery_notes;
	}
	
	/**
	 * Returns the delivery_notes value
	 *
	 * @return array
	 */
	public function getDeliversNotes() {
		return $this->delivery_notes;
	}
	
	/**
	 * Sets the order_items value
	 *
	 * @param array $order_items
	 * @return void
	 */
	public function setOrderItems($order_items) {
		$this->order_items = $order_items;
	}
	
	/**
	 * Returns the order_items value
	 *
	 * @return array
	 */
	public function getOrderItems() {
		return $this->order_items;
	}

	/**
	 * Sets the customer_number value
	 *
	 * @param string $customer_number
	 * @return void
	 */
	public function setCustomerNumber($customer_number) {
		$this->customer_number = $customer_number;
	}
	
	/**
	 * Returns the customer_number value
	 *
	 * @return string
	 */
	public function getCustomerNumber() {
		return $this->customer_number;
	}
	
	/**
	 * Sets the external_customer_number value
	 *
	 * @param string $external_customer_number
	 * @return void
	 */
	public function setExternalCustomerNumber($external_customer_number) {
		$this->external_customer_number = $external_customer_number;
	}
	
	/**
	 * Returns the external_customer_number value
	 *
	 * @return string
	 */
	public function getExternalCustomerNumber() {
		return $this->external_customer_number;
	}
	
	/**
	 * Sets the customer_mail value
	 *
	 * @param string $customer_mail
	 * @return void
	 */
	public function setCustomerMail($customer_mail) {
		$this->customer_mail = $customer_mail;
	}
	
	/**
	 * Returns the customer_mail value
	 *
	 * @return string
	 */
	public function getCustomerMail() {
		return $this->customer_mail;
	}

	/**
	 * Sets the customer_phone value
	 *
	 * @param string $customer_phone
	 * @return void
	 */
	public function setCustomerPhone($customer_phone) {
		$this->customer_phone = $customer_phone;
	}
	
	/**
	 * Returns the customer_phone value
	 *
	 * @return string
	 */
	public function getCustomerPhone() {
		return $this->customer_phone;
	}
	
	/**
	 * Sets the customer_fax value
	 *
	 * @param string $customer_fax
	 * @return void
	 */
	public function setCustomerFax($customer_fax) {
		$this->customer_fax = $customer_fax;
	}
	
	/**
	 * Returns the customer_fax value
	 *
	 * @return string
	 */
	public function getCustomerFax() {
		return $this->customer_fax;
	}
	
	/**
	 * Sets the customer_mobile value
	 *
	 * @param string $customer_mobile
	 * @return void
	 */
	public function setCustomerMobile($customer_mobile) {
		$this->customer_mobile = $customer_mobile;
	}
	
	/**
	 * Returns the customer_mobile value
	 *
	 * @return string
	 */
	public function getCustomerMobile() {
		return $this->customer_mobile;
	}
	
	/**
	 * Sets the shipping_check_string value
	 *
	 * @param string $shipping_check_string
	 * @return void
	 */
	public function setManualConfirmShippingUrl($manual_confirm_shipping_url) {
		$this->manual_confirm_shipping_url = $manual_confirm_shipping_url;
	}
	
	/**
	 * Returns the shipping_check_string value
	 *
	 * @return string
	 */
	public function getManualConfirmShippingUrl() {
		return $this->manual_confirm_shipping_url;
	}
	
	/**
	 * Sets the order_currency value
	 *
	 * @param string $order_currency
	 * @return void
	 */
	public function setOrderCurrency($order_currency) {
		$this->order_currency = $order_currency;
	}
	
	/**
	 * Returns the order_currency value
	 *
	 * @return string
	 */
	public function getOrderCurrency() {
		return $this->order_currency;
	}
}


/**
 * Die ShopgateOrderAdress-Klasse in der die Adresse des Bestellenden gespeichert wird.
 * 
 * @author Martin Weber
 * @version 1.0.0
 */

class ShopgateOrderAddress {
	private $first_name;
	private $surname;
	private $company;
	private $street;
	private $city;
	private $zipcode;
	private $country;
	
	
	/**
	 * Der Konstruktor der ShopgateOrderAddress-Klasse.
	 * 
	 * @param array $data 	Ein Array mit allen Informationen über die Adresse.
	 */
	public function __construct($user=null) {
		if(!empty($user)) {
			$this->setFirstName($user["first_name"]);
			$this->setSurname($user["surname"]);
			$this->setCompany($user["company"]);
			$this->setStreet($user["street"]);
			$this->setCity($user["city"]);
			$this->setZipcode($user["zipcode"]);
			$this->setCountry($user["country"]);
		}
	}
	/**
	 * Sets the first_name value
	 *
	 * @param string $first_name
	 * @return void
	 */
	public function setFirstName($first_name) {
		$this->first_name = $first_name;
	}
	
	/**
	 * Returns the first_name value
	 *
	 * @return string
	 */
	public function getFirstName() {
		return $this->first_name;
	}
	
	/**
	 * Sets the surname value
	 *
	 * @param string $surname
	 * @return void
	 */
	public function setSurname($surname) {
		$this->surname = $surname;
	}
	
	/**
	 * Returns the surname value
	 *
	 * @return string
	 */
	public function getSurname() {
		return $this->surname;
	}
	
	/**
	 * Sets the company value
	 *
	 * @param string $company
	 * @return void
	 */
	public function setCompany($company) {
		$this->company = $company;
	}
	
	/**
	 * Returns the company value
	 *
	 * @return string
	 */
	public function getCompany() {
		return $this->company;
	}
	
	/**
	 * Sets the street value
	 *
	 * @param string $street
	 * @return void
	 */
	public function setStreet($street) {
		$this->street = $street;
	}
	
	/**
	 * Returns the street value
	 *
	 * @return string
	 */
	public function getStreet() {
		return $this->street;
	}
	
	/**
	 * Sets the city value
	 *
	 * @param string $city
	 * @return void
	 */
	public function setCity($city) {
		$this->city = $city;
	}
	
	/**
	 * Returns the city value
	 *
	 * @return string
	 */
	public function getCity() {
		return $this->city;
	}
	
	/**
	 * Sets the zipcode value
	 *
	 * @param string $zipcode
	 * @return void
	 */
	public function setZipcode($zipcode) {
		$this->zipcode = $zipcode;
	}
	
	/**
	 * Returns the zipcode value
	 *
	 * @return string
	 */
	public function getZipcode() {
		return $this->zipcode;
	}
	
	/**
	 * Sets the country value
	 *
	 * @param string $country
	 * @return void
	 */
	public function setCountry($country) {
		$this->country = $country;
	}
	
	/**
	 * Returns the country value
	 *
	 * @return string
	 */
	public function getCountry() {
		return $this->country;
	}
}


/**
 * Die ShopgateOrderItem-Klasse in der Informationen zu einzelnen Produkten einer Bestellung gespeichert werden.
 * 
 * @author Martin Weber
 * @version 1.0.0
 */

class ShopgateOrderItem {
	private $name;
	private $item_number;
	private $unit_amount;
	private $unit_amount_with_tax;
	private $currency;
	private $quantity;
	private $internal_order_info;
	private $tax_percent;
	
	
	/**
	 * Der Konstruktor der ShopgateOrderItem-Klasse.
	 * 
	 * @param array $data 	Ein Array mit allen Informationen zu einem Produkt der Bestellung.
	 */
	public function __construct($data=null) {
		if(!empty($data)) {
			$this->setName($data["name"]);
			$this->setItemNumber($data["item_number"]);
			$this->setUnitAmount($data["unit_amount"]);
			$this->setUnitAmountWithTax($data["unit_amount_with_tax"]);
			$this->setCurrency($data["currency"]);
			$this->setQuantity($data["quantity"]);
			$this->setInternalOrderInfo($data["internal_order_info"]);
			$this->setTaxPercent($data["tax_percent"]);
		}
	}
	
	/**
	 * Sets the name value
	 *
	 * @param string $name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}
	
	/**
	 * Returns the name value
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Sets the item_number value
	 *
	 * @param string $item_number
	 * @return void
	 */
	public function setItemNumber($item_number) {
		$this->item_number = $item_number;
	}
	
	/**
	 * Returns the item_number value
	 *
	 * @return string
	 */
	public function getItemNumber() {
		return $this->item_number;
	}
	
	/**
	 * Sets the unit_amount value
	 *
	 * @param int $unit_amount
	 * @return void
	 */
	public function setUnitAmount($unit_amount) {
		$this->unit_amount = $unit_amount;
	}
	
	/**
	 * Returns the unit_amount value
	 *
	 * @return int
	 */
	public function getUnitAmount() {
		return $this->unit_amount;
	}
	
	/**
	 * Sets the unit_amount_with_tax value
	 *
	 * @param int $unit_amount_with_tax
	 * @return void
	 */
	public function setUnitAmountWithTax($unit_amount_with_tax) {
		$this->unit_amount_with_tax = $unit_amount_with_tax;
	}
	
	/**
	 * Returns the unit_amount_with_tax value
	 *
	 * @return int
	 */
	public function getUnitAmountWithTax() {
		return $this->unit_amount_with_tax;
	}
	
	/**
	 * Sets the currency value
	 *
	 * @param string $currency
	 * @return void
	 */
	public function setCurrency($currency) {
		$this->currency = $currency;
	}
	
	/**
	 * Returns the currency value
	 *
	 * @return string
	 */
	public function getCurrency() {
		return $this->currency;
	}
	
	/**
	 * Sets the quantity value
	 *
	 * @param int $quantity
	 * @return void
	 */
	public function setQuantity($quantity) {
		$this->quantity = $quantity;
	}
	
	/**
	 * Returns the quantity value
	 *
	 * @return int
	 */
	public function getQuantity() {
		return $this->quantity;
	}
	
	/**
	 * Sets the internal_order_info value
	 *
	 * @param string $internal_order_info
	 * @return void
	 */
	public function setInternalOrderInfo($internal_order_info) {
		$this->internal_order_info = $internal_order_info;
	}
	
	/**
	 * Returns the internal_order_info value
	 *
	 * @return string
	 */
	public function getInternalOrderInfo($asArray=false) {
		if($asArray)
			return json_decode($this->internal_order_info, true);
		
		return $this->internal_order_info;
	}
	
	/**
	 * Sets the tax_percent value
	 *
	 * @param int $tax_percent
	 * @return void
	 */
	public function setTaxPercent($tax_percent) {
		$this->tax_percent = $tax_percent;
	}
	
	/**
	 * Returns the tax_percent value
	 *
	 * @return int
	 */
	public function getTaxPercent() {
		return $this->tax_percent;
	}
}

/**
 * Die ShopgateDeliveryNote-Klasse in der Information zu der Lieferung einer Bestellug gespeichert werden. 
 * 
 * @author Martin Weber
 * @version 1.0.0
 */
class ShopgateDeliveryNote {
	const DHL = "DHL";
	
	private $shipping_service_id = ShopgateDeliveryNote::DHL;
	private $tracking_number;
	private $created;
	
	
	/**
	 * Der Konstruktor der ShopgateDeliveryNote-Klasse.
	 * 
	 * @param array $data 	Ein Array mit allen Information über die Lieferung der Bestellung.
	 */
	public function __construct($data=null) {
		if(!empty($data)) {
			$this->setShippingServiceId($data["shipping_service_id"]);
			$this->setTrackingNumber($data["tracking_number"]);
			$this->setCreated($data["created"]);
		}
	}
	/**
	 * Sets the shipping_service_id value
	 *
	 * @param string $shipping_service_id
	 * @return void
	 */
	public function setShippingServiceId($shipping_service_id) {
		$this->shipping_service_id = $shipping_service_id;
	}
	
	/**
	 * Returns the shipping_service_id value
	 *
	 * @return string
	 */
	public function getShippingServiceId() {
		return $this->shipping_service_id;
	}
	
	/**
	 * Sets the tracking_number value
	 *
	 * @param string $tracking_number
	 * @return void
	 */
	public function setTrackingNumber($tracking_number) {
		$this->tracking_number = $tracking_number;
	}
	
	/**
	 * Returns the tracking_number value
	 *
	 * @return string
	 */
	public function getTrackingNumber() {
		return $this->tracking_number;
	}
	
	/**
	 * Sets the created value
	 *
	 * @param string $created
	 * @return void
	 */
	public function setCreated($created) {
		$this->created = $created;
	}
	
	/**
	 * Returns the created value
	 *
	 * @return string
	 */
	public function getCreated() {
		return $this->created;
	}
	
	/**
	 * Sets the items value
	 *
	 * @param array $items
	 * @return void
	 */
	public function setItems($items) {
		$this->items = $items;
	}
	
	/**
	 * Returns the items value
	 *
	 * @return array
	 */
	public function getItems() {
		return $this->items;
	}
	
	
}

