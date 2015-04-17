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
			{l s='Go to' mod='sociallogin'} <a href="https://dev.twitter.com/apps" target="_blank">{l s='Twitter Developers' mod='sociallogin'}</a>
			. {l s='and login with your credentials' mod='sociallogin'}.
		</li>
		<li>
			{l s='Click on the "Create New App" button' mod='sociallogin'}.
		</li>
		<li>
			{l s='Fill out all the required fields and' mod='sociallogin'}:
			<br />
			{l s='Type in "Website"' mod='sociallogin'}: 
			<input class="fixed-width-xxl" type="text" readonly="readonly" onclick="this.focus();this.select()" value="{$link->getPageLink('index', false)|escape:'html'}"></input>
			<br />
			{l s='In the "Callback URL" field' mod='sociallogin'}: 
			<input type="text" readonly="readonly" onclick="this.focus();this.select()" value="{$link->getModuleLink('sociallogin', 'login', ['p' => 'twitter'], true)|escape:'html':'UTF-8'}"></input>
			<br />
			{l s='Read and agree to rules, and then "Create your Twitter application"' mod='sociallogin'}.
			<br />
			{l s='Go to "Settings" tab, check the option "Allow this application to be used to Sign in with Twitter" and click on "Update settings"' mod='sociallogin'}.
		</li>
		<li>
			{l s='Copy "API key" and "API secret" under "API Keys" menu' mod='sociallogin'}.
		</li>
	</ol>
	</div>
</div>