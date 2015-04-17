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

class SocialLoginDeleteModuleFrontController extends ModuleFrontController
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
		$array_networks = $this->module->array_networks;

		if (Tools::getIsset('p'))
			$action = Tools::getValue('p');

		if (Tools::getIsset('token'))
			$token = Tools::getValue('token');

		// This is the one origin url can delete connections
		$url_referer = $this->context->link->getModuleLink('sociallogin', 'account', array(), true);

		if ((!isset($action) || !isset($token)) || !$this->context->customer->isLogged()
		|| $token != Tools::getToken($action) || !in_array($action, $array_networks) || !isset($_SERVER['HTTP_REFERER'])
		|| $_SERVER['HTTP_REFERER'] != $url_referer)
			Tools::redirect($url_referer);

		include_once (_PS_MODULE_DIR_.$this->module->name.'/classes/SocialLoginModel.php');
		SocialLoginModel::deleteCustomerConnection($this->context->customer->id, $action);
		Tools::redirect($url_referer);
	}
}