<?php
/**
 * Copyright 2014 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License.You may obtain
 * a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 *  @author    Ludovic Drin <ludovic@lengow.com> Romain Le Polh <romain@lengow.com>
 *  @copyright 2014 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 */

$sep = DIRECTORY_SEPARATOR;
require_once dirname(__FILE__).$sep.'..'.$sep.'loader.php';
try
{
	loadFile('core');
	loadFile('connector');
	if (_PS_VERSION_ >= '1.5')
		loadFile('gender');
	loadFile('address');
	loadFile('cart');
	loadFile('product');
	loadFile('order');
	loadFile('orderdetail');
	loadFile('payment');
	loadFile('marketplace');
	loadFile('customer');
	loadFile('carrier');
} catch(Exception $e)
{	
	try
	{
		loadFile('core');
		LengowCore::log($e->getMessage(), null, 1);
	} catch (Exception $ex)
	{
		echo date('Y-m-d : H:i:s ').$e->getMessage().'<br />';
	}
}
if (_PS_VERSION_ < '1.5')
	require_once _PS_MODULE_DIR_.'lengow'.$sep.'backward_compatibility'.$sep.'backward.php';

/**
 * The Lengow Import Class.
 *
 * @author Ludovic Drin <ludovic@lengow.com>
 * @copyright 2013 Lengow SAS
 */
class LengowImportAbstract {

	/**
	 * Version.
	 */
	const VERSION = '1.0.1';

	public $context;
	public $id_lang;
	public $id_shop;
	public $id_shop_group;
	public static $force_log_output = true;
	public static $import_start = false;
	public static $debug = false;
	public $single_order = false;
	protected $_customer;
	protected $_billing_address;
	protected $_shipping_address;
	protected $_lengow_order_id;
	protected $_total_saleable_quantity = 0;
	protected $_lengow_new_order = false;
	public $count_orders_added = 0;
	public $count_orders_updated = 0;
	public $forceImport = false;


	/**
	* Construct the import manager
	*
	* @param $id_lang integer ID lang
	* @param $id_shop integer ID shop
	*/
	public function __construct($id_lang = null, $id_shop = null)
	{
		$this->context = LengowCore::getContext();
		if (empty($id_lang))
			$this->id_lang = $this->context->language->id;
		if (empty($id_shop))
			$this->id_shop = $this->context->shop->id;
		$this->id_shop_group = $this->context->shop->id_shop_group;
		if (Configuration::get('LENGOW_REPORT_MAIL') && !LengowCore::isDebug())
			LengowCore::sendMailAlert();
	}

	/**
	* Construct the import manager
	*
	* @param $command varchar The command of import
	* @param mixed
	*/
	public function exec($command, $args = array())
	{
		switch ($command)
		{
			case 'orders':
				return $this->_importOrders($args);
			case 'singleOrder' :
				return $this->_importOrders($args);
			default:
				return $this->_importOrders($args);
		}
	}

	protected function _buildLengowOrderId($lengow_order)
	{
		if (self::$debug == true)
			return (string)$lengow_order->order_id.'--'.time();
		else
			return (string)$lengow_order->order_id;
	}

	/**
	 * Sets the Debug field as true if debug mode
	 * @param type $args
	 */
	protected function _setDebug()
	{
		self::$force_log_output = true;
		self::$debug = LengowCore::isDebug();
	}

	/**
	 * Return the args order
	 * @param array $args
	 * @return array
	 */
	protected function _setArgsOrder($args)
	{
		if (array_key_exists('orderid', $args) && $args['orderid'] != '' && array_key_exists('feed_id', $args) && $args['feed_id'] != '')
		{
			$args_order = array(
				'orderid' => $args['orderid'],
				'feed_id' => $args['feed_id'],
				'id_group' => LengowCore::getGroupCustomer()
			);
			self::$force_log_output = -1;
			if ($this->forceImport)
				$this->single_order = true;
		}
		else
		{
			$args_order = array(
				'dateFrom' => $args['dateFrom'],
				'dateTo' => $args['dateTo'],
				'id_group' => LengowCore::getGroupCustomer(),
				'state' => 'plugin'
			);
		}
		return $args_order;
	}

	protected function _getImportOrders($lengow_connector, $args_order, $args)
	{
		$orders = $lengow_connector->api('commands', $args_order);
		if (!is_object($orders))
		{
			LengowCore::log('Error on lengow webservice', null, self::$force_log_output);
			LengowCore::setImportEnd();
			die();
		}
		else
		{
			$find_count_orders = count($orders->orders->order);
			LengowCore::log('Find '.$find_count_orders.' order'.($find_count_orders > 1 ? 's' : ''), null, self::$force_log_output);
		}

		$count_orders = (integer)$orders->orders_count->count_total;
		if ($count_orders == 0)
		{
			LengowCore::log('No orders to import between '.$args['dateFrom'].' and '.$args['dateTo'], null, self::$force_log_output);
			LengowCore::setImportEnd();
			return false;
		}
		return $orders;
	}

	protected function _deleteOrderData()
	{
		$this->_customer = null;
		$this->_billing_address = null;
		$this->_shipping_address = null;
		$this->_lengow_order_id = null;
	}

