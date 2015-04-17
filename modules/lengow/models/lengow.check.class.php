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
	loadFile('option');
	loadFile('connector');
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

class LengowCheck {

	static private $_module = '';

	public static $XML_PLUGINS = 'plugins.xml';
	public static $DOM;

	private static $_FILES_TO_CHECK = array (
									'lengow.customer.class.php',
									);
	/**
	* Get header table
	*
	* @return string
	*/
	private static function _getAdminHeader()
	{
		return '<table class="table" cellpadding="0" cellspacing="0"><tbody>';
	}

	/**
	* Get HTML Table content of checklist
	*
	* @param array $checklist
	* @return string|null PS_MAIL_METHOD
	*/
	private static function _getAdminContent($checklist = array())
	{
		if (empty($checklist))
			return null;

		$out = '';
		foreach ($checklist as $check)
		{
			$out .= '<tr>';
			$out .= '<td><b>'.$check['message'].'</b></td>';
			if ($check['state'] == 1)
				$out .= '<td><img src="'._PS_BASE_URL_.__PS_BASE_URI__.'/img/admin/enabled.gif" alt="ok"></td>';
			elseif ($check['state'] == 2)
				$out .= '<td><img src="'._PS_BASE_URL_.__PS_BASE_URI__.'/img/admin/error.png" alt="warning"></td>';
			else
				$out .= '<td><img src="'._PS_BASE_URL_.__PS_BASE_URI__.'/img/admin/disabled.gif" alt="nok"></td>';
			$out .= '</tr>';

			if ($check['state'] === 0 || $check['state'] === 2)
			{
				$out .= '<tr><td colspan="2"><p>'.$check['help'];
				if (array_key_exists('help_link', $check) && $check['help_link'] != '')
					$out .= '<br /><a target="_blank" href="'.$check['help_link'].'">'.$check['help_label'].'</a>';
				$out .= '</p></td></tr>';
			}

			if (array_key_exists('additional_infos', $check))
			{
				$out .= '<tr><td colspan="2"><p>';
				$out .= $check['additional_infos'];
				$out .= '</p></td></tr>';
			}
		}

		return $out;
	}

	/**
	* Get footer table
	*
	* @return string
	*/
	private static function _getAdminFooter()
	{
		return '</tbody></table>';
	}

	/**
	* Get mail configuration informations
	*
	* @return string
	*/
	public static function getMailConfiguration()
	{
		$mail_method = Configuration::get('PS_MAIL_METHOD');
		if ($mail_method == 2)
			return self::$_module->l('Email are enabled with custom settings.', 'lengow.check.class');
		elseif ($mail_method == 3 && _PS_VERSION_ >= '1.5.0')
			return self::$_module->l('Email are desactived.', 'lengow.check.class');
		elseif ($mail_method == 3)
			return self::$_module->l('Error mail settings, PS_MAIL_METHOD is 3 but this value is not allowed in Prestashop 1.4', 'lengow.check.class');
		else
			return self::$_module->l('Email using php mail function.', 'lengow.check.class');
	}

	/**
	* Check if PHP Curl is activated
	*
	* @return boolean
	*/
	public static function isCurlActivated()
	{
		return function_exists('curl_version');
	}

	/**
	* Check if SimpleXML Extension is activated
	*
	* @return boolean
	*/
	public static function isSimpleXMLActivated()
	{
		return function_exists('simplexml_load_file');
	}

	/**
	* Check if SimpleXML Extension is activated
	*
	* @return boolean
	*/
	public static function isJsonActivated()
	{
		return function_exists('json_decode');
	}

	/**
	* Check if shop functionality are enabled
	*
	* @return boolean
	*/
	public static function isShopActivated()
	{
		if (Configuration::get('PS_CATALOG_MODE'))
			return false;
		return true;
	}

	/**
	* Check API Authentification
	*
	* @return boolean
	*/
	public static function isValidAuth()
	{
		if (!self::isCurlActivated())
			return false;

		$id_customer = (int)Configuration::get('LENGOW_ID_CUSTOMER');
		$token = Configuration::get('LENGOW_TOKEN');
		$connector = new LengowConnector($id_customer, $token);
		$result = $connector->api('authentification');
		if ($result['return'] == 'Ok')
			return true;
		else
			return $result['ip'];
	}

	/**
	* Get website IP
	*
	* @return string IP Address
	*/
	public static function getWebsiteAddress()
	{
		if (!self::isCurlActivated())
			return false;

		// Fake customer id to force API to return IP
		$id_customer = 0;
		$token = Configuration::get('LENGOW_TOKEN');

		$connector = new LengowConnector((int)$id_customer, $token);
		$result = $connector->api('authentification');

		if (is_array($result) && array_key_exists('ip', $result))
			return $result['ip'];
		else
			return 'IP not found';
	}

