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

class LengowAddressAbstract extends Address {

	/**
	* Specify if an address is already in base
	*
	* @param $alias string The Alias
	*
	* @return mixed Addres or false
	*/
	public static function getByAlias($alias)
	{
		$row = Db::getInstance()->getRow('
				 SELECT `id_address`
				 FROM '._DB_PREFIX_.'address a
				 WHERE a.`alias` = "'.(string)$alias.'"');
		if ($row['id_address'] > 0)
			return new LengowAddress($row['id_address']);
		return false;
	}

	/**
	* Hash an alias and get the address with unique hash
	*
	* @param $alias string The Alias
	*
	* @return mixed Address or false
	*/
	public static function getByHash($alias)
	{
		return self::getByAlias(self::hash($alias));
	}

	/**
	* Filter non printable caracters
	*
	* @param $text string
	*
	* @return string
	*/
	public static function _filter($text)
	{
		return preg_replace('/[!<>?=+@{}_$%]*$/u', '', $text); // remove non printable
	}

	/**
	* Filter non printable caracters
	*
	* @param $text string
	*
	* @return string
	*/
	public static function extractName($fullname)
	{
		$array_name = explode(' ', $fullname);
		$firstname = $array_name[0];
		$lastname = str_replace($firstname.' ', '', $fullname);
		$firstname = empty($firstname) ? '' : self::cleanName($firstname);
		$lastname = empty($lastname) ? '' : self::cleanName($lastname);
		return array('firstname' => Tools::ucfirst(Tools::strtolower($firstname)),
			'lastname' => Tools::ucfirst(Tools::strtolower($lastname)));
	}

	/**
	* Clean firstname or lastname to Prestashop
	*
	* @param $text string Name
	*
	* @return string
	*/
	public static function cleanName($name)
	{
		return LengowCore::replaceAccentedChars(Tools::substr(trim(preg_replace('/[0-9!<>,;?=+()@#"�{}_$%:]/', '', $name)), 0, 31));
	}

	/**
	* Hash address with md5
	*
	* @param $text string Full address
	*
	* @return string Hash
	*/
	public static function hash($address)
	{
		return md5($address);
	}

	/**
	* Initiliaze an address corresponding to the specified id address or if empty to the
	* default shop configuration
	* Overrides initialize to make it compatible with all versions (since 1.6.0.11 new parameter) 
	* 
	* @param int $id_address
	* @return Address address
	*/
	public static function initializeLengow($id_address = null)
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			// if an id_address has been specified retrieve the address
			if ($id_address)
			{
				$address = new Address((int)$id_address);

				if (!Validate::isLoadedObject($address))
					throw new PrestaShopException('Invalid address');
			}
			else
			{
				// set the default address
				$address = new Address();
				$address->id_country = (int)Context::getContext()->country->id;
				$address->id_state = 0;
				$address->postcode = 0;
			}

			return $address;
		}
		if (version_compare(_PS_VERSION_, '1.6.11', '>='))
			return parent::initialize($id_address, false);
		else
			return parent::initialize($id_address);
	}


	/**
	 * Retrieves an address according to the type passed as a parameter (billing or shipping)
	 * @param type $type
	 * @param type $lengow_order
	 * @param type $id_customer
	 * @param type $lengow_order_id
	 * @return String
	 */
	public function getAddress($type, $lengow_order, $id_customer, $lengow_order_id)
	{
		$address_elements = self::_getAddressImport($lengow_order, $type);
		$result = self::_checkAddress($address_elements['address'], $lengow_order_id, $type);
		if (is_string($result))
			return $result;

		$address_elements['address'] = $result;
		$this->buildAddress($address_elements, $id_customer);
		$this->alias = self::hash((string)$address_elements['address']['full_address']);
	}

	/**
	 * Retrieves the address elements from the import
	 * @param type $lengow_order
	 * @param type $type
	 * @return type
	 */
	protected static function _getAddressImport($lengow_order, $type)
	{
		$elements = array(
			'customer' => array(
				'society' => null,
				'civility' => null,
				'email' => null,
				'lastname' => null,
				'firstname' => null,
			),
			'address' => array(
				'addressElements' => array(
					'address' => null,
					'address_2' => null,
					'address_complement' => null,
				),
				'countryElements' => array(
					'zipcode' => null,
					'country' => null,
					'country_iso' => null,
				),
				'city' => null,
				'phoneElements' => array(
					'phone_home' => null,
					'phone_office' => null,
					'phone_mobile' => null,
				),
				'full_address' => null,
			),
		);

		return self::_getAddressElements($type, $lengow_order, $elements);
	}

	/**
	 * Fills an array containing the address elements
	 * @param type $address_type
	 * @param type $lengow_order
	 * @param type $elements
	 * @return type
	 */
	protected static function _getAddressElements($address_type, $lengow_order, $elements)
	{
		$address = $address_type.'_address';
		foreach ($elements as $key => $value)
		{
			foreach ($value as $subKey => $subValue)
			{
				if (is_array($subValue))
				{
					foreach ($subValue as $finalkey => $finalvalue)
					{
						$finalvalue = $finalvalue; // Useless line but Prestashop validator requires it
						$node = $address_type.'_'.$finalkey;
						$subValue[$finalkey] = self::_getElement($lengow_order, $address, $node);
						$value[$subKey] = $subValue;
					}
				}
				else
				{
					$node = $address_type.'_'.$subKey;
					$value[$subKey] = self::_getElement($lengow_order, $address, $node);
				}
				$elements[$key] = $value;
			}
		}
		return $elements;
	}

	/**
	 * Returns the value of an address element
	 * @param type $lengow_order
	 * @param type $address
	 * @param type $node
	 * @return type
	 */
	protected static function _getElement($lengow_order, $address, $node)
	{
		return trim((string)$lengow_order->$address->$node);
	}

	/**
	 * Fills the address attributes
	 * @param type $elements
	 * @param type $id_customer
	 */
	public function buildAddress($elements, $id_customer)
	{
		$customer = $elements['customer'];
		$customer = LengowCustomer::checkCustomerNames($customer);

		$address = $elements['address'];

		$this->id_customer = $id_customer;
		$this->firstname = $customer['firstname'];
		$this->lastname = $customer['lastname'];
		$this->id_country = $address['countryElements']['id_country'];
		$this->country = (string)$address['countryElements']['country'];

		if (empty($address['addressElements']['address']) && !empty($address['addressElements']['adress_2']))
			$this->address1 = preg_replace('/[!<>?=+@{}_$%]/sim', '', (string)$address['addressElements']['adress_2']);
		else
		{
			$this->address1 = preg_replace('/[!<>?=+@{}_$%]/sim', '', (string)$address['addressElements']['address']);
			$this->address2 = empty($address['addressElements']['address_2']) ? '' : preg_replace('/[!<>?=+@{}_$%]/sim', '', (string)$address['addressElements']['address_2']);
		}

		if ($customer['society'])
			$this->company = (string)$customer['society'];
		$this->city = preg_replace('/[!<>?=+@{}_$%]/sim', '', (string)$address['city']);
		$this->postcode = $address['countryElements']['zipcode'];
		$this->alias = self::hash((string)$address['full_address']);
		$this->_checkPhoneElements($address['phoneElements']);
	}

	/**
	 * Checks if the address's elements are valid
	 * @param Array $address
	 * @param String $lengow_order_id
	 * @param String $type
	 * @return Array
	 */
	protected static function _checkAddress($address, $lengow_order_id, $type)
	{
		foreach ($address as $key => $value)
		{
			switch ($key)
			{
				case 'addressElements' :
					$result = self::_checkAddressElements($value, $lengow_order_id, $type);
					break;
				case 'city' :
					$result = self::_checkCityElement($value, $lengow_order_id);
					break;
				case 'countryElements' :
					$result = self::_checkCountryElements($value, $lengow_order_id, $type);
					break;
				default :
					break;
			}
			if (is_string($result))
				return $result;
			if ($result)
				$address[$key] = $result;
		}
		return $address;
	}

	/**
	 * Checks if the elements concerning the country from the import are valid
	 * @param type $countryElements
	 * @param type $lengow_order_id
	 * @param type $mode
	 * @return type
	 * @throws Exception
	 */
	protected static function _checkCountryElements($countryElements, $lengow_order_id, $mode)
	{
		$country_iso = (string)$countryElements['country_iso'];
		$zipcode = (string)$countryElements['zipcode'];
		$country = (string)$countryElements['country'];
		try {
			if (empty($country_iso))
			{
				$id_country = Context::getContext()->country->id;
				LengowCore::log('(Warning) no country ISO', $lengow_order_id, LengowImport::$force_log_output);
			}
			else if (!$id_country = Country::getByIso((string)$country_iso))
				{
					$id_country = Context::getContext()->country->id;
					if ($mode == 'billing')

						LengowCore::log('(Warning) no country '.(string)$country_iso.' ('.$mode.') exists on this PRESTASHOP', $lengow_order_id, LengowImport::$force_log_output);
					else
						return 'no country '.(string)$country_iso.' ('.$mode.') exists on this PRESTASHOP';

				}

			if (!empty($zipcode))
			{
				$billing_country = new Country($id_country);
				if ($billing_country->zip_code_format != '' && !LengowCore::checkZipCode($billing_country->zip_code_format, $zipcode, $billing_country->iso_code))
					$zipcode = preg_replace('/[^0-9-]+/', '', $zipcode);
			}
			else
			{
				LengowCore::log('Warning) No zipcode', $lengow_order_id);
				$zipcode = ' ';
			}
		} catch (Exception $e)
		{
			LengowCore::log('(Warning) '.$e->getMessage(), $lengow_order_id);
		}

		$countryElements['country_iso'] = $country_iso;
		$countryElements['zipcode'] = $zipcode;
		$countryElements['country'] = $country;
		$countryElements['id_country'] = $id_country;

		return $countryElements;
	}

	protected static function _checkAddressLength($address1, $address2, $lengow_order_id, $type)
	{
		if (isset(LengowAddress::$definition))
		{
			$address1_maxlength = (int)LengowAddress::$definition['fields']['address1']['size'];
			$address2_maxlength = (int)LengowAddress::$definition['fields']['address2']['size'];

			if (Tools::strlen($address1) > $address1_maxlength && Tools::strlen($address2) > $address2_maxlength)
			{
				$address1 = Tools::substr($address1, 0, $address1_maxlength);
				$address2 = Tools::substr($address1, 0, $address2_maxlength);
				LengowCore::log('(Warning) '.$type.' address lines too long. Lines have been truncated to '.$address1_maxlength.' caracters', $lengow_order_id, LengowImport::$force_log_output);
			}

			if (Tools::strlen($address1) > $address1_maxlength)
			{
				$address1_maxlength = (int)LengowAddress::$definition['fields']['address1']['size'];
				$address2_maxlength = (int)LengowAddress::$definition['fields']['address2']['size'];

				$address1_array = explode(' ', $address1);
				$sum = 0;
				$index = -1;
				foreach ($address1_array as $address_part)
				{
					if ($sum > $address1_maxlength)
						break;

					$sum += Tools::strlen($address_part.' ');
					$index++;
				}
				$address1 = '';
				for ($i = 0; $i < $index; $i++)
				{
					if (!empty($address1))
						$address1 .= ' ';

					$address1 .= $address1_array[$i];
				}
				$n = count($address1_array);
				$address1_extra = '';
				for ($i = $index; $i < $n; $i++)
					$address1_extra .= $address1_array[$i].' ';

				$address2 = $address1_extra.$address2;
				if (Tools::strlen($address2) > $address2_maxlength)
				{
					$address2 = Tools::substr($address2, 0, $address2_maxlength);
					LengowCore::log('(Warning) address line 2 too long. It has been truncated to '.$address1_maxlength.' caracters', $lengow_order_id, LengowImport::$force_log_output);
				}

			}
		}
		return array('address' => $address1, 'address_2' => $address2);

	}


	/**
	 * Checks if the address lines are valid
	 * @param type $address
	 * @param type $lengow_order_id
	 * @return type
	 * @throws Exception
	 */
	protected static function _checkAddressElements($address, $lengow_order_id, $type)
	{
		$address1 = (string)$address['address'];
		$address2 = (string)$address['address_2'];
		$address2 .= (string)$address['address_complement'];
		if (empty($address1) && empty($address2))
			return 'no address';
		else
		{
			if (empty($address1))
			{
				$address1 = $address2;
				$address2 = '';
			}
			$address = self::_checkAddressLength($address1, $address2, $lengow_order_id, $type);

			return $address;
		}
	}

	/**
	 * Checks if the city element exists
	 * @param type $city
	 * @param type $lengow_order_id
	 * @throws Exception
	 */
	protected static function _checkCityElement($city)
	{
		if (empty($city))
			return 'no city';
	}

	/**
	 * Validates and clean the phone elements
	 * @param type $phoneElements
	 */
	protected function _checkPhoneElements($phoneElements)
	{
		if (isset(LengowAddress::$definition))
			$maxLength = self::$definition['fields']['phone']['size'];
		else
			$maxLength = 16;

		$phone_home = Tools::substr(LengowCore::cleanPhone((string)$phoneElements['phone_home']), 0, $maxLength);
		$phone_office = Tools::substr(LengowCore::cleanPhone((string)$phoneElements['phone_office']), 0, $maxLength);
		$phone_mobile = Tools::substr(LengowCore::cleanPhone((string)$phoneElements['phone_mobile']), 0, $maxLength);

		if (!empty($phone_home))
			$this->phone = $phone_home;
		if (!empty($phone_mobile))
			$this->phone_mobile = $phone_mobile;
		if (!empty($phone_office))
			$this->phone_mobile = $phone_office;
	}

	/**
	 * Validate an address
	 * @param type $address
	 * @param type $type
	 * @param type $lengow_order_id
	 * @return boolean
	 * @throws Exception
	 */
	public function validateAddress($type, $lengow_order_id)
	{
		try {
			if (!$error = $this->validateFields(false, true))
				throw new Exception($error);
			$this->add();
			return true;
		} catch (Exception $e) {
			LengowCore::log('Saving error '.$type.' address : '.$e->getMessage(), $lengow_order_id, LengowImport::$force_log_output);
			LengowCore::endProcessOrder($lengow_order_id, 1, 0, 'Saving error '.$type.' address : '.$e->getMessage());
			return false;
		}

	}

}