	protected function _processOrders($orders, $lengow_connector)
	{
		foreach ($orders->orders->order as $data)
		{
			$this->_deleteOrderData();
			$lengow_order = $data;

			// if ((string)$lengow_order->order_id != '1503031549JWZK5')
			// 	continue;

			$this->_lengow_order_id = $this->_buildLengowOrderId($lengow_order);
			if (!LengowCore::getImportProcessingFee())
			{
				$data->order_amount = new SimpleXMLElement('<order_amount><![CDATA['
															.((float)$data->order_amount - (float)$data->order_processing_fee)
															.']]></order_amount>');
				$data->order_processing_fee = new SimpleXMLElement('<order_processing_fee><![CDATA[ ]]></order_processing_fee>');
				LengowCore::log('rewrite amount without processing fee', (string)$data->order_id, self::$force_log_output);
			}

			LengowCore::disableMail();
			// Check if order is already process or imported
			$id_flux = (integer)$lengow_order->idFlux;
			if ($this->_isProccessing())
				continue;
			LengowCore::startProcessOrder($this->_lengow_order_id, Tools::jsonEncode($lengow_order));
			
			$marketplace = LengowCore::getMarketplaceSingleton((string)$lengow_order->marketplace);
			// Check if order is already sent by marketplace and not authorised by merchant
			if ($this->_isSentByMarketPlace($lengow_order) && !in_array($marketplace->name, LengowCore::getForceMarketplaces()))
				continue;
			// Check if status is available for import
			if (!$this->_isAvailableForImport($lengow_order))
				continue;
			// Check if order is already imported
			if (LengowOrder::isAlreadyImported($this->_lengow_order_id, $id_flux) && $this->single_order == false)
			{
				LengowCore::log('already imported in Prestashop with order ID '.LengowOrder::getOrderId($this->_lengow_order_id, $id_flux), $this->_lengow_order_id, self::$force_log_output);
				if (self::$debug)
				{
					$this->_lengow_order_id = null;
					continue;
				}
				$this->_updateOrder($marketplace, $lengow_order, $id_flux);
			}
			else
			{
				// Import only process order or shipped order and not imported with previous module
				$lengow_order_state = (string)$lengow_order->order_status->marketplace;
				$id_order_presta = (empty($lengow_order->order_external_id)) ? false : (string)$lengow_order->order_external_id;
				if (self::$debug == true || $this->single_order == true)
					$id_order_presta = false;

				if (($marketplace->getStateLengow($lengow_order_state) == 'processing' || $marketplace->getStateLengow($lengow_order_state) == 'shipped') && !$id_order_presta)
				{
					// Currency
					$id_currency = (int)Currency::getIdByIsoCode((string)$lengow_order->order_currency);
					if (!$id_currency)
					{
						LengowCore::log('no currency', $this->_lengow_order_id, self::$force_log_output);
						LengowCore::endProcessOrder($this->_lengow_order_id, 1, 0, 'No currency');
						continue;
					}
					// Customer
					$this->_customer = new LengowCustomer();
					if (!$this->_customer->getCustomerImport($lengow_order, $this->_lengow_order_id))
						continue;

					// Billing and shipping address
					$result = $this->_setAddresses($lengow_order, (string)$lengow_order->tracking_informations->tracking_relay);

					if (is_string($result) || !$result)
					{
						if (is_string($result))
						{
							LengowCore::log($result, $this->_lengow_order_id, self::$force_log_output);
							LengowCore::endProcessOrder($this->_lengow_order_id, 1, 0, Tools::ucfirst($result));
						}
						else
						{
							LengowCore::log('error while validating order', $this->_lengow_order_id, self::$force_log_output);
							LengowCore::endProcessOrder($this->_lengow_order_id, 1, 0, Tools::ucfirst('error while validating order'));
						}
						continue;
					}

					$carrier_infos = $this->getCarrier($lengow_order, $marketplace->name);
					$id_relay = (string)$lengow_order->tracking_informations->tracking_relay;
					
					// Building Cart
					if (_PS_VERSION_ < '1.5')
						$cart = Context::getContext()->cart;
					$cart = new LengowCart();
					$cart->buildCart($this->_billing_address->id, $this->_shipping_address->id, $this->_customer->id, $this->id_lang, $carrier_infos['id_carrier'], $id_currency);
					$continue = $cart->validateCart($this->_lengow_order_id);
					if ($continue)
						continue;

					Context::getContext()->cart = new Cart($cart->id);
					$shipping_price = 0;
					$lengow_products_order = $lengow_order->cart->products->product;

					if (!$cart->lengow_products = $this->_extractCartProducts($cart, $lengow_products_order, $marketplace))
					{
						$cart->delete();
						continue;
					}
					// Check product wharehouse
					if (!$this->_checkWareHouse($cart))
						continue;
					$cart->lengow_shipping = $shipping_price;
					$payment = new LengowPaymentModule();
					$payment->active = true;

					if ($marketplace->getStateLengow($lengow_order_state) == 'shipped')
						$id_status_import = LengowCore::getOrderState('shipped');
					else
						$id_status_import = LengowCore::getOrderState('process');

					$import_method_name = LengowCore::getImportMethodName();
					$order_amount_pay = (float)$lengow_order->order_amount;
					LengowCart::$current_order['products'] = $cart->lengow_products;
					LengowCart::$current_order['total_pay'] = $order_amount_pay;
					LengowCart::$current_order['shipping_price'] = (float)$lengow_order->order_shipping;
					LengowCart::$current_order['wrapping_price'] = (float)$lengow_order->order_processing_fee;
					if ($import_method_name == 'lengow')
						$method_name = 'Lengow';
					else
						$method_name = (string)$lengow_order->marketplace.((string)$lengow_order->order_payment->payment_type ? ' - '.(string)$lengow_order->order_payment->payment_type : '');

					$message = 'Import Lengow | '
							.'ID order : '.(string)$lengow_order->order_id.' | '."\r\n"
							.'Marketplace : '.(string)$lengow_order->marketplace.' | '."\r\n"
							.'ID flux : '.(integer)$lengow_order->idFlux.' | '."\r\n"
							.'Total paid : '.(float)$lengow_order->order_amount.' | '."\r\n"
							.'Shipping : '.(string)$lengow_order->order_shipping.' | '."\r\n"
							.'Message : '.(string)$lengow_order->order_comments."\r\n";
					LengowCore::disableMail();
					// HACK force flush
					if (_PS_VERSION_ >= '1.5')
					{
						$this->context->customer = new Customer($this->context->cart->id_customer);
						$this->context->language = new Language($this->context->cart->id_lang);
						$this->context->shop = new Shop($this->context->cart->id_shop);
						$id_currency = (int)$this->context->cart->id_currency;
						$this->context->currency = new Currency($id_currency, null, $this->context->shop->id);
						Context::getContext()->cart->getDeliveryOptionList(null, true);
						Context::getContext()->cart->getPackageList(true);
						Context::getContext()->cart->getDeliveryOption(null, false, false);
					}
					$lengow_total_pay = (float)Tools::ps_round((float)$this->context->cart->getOrderTotal(true, Cart::BOTH, null, null, false), 2);

					// Validate Order
					if (!$this->_lengow_new_order)
					{
						LengowCore::log('No new order to import', null, self::$force_log_output);
						LengowCore::endProcessOrder(!$this->_lengow_order_id, 1, 0, 'No new order to import');
						if (Validate::isLoadedObject($cart))
							$cart->delete();
					}
					else
					{
						try {
							$payment_validate = $payment->validateOrder($cart->id, $id_status_import, $lengow_total_pay, $method_name, $message, array(), null, true);
						} catch (Exception $e) {
							LengowCore::log('error validate order ('.$e->getMessage().')', $this->_lengow_order_id, self::$force_log_output);
							LengowCore::endProcessOrder($this->_lengow_order_id, 1, 0, 'Order '.$this->_lengow_order_id.' : Error validate order ('.$e->getMessage().')');
							continue;
						}
					}

					// Check payment
					if ($payment_validate)
					{
						$id_order = $payment->currentOrder;
						$id_flux = (integer)$lengow_order->idFlux;
						$marketplace = (string)$lengow_order->marketplace;
						$message = (string)$lengow_order->order_comments;
						$total_paid = (float)$lengow_order->order_amount;
						$carrier = (string)$lengow_order->order_shipping;
						$tracking = (string)$lengow_order->tracking_informations->tracking_number;
						$extra = Tools::jsonEncode($lengow_order);
						LengowOrder::addLengow($id_order, $this->_lengow_order_id, $id_flux, $marketplace, $message, $total_paid, $carrier, $tracking, $extra);
						$new_lengow_order = new LengowOrder($id_order);

						// Force price
						if (Configuration::get('LENGOW_FORCE_PRICE'))
						{
							$current_order = LengowCart::$current_order;
							if ($new_lengow_order->total_paid != $order_amount_pay)
								$new_lengow_order->rebuildOrder($current_order['products'], $current_order['total_pay'], $current_order['shipping_price'], $current_order['wrapping_price']);
						}

						if (isset($carrier_infos['module']))
						{
							switch ($carrier_infos['module'])
							{
								case 'socolissimo':
								$shipping_address_complement = ($lengow_order->delivery_address->delivery_address_complement != '' ? pSQL($lengow_order->delivery_address->delivery_address_complement) : '');
								$shipping_society = ($lengow_order->delivery_address->delivery_country->delivery_society != '' ? pSQL($lengow_order->delivery_address->delivery_country->delivery_society) : '');
								$this->addColissimoAddress($cart->id, $this->_customer->id,
										$this->_shipping_address->lastname,
										$this->_shipping_address->firstname,
										$shipping_address_complement,
										$this->_shipping_address->address1,
										$this->_shipping_address->address2,
										$this->_shipping_address->postcode,
										$this->_shipping_address->city,
										$this->_shipping_address->phone_mobile,
										$this->_customer->email,
										$shipping_society,
										$id_relay
										);								
								break;
								case 'mondialrelay':
									$relay = self::getRelayPoint($id_relay, $cart->id_address_delivery);
									if ($relay !== false)
										$new_lengow_order->addRelayPoint($relay);
									else
										LengowCore::log('Unable to find Relay Point', $this->_lengow_order_id, self::$force_log_output);
									break;
								default:
									# code...
									break;
							}
							
						}
						// Force carrier
						if (_PS_VERSION_ >= '1.5')
						{
							$order_carrier = new OrderCarrier($new_lengow_order->getIdOrderCarrier());
							if ($order_carrier->id_carrier != $carrier_infos['id_carrier'])
								$new_lengow_order->forceCarrier($carrier_infos['id_carrier']);
						}
		
						// Update status on lengow if no debug
						if (self::$debug == false)
						{
							$lengow_connector = new LengowConnector((integer)LengowCore::getIdCustomer(), LengowCore::getTokenCustomer());
							$orders = $lengow_connector->api('updatePrestaInternalOrderId', array('idClient' => LengowCore::getIdCustomer(),
								'idFlux' => $id_flux,
								'idGroup' => LengowCore::getGroupCustomer(),
								'idCommandeMP' => $new_lengow_order->lengow_id_order,
								'idCommandePresta' => $new_lengow_order->id));
						}
						LengowCore::log('success import on presta (ORDER '.$id_order.')', $this->_lengow_order_id, 1);
						LengowCore::endProcessOrder($this->_lengow_order_id, 0, 1, 'Order imported on presta (ORDER '.$id_order.')');
						// Custom Hook
						if (_PS_VERSION_ >= '1.5')
						{
							Hook::exec('actionValidateLengowOrder', array(
								'id_order' => $id_order,
								'lengow_order_id' => $this->_lengow_order_id
							));
						}
						$this->count_orders_added++;
						if (Configuration::get('LENGOW_IMPORT_SINGLE'))
						{
							LengowCore::setImportEnd();
							die();
						}
						elseif ((Tools::getValue('limit') != '' && Tools::getValue('limit') > 0))
						{
							if ($this->count_orders_added == (int)Tools::getValue('limit'))
							{
								LengowCore::setImportEnd();
								die();
							}
						}
					}
					else
					{
						LengowCore::log('fail import on presta', $this->_lengow_order_id, self::$force_log_output);
						LengowCore::endProcessOrder($this->_lengow_order_id, 1, 0, 'Fail import on presta');
						if (Validate::isLoadedObject($cart))
							$cart->delete();
					}
				}
				else
				{
					if ($id_order_presta)
					{
						LengowCore::log('already imported in Prestashop with order ID '.$id_order_presta, $this->_lengow_order_id, self::$force_log_output);
						LengowCore::endProcessOrder($this->_lengow_order_id, 0, 1, 'Already imported in Prestashop with order ID '.$id_order_presta);
					}
					else
					{
						LengowCore::log('order\'s status not available to import', $this->_lengow_order_id, self::$force_log_output);
						LengowCore::deleteProcessOrder($this->_lengow_order_id);
					}
				}
			}
			unset($payment);
			unset($cart);
		}
	}

