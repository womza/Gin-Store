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

class SocialLoginModel extends ObjectModel
{
	public $id_customer;
	public $user_code;
	public $name;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'social_login_customer',
		'primary' => 'id_customer',
		'fields' => array(
			'id_customer' =>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
			'user_code' =>		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),
			'name' =>			array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),
			'id_shop' =>		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false)
		),
	);

	/**
	 *
	 * @return bool
	 */
	public function addCustomerLog()
	{
		if (is_null($this->id_customer) || is_null($this->user_code) || is_null($this->name))
			return;

		$context = Context::getContext();
		$id_shop = $context->shop->id;

		// $res = parent::add($autodate, $null_values);
		$res = Db::getInstance()->insert(
			'social_login_customer',
			array(
				'id_customer' => (int)$this->id_customer,
				'user_code' => pSQL($this->user_code),
				'name' => pSQL($this->name),
				'id_shop' => (int)$id_shop,
			), false, true, Db::INSERT_IGNORE);

		return $res;
	}

	/**
	 *
	 * @return bool
	 */
	public function updateCustomerLog()
	{
		if (is_null($this->id_customer) || is_null($this->user_code) || is_null($this->name))
			return;

		$context = Context::getContext();
		$id_shop = $context->shop->id;

		// $res = parent::add($autodate, $null_values);
		$res = Db::getInstance()->update(
			'social_login_customer',
			array(
				'id_customer' => (int)$this->id_customer,
				'user_code' => pSQL($this->user_code),
				'name' => pSQL($this->name),
				'id_shop' => (int)$id_shop,
			),
			'id_customer=`'.(int)$this->id_customer.'` AND id_shop=`'.(int)$id_shop.'` AND name=`'.pSQL($this->name).'`',
			1);

		return $res;
	}

	/**
	 *
	 * @param intval $id_customer
	 * @return bool
	 */
	public static function getCustomerLog($id_customer = null)
	{
		if (!Validate::isNullOrUnsignedId($id_customer))
			return;

		$context = Context::getContext();
		$id_shop = $context->shop->id;

		// $res = parent::add($autodate, $null_values);

		$res = Db::getInstance()->executeS(
			'SELECT `name`, `user_code`
			FROM `'._DB_PREFIX_.'social_login_customer`
			WHERE `id_customer`='.(int)$id_customer.'
			AND `id_shop`='.(int)$id_shop);

		return $res;
	}

	/**
	 *
	 * @param strval $user_code
	 * @param strval $name network
	 * @return bool
	 */
	public static function getCustomerByUserCode($user_code = null, $name = null)
	{
		if (is_null($user_code) || is_null($name))
			return;

		$context = Context::getContext();
		$id_shop = $context->shop->id;

		$res = Db::getInstance()->getRow(
			'SELECT `id_customer`
			FROM `'._DB_PREFIX_.'social_login_customer`
			WHERE `user_code` = \''.pSQL($user_code).'\'
			AND `name` = \''.pSQL($name).'\'
			AND `id_shop` = '.(int)$id_shop);

		return $res['id_customer'];
	}

	/*
	public static function checkIfCustomerIdExists($id_customer)
	{
		if (!Validate::isNullOrUnsignedId($id_customer))
			return;

		$context = Context::getContext();
		$id_shop = $context->shop->id;

		$res = Db::getInstance()->getRow(
			'SELECT `id_customer`
			FROM `'._DB_PREFIX_.'customer`
			WHERE `id_customer` = '.(int)$id_customer.'
			AND `id_shop` = '.(int)$id_shop);

		return $res['id_customer'];
	}
	*/

	/**
	 *
	 * @param strval $id_customer
	 * @param strval $name network
	 * @return bool
	 */
	public static function deleteCustomerConnection($id_customer, $name)
	{
		if (!Validate::isNullOrUnsignedId($id_customer) || !Validate::isGenericName($name))
			return;

		$context = Context::getContext();
		$id_shop = $context->shop->id;

		$res = Db::getInstance()->delete(
			'social_login_customer',
			'id_customer = '.(int)$id_customer.
			' AND name = \''.pSQL($name).'\'
			 AND id_shop = '.(int)$id_shop
		);

		return $res;
	}

	/*
	public static function cleanDataBase()
	{
		$res = Db::getInstance()->execute(
			'DELETE FROM slc
			USING '._DB_PREFIX_.'social_login_customer as slc
			LEFT OUTER JOIN '._DB_PREFIX_.'customer as cus
			ON slc.id_customer = cus.id_customer
			WHERE cus.id_customer IS NULL
			'
		);

		return $res;
	}
	*/
}