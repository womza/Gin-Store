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
	loadFile('export');
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


$lengow = new Lengow();
/* CheckIP */
if (LengowCore::checkIP())
{
	/* Force GET parameters */
	$format = null;
	if (Tools::getValue('format'))
		$format = Tools::getValue('format');

	$fullmode = null;
	if (Tools::getValue('mode') && Tools::getValue('mode') == 'full')
		$fullmode = true;
	else if (Tools::getValue('mode') && Tools::getValue('mode') == 'simple')
		$fullmode = false;

	$export_feature = null;
	if ($export_feature = Tools::getValue('feature'))
		$export_feature = true;

	$stream = null;
	if (Tools::getValue('stream') == '1')
		$stream = true;
	elseif (Tools::getValue('stream') === '0')
		$stream = false;

	$all = null;
	if (Tools::getValue('all'))
		$all = Tools::getValue('all');

	if ($id_shop = Tools::getValue('shop'))
		if ($shop = new Shop($id_shop))
			Context::getContext()->shop = $shop;

	if ($iso_code = Tools::getValue('cur'))
		if ($id_currency = Currency::getIdByIsoCode($iso_code))
			Context::getContext()->currency = new Currency($id_currency);

	if ($iso_code = Tools::getValue('lang'))
		if ($id_language = Language::getIdByIso($iso_code))
			Context::getContext()->language = new Language($id_language);

	$title = null;
	if (Tools::getValue('title') && Tools::getValue('title') == 'full')
		$title = true;
	elseif (Tools::getValue('title') && Tools::getValue('title') == 'simple')
		$title = false;

	$all_product = null;
	if (Tools::getValue('active') && Tools::getValue('active') == 'enabled')
		$all_product = false;
	elseif (Tools::getValue('active') && Tools::getValue('active') == 'all')
		$all_product = true;

	$out_stock = null;
	if (Tools::getValue('out_stock') === '0')
		$out_stock = false;
	elseif (Tools::getValue('out_stock') == '1')
		$out_stock = true;

	$product_ids = null;
	if (Tools::getValue('ids') && Tools::getValue('ids') != '')
	{
		$product_ids = explode(',', Tools::getValue('ids'));
		if (empty($product_ids))
			$product_ids = null;
	}

	$limit = null;
	if (Tools::getValue('limit') && Tools::getValue('limit') > 0)
		$limit = Tools::getValue('limit');

	$export = new LengowExport($format, $fullmode, $all, $stream, $title, $all_product, null, $limit, $product_ids, $out_stock);
	$export->exec();
}
else
	die('Unauthorized access for IP : '.$_SERVER['REMOTE_ADDR']);