	protected function _updateOrder($marketplace, $lengow_order, $id_flux)
	{
		$id_state_lengow = LengowCore::getOrderState($marketplace->getStateLengow((string)$lengow_order->order_status->marketplace));

		$order = LengowOrder::getByOrderIDFlux($this->_lengow_order_id, $id_flux);
		// Update status' order only if in process or shipped
		if ($order->current_state != $id_state_lengow)
		{
			// Change state process to shipped
			if ($order->current_state == LengowCore::getOrderState('process') && $marketplace->getStateLengow((string)$lengow_order->order_status->marketplace) == 'shipped')
			{
				$history = new OrderHistory();
				$history->id_order = $order->id;
				$history->changeIdOrderState(LengowCore::getOrderState('shipped'), $order, true);
				try {
					if (!$error = $history->validateFields(false, true))
						throw new Exception($error);
					$history->add();
				} catch (Exception $e) {
					LengowCore::log('error while adding history : '.$e->getMessage(), $this->_lengow_order_id, self::$force_log_output);
				}
				$tracking_number = (string)$lengow_order->tracking_informations->tracking_number;
				if ($tracking_number)
				{
					$order->shipping_number = $tracking_number;
					try {
						if (!$error = $order->validateFields(false, true))
							throw new Exception($error);
						$order->update();
					} catch (Exception $e) {
						LengowCore::log('error while updating state to shipped : '.$e->getMessage(), $this->_lengow_order_id, self::$force_log_output);
						$this->_lengow_order_id = null;
						continue;
					}
				}
				LengowCore::log('state updated to shipped', $this->_lengow_order_id, self::$force_log_output);
				$this->count_orders_updated++;
				//LengowCore::enableMail();
			}
			// Change state process or shipped to cancel
			else if (($order->current_state == LengowCore::getOrderState('process') || $order->current_state == LengowCore::getOrderState('shipped'))
						&& $marketplace->getStateLengow((string)$lengow_order->order_status->marketplace) == 'canceled')
				{
				$history = new OrderHistory();
				$history->id_order = $order->id;
				$history->changeIdOrderState(LengowCore::getOrderState('cancel'), $order, true);
				try {
					if (!$error = $history->validateFields(false, true))
						throw new Exception($error);
					$history->add();
				} catch (Exception $e) {
					LengowCore::log('error while updating state to cancel', $this->_lengow_order_id, self::$force_log_output);
					$this->_lengow_order_id = null;
					continue;
				}
				LengowCore::log('state updated to cancel', $this->_lengow_order_id, self::$force_log_output);
				$this->count_orders_updated++;
			}
			LengowCore::endProcessOrder($this->_lengow_order_id, 0, 1);
		}
		else
			LengowCore::endProcessOrder($this->_lengow_order_id, 0, 1, 'Already imported in Prestashop with order ID '.LengowOrder::getOrderId($this->_lengow_order_id, $id_flux));


	}


