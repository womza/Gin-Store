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

/**
 * @since 1.5.0
 */

class SocialLoginLoginModuleFrontController extends ModuleFrontController
{
	public $ssl = true;
	public $display_column_left = false;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
		// Init context
		$this->context = Context::getContext();

		// Load some module vars
		$module_name = $this->module->name;
		$array_networks = $this->module->array_networks;
		include_once (_PS_MODULE_DIR_.$module_name.'/classes/CustomerClass.php');
		include_once (_PS_MODULE_DIR_.$module_name.'/libraries/oauth_client.php');

		// Microsoft callback is without p variable
		if (Tools::getIsset('p'))
			$action = Tools::getValue('p');
		elseif (Tools::getIsset('code') && Tools::getIsset('state'))
			$action = 'microsoft';

		// Save in cookie when this page is called from button action
		$saved_referer = $this->context->cookie->__isset('social_login_referer');
		if (!$saved_referer)
		{
			$referer = null;
			$url = null;

			if (isset($_SERVER['HTTP_REFERER']))
				$url = Tools::secureReferrer($_SERVER['HTTP_REFERER']);

			if (Tools::getIsset('request'))
				$referer = Tools::getValue('request');

			if (is_null($url) && is_null($referer))
				Tools::redirect('index.php?controller=authentication');

			$saved_referer = $this->setCookieReferer($referer, $url);
		}

		// If is first time that call this page from button
		if ($saved_referer && isset($action) && in_array($action, $array_networks))
		{
			// Get configuration of each socia network
			$array_config = $this->module->getConfigSocial();

			// Define oauth class
			$client = new oauth_client_class();
			$client->server = Tools::ucfirst($action);
			$client->debug = false;

			// Define redirect_uri for callback
			if ($action == 'microsoft')
				$client->redirect_uri = $this->context->link->getModuleLink('sociallogin', 'login', array(), true);
			else
				$client->redirect_uri = $this->context->link->getModuleLink('sociallogin', 'login', array('p' => $action), true);

			// Define key and secret
			$client->client_id = $array_config[$action]['key'];
			$client->client_secret = $array_config[$action]['secret'];

			// Validate key and secret
			if (!Validate::isGenericName($client->client_id) || !Validate::isGenericName($client->client_secret))
				Tools::redirect('index.php?controller=authentication');

			// Get api details to request user data
			$api_user_url = $client->getApiUserUrl();
			$client->scope = $api_user_url['scope'];

			// Initizalize class oauth with predefined values of server
			if ($success = $client->initialize())
			{
				if (($success = $client->process()))
				{
					$user = '';
					if (Tools::strlen($client->authorization_error))
					{
						$client->error = $client->authorization_error;
						$success = false;
					}
					elseif (Tools::strlen($client->access_token))
						$success = $client->callAPI($api_user_url['url'], $api_user_url['method'], $api_user_url['parameters'], $api_user_url['options'], $user);
				}
				$success = $client->finalize($success);
			}

			if ($client->exit)
				exit;

			// Predefined value for login complete
			$login_complete = 0;

			// If proccess of call api is complete
			if ($success)
			{
				// Decode $user recieved
				$data_profile = $client->getUserData($user);

				// data profile with missing register data to create new decoder
				if (empty($data_profile['user']['id_user']))
					Logger::AddLog(Tools::jsonEncode($user), 3, 1, 'sociallogin');
				else
					$login_complete = $this->userLog($data_profile);
				// $login_complete = 'error' error when save
				// $login_complete = 'complete' login and save
				// $login_complete = 'pending' missing data, customer must complete register

				// Get referer from cookie
				$url = $this->context->cookie->__get('social_login_referer');

				// Customer must complete register manually
				if (isset($login_complete) && $login_complete == 'pending')
				{
					$first_name = pSQL($data_profile['required']['first_name']);
					$last_name = pSQL($data_profile['required']['last_name']);
					$email = pSQL($data_profile['required']['email']);

					$gender = (int)$data_profile['optional']['gender'];
					$id_user = pSQL($data_profile['user']['id_user']);
					// if (!Validate::isName($first_name))
					// $first_name = $this->module->l('first_name');
					// if (!Validate::isName($last_name))
					// $last_name = $this->module->l('last_name');
					// if (!Validate::isEmail($email))
					// $email = Tools::strtolower($first_name.'.'.$last_name.'@'.$action.'.com');

					// Save cookie variables
					$this->context->cookie->__set($module_name.'first_name', $first_name);
					$this->context->cookie->__set($module_name.'last_name', $last_name);
					$this->context->cookie->__set($module_name.'email', $email);
					$this->context->cookie->__set($module_name.'gender', $gender);
					$this->context->cookie->__set($module_name.'id_user', $id_user);
					$this->context->cookie->__set($module_name.'action', $action);

					$var = array($this->module->id, psQL($first_name), pSQL($last_name), pSQL($email), pSQL($gender), pSQL($id_user), pSQL($action));
					$parameters = implode('|', $var);
					$token = Tools::encrypt($parameters);

					$url = $this->context->link->getPageLink('authentication', true, null, 'create_account=1&module=sociallogin&token='.$token);
				}
			}
			else
			{
				// Some error in token process
				echo Tools::purifyHTML($client->error);
				if (Configuration::get($this->module->name.'_POPUP'))
					echo '<script>setTimeout("self.close()", 3000); </script>';
				else
				{
					sleep(3);
					Tools::redirect('index.php?controller=authentication');
				}
			}
		} // end if saved_referer
		else
			$url = $this->context->link->getPageLink('authentication', true);

