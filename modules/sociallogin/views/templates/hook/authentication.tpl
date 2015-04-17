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

{if !$logged && isset($id_user) && isset($network)}
<div class="alert alert-info">
	<h2>{l s='Complete your register' mod='sociallogin'}</h2>
	<ul>
		<li>{l s='Please, fill in some missing fields in the form to complete your registration with' mod='sociallogin'} {$network|escape:'html':'UTF-8'|capitalize}.</li>
		<li>{l s='After click on Register when you come back you will can login with your social account' mod='sociallogin'}.</li>
	</ul>
	<p><a class="alert-link" href="{$link->getPageLink('authentication', true)|escape:'html':'UTF-8'}" title="{l s='Back' mod='sociallogin'}">&laquo; {l s='Back' mod='sociallogin'}</a></p>
</div>

<input type="hidden" name="id_user" value="{$id_user|escape:'html':'UTF-8'}" />
<input type="hidden" name="network" value="{$network|escape:'html':'UTF-8'}" />
{/if}