	/**
	 * Makes the Orders API Url.
	 *
	 * @param array $args The arguments to request at the API
	 */
	protected function _importOrders($args = array())
	{
		$this->_setDebug();
		LengowCore::setImportProcessing();
		LengowCore::disableSendState();
		self::$import_start = true;
		$lengow_connector = new LengowConnector((integer)LengowCore::getIdCustomer(), LengowCore::getTokenCustomer());

		$args_order = $this->_setArgsOrder($args);

		if (!$orders = $this->_getImportOrders($lengow_connector, $args_order, $args))
			return false;
		$this->_processOrders($orders, $lengow_connector);

		self::$import_start = false;
		LengowCore::setImportEnd();

		return array('new' => $this->count_orders_added,
			'update' => $this->count_orders_updated);
	}

	/**
	 * Retrieve the order information from the import
	 * @param type $lengow_order
	 * @return type
	 */
	protected static function _getOrderInformation($lengow_order)
	{
		return array(
			'marketplace' => (string)$lengow_order->marketplace,
			'payment_type' => (string)$lengow_order->order_payment->payment_type,
			'order_amount' => (float)$lengow_order->order_amount,
			'order_shipping' => (float)$lengow_order->oder_shipping,
			'order_processing_fee' => (float)$lengow_order->order_processing_fee,
			'order_id' => (string)$lengow_order->order_id,
			'id_flux' => (integer)$lengow_order->id_flux,
			'order_comment' => (string)$lengow_order->order_comments,
			'delivery_address_complement' => (string)$lengow_order->delivery_address->develivery_address_complement,
			'delivery_society' => (string)$lengow_order->delivery_address->delivery_society,
		);
	}

