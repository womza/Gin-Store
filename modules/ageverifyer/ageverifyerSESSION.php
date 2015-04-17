<?php
include( '../../config/config.inc.php' );
if ( !defined( '_PS_VERSION_' ) )
  exit;


//get token
$tokenModule = Tools::getValue('token');
if( Tools::getToken(false) != $tokenModule)
{ 
 die("No Valid Token");
} 


//init context for cookie
$context = Context::getContext();



if(Tools::getValue("over18") && Tools::getValue("day") &&  Tools::getValue("month") &&  Tools::getValue("year") )
{
	
	//age to check against
	$ageAllowed = 18;
	//get formular data
	$monthCustomer=(int)Tools::getValue('month');            
	$dayCustomer= (int)Tools::getValue('day');
	$yearCustomer= (int)Tools::getValue('year');
	
	//get actual date
	$actualMonth=date("n");           
	$actualDay=date("j");
	$actualYear=date("Y");
	
	$auth = null;
	
	$yearDifference=$actualYear-$yearCustomer;
	
	//checkAge depending on data
	if($yearDifference < 120)              
	{
	    if($yearDifference>$ageAllowed)   
		{
			$auth = "TRUE";
		}
		else if($yearDifference==$ageAllowed AND $monthCustomer<$actualMonth) 
		{
			$auth = "TRUE";
		}
		else if($yearDifference==$ageAllowed AND $monthCustomer==$actualMonth AND $dayCustomer<=$actualDay) 
		{
			$auth = "TRUE";
		}
	
	}
	
	//Set Cooki to be alid or invalid
	if (Tools::getValue("over18") == "TRUE" && $auth == "TRUE") 
	{
		$context->cookie->over18 = "TRUE";
	}
	else
	{
		$context->cookie->over18 = "FALSE";
	}
}
else if( Tools::getValue("over18") && Tools::getValue("magicWord") ) //light version check
{
	if(Tools::getValue("magicWord") ==  md5("myLightVersion"))
	{
		//Set Session to be valid or invalid
		if (Tools::getValue("over18") == "TRUE") 
		{
			$context->cookie->over18 = "TRUE";
		}
		else
		{
			$context->cookie->over18 = "FALSE";
		}
	}
	else
	{
		$context->cookie->over18 = "FALSE";
	}
}
else
{
	$context->cookie->__set("over18",null);
}
?>