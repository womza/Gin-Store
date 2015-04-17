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

/**
 * The Lengow Tax Class.
 *
 * @author Romain Le Polh <romain@lengow.com>
 * @copyright 2013 Lengow SAS
 */
class LengowTaxRuleAbstract extends TaxRule
{
	/**
	* Get all tax rules for specific group id
	*
	* @param int $id_lang
	* @param int $id_group
	* @return array list of tax rules
	*/
	public static function getLengowTaxRulesByGroupId($id_lang, $id_group)
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			return Db::getInstance()->executeS('
			SELECT g.`id_tax_rule`,
							 c.`name` AS country_name,
							 s.`name` AS state_name,
							 t.`rate`,
							 g.`state_behavior`,
							 g.`id_country`,
							 g.`id_state`
			FROM `'._DB_PREFIX_.'tax_rule` g
			LEFT JOIN `'._DB_PREFIX_.'country_lang` c ON (g.`id_country` = c.`id_country` AND `id_lang` = '.(int)$id_lang.')
			LEFT JOIN `'._DB_PREFIX_.'state` s ON (g.`id_state` = s.`id_state`)
			LEFT JOIN `'._DB_PREFIX_.'tax` t ON (g.`id_tax` = t.`id_tax`)
			WHERE `id_tax_rules_group` = '.(int)$id_group.'
			ORDER BY `country_name` ASC, `state_name` ASC'
			);
		}
		return TaxRule::getTaxRulesByGroupId($id_lang, $id_group);
	}
}