	/**
	 * Check if a product is available in one or several warehouses
	 * @return boolean
	 */
	protected function _checkWareHouse($cart)
	{
		if (_PS_VERSION_ >= '1.5' && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') == 1)
		{
			foreach ($cart->getPackageList() as $value)
			{
				if (count($value) > 1)
				{
					LengowCore::log('products are stocked in different warehouse', $this->_lengow_order_id, self::$force_log_output);
					LengowCore::endProcessOrder($this->_lengow_order_id, 1, 0, 'Order '.$this->_lengow_order_id.' : Products are stocked in diferent warehouse.');
					return false;
				}
			}
		}
		return true;
	}


	/**
	* Convert xml to array
	*
	* @param $xml_object object The SimpleXML Object
	* @param $out array The output array
	*
	* @return array
	*/
	public function toArray($xml_object, $out = array())
	{
		foreach ((array)$xml_object as $index => $node)
			$out[$index] = (is_object($node)) ? $this->toArray($node) : $node;
		return $out;
	}

	/**
	* Save delivery address into socolissimo delivery table
	* Fix for old version of Socolissimo module
	*
	* @return boolean
	*/
	protected function addColissimoAddress($cart_id, $id_customer, $lastname, $firstname, $complement, $address1, $address2, $postcode, $city, $phone_mobile, $email, $society, $id_relay, $dvmode = 'A2P')
	{
		$sql = 'INSERT INTO '._DB_PREFIX_.'socolissimo_delivery_info (
					`id_cart`, `id_customer`, `delivery_mode`, `prid`, `prname`, `prfirstname`, `prcompladress`,
					`pradress1`, `pradress2`, `pradress3`, `pradress4`, `przipcode`, `prtown`, `cephonenumber`, `ceemail` ,
					`cecompanyname`, `cedeliveryinformation`, `cedoorcode1`, `cedoorcode2`)
				VALUES ('.$cart_id.', '.$id_customer.',
				\''.pSQL($dvmode).'\',
				\''.pSQL($id_relay).'\',
				\''.pSQL($lastname).'\',
				\''.pSQL($firstname).'\',
				\''.pSQL($complement).'\',
				\''.pSQL($address1).'\',
				\''.pSQL($address2).'\',
				\''.pSQL($address1).'\',
				\''.pSQL($address2).'\',
				\''.pSQL($postcode).'\',
				\''.pSQL($city).'\',
				\''.pSQL(LengowCore::cleanPhone($phone_mobile)).'\',
				\''.pSQL($email).'\',
				\''.pSQL($society).'\',
				\'\',
				\'\',
				\'\')';


		if (Db::getInstance()->execute($sql))
			return true;

		return false;
	}

	/**
	* Get RelayPoint info with Mondial Relay module
	*
	* @param string Id Tracking Relay
	* @param int Id Address Delivery
	*
	* @return boolean|array False if not found, Relay array
	*/
	public static function getRelayPoint($tracking_relay, $id_address_delivery)
	{
		$sep = DIRECTORY_SEPARATOR;
		require_once _PS_MODULE_DIR_.'mondialrelay'.$sep.'classes'.$sep.'MRRelayDetail.php';
		$tracking_relay = str_pad($tracking_relay, 6, '0', STR_PAD_LEFT);
		$params = array(
			'relayPointNumList' => array($tracking_relay),
			'id_address_delivery' => $id_address_delivery
		);
		$MRRelayDetail = new MRRelayDetail($params, new MondialRelay());
		$MRRelayDetail->init();
		$MRRelayDetail->send();

		$result = $MRRelayDetail->getResult();

		if (empty($result['error'][0]) && array_key_exists($tracking_relay, $result['success']))
		{
			$relay = $result['success'][$tracking_relay];
			return $relay;
		}
		else
			return false;
	}

	/**
	 * Extract the products of a cart from an order
	 * @param type $cart
	 * @param type $lengow_products_order
	 * @return boolean
	 */
	protected function _extractCartProducts($cart, $lengow_products_order, $marketplace)
	{
		$lengow_products = array();
		foreach ($lengow_products_order as $lengow_product)
		{
			$productElements = self::_getProductImport($lengow_product);
			if (!empty($productElements['status']))
			{
				if ($marketplace->getStateLengow((string)$productElements['status']) == 'canceled')
					return false;
			}
			$result = self::_searchProduct($productElements, $this->_lengow_order_id);
			if (is_string($result))
			{
				LengowCore::log($result, $this->_lengow_order_id, self::$force_log_output);
				LengowCore::endProcessOrder($this->_lengow_order_id, 1, 0, $result);
				return false;
			}

			$product_ids = $result;

			$product = new LengowProduct($product_ids['id_product'], $this->id_lang);
			$product_ids['id_product_attribute'] = isset($product_ids['id_product_attribute']) ? $product_ids['id_product_attribute'] : 0;

			$product_sku = $product_ids['id_product'].($product_ids['id_product_attribute'] > 0 ? '_'.$product_ids['id_product_attribute'] : '');
			$error_message = self::_checkAvailability($product, $product_sku);
			if ($error_message)
			{
				LengowCore::log($error_message, $this->_lengow_order_id, self::$force_log_output);
				LengowCore::endProcessOrder($this->_lengow_order_id, 1, 0, $error_message);
				return false;
			}
			$lengow_products[$product_sku] = array(
				'id' => $product_sku,
				'qty' => $productElements['quantity'],
				'price' => $productElements['price_unit'],
				'name' => $productElements['title'],
				'shipping' => 0,
				'fees' => 0,
			);
			if (_PS_VERSION_ < '1.5')
				$product_taxes = Tax::getProductTaxRate($product->id, $this->_shipping_address->id);
			else
				$product_taxes = $product->getTaxesRate($this->_shipping_address);
			$lengow_products[$product_sku]['tax_rate'] = $product_taxes;
			$lengow_products[$product_sku]['id_tax'] = isset($product->id_tax) ? $product->id_tax : false;
			$lengow_products[$product_sku]['id_product'] = $product->id;
			$lengow_products[$product_sku]['id_address_delivery'] = $this->_shipping_address->id;

			if ($product_ids['id_product_attribute'] > 0)
				$lengow_products[$product_sku]['id_product_attribute'] = $product_ids['id_product_attribute'];

			$error_msg = $this->_updateCartQuantity($cart, $product_ids, $productElements['quantity'], $this->_shipping_address);
			if ($error_msg)
			{
				LengowCore::log($error_msg, $this->_lengow_order_id, self::$force_log_output);
				LengowCore::endProcessOrder($this->_lengow_order_id, 1, 0, $error_msg);
				return false;
			}
			$this->_total_saleable_quantity += (integer)$lengow_product->quantity;
			$this->_lengow_new_order = true;
		}
		return $lengow_products;
	}

	/**
	 * Check if the order has already been processed or imported
	 * @param type $id_flux
	 * @return boolean
	 */
	protected function _isProccessing()
	{
		if (LengowCore::isProcessing($this->_lengow_order_id) && self::$debug != true && $this->single_order == false)
		{
				$msg = LengowCore::getOrgerLog($this->_lengow_order_id);
				if ($msg != '')
					LengowCore::log($msg, $this->_lengow_order_id, self::$force_log_output);
				else
					LengowCore::log('order is flagged as processing, ignore it', $this->_lengow_order_id, self::$force_log_output);
			return true;
		}
	}

	/**
	 * Check if the given order is sent by the marketplace
	 * @param type $lengow_order
	 * @param type $lengow_order_id
	 * @return boolean
	 */
	protected function _isSentByMarketPlace($lengow_order)
	{
		if ((int)$lengow_order->tracking_informations->tracking_deliveringByMarketPlace == 1 && !array_key_exists((string)$lengow_order->marketplace, Configuration::get('LENGOW_IMPORT_MARKETPLACES')))
		{
			LengowCore::log('shipped by '.(string)$lengow_order->marketplace, $this->_lengow_order_id, self::$force_log_output);
			LengowCore::endProcessOrder($this->_lengow_order_id, 0, 1, 'Shipped by '.(string)$lengow_order->marketplace);
			return true;
		}
	}

	/**
	 * Return all product elements from import
	 * @param type $lengow_product
	 * @return type
	 */
	protected static function _getProductImport($lengow_product)
	{
		$elements = array(
			'idLengow' => null,
			'idMP' => null,
			'sku' => array(
				'field' => null,
				'value' => null,
			),
			'ean' => null,
			'title' => null,
			'category' => null,
			'brand' => null,
			'url_produit' => null,
			'url_image' => null,
			'order_lineid' => null,
			'quantity' => null,
			'price' => null,
			'price_unit' => null,
			'shipping_price' => null,
			'tax' => null,
			'status' => null,
		);
		return self::_getProductElements($elements, $lengow_product);
	}

	/**
	 * Retrieve elements concerning the current product from the import
	 * @param type $elements
	 * @param type $lengow_product
	 * @return type
	 */
	protected static function _getProductElements($elements, $lengow_product)
	{
		foreach ($elements as $key => $value)
		{
			if (is_array($value))
			{
				$keys = array_keys($value);
				foreach ($keys as $subkey)
				{
					if ($key == 'sku')
					{
						switch ($subkey)
						{
							case 'field' :
								$result = Tools::strtolower((string)$lengow_product->sku[$subkey][0]);
								if ($result == 'identifiant_unique')
									$result = 'id_product';
								$value[$subkey] = $result;

								break;
							case 'value' :
								$value[$subkey] = (string)$lengow_product->$key;
								break;
						}
					}
				}
				$elements[$key] = $value;
			}
			else
			{
				switch ('key')
				{
					case ('idLengow') :
						$elements[$key] = (int)$lengow_product->$key;
						break;
					case('title') :
					case ('status') :
						$elements[$key] = (string)$lengow_product->$key;
						break;
					case('quantity') :
						$elements[$key] = (int)$lengow_product->$key;
						break;
					case('price') :
					case('price_unit');
						$elements[$key] = (float)$lengow_product->$key;
						break;
					default :
						$elements[$key] = (string)$lengow_product->$key;
						break;
				}
			}
		}
		return $elements;
	}

	/**
	 * Search a product in the different fields of the import
	 * @param type $productElements
	 * @param type $lengow_order_id
	 * @return type
	 */
	protected static function _searchProduct($productElements, $lengow_order_id)
	{
		$sku = (string)$productElements['sku']['value'];
		$product_field = $productElements['sku']['field'];
		$ean = (string)$productElements['ean'];
		$idMP = (string)$productElements['idMP'];
		$idLengow = (string)$productElements['idLengow'];
		$args = array($sku, $ean, $idMP, $idLengow);

		$found_product_ids = LengowProduct::matchProduct($sku, $product_field);
		$id_product_exists = false;
		$found = false;
		$product = null;
		if (LengowProduct::checkProductId($found_product_ids['id_product'], $args))
		{
			$id_product_exists = true;
			$product = new LengowProduct($found_product_ids['id_product']);
			$found = true;
			if (isset($found_product_ids['id_product_attribute']))
			{

				if (!LengowProduct::checkProductAttributeId($product, $found_product_ids['id_product_attribute']))
				{
					$found = false;
					unset($product);
				}
			}
		}
		if ($found)
			LengowCore::log('product sku '.$found_product_ids['id_product']
							.(isset($found_product_ids['id_product_attribute']) ? '_'.$found_product_ids['id_product_attribute'] : '')
							.' found', $lengow_order_id);
		else // if the product ids are not in the sku, advanced search
		{
			LengowCore::log('product sku '.$found_product_ids['id_product']
					.(isset($found_product_ids['id_product_attribute']) ? '_'.$found_product_ids['id_product_attribute'] : '')
					.' not found.Searching further...', $lengow_order_id);
			$args = array($sku, $ean, $idMP, $idLengow);
			$first_found_ids = $found_product_ids;
			$found_product_ids = LengowProduct::advancedSearch($args);
			if (!$found_product_ids) // if no id was found in the advanced search
			{
				if (!$id_product_exists)
					return 'unable to find product '.$first_found_ids['id_product'];
				return 'unable to find combination '.$first_found_ids['id_product_attribute'].' for product '.$first_found_ids['id_product'];
			}
			if (!LengowProduct::checkProductId($found_product_ids['id_product'], $args))
				return 'unable to find product '.$first_found_ids['id_product'];
			elseif (isset($found_product_ids['id_product_attribute']))
			{
				if ($found_product_ids['id_product_attribute'] != 0)
				{
					$product = new LengowProduct($found_product_ids['id_product']);
					if (!LengowProduct::checkProductAttributeId($product, $found_product_ids['id_product_attribute']))
						return 'unable to find combination '.$found_product_ids['id_product_attribute'].' for product '.$found_product_ids['id_product'];
				}
			}
			LengowCore::log('product '.$found_product_ids['id_product']
					.(isset($found_product_ids['id_product_attribute']) ? '_'.$found_product_ids['id_product_attribute'] : '')
					.' found', $lengow_order_id);
		}
		return $found_product_ids;
	}

	/**
	 * Check whether a product is active and available or not
	 * @param type $product
	 * @return type String
	 */
	protected static function _checkAvailability($product, $product_sku)
	{
		if (!Configuration::get('LENGOW_IMPORT_FORCE_PRODUCT'))
		{
			if (!$product->active)
				return 'product '.$product_sku.' is disabled in your back office';

			if (!$product->available_for_order)
				return 'product '.$product_sku.' is not available for order';
		}
		$product_ids = array();
		$ids = explode('_', $product_sku);
		$product_ids['product_id'] = $ids[0];
		if (count($ids) > 1 & !empty($ids[1]))
			$product_ids['id_attribute_product'] = $ids[1];

		if (count($product->getCombinations()) > 0 && !isset($product_ids['id_attribute_product']))
			return 'product '.$product_sku.' is a parent product : variation needed';
	}

	/**
	 * Redirect to other functions that update or insert a product and its quantin database
	 * @param type $cart
	 * @param type $product_ids
	 * @param type $product_quantity
	 * @return type
	 */
	protected function _updateCartQuantity($cart, $product_ids, $product_quantity)
	{
		if (Configuration::get('LENGOW_IMPORT_FORCE_PRODUCT') == true)
		{
			$id_product_complete = $product_ids['id_product'].(isset($product_ids['id_product_attribute']) ? '_'.$product_ids['id_product_attribute'] : '');
			// Force disabled or out of stock product
			$cart_current_quantity = $cart->containsProduct($product_ids['id_product'], $product_ids['id_product_attribute'], null);
			if ($cart_current_quantity != false)
				$result_update = self::_updateCartProduct($product_quantity, $product_ids, $cart_current_quantity, $cart, $this->_shipping_address->id);
			else
				$result_add = self::_addCartProduct($product_ids, $cart->id, $this->_shipping_address->id, $product_quantity, $this->context->shop->id);

			if ((isset($result_add) && $result_add === false) || (isset($result_update) && $result_update === false))
				return 'Error when adding product ['.$id_product_complete.'] on cart';
		}
		else // Basic functionnality
			return self::_basicUpdate($cart, $product_quantity, $product_ids);
	}

	/**
	 * Updates the quantity of a product in database
	 * @param type $cart
	 * @param type $product_quantity
	 * @param type $product_ids
	 * @return string
	 */
	protected static function _basicUpdate($cart, $product_quantity, $product_ids)
	{
		$id_product_complete = $product_ids['id_product'].(isset($product_ids['id_product_attribute']) ? '_'.$product_ids['id_product_attribute'] : '');
		$id_attribute_product = null;

		if (isset($product_ids['id_product_attribute']))
			$id_attribute_product = $product_ids['id_product_attribute'];

		$update_quantity = $cart->updateQty($product_quantity, $product_ids['id_product'], $id_attribute_product);
		if (!$update_quantity || $update_quantity < 0)
		{
			if ($update_quantity < 0)
				$msg = 'product cart ['.$id_product_complete.'] quantity is under minimal quantity';
			else
			{
				if (!$id_attribute_product)
					$id_attribute_product = 0;
				$msg = 'product cart ['.$id_product_complete.'] not enough quantity ('
						.$product_quantity.' ordering, '
						.LengowProduct::getRealQuantity($product_ids['id_product'], $id_attribute_product)
						.' in stock)';
			}
			return $msg;
		}
	}

	/**
	 * Inserts into the cart_product table products of a given cart
	 * @param type $product_ids
	 * @param type $cart_id
	 * @param type $shipping_address_id
	 * @param type $product_quantity
	 * @param type $id_shop
	 * @return type
	 */
	protected static function _addCartProduct($product_ids, $cart_id, $shipping_address_id, $product_quantity, $id_shop)
	{
		if (_PS_VERSION_ >= '1.5')
		{
			$result_add = Db::getInstance()->insert('cart_product', array(
				'id_product' => (int)$product_ids['id_product'],
				'id_product_attribute' => (int)$product_ids['id_product_attribute'],
				'id_cart' => (int)$cart_id,
				'id_address_delivery' => (int)$shipping_address_id,
				'id_shop' => $id_shop,
				'quantity' => (int)$product_quantity,
				'date_add' => date('Y-m-d H:i:s'),
			));
		}
		else
		{
			$result_add = Db::getInstance()->autoExecute(_DB_PREFIX_.'cart_product', array(
				'id_product' => (int)$product_ids['id_product'],
				'id_product_attribute' => (int)$product_ids['id_product_attribute'],
				'id_cart' => (int)$cart_id,
				'quantity' => (int)$product_quantity,
				'date_add' => date('Y-m-d H:i:s')
					), 'INSERT');
		}
		return $result_add;
	}

	/**
	 * Updates the cart_product table
	 * @param type $product_quantity
	 * @param type $product_ids
	 * @param type $cart_current_quantity
	 * @param type $cart_id
	 * @param type $shipping_address_id
	 * @return type
	 */
	protected static function _updateCartProduct($product_quantity, $product_ids, $cart_current_quantity, $cart, $shipping_address_id)
	{
		if (_PS_VERSION_ >= '1.5')
			$result_update = Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'cart_product` '
										.'SET `quantity` = `quantity` + '.(int)$product_quantity.', `date_add` = NOW() '
										.'WHERE `id_product` = '.(int)$product_ids['id_product']
										.(!empty($product_ids['id_product_attribute']) ? ' AND `id_product_attribute` = '.(int)$product_ids['id_product_attribute'].' ' : ' ')
										.'AND `id_cart` = '
										.(int)$cart->id.(Configuration::get('PS_ALLOW_MULTISHIPPING') && $cart->isMultiAddressDelivery() ? ' AND `id_address_delivery` = '.(int)$shipping_address_id : ' ')
										.'LIMIT 1'
			);
		else
		{
			$result_update = Db::getInstance()->autoExecute(_DB_PREFIX_.'cart_product', array(
				'quantity' => (int)$cart_current_quantity['quantity'] + (int)$product_quantity,
				'date_add' => date('Y-m-d H:i:s')
					), 'UPDATE', '`id_product` = '.(int)$product_ids['id_product'].' AND `id_cart` = '.(int)$cart->id.' AND `id_product_attribute` = '.(int)$product_ids['id_product_attribute'], 1);
		}
		return $result_update;
	}