	/**
	* Check if config folder is writable
	*
	* @return boolean
	*/
	public static function isConfigWritable()
	{
		$config_folder = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config';
		return is_writable($config_folder);
	}

	/**
	* Check disabled product option
	*
	* @return boolean
	*/
	public static function isDisabledProduct()
	{
		return (Configuration::get('LENGOW_EXPORT_DISABLED') == true) ? false : true;
	}

	/**
	* Get array of requirements and their status
	*
	* @return array
	*/
	private static function _getCheckListArray()
	{
		$checklist = array();

		self::$_module = new Lengow();

		$checklist[] = array(
			'message' => self::$_module->l('Lengow needs the CURL PHP extension', 'lengow.check.class'),
			'help' => self::$_module->l('The CURL extension is not installed or enabled in your PHP installation. Check the manual for information on how to install or enable CURL on your system.', 'lengow.check.class'),
			'help_link' => 'http://www.php.net/manual/en/curl.setup.php',
			'help_label' => self::$_module->l('Go to Curl PHP extension manual', 'lengow.check.class'),
			'state' => (int)self::isCurlActivated()
		);
		$checklist[] = array(
			'message' => self::$_module->l('Lengow needs the SimpleXML PHP extension', 'lengow.check.class'),
			'help' => self::$_module->l('The SimpleXML extension is not installed or enabled in your PHP installation. Check the manual for information on how to install or enable SimpleXML on your system.', 'lengow.check.class'),
			'help_link' => 'http://www.php.net/manual/en/book.simplexml.php',
			'help_label' => self::$_module->l('Go to SimpleXML PHP extension manual', 'lengow.check.class'),
			'state' => (int)self::isSimpleXMLActivated()
		);
		$checklist[] = array(
			'message' => self::$_module->l('Lengow needs the JSON PHP extension', 'lengow.check.class'),
			'help' => self::$_module->l('The JSON extension is not installed or enabled in your PHP installation. Check the manual for information on how to install or enable JSON on your system.', 'lengow.check.class'),
			'help_link' => 'http://www.php.net/manual/fr/book.json.php',
			'help_label' => self::$_module->l('Go to JSON PHP extension manual', 'lengow.check.class'),
			'state' => (int)self::isJsonActivated()
		);
		$checklist[] = array(
			'message' => self::$_module->l('Lengow authentification', 'lengow.check.class'),
			'help' => self::$_module->l('Please check your Client ID, Group ID and Token API.', 'lengow.check.class'),
			'help_link' => 'https://solution.lengow.com/api/',
			'help_label' => self::$_module->l('Go to Lengow dashboard', 'lengow.check.class'),
			'state' => (int)self::isValidAuth() == 1 ? 1 : 0,
			'additional_infos' => sprintf(self::$_module->l('Make sure your website IP (%s) address is filled in your Lengow Dashboard.', 'lengow.check.class'), self::getWebsiteAddress())
		);
		$checklist[] = array(
			'message' => self::$_module->l('Shop functionality', 'lengow.check.class'),
			'help' => self::$_module->l('Shop functionality are disabled, order import will be impossible, please enable them in your products settings.', 'lengow.check.class'),
			'state' => (int)self::isShopActivated()
		);
		$checklist[] = array(
			'message' => self::$_module->l('Config folder is writable', 'lengow.check.class'),
			'help' => self::$_module->l('The config folder must be writable.', 'lengow.check.class'),
			'state' => (int)self::isConfigWritable()
		);
		$checklist[] = array(
			'message' => self::$_module->l('Export disabled products', 'lengow.check.class'),
			'help' => self::$_module->l('Disabled product are enabled in export, Marketplace order import will not work with this configuration.', 'lengow.check.class'),
			'state' => (int)self::isDisabledProduct()
		);
		$checklist[] = array(
			'message' => self::$_module->l('Prestashop plugin version', 'lengow.check.class'),
			'help' => self::$_module->l('There is a new version of Lengow Module, please update it.', 'lengow.check.class'),
			'help_link' => 'http://www.lengow.fr/plugin-prestashop.html',
			'help_label' => self::$_module->l('Download the latest version', 'lengow.check.class'),
			'state' => (int)self::checkPluginVersion(self::$_module->version)
		);
		$files = self::checkFiles();
		$checklist[] = array(
			'message' => self::$_module->l('Module files check', 'lengow.check.class'),
			'help'	=> self::$_module->l('Please move the following files from the install to the override folder of your Lengow module : '.implode(', ', $files), 'lengow.check.class'),
			'state' => empty($files) ? 1 : 0,
		);
		if (Configuration::get('LENGOW_DEBUG'))
		{
			$checklist[] = array(
				'message' => self::$_module->l('Mail configuration (Be carefull, debug mode is activated)', 'lengow.check.class'),
				'help' => self::getMailConfiguration(),
				'state' => 2
			);
		}

		return $checklist;
	}

