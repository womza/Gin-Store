<?php
/**
 * Copyright 2015 Lengow SAS.
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
 *  @author    Ludovic Drin <ludovic@lengow.com> Romain Le Polh <romain@lengow.com> Mathieu Sabourin <mathieu.sabourin@lengow.com>
 *  @copyright 2015 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 */

class LengowCarrierAbstract extends Carrier
{
	/**
	 * Returns carrier id according to the module name given
	 * @param string $module_name 
	 * @return int carrier id
	 */
	public static function getIdByModuleName($module_name, $id_lang)
	{
		$module_name = Tools::strtolower(str_replace(' ', '', $module_name));
		if ($module_name == 'laposte')
			$module_name = 'socolissimo';
		foreach (self::getCarriers($id_lang, true, false, false, null, self::ALL_CARRIERS) as $c)
		{

			if (Tools::strtolower(str_replace(' ', '', $c['external_module_name'])) == $module_name)
				return $c['id_carrier'];
		}
		return false;
	}

	public static function getIdByCarrierName($name, $id_lang)
	{
		$name = Tools::strtolower(str_replace(' ', '', $name));
		foreach (self::getCarriers($id_lang, true, false, false, null, self::ALL_CARRIERS) as $c)
		{
			if (Tools::strtolower(str_replace(' ', '', $c['name'])) == $name)
				return $c['id_carrier'];
		}
		return false;
	}

	/**
	 * Returns the carrier received from the marketplace and returns the matched prestashop carrier if found
	 * @param string $mp_carrier 
	 * @return int carrier id
	 */
	public static function matchMpCarrier($mp_carrier, $id_lang)
	{
		$id_carrier = self::getIdByCarrierName($mp_carrier, $id_lang);
		if (!$id_carrier)
			$id_carrier = self::getIdByModuleName($mp_carrier, $id_lang);
		
		return $id_carrier;
	}
}