	/**
	 * Check wether an order can be imported or not
	 * @param type $lengow_order
	 * @return boolean
	 */
	protected function _isAvailableForImport($lengow_order)
	{
		if ((string)$lengow_order->order_status->marketplace == '')
		{
			LengowCore::log('no order\'s status', $this->_lengow_order_id, self::$force_log_output);
			LengowCore::endProcessOrder($this->_lengow_order_id, 0, 1, 'No order\'s status');
			return false;
		}
		if ((string)$lengow_order->tracking_informations->tracking_deliveringByMarketPlace == '')
		{
			LengowCore::log('delivery by the marketplace ('.$lengow_order->marketplace.')', $this->_lengow_order_id, self::$force_log_output);
			LengowCore::endProcessOrder($this->_lengow_order_id, 0, 1, 'Delivery by the marketplace ('.$lengow_order->marketplace.')');
			return false;
		}
		return true;
	}

	/**
	 * Check wether an order can be imported or not
	 * @param type $lengow_order
	 * @return boolean
	 */
	protected function _setAddresses($lengow_order, $id_relay)
	{
		$billing_names = LengowCustomer::checkCustomerNames(array(
												'firstname' => $lengow_order->billing_address->billing_firstname,
												'lastname' => $lengow_order->billing_address->billing_lastname,
												)
										);
		$billing_address_firstname = $billing_names['firstname'];
		$billing_address_lastname = $billing_names['lastname'];



		$this->_billing_address = LengowAddress::getByHash((string)$lengow_order->billing_address->billing_full_address);
		if ($this->_billing_address)
			$different_billing_names = !($billing_address_firstname == $this->_billing_address->firstname &&
											$billing_address_lastname == $this->_billing_address->lastname) ||
										!($billing_address_lastname == $this->_billing_address->firstname &&
										$billing_address_firstname == $this->_billing_address->lastname);

		if (!$this->_billing_address || $different_billing_names)
		{
			$this->_billing_address = new LengowAddress();
			$error = $this->_billing_address->getAddress('billing', $lengow_order, $this->_customer->id, $this->_lengow_order_id);
			if ($error)
				return $error;
			if (!$this->_billing_address->validateAddress('billing', $this->_lengow_order_id))
				return false;
		}

		$shipping_names = LengowCustomer::checkCustomerNames(array(
												'firstname' => $lengow_order->delivery_address->delivery_firstname,
												'lastname' => $lengow_order->delivery_address->delivery_lastname,
												)
										);
		$shipping_address_firstname = $shipping_names['firstname'];
		$shipping_address_lastname = $shipping_names['lastname'];

		$full_billing_address = (string)$this->_billing_address->firstname.' '
					.$this->_billing_address->lastname.' '
					.$this->_billing_address->address1.' '
					.(empty($this->_billing_address->address2) ? '' : $this->_billing_address->address2.' ')
					.$this->_billing_address->postcode.' '
					.$this->_billing_address->country;

		$full_shipping_address = $shipping_address_firstname.' '
				.$shipping_address_lastname.' '
				.(string)$lengow_order->delivery_address->delivery_full_address;

		if (Tools::strtolower($full_billing_address) != Tools::strtolower($full_shipping_address))
		{
				$this->_shipping_address = new LengowAddress();
				$error = $this->_shipping_address->getAddress('delivery', $lengow_order, $this->_customer->id, $this->_lengow_order_id);
				if (!empty($id_relay))
					$this->_shipping_address->address2 .= ' - Relay '.$id_relay;
				if (!empty($this->_billing_address->phone) && empty($this->_shipping_address->phone))
					$this->_shipping_address->phone = $this->_billing_address->phone;
				if (!empty($this->_billing_address->phone_mobile) && empty($this->_shipping_address->phone_mobile))
					$this->_shipping_address->phone_mobile = $this->_billing_address->phone_mobile;
				if ($error)
					return $error;
				if (!$this->_shipping_address->validateAddress('shipping', $this->_lengow_order_id))
					return false;
		}
		else
		{
			if ($this->_billing_address->id)
				$this->_shipping_address = new LengowAddress($this->_billing_address->id);
			else
				return 'error while validating billing address';
		}

		if (empty($this->_billing_address->phone) && !empty($this->_shipping_address->phone))
		{
			$this->_billing_address->phone = $this->_shipping_address->phone;
			$error = $this->_billing_address->save();
			if (!$error)
				return false;
		}
		return true;
	}


