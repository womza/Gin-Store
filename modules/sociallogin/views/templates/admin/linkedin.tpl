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
			{l s='Go to' mod='sociallogin'} <a href="https://www.linkedin.com/secure/developer" target="_blank">{l s='LinkedIn developers' mod='sociallogin'}</a>
			. {l s='Click on "API Keys" under "Support" tab and login with you account' mod='sociallogin'}.
		</li>
		<li>
			{l s='After logging in, click on "Add New Application"' mod='sociallogin'}.
		</li>
		<li>
			{l s='Fill out all the required fields and enter' mod='sociallogin'}:
			<br />
			{l s='Enter in "Website URL"under "Application Info"' mod='sociallogin'}: 
			<input class="fixed-width-xxl" type="text" readonly="readonly" onclick="this.focus();this.select()" value="{$link->getPageLink('index', false)|escape:'html'}"></input>
			<br />
			{l s='Enter in "OAuth 2.0 Redirect URLs" under "OAuth User Agreement"' mod='sociallogin'}: 
			<input type="text" readonly="readonly" onclick="this.focus();this.select()" value="{$link->getModuleLink('sociallogin', 'login', ['p' => 'linkedin'], true)|escape:'html':'UTF-8'}"></input>
			<br />
			{l s='After that hit "Save" button.' mod='sociallogin'}
		</li>
		<li>
			{l s='You will see the "API Key" and "Secret Key" for your application which you can copy and add bellow' mod='sociallogin'}.
		</li>
	</ol>
	</div>
</div>