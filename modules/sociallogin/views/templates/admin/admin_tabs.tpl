{*
* 2015 Jorge Vargas
*
* NOTICE OF LICENSE
*
* This source file is subject to the End User License Agreement (EULA)
*
* See attachmente file LICENSE
*
* @author    Jorge Vargas <jorgevargaslarrota@hotmail.com>
* @copyright 2007-2015 Jorge Vargas
* @license   End User License Agreement (EULA)
* @package   sociallogin
* @version   1.0
*}

<ul class="nav nav-tabs" role="tablist" id="myTab">
	<li class="active"><a role="tab" data-toggle="tab" href="#home">Home</a></li>
	{foreach from=$social_networks item=item key=k}
	<li><a role="tab" data-toggle="tab" href="#{$item.name}">{$item.name|capitalize}</a></li>
	{/foreach}
</ul>