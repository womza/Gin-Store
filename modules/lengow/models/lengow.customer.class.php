<?php
/**
 * Copyright 2014 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
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
	loadFile('address');
	loadFile('cart');
	loadFile('product');
	loadFile('order');
	loadFile('orderdetail');
	loadFile('payment');
	loadFile('marketplace');
	loadFile('import');
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

class LengowCustomerAbstract extends Customer
{

	/**
	 * Fills the fields of the current customer
	 * @param type $customerElements
	 */
	protected function _buildCustomer($customerElements)
	{
		$this->company = LengowAddress::cleanName((string)$customerElements['society']);
		$this->email = $customerElements['email'];
		$this->firstname = $customerElements['firstname'];
		$this->lastname = $customerElements['lastname'];
		$this->passwd = md5(rand());
		if (_PS_VERSION_ >= '1.5')
			$this->id_gender = LengowGender::getGender((string)$customerElements['civility']);
	}

	/**
	 * Retrieves the customer from the import
	 * @param type $lengow_order
	 * @param type $lengow_order_id
	 * @return boolean
	 */
	public function getCustomerImport($lengow_order, $lengow_order_id)
	{
		$customer_elements = array(
			'society' => null,
			'civility' => null,
			'email' => null,
			'lastname' => null,
			'firstname' => null,
		);
		$customer_elements = self::_getCustomerElements($lengow_order, $customer_elements);
		$customer_elements['email'] = self::_checkEmailElement($customer_elements['email'], $lengow_order_id);
		$this->getByEmail($customer_elements['email']);
		if (!$this->id)
		{
			$customer_elements = self::checkCustomerNames($customer_elements);
			$this->_buildCustomer($customer_elements);
			return $this->_validateCustomer($lengow_order_id);
		}
		return true;

	}

	/**
	 * Retrieves the customer elements from the import
	 * @param type $lengow_order
	 * @param type $elements
	 * @return type
	 */
	protected static function _getCustomerElements($lengow_order, $elements)
	{
		$type = 'billing_';
		foreach ($elements as $key => $value)
		{
			$value = $value; // Useless line for prestashop validator
			$node = $type.$key;
			$elements[$key] = (string)$lengow_order->billing_address->$node;

		}
		return $elements;

	}

	/**
	 * Checks and cleans the name elements
	 * @param type $customerElements
	 * @return string
	 */
	public static function checkCustomerNames($customerElements)
	{
		$firstname = LengowAddress::cleanName($customerElements['firstname']);
		$lastname = LengowAddress::cleanName($customerElements['lastname']);
		if (empty($firstname) || empty($lastname))
		{
			if (empty($firstname))
			{
				$name = LengowAddress::extractName($lastname);
				$firstname = $name['firstname'];
				$lastname = $name['lastname'];
			}
			else
			{
				$name = LengowAddress::extractName($firstname);
				$firstname = $name['firstname'];
				$lastname = $name['lastname'];
			}
		}
		if (empty($firstname))
			$firstname = '--';
		if (empty($lastname))
			$lastname = '--';

		$customerElements['firstname'] = $firstname;
		$customerElements['lastname'] = $lastname;
		return $customerElements;
	}

	/**
	 * Checks if the customer's email exists
	 * @param type $email_address
	 * @param type $lengow_order_id
	 * @return string
	 */
	protected static function _checkEmailElement($email_address, $lengow_order_id)
	{
		if (empty($email_address) || (bool)Configuration::get('LENGOW_IMPORT_FAKE_EMAIL'))
		{
			$email_address = 'no-mail+'.$lengow_order_id.'@'.LengowCore::getHost();
			if (empty($email_address))
				LengowCore::log('no customer email, generate unique : '.$email_address, $lengow_order_id, LengowImport::$force_log_output);
		}
		if (LengowImport::$debug)
			$email_address = '_'.$email_address;
		return $email_address;
	}

	/**
	 * Validates a customer
	 * @param type $customer
	 * @param type $lengow_order_id
	 * @return boolean
	 * @throws Exception
	 */
	protected function _validateCustomer($lengow_order_id)
	{
		try {
			if (!$error = $this->validateFields(false, true))
				throw new Exception($error);
			$this->add();
			return true;
		} catch (Exception $e) {
			LengowCore::log('customer creation failed : '.$e->getMessage(), $lengow_order_id, LengowImport::$force_log_output);
			LengowCore::endProcessOrder($lengow_order_id, 1, 0, 'Customer creation failed : '.$e->getMessage());
			return false;
		}
	}
}