		// Unset referer cookie variable
		$this->context->cookie->__unset('social_login_referer');

		if (Configuration::get($this->module->name.'_POPUP'))
		{
			echo '<script>window.opener.location.href="'.Tools::secureReferrer($url).'";</script>';
			echo '<script>window.opener.focus();</script>';
			echo '<script>self.close();</script>';
		}
		else
			Tools::redirect($url);
	}

	/**
	 * @var $referer string name of controller
	 * @var $url string http_referer server
	 * @return bool after save in cookie url to response
	 */
	private function setCookieReferer($referer, $url)
	{
		$response = $this->getUrlReferer($referer);

		if (Validate::isAbsoluteUrl($url))
			$this->context->cookie->__set('social_login_referer', $url);
		elseif (Validate::isAbsoluteUrl($response))
			$this->context->cookie->__set('social_login_referer', $response);
		else
			return false;

		return true;
	}

	/**
	 * @var $referer string controller origin name
	 * @return $url is controller exists or false
	 */
	private function getUrlReferer($referer)
	{
		switch ($referer)
		{
			case 'module-sociallogin-account':
				$url = $this->context->link->getModuleLink('sociallogin', 'account', array(), true);
				break;
			case 'authentication':
				$url = $this->context->link->getPageLink('my-account', true);
				break;
			case 'order-opc':
			case 'order':
				$url = $this->context->link->getPageLink('order', true);
				break;
			default:
				$url = $this->context->link->getPageLink('authentication', true);
		}

		return $url;
	}

	/**
	* @param array $data_profile = array(
	*		'required' => array(
	*			'first_name' => $first_name,
	*			'last_name' => $last_name,
	*			'email' => $email,
	*		),
	*		'optional' => array(
	*			'gender' => $gender,
	*		),
	*		'user' => array(
	*			'id_user' => $id,
	*			'newtwork' => $action,
	*		)
	*	);
	* @return 1 complete 0 collect missing data
	*/
	private function userLog($data_profile)
	{
		$customer_log = new CustomerClass();
		$customer_log->first_name = $data_profile['required']['first_name'];
		$customer_log->last_name = $data_profile['required']['last_name'];
		$customer_log->email = $data_profile['required']['email'];
		$customer_log->gender = $data_profile['optional']['gender'];
		$customer_log->id_user = $data_profile['user']['id_user'];
		$customer_log->network = $data_profile['user']['network'];

		return $customer_log->userLog($data_profile);
	}
}