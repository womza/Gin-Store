<?php
/**
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

/**
 * Function used to update your module from previous versions to the version 1.0.8,
 * Don't forget to create one file per version.
 */
function upgrade_module_1_0_9()
{
	/**
	 * Do everything you want right there,
	 * You could add a column in one of your module's tables
	 */
	$sql = array();

	$sql[] = 'ALTER TABLE `'._DB_PREFIX_.'social_login_customer`
		ADD INDEX (`id_shop`);';
	$sql[] = 'ALTER TABLE `'._DB_PREFIX_.'social_login_customer`
		ADD CONSTRAINT `'._DB_PREFIX_.'social_login_customer_fk1`
		FOREIGN KEY (`id_shop`) REFERENCES `'._DB_PREFIX_.'shop` (`id_shop`) ON DELETE CASCADE ON UPDATE CASCADE;';
	$sql[] = 'ALTER TABLE `'._DB_PREFIX_.'social_login_customer`
		ADD CONSTRAINT `'._DB_PREFIX_.'social_login_customer_fk2`
		FOREIGN KEY (`id_customer`) REFERENCES `'._DB_PREFIX_.'customer` (`id_customer`) ON DELETE CASCADE ON UPDATE CASCADE;';

	foreach ($sql as $query)
		if (Db::getInstance()->execute($query) == false)
			return false;

	return true;
}
