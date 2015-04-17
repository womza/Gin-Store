<?php
/**
 * Copyright 2014 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *	 http://www.apache.org/licenses/LICENSE-2.0
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

@set_time_limit(0);
$sep = DIRECTORY_SEPARATOR;
require_once '..'.$sep.'..'.$sep.'..'.$sep.'config'.$sep.'config.inc.php';
require_once '..'.$sep.'..'.$sep.'..'.$sep.'init.php';
require_once '..'.$sep.'lengow.php';

require_once '..'.$sep.'loader.php';
try
{
	loadFile('core');
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

if (LengowCore::checkIP())
{
	$import = new LengowImport();
	if (Tools::getValue('idFlux') && is_numeric(Tools::getValue('idFlux')) && Tools::getValue('idOrder'))
	{		
		if (Tools::getValue('force') && Tools::getValue('force') > 0)
			$import->forceImport = true;

		$import->exec('singleOrder',
						array(
							'feed_id' => (int)Tools::getValue('idFlux'),
							'orderid' => Tools::getValue('idOrder')
							));
	}
	elseif (!Tools::getValue('idFlux') && !Tools::getValue('idOrder'))
	{
		$date_to = date('Y-m-d');
		$days = (integer)LengowCore::getCountDaysToImport();
		if (Tools::getValue('days'))
			$days = (integer)Tools::getValue('days');
		if ($id_shop = Tools::getValue('shop'))
			if ($shop = new Shop($id_shop))
				Context::getContext()->shop = $shop;
	
		$date_from = date('Y-m-d', strtotime(date('Y-m-d').' -'.$days.'days'));
		$import->exec('orders', array('dateFrom' => $date_from,
										'dateTo' => $date_to,
									));
		LengowCore::setImportEnd();	
	}
	else
		die('Invalid arguments');
}
else
	die('Unauthorized access for IP : '.$_SERVER['REMOTE_ADDR']);