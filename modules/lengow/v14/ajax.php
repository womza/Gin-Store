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

@set_time_limit(0);
$sep = DIRECTORY_SEPARATOR;
require_once '..'.$sep.'..'.$sep.'..'.$sep.'config'.$sep.'config.inc.php';
require_once '..'.$sep.'..'.$sep.'..'.$sep.'init.php';
require_once '..'.$sep.'lengow.php';
require_once _PS_MODULE_DIR_.'lengow'.$sep.'models'.$sep.'lengow.connector.class.php';
require_once _PS_MODULE_DIR_.'lengow'.$sep.'models'.$sep.'lengow.import.class.php';

$action = Tools::getValue('action');

switch ($action)
{
	case 'reimport_order':
		$error = false;
		$order_id = Tools::getValue('orderid');
		$order = new LengowOrder($order_id);
		$lengow_order_id = Tools::getValue('lengoworderid');
		$feed_id = Tools::getValue('feed_id');
		LengowCore::deleteProcessOrder($lengow_order_id);
		$import = new LengowImport();
		$new_lengow_order = $import->exec('commands', array('id_order_lengow' => $lengow_order_id, 'feed_id' => $feed_id));

		if ($new_lengow_order != false)
		{
			$id_state_cancel = Configuration::get('LENGOW_STATE_ERROR');
			$order->setCurrentState($id_state_cancel, (int)Context::getContext()->employee->id);
			$new_lengow_order_url = 'index.php?tab=AdminOrders&id_order='.$new_lengow_order.'&vieworder&token='.Tools::getAdminTokenLite('AdminOrders');
			$reimport_message = sprintf('You can see the new order by clicking here : <a href=\'%s\'>View Order %s</a>', $new_lengow_order_url, $new_lengow_order);
		}
		else
		{
			$error = true;
			$reimport_message = 'Error during import';
		}

		$result = array(
			'status' => ($error == false) ? 'success' : 'error',
			'msg' => $reimport_message,
			'new_order_url' => $new_lengow_order_url,
			'new_order_id' => $new_lengow_order
		);

		echo Tools::jsonEncode($result);
		break;
	default:
		$is_https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 's' : '';
		$lengow_connector = new LengowConnector((integer)LengowCore::getIdCustomer(), LengowCore::getTokenCustomer());
		$params = 'format='.Tools::getValue('format');
		$params .= '&mode='.Tools::getValue('mode');
		$params .= '&all='.Tools::getValue('all');
		$params .= '&shop='.Tools::getValue('shop');
		$params .= '&cur='.Tools::getValue('cur');
		$params .= '&lang='.Tools::getValue('lang');
		$new_flow = (defined('_PS_SHOP_DOMAIN_') ? 'http'.$is_https.'://'._PS_SHOP_DOMAIN_ : _PS_BASE_URL_).__PS_BASE_URI__.'modules/lengow/webservice/export.php?'.$params;
		$args = array(
					'idClient' => LengowCore::getIdCustomer(),
					'idGroup' => LengowCore::getGroupCustomer(),
					'urlFlux' => $new_flow
				);
		$data_flows = get_object_vars(Tools::jsonDecode(Configuration::get('LENGOW_FLOW_DATA')));
		if ($id_flow = Tools::getValue('idFlow'))
		{
			$args['idFlux'] = $id_flow;
			$data_flows[$id_flow] = array(
				'format' => Tools::getValue('format'),
				'mode' => Tools::getValue('mode') == 'yes' ? 1 : 0,
				'all' => Tools::getValue('all') == 'yes' ? 1 : 0 ,
				'currency' => Tools::getValue('cur') ,
				'shop' => Tools::getValue('shop') ,
				'language' => Tools::getValue('lang') ,
			);
			Configuration::updateValue('LENGOW_FLOW_DATA', Tools::jsonEncode($data_flows));
		}
		if ($call = $lengow_connector->api('updateRootFeed', $args))
			echo Tools::jsonEncode(array('return' => true, 'flow' => $new_flow));
		else
			echo Tools::jsonEncode(array('return' => false));
		break;
}