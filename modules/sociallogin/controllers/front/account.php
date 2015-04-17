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

class SocialloginAccountModuleFrontController extends ModuleFrontController
{
	public $ssl = true;
	public $display_column_left = false;

	public function initContent()
	{
		parent::initContent();
		$this->context = Context::getContext();

		if (!$this->context->customer->isLogged())
			Tools::redirect('index.php?controller=authentication');

		if ($this->context->customer->id)
		{
			$customer = $this->context->customer;

			require_once ($this->module->getLocalPath().'classes/SocialLoginModel.php');
			$customer_log = SocialLoginModel::getCustomerLog((int)$customer->id);

			$customer_network = array();
			foreach ($customer_log as $value)
				$customer_network[] = $value['name'];

			$array_customers = array();
			foreach ($this->module->array_networks as $value)
				$array_customers[$value] = in_array($value, $customer_network) ? 1 : 0;

			$this->context->smarty->assign(array(
				'customer_log' => $array_customers,
				'token' => Tools::getToken('sociallogin'),
			));

			$this->module->prepareCache();

			$this->setTemplate('account.tpl');
		}
	}
}