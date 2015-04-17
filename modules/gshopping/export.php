<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/gshopping.php');

ini_set ('max_execution_time', 5400);

$gShopping = new GShopping();
if ($gShopping->active)
	$gShopping->exportCatalog(substr(Tools::getValue('lang'), 0, 2), substr(Tools::getValue('currency'), 0, 3));