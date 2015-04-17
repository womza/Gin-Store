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

<div class="panel">
	<div class="panel-heading">
		<i class="icon-info-sign"></i> {l s='Help' mod='sociallogin'}
	</div>
	<div class="panel-body">
	<ol>
		<li>
			{l s='Go to' mod='sociallogin'} <a href="https://account.live.com/developers/applications" target="_blank">{l s='Developer center Microsoft' mod='sociallogin'}</a>
			{l s='and login with your account' mod='sociallogin'}.
		</li>
		<li>
			{l s='Click on "Create application", then type the application name and select language, last click "Accept" button' mod='sociallogin'}.
		</li>
		<li>
			{l s='In left panel, click on "API Configuration" and' mod='sociallogin'}:
			<br />
			{l s='Click "Yes" under "Mobile client or desktop application", then' mod='sociallogin'}: 
			<br />
			{l s='Fill the "URL redirection"' mod='sociallogin'}: 
			<input type="text" readonly="readonly" onclick="this.focus();this.select()" value="{$link->getModuleLink('sociallogin', 'login', [], true)|escape:'html':'UTF-8'}"></input>
			<br />
			{l s='Press the "Save" button' mod='sociallogin'}.
		</li>
		<li>
			{l s='Copy and paste bellow the "Client Id" and "Client secret key" under "Application Settings" in left menu' mod='sociallogin'}.
		</li>
	</ol>
	</div>
</div>