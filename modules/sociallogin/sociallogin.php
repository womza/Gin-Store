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

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class SocialLogin extends Module
{
	private $html = '';
	public $array_networks = array();
	protected $errors = array();

	public function __construct()
	{
		$this->name = 'sociallogin';
		$this->version = '1.0.10';
		$this->tab = 'social_networks';
		$this->author = 'jorgevrgs';
		$this->bootstrap = true;
		$this->controllers = array('login', 'account', 'delete');
		$this->module_key = 'ea12024d6ddc25c14ddb2e6e33d8249f';

		parent::__construct();

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Social Login');
		$this->description = $this->l('Add Facebook, Twitter, Google, LinkedIn, Microsoft social Connects');
		$this->confirmUninstall = $this->l('Are you sure you want to remove it? Be careful, all your configuration and your data will be lost');

		$this->array_networks = array('facebook', 'google', 'linkedin', 'microsoft', 'twitter', 'yahoo');
	}

	public function install()
	{
		if (!function_exists('curl_init'))
			$this->errors[] = ($this->l('Social Login needs the PHP Curl extension, 
		please ask your hosting provider to enable it prior to install this module.'));

		foreach ($this->array_networks as $value)
			Configuration::updateValue($this->name.Tools::strtoupper($value).'_ACTIVE', 0);

		Configuration::updateValue($this->name.'_BUTTON', 0);
		Configuration::updateValue($this->name.'_SIZE', 'st');
		Configuration::updateValue($this->name.'_POPUP', 1);
		Configuration::updateValue($this->name.'_SIGN_IN', 0);
		Configuration::updateValue($this->name.'_MANUALLY', 0);
		Configuration::updateValue($this->name.'_POSITIONS', serialize(array('authentication')));

		include(dirname(__FILE__).'/sql/install.php');

		return parent::install() &&
			$this->registerHook('displayHeader') &&
			$this->registerHook('displayCustomerAccount') &&
			$this->registerHook('actionCustomerAccountAdd') &&
			$this->registerHook('displayCustomerAccountFormTop') &&
			$this->registerHook('displayAdminCustomers');
	}

	public function uninstall()
	{
		foreach ($this->array_networks as $value)
			Configuration::deleteByName($this->name.Tools::strtoupper($value).'_ACTIVE');

		Configuration::deleteByName($this->name.'_BUTTON');
		Configuration::deleteByName($this->name.'_SIZE');
		Configuration::deleteByName($this->name.'_POPUP');
		Configuration::deleteByName($this->name.'_SIGN_IN');
		Configuration::deleteByName($this->name.'_MANUALLY');
		Configuration::deleteByName($this->name.'_POSITIONS');

		include(dirname(__FILE__).'/sql/uninstall.php');

		return parent::uninstall();
	}

	public function getContent()
	{
		$this->prepareCache();
		$this->html .= $this->display(__FILE__, 'views/templates/admin/admin_tabs.tpl');

		$this->html .= '<div class="panel tab-content">';

		$this->context->smarty->assign(array(
			'tab_active' => 'home',
			'link' => $this->context->link,
			'shop' => $this->context->shop,
			'shop_protocol' => Tools::getShopProtocol(),
		));

		// $admin_tpl = $this->_path.'views/templates/admin/';
		$tab_array = array_merge(array('home'), $this->array_networks);
		foreach ($tab_array as $name)
		{
			$this->html .= '<div id="'.$name.'" class="tab-pane '.($name == 'home' ? 'active' : '').'">';

			if (Tools::isSubmit('submit'.$name))
			{
				$this->context->smarty->assign(array(
					'tab_active' => $name,
				));

				$this->postValidation($name);
				if (!count($this->errors))
					$this->postProcess($name);
				else
				{
					foreach ($this->errors as $err)
						$this->html .= $this->displayError($err);
				}
			}

			$this->html .= $this->display(__FILE__, 'views/templates/admin/'.$name.'.tpl');

			if ($name == 'home')
				$this->html .= $this->homeForm($name);
			else
				$this->html .= $this->renderForm($name);

			$this->html .= '</div>';
		}

		$this->html .= '</div>';
		$this->html .= $this->display(__FILE__, 'views/templates/admin/footer.tpl');

		return $this->html;
	}

	private function postValidation($name)
	{
		if (!in_array($name, $this->array_networks))
			return;

		if (Tools::isSubmit('submit'.$name))
		{
			$value = array(
				'name' => $name,
				'active' => Tools::getValue(Tools::strtoupper($name).'_ACTIVE'),
				'key' => Tools::getValue(Tools::strtoupper($name).'_KEY'),
				'secret' => Tools::getValue(Tools::strtoupper($name).'_SECRET'),
			);
			if ($value['active'] && empty($value['key']))
				$this->errors[] = $value['name'].' '.$this->l('is active but App Key is empty');

			if ($value['active'] && empty($value['secret']))
				$this->errors[] = $value['name'].' '.$this->l('is active but App Secret is empty');
		}
	}

	private function postProcess($name)
	{
		if (Tools::isSubmit('submit'.$name) && 'submit'.$name == 'submithome')
		{
			Configuration::updateValue($this->name.'_BUTTON', (int)Tools::getValue(Tools::strtoupper($name).'_BUTTON'));
			Configuration::updateValue($this->name.'_SIZE', pSQL(Tools::getValue(Tools::strtoupper($name).'_SIZE')));
			Configuration::updateValue($this->name.'_POPUP', (int)Tools::getValue(Tools::strtoupper($name).'_POPUP'));
			Configuration::updateValue($this->name.'_SIGN_IN', (int)Tools::getValue(Tools::strtoupper($name).'_SIGN_IN'));
			Configuration::updateValue($this->name.'_MANUALLY', (int)Tools::getValue(Tools::strtoupper($name).'_MANUALLY'));
			Configuration::updateValue($this->name.'_POSITIONS', serialize(Tools::getValue(Tools::strtoupper($name).'_POSITIONS')));

			$this->html .= $this->displayConfirmation($this->l('Settings updated'));
		}
		elseif (Tools::isSubmit('submit'.$name) && in_array($name, $this->array_networks))
		{
			$value = array(
				$this->name.Tools::strtoupper($name).'_ACTIVE' => (int)Tools::getValue(Tools::strtoupper($name).'_ACTIVE'),
				$this->name.Tools::strtoupper($name).'_KEY' => pSQL(Tools::getValue(Tools::strtoupper($name).'_KEY')),
				$this->name.Tools::strtoupper($name).'_SECRET' => pSQL(Tools::getValue(Tools::strtoupper($name).'_SECRET')),
			);

			foreach ($value as $key => $value)
				Configuration::updateValue($key, $value);

			$this->html .= $this->displayConfirmation($this->l('Settings updated'));
		}
	}

	private function homeForm($name)
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-wrench'
				),
				'input' => array(
					array(
						'type' => 'radio',
						'label' => $this->l('Type of button'),
						'name' => Tools::strtoupper($name).'_BUTTON',
						'values' => array(
							array(
								'id' => 'icon',
								'value' => 1,
								'label' => $this->l('Icon')
							),
							array(
								'id' => 'text',
								'value' => 0,
								'label' => $this->l('Icon with text')
							),
						),
					),
					array(
						'type' => 'radio',
						'label' => $this->l('Size of button'),
						'name' => Tools::strtoupper($name).'_SIZE',
						'values' => array(
							array(
								'id' => 'xs',
								'value' => 'xs',
								'label' => $this->l('Extra small')
							),
							array(
								'id' => 'sm',
								'value' => 'sm',
								'label' => $this->l('Small')
							),
							array(
								'id' => 'st',
								'value' => 'st',
								'label' => $this->l('Standard')
							),
							array(
								'id' => 'lg',
								'value' => 'lg',
								'label' => $this->l('Large')
							),
						),
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Pop-up window'),
						'name' => Tools::strtoupper($name).'_POPUP',
						'desc' => $this->l('If set in "Yes" customer will see a pop-up window, else redirect'),
						'values' => array(
							array(
								'id' => 'on',
								'value' => 1,
								'label' => $this->l('On')
							),
							array(
								'id' => 'off',
								'value' => 0,
								'label' => $this->l('Off')
							),
						),
					),
					array(
						'type' => 'switch',
						'label' => $this->l('"Sign in with" text'),
						'name' => Tools::strtoupper($name).'_SIGN_IN',
						'desc' => $this->l('If set in "Yes" shows e.g. "Sign in with Facebook", else shows only name,
							 this option is available only in "Icon with test" mode'),
						'values' => array(
							array(
								'id' => 'on',
								'value' => 1,
								'label' => $this->l('On')
							),
							array(
								'id' => 'of',
								'value' => 0,
								'label' => $this->l('Off')
							),
						),
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Register manually'),
						'name' => Tools::strtoupper($name).'_MANUALLY',
						'desc' => $this->l('If set "Yes" customer must complete your register manually.'),
						'values' => array(
							array(
								'id' => 'on',
								'value' => 1,
								'label' => $this->l('On')
							),
							array(
								'id' => 'of',
								'value' => 0,
								'label' => $this->l('Off')
							),
						),
					),
					array(
						'type' => 'select',
						'multiple' => true,
						'label' => $this->l('Include in'),
						'name' => Tools::strtoupper($name).'_POSITIONS[]',
						'desc' => $this->l('Use "Ctrl" or "Cmd" key to select multiple'),
						//'size' => 5,
						'class' => ' fixed-width-xxl',
						'options' => array(
							'query' => array(
								array(
									'id_option' => 'authentication',
									'name' => $this->l('Authentication'),
								),
								array(
									'id_option' => 'next_login',
									'name' => $this->l('Header user info'),
								),/*
								array(
									'id_option' => 'header',
									'name' => $this->l('Top (Under construction)'),
								),
								array(
									'id_option' => 'footer',
									'name' => $this->l('Footer (Under construction)'),
								),*/
								array(
									'id_option' => 'product',
									'name' => $this->l('Product page'),
								),
							),
							'id' => 'id_option',
							'name' => 'name'
						)
					),
				),
				'submit' => array(
					'title' => $this->l('Update settings'),
				),
			),
		);

		$helper = $this->helperForm($name);

		$helper->tpl_vars = array(
			'fields_value' => array(
				Tools::strtoupper($name).'_BUTTON' => Tools::getValue(Tools::strtoupper($name).'_BUTTON', Configuration::get($this->name.'_BUTTON')),
				Tools::strtoupper($name).'_SIZE' => Tools::getValue(Tools::strtoupper($name).'_SIZE', Configuration::get($this->name.'_SIZE')),
				Tools::strtoupper($name).'_POPUP' => Tools::getValue(Tools::strtoupper($name).'_POPUP', Configuration::get($this->name.'_POPUP')),
				Tools::strtoupper($name).'_SIGN_IN' => Tools::getValue(Tools::strtoupper($name).'_SIGN_IN', Configuration::get($this->name.'_SIGN_IN')),
				Tools::strtoupper($name).'_MANUALLY' => Tools::getValue(Tools::strtoupper($name).'_MANUALLY', Configuration::get($this->name.'_MANUALLY')),
				Tools::strtoupper($name).'_POSITIONS[]' => Tools::getValue(Tools::strtoupper($name).'_POSITIONS',
					Tools::unSerialize(Configuration::get($this->name.'_POSITIONS'))),
			),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	private function renderForm($name)
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-wrench'
				),
				'input' => array(
					array(
						'type' => 'switch',
						'label' => Tools::ucfirst($name).' '.$this->l('is active'),
						'name' => Tools::strtoupper($name).'_ACTIVE',
						'values' => array(
							array(
								'id' => 'on',
								'value' => 1,
								'label' => $this->l('On')
							),
							array(
								'id' => 'off',
								'value' => 0,
								'label' => $this->l('Off')
							),
						),
					),
					array(
						'type' => 'text',
						'label' => Tools::ucfirst($name).' '.$this->l('App key'),
						'name' => Tools::strtoupper($name).'_KEY',
					),
					array(
						'type' => 'text',
						'label' => Tools::ucfirst($name).' '.$this->l('App secret'),
						'name' => Tools::strtoupper($name).'_SECRET',
					),
				),
				'submit' => array(
					'title' => $this->l('Update settings'),
				),
			),
		);

		$helper = $this->helperForm($name);

		$active_name = Tools::strtoupper($name).'_ACTIVE';
		$active = Configuration::get($this->name.Tools::strtoupper($name).'_ACTIVE');

		$key_name = Tools::strtoupper($name).'_KEY';
		$key = Configuration::get($this->name.Tools::strtoupper($name).'_KEY');

		$secret_name = Tools::strtoupper($name).'_SECRET';
		$secret = Configuration::get($this->name.Tools::strtoupper($name).'_SECRET');

		$helper->tpl_vars = array(
			'fields_value' => array(
				Tools::strtoupper($name).'_ACTIVE' => Tools::getValue($active_name, $active),
				Tools::strtoupper($name).'_KEY' => Tools::getValue($key_name, $key),
				Tools::strtoupper($name).'_SECRET' => Tools::getValue($secret_name, $secret),
			),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	private function helperForm($name)
	{
		$helper = new HelperForm();

		// Module, token and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

		// Language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;

		// Title and toolbar
		$helper->title = $this->displayName;
		$helper->show_toolbar = true; // false -> remove toolbar
		$helper->toolbar_scroll = true; // yes -> Toolbar is always visible on the top of the screen.
		$helper->submit_action = 'submit'.$name;
		$helper->toolbar_btn = array(
			'save' => array(
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
				'&token='.Tools::getAdminTokenLite('AdminModules'),
			),
			'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);

		return $helper;
	}

	public function hookDisplayAdminCustomers()
	{
		$id_customer = (int)Tools::getValue('id_customer');
		$customer = new Customer($id_customer);

		if (!Validate::isLoadedObject($customer))
			return;

		require_once ('classes/SocialLoginModel.php');
		$customer_log = SocialLoginModel::getCustomerLog((int)$id_customer);

		$customer_network = array();
		foreach ($customer_log as $value)
			$customer_network[$value['name']] = $value['user_code'];

		$array_customers = array();
		foreach ($this->array_networks as $value)
			$array_customers[$value] = isset($customer_network[$value]) ? $customer_network[$value] : 0;

		$this->context->smarty->assign(array(
			'customer_log' => $array_customers,
		));

		return $this->display(__FILE__, 'views/templates/admin/customer.tpl');

	}

	public function hookDisplayHeader()
	{
		$php_self = '';
		$page_name = '';
		$positions = Tools::unSerialize(Configuration::get($this->name.'_POSITIONS'));

		if (isset($this->context->controller->php_self))
			$php_self = $this->context->controller->php_self;
		if (isset($this->context->controller->page_name))
			$page_name = $this->context->controller->page_name;

		if ((!in_array('next_login', $positions) || !count($positions)) && $page_name != 'module-sociallogin-account'
			&& (!in_array($php_self, array('authentication', 'order', 'order-opc')) || !in_array('authentication', $positions)))
			return;

		//if (!empty($php_self) ^ $page_name != 'module-sociallogin-account')
			//return;
		if (!$this->context->customer->isLogged() || $page_name == 'module-sociallogin-account')
		{
			$this->context->controller->addCSS($this->_path.'views/css/bootstrap-social.css', 'all');
			$this->context->controller->addCSS(Tools::getShopProtocol().'maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css', 'all');
			//$this->context->controller->addCSS($this->_path.'css/font-awesome.min.css', 'all');
			$this->context->controller->addJS($this->_path.'views/js/action.js');
		}

		// $cache_id = $button.$size.$popup.$sign_in;
		// if (!$this->isCached('header.tpl', $this->getCacheId('header|'.$cache_id)))
		$this->prepareCache();
		$this->context->smarty->assign(array(
			'button' => Configuration::get($this->name.'_BUTTON'),
			'size' => Configuration::get($this->name.'_SIZE'),
			'popup' => Configuration::get($this->name.'_POPUP'),
			'sign_in' => Configuration::get($this->name.'_SIGN_IN'),
			'positions' => $positions,
		));

		return $this->display(__FILE__, 'header.tpl'); //, $this->getCacheId('header|'.$cache_id));
	}

	public function hookActionCustomerAccountAdd($params)
	{
		$new_customer = $params['newCustomer'];
		if (!Validate::isLoadedObject($new_customer))
			return false;

		$post_vars = $params['_POST'];

		if (isset($post_vars['id_user']) && isset($post_vars['id_user']))
		{
			$user_social = array(
				'id_customer' => (int)$new_customer->id,
				'user_code' => $post_vars['id_user'],
				'name' => $post_vars['network'],
			);

			require_once ('classes/CustomerClass.php');
			$customer_class = new CustomerClass();
			$customer_class->registerUserData($user_social);
		}

		// Clear cookie variables
		$this->context->cookie->__unset($this->name.'first_name');
		$this->context->cookie->__unset($this->name.'last_name');
		$this->context->cookie->__unset($this->name.'email');
		$this->context->cookie->__unset($this->name.'gender');
		$this->context->cookie->__unset($this->name.'id_user');
		$this->context->cookie->__unset($this->name.'action');
	}

	public function hookDisplayCustomerAccountForm()
	{
		// if (!$this->isCached('authentication.tpl', $this->getCacheId()))
		$this->prepareCache();

		if (Tools::getIsset('token') && Tools::getIsset('module') && Tools::getValue('module') == $this->name)
		{
			$token_received = Tools::getValue('token');

			// Load cookie variables
			$first_name = $this->context->cookie->__get($this->name.'first_name');
			$last_name = $this->context->cookie->__get($this->name.'last_name');
			$email = $this->context->cookie->__get($this->name.'email');
			$gender = $this->context->cookie->__get($this->name.'gender');
			$id_user = $this->context->cookie->__get($this->name.'id_user');
			$action = $this->context->cookie->__get($this->name.'action');

			$var = array($this->id, psQL($first_name), pSQL($last_name), pSQL($email), pSQL($gender), pSQL($id_user), pSQL($action));
			$parameters = implode('|', $var);
			$token = Tools::encrypt($parameters);
			if ($token_received === $token)
			{
				$_POST['id_gender'] = (int)$gender;
				$_POST['customer_firstname'] = pSQL($first_name);
				$_POST['customer_lastname'] = pSQL($last_name);
				$_POST['email'] = pSQL($email);
				$_POST['passwd'] = pSQL(Tools::passwdGen());

				$this->context->smarty->assign(array(
					'id_user' => pSQL($id_user),
					'network' => pSQL($action),
				));
			}
			else
				Tools::redirect('index.php?controller=authentication');
		}
		elseif (Tools::isSubmit('submitAccount'))
		{
			if (Tools::getIsset('id_user'))
				$id_user = Tools::getValue('id_user');
			if (Tools::getIsset('network'))
				$network = Tools::getValue('network');

			if (isset($id_user) && isset($network))
				$this->context->smarty->assign(array(
					'id_user' => $id_user,
					'network' => $network,
				));
		}

		return $this->display(__FILE__, 'authentication.tpl');
	}

	public function hookDisplayCustomerAccountFormTop()
	{
		return $this->hookDisplayCustomerAccountForm();
	}

	public function prepareCache()
	{
		$array_output = array();
		$request = '';

		if (isset($this->context->controller->php_self))
			$request = $this->context->controller->php_self;
		elseif (isset($this->context->controller->page_name))
			$request = $this->context->controller->page_name;

		// To specific if at least one network is available
		$one_active_net = false;

		foreach ($this->array_networks as $value)
		{
			$app_active = Configuration::get($this->name.Tools::strtoupper($value).'_ACTIVE');
			$app_key = Configuration::get($this->name.Tools::strtoupper($value).'_KEY');
			$app_secret = Configuration::get($this->name.Tools::strtoupper($value).'_SECRET');
			$complete_config = false;

			if ($app_active && !empty($app_key) && !empty($app_secret))
			{
				$complete_config = true;
				$one_active_net = true;
			}

			$array_output[$value] = array(
				'name' => $value,
				//'active' => Configuration::get($this->name.Tools::strtoupper($value).'_ACTIVE'),
				//'key' => Configuration::get($this->name.Tools::strtoupper($value).'_KEY'),
				//'secret' => Configuration::get($this->name.Tools::strtoupper($value).'_SECRET'),
				'complete_config' => $complete_config,
				'connect' => $this->context->link->getModuleLink('sociallogin', 'login', array('p' => $value, 'request' => $request), true),
			);

			// Link to delete button
			if (Validate::isLoadedObject($this->context->customer) && $request == 'module-sociallogin-account')
			{
				if ($this->context->customer->isLogged())
				{
					$delete = $this->context->link->getModuleLink('sociallogin', 'delete', array(
						'p' => $value,
						'token' => Tools::getToken($value)),
					true);
					$array_output[$value]['delete'] = $delete;
				}
			}
		}

		if ($one_active_net)
			$this->context->smarty->assign('show_authentication_block', $one_active_net);

		$this->context->smarty->assign(array(
			'social_networks' => $array_output,
		));
	}

	public function hookDisplayCustomerAccount()
	{
		return $this->display(__FILE__, 'my-account.tpl');
	}

	/**
	 * @return array $array_output configuration of each network
	 */
	public function getConfigSocial()
	{
		$array_output = array();
		foreach ($this->array_networks as $value)
		{
			$array_output[$value] = array(
				'name' => $value,
				'active' => Configuration::get($this->name.Tools::strtoupper($value).'_ACTIVE'),
				'key' => Configuration::get($this->name.Tools::strtoupper($value).'_KEY'),
				'secret' => Configuration::get($this->name.Tools::strtoupper($value).'_SECRET'),
			);
		}

		return $array_output;
	}
}