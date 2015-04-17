<?php

if (version_compare(_PS_VERSION_, '1.4.0.2', '>=') && version_compare(_PS_VERSION_, '1.4.1.0', '<=') && !class_exists('CartCore'))
{
	include_once(dirname(__FILE__).'/../../../../classes/Cart.php');
}

if (version_compare(_PS_VERSION_, '1.4.0.2', '>='))
{
	
	class Cart extends CartCore
	{


	}
}
?>