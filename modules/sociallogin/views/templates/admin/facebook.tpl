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
				{l s='Go to' mod='sociallogin'} <a href="https://developers.facebook.com/apps/" target="_blank">{l s='Facebook Developer' mod='sociallogin'}</a>
				{l s='link and log in with your facebook credentials' mod='sociallogin'}.
			</li>
			<li>
				{l s='Click on' mod='sociallogin'} <b>"{l s='+ Create New App' mod='sociallogin'}"</b> {l s='button.  A pop-up box will appear, enter' mod='sociallogin'} <b>"{l s='Display Name' mod='sociallogin'}"</b> {l s='and select' mod='sociallogin'} <b>"{l s='Category' mod='sociallogin'}"</b> {l s='for app and hit' mod='sociallogin'} <b>"{l s='Create App' mod='sociallogin'}"</b> {l s='button' mod='sociallogin'}.
			</li>
			<li>
				{l s='Select' mod='sociallogin'} <b>"{l s='Settings' mod='sociallogin'}"</b> {l s='menu from left sidebar then Click on' mod='sociallogin'} <b>"{l s='+Add Platform' mod='sociallogin'}"</b>.
			</li>
			<li>
				{l s='Select' mod='sociallogin'} <b>"{l s='Website' mod='sociallogin'}"</b> {l s='platform' mod='sociallogin'}"</b>.
				<br />
				{l s='Enter this in' mod='sociallogin'} <b>"{l s='App Domains' mod='sociallogin'}"</b>: 
				<input class="fixed-width-xxl" type="text" readonly="readonly" onclick="this.focus();this.select()" value="{$shop->domain|escape:'html'}"></input>
				<br />
				{l s='Enter this in' mod='sociallogin'} <b>"{l s='Site URL' mod='sociallogin'}"</b>: 
				<input class="fixed-width-xxl" type="text" readonly="readonly" onclick="this.focus();this.select()" value="{$link->getPageLink('index', false)|escape:'html'}"></input>
				<br />
				{l s='After that click on' mod='sociallogin'} <b>"{l s='Save Changes' mod='sociallogin'}"</b> {l s='button' mod='sociallogin'}.
				<br />
				<br />
				{l s='NOTE' mod='sociallogin'}: {l s='Enter your e-mail in' mod='sociallogin'} <b>"{l s='Contact Email' mod='sociallogin'}"</b> {l s='to make app availble to all user' mod='sociallogin'}.
			</li>
			<li>
				{l s='Select' mod='sociallogin'} <b>"{l s='Status & Review' mod='sociallogin'}"</b> {l s='menu at left sidebar and change' mod='sociallogin'} <b>"{l s='App status' mod='sociallogin'}"</b> {l s='to' mod='sociallogin'} <b>"{l s='Yes' mod='sociallogin'}"</b>. {l s='A pop-up box will appear for confirmation and hit' mod='sociallogin'} <b>"{l s='Confirm' mod='sociallogin'}"</b> {l s='button in the popup' mod='sociallogin'}.
			</li>
			<li>
				{l s='Select "Dashboard" menu from left sidebar. Add' mod='sociallogin'} <b>"{l s='API Key' mod='sociallogin'}"</b> {l s='and' mod='sociallogin'} <b>"{l s='Secret Key' mod='sociallogin'}"</b> {l s='to this form' mod='sociallogin'}.
			</li>
		</ol>
	</div>
</div>