	/**
	* Get admin table html
	*
	* @return string Html table
	*/
	public static function getHtmlCheckList()
	{
		$out = '';
		$out .= self::_getAdminHeader();
		$out .= self::_getAdminContent(self::_getCheckListArray());
		$out .= self::_getAdminFooter();
		return $out;
	}

	/**
	* Get check list json
	*
	* @return string Json
	*/
	public static function getJsonCheckList()
	{
		return Tools::jsonEncode(self::_getCheckListArray());
	}

	/**
	* Check module version
	*
	* @return boolean true if up to date, false if old version currently installed
	*/
	public static function checkPluginVersion($current_version = null)
	{
		if ($current_version == null)
			return false;

		// Load xml
		try {
			if (_PS_MODULE_DIR_)
				self::$DOM = simplexml_load_file(_PS_MODULE_DIR_.'lengow'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.self::$XML_PLUGINS);
			else
				self::$DOM = simplexml_load_file(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.self::$XML_PLUGINS);
		} catch (Exception $e) {
			LengowCore::log('Unable to download plugins.xml => '.$e->getMessage());
			return true;
		}


		// Compare version
		if (is_object(self::$DOM))
		{
			$object = self::$DOM->xpath('/plugins/plugin[@name=\'prestashop\']');
			if (!empty($object))
			{
				$plugin = $object[0];
				if (version_compare($current_version, $plugin->version, '<'))
					return false;
				else
					return true;
			}
		}
		return true;
	}

	/**
	* Show import logs
	*
	* @return string Html Content
	*/
	public static function getHtmlLogs($days = 10, $show_extra = false)
	{
		if (Tools::getValue('delete') != '')
			LengowCore::deleteProcessOrder(Tools::getValue('delete'));

		$db = Db::getInstance();

		$sql_logs = 'SELECT * FROM '._DB_PREFIX_.'lengow_logs_import '
				.' WHERE TO_DAYS(NOW()) - TO_DAYS(date) <= '.(int)$days
				.' ORDER BY `date` DESC';

		$results = $db->ExecuteS($sql_logs);

		echo '<style type="text/css">
			table.gridtable {
				font-family: verdana,arial,sans-serif;
				font-size:11px;
				color:#333333;
				border-width: 1px;
				border-color: #666666;
				border-collapse: collapse;
			}
			table.gridtable th {
				border-width: 1px;
				padding: 8px;
				border-style: solid;
				border-color: #666666;
				background-color: #dedede;
			}
			table.gridtable td {
				border-width: 1px;
				padding: 8px;
				border-style: solid;
				border-color: #666666;
				background-color: #ffffff;
			}
			</style>';

		if (!empty($results))
		{
			echo '<table class="gridtable">';
			echo '<tr>';
			echo '<th>Lengow Order ID</th>';
			echo '<th>Is processing</th>';
			echo '<th>Is finished</th>';
			echo '<th>Message</th>';
			echo '<th>Date</th>';
			echo '<th>Action</th>';
			if ($show_extra == true)
				echo '<th>Extra</th>';
			echo '</tr>';
			foreach ($results as $row)
			{
				echo '<tr>';
				echo '<td>'.$row['lengow_order_id'].'</td>';
				echo '<td>'.($row['is_processing'] == 1 ? 'Yes' : 'No').'</td>';
				echo '<td>'.($row['is_finished'] == 1 ? 'Yes' : 'No').'</td>';
				echo '<td>'.$row['message'].'</td>';
				echo '<td>'.$row['date'].'</td>';
				if ($show_extra == true)
					echo '<td>'.$row['extra'].'</td>';
				echo '<td><a href="?action=logs&delete='.$row['lengow_order_id'].'">Supprimer</a></td>';
				echo '</tr>';
			}
			echo '</table>';
		}
	}

	public static function checkFiles()
	{
		$sep = DIRECTORY_SEPARATOR;
		$files_install = array();
		foreach (self::$_FILES_TO_CHECK as $file)
		{
			if (!file_exists(_PS_MODULE_DIR_.'lengow'.$sep.'override'.$sep.$file) && file_exists(_PS_MODULE_DIR_.'lengow'.$sep.'install'.$sep.$file))
				$files_install[] = $file;
		}
		return $files_install;
	}

}
