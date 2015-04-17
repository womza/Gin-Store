<?php
/**
* 2014 Jorge Vargas
*
* NOTICE OF LICENSE
*
* This source file is subject to the End User License Agreement (EULA)
*
* See attachmente file LICENSE
*
* @author    Jorge Vargas <jorgevargaslarrota@hotmail.com>
* @copyright 2007-2014 Jorge Vargas
* @license   End User License Agreement (EULA)
* @package   sociallogin
* @version   1.0
*/

if (!defined('_PS_VERSION_'))
	exit;

require_once ('SocialLoginModel.php');

class CustomerClass extends Module
{
	public $first_name = '';
	public $last_name = '';
	public $email = '';
	public $gender = 0;
	public $id_user = null;
	public $network = '';

	private $id_customer = null;

	public function __construct()
	{
		$this->context = Context::getContext();
		$this->module = new SocialLogin();
	}

	/**
	*
	* @return strval 'error' log user error, 'complete' login and connected, 'pending' customer to register
	*/
	public function userLog()
	{
		if (!$this->network || !$this->id_user)
			return false;

		if (isset($this->context->customer) && $this->context->customer->isLogged())
		{
			$customer = $this->context->customer;
			$this->id_customer = (int)$this->context->customer->id;
			$is_logged = true;
		}
		else
		{
			// If social account is connected to a customer id
			$this->id_customer = (int)SocialLoginModel::getCustomerByUserCode($this->id_user, $this->network);
			$customer = new Customer($this->id_customer);

			if (!Validate::isLoadedObject($customer))
			{
				// Prevent deleted customers in older versions
				if (Validate::isNullOrUnsignedId($this->id_customer))
					SocialLoginModel::deleteCustomerConnection($this->id_customer, $this->network);

				// Validate is customer not logged must agree manually your information
				if ($this->network == 'twitter' || Configuration::get($this->module->name.'_MANUALLY')
					|| !Validate::isEmail($this->email) || !Validate::isName($this->first_name) || !Validate::isName($this->last_name))
					return 'pending';

				// If customer exists get id
				$this->id_customer = (int)$customer->customerExists($this->email, true, true);
				// If customer not exists create new user
				if (!Validate::isNullOrUnsignedId($this->id_customer))
					$this->id_customer = (int)$this->createUser();

				// If customer exists load object with email address
				$customer = new Customer($this->id_customer);
			}

			// If customer not logged then log in
			if (!$customer->isLogged())
				$is_logged = $this->loginUser($customer);
		}

		// Register customer in database
		$log_user = $this->registerUserData();

		if ($log_user && isset($is_logged) && $is_logged)
			return 'complete';

		return 'error';
	}

	/**
	*
	* @return intval $authentication->id is customer id
	*/
	private function createUser()
	{
		// Hook::exec('actionBeforeSubmitAccount');
		$customer = new Customer();
		$customer->firstname = pSQL($this->first_name);
		$customer->lastname = pSQL($this->last_name);
		$customer->email = pSQL($this->email);
		$customer->id_gender = (int)$this->gender;

		// generate passwd
		$real_passwd = Tools::passwdGen();
		$passwd = Tools::encrypt($real_passwd);
		$customer->passwd = $passwd;

		// Create customer
		$customer->add();

		// Get created user
		$authentication = $customer->getByEmail(trim($this->email), trim($real_passwd));
		if (!Validate::isLoadedObject($authentication))
			return false;

		Mail::Send((int)$this->context->cookie->id_lang, 'account', Mail::l('Welcome!'), array(
			'{firstname}' => $authentication->firstname,
			'{lastname}' => $authentication->lastname,
			'{email}' => $authentication->email,
			'{passwd}' => $real_passwd),
			$authentication->email,
			$authentication->firstname.' '.$authentication->lastname
		);

		return (int)$authentication->id;
	}

	/**
	 *
	 * @return bool true if data added to database
	 */
	public function registerUserData()
	{
		$social_login_model = new SocialLoginModel();
		$social_login_model->id_customer = $this->id_customer;
		$social_login_model->user_code = $this->id_user;
		$social_login_model->name = $this->network;

		return $social_login_model->addCustomerLog();
	}

	/**
	 *
	 * @param class $customer
	 * @return bool
	 */
	private function loginUser(Customer $customer)
	{
		if (!Validate::isLoadedObject($customer))
			return false;

		//$customer->id = $this->id_customer;
		if (!isset($this->context->cookie->id_compare))
			$this->context->cookie->id_compare = CompareProduct::getIdCompareByIdCustomer($customer->id);
		$this->context->cookie->id_customer = (int)$customer->id;
		$this->context->cookie->customer_lastname = $customer->lastname;
		$this->context->cookie->customer_firstname = $customer->firstname;
		$this->context->cookie->passwd = $customer->passwd;
		$this->context->cookie->logged = 1;
		$customer->logged = 1;
		$this->context->cookie->email = $customer->email;

		// Add customer to the context
		$this->context->customer = $customer;

		if (Configuration::get('PS_CART_FOLLOWING') && (empty($this->context->cookie->id_cart)
			|| Cart::getNbProducts($this->context->cookie->id_cart) == 0) && $id_cart = (int)Cart::lastNoneOrderedCart($this->context->customer->id))
			$this->context->cart = new Cart($id_cart);
		else
		{
			$id_carrier = (int)$this->context->cart->id_carrier;
			$this->context->cart->id_carrier = 0;
			$this->context->cart->setDeliveryOption(null);
			$this->context->cart->id_address_delivery = (int)Address::getFirstCustomerAddressId((int)$customer->id);
			$this->context->cart->id_address_invoice = (int)Address::getFirstCustomerAddressId((int)$customer->id);
		}
		$this->context->cart->id_customer = (int)$customer->id;
		$this->context->cart->secure_key = $customer->secure_key;

		if (isset($id_carrier) && $id_carrier && Configuration::get('PS_ORDER_PROCESS_TYPE'))
		{
			$delivery_option = array($this->context->cart->id_address_delivery => $id_carrier.',');
			$this->context->cart->setDeliveryOption($delivery_option);
		}

		$this->context->cart->save();
		$this->context->cookie->id_cart = (int)$this->context->cart->id;
		$this->context->cookie->write();
		$this->context->cart->autosetProductAddress();

		Hook::exec('actionAuthentication');

		// Login information have changed, so we check if the cart rules still apply
		CartRule::autoRemoveFromCart($this->context);
		CartRule::autoAddToCart($this->context);

		if ($this->context->customer->isLogged())
			return true;

		return false;
	}
}