	public function getCarrier($lengow_order, $marketplace_name)
	{
		$carrier = Tools::strtolower((string)$lengow_order->tracking_informations->tracking_carrier);
		$id_relay = (string)$lengow_order->tracking_informations->tracking_relay;
		if (!empty($id_relay))
		{
			if ($marketplace_name == 'cdiscount')
			{
				$tracking_method = (string)$lengow_order->tracking_informations->tracking_method;
				switch ($tracking_method)
				{
					case 'SO1':
						if (LengowCore::isColissimoInstalled())
						{
							$id_carrier = LengowCarrier::getIdByModuleName('socolissimo', $this->id_lang);
							$module = 'socolissimo';
						}					
						break;
					case 'REL' :
						if (LengowCore::isMondialRelayInstalled())
						{
							$id_carrier = LengowCarrier::getIdByModuleName('mondialrelay', $this->id_lang);
							$module = 'mondialrelay';
						}
						else
							LengowCore::log('Warning : MondialRelay module is not installed or need to be activated.', $this->_lengow_order_id);
						break;
				}
			}
			else
			{
				switch ($carrier)
				{
					case 'laposte':
						if (LengowCore::isColissimoInstalled())
						{
							$id_carrier = LengowCarrier::getIdByModuleName('socolissimo', $this->id_lang);
							$module = 'socolissimo';
						}
						else
							LengowCore::log('Warning : SoColissimo module is not installed or need to be activated.', $this->_lengow_order_id);
						break;
					case 'mondial relay':
						if (LengowCore::isMondialRelayInstalled())
						{
							$id_carrier = LengowCarrier::getIdByModuleName('mondialrelay', $this->id_lang);
							$module = 'mondialrelay';

						}
						else
							LengowCore::log('Warning : MondialRelay module is not installed or need to be activated.', $this->_lengow_order_id);
						break;
					default:
						# code...
						break;
				}
			}
		}
		if (!isset($id_carrier) || !$id_carrier)
		{
			if (Configuration::get('LENGOW_MP_SHIPPING_METHOD'))
				$id_carrier = LengowCarrier::matchMpCarrier($carrier, $this->id_lang);
			if (!isset($id_carrier) || !$id_carrier)
				$id_carrier = LengowCore::getDefaultCarrier();
		}

		$carrier_infos = array('id_carrier' => $id_carrier);
		if (isset($module))
			$carrier_infos['module'] = $module;

		return $carrier_infos;
	}
}
