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
			{l s='Go to' mod='sociallogin'} <a href="http://developer.yahoo.com/" target="_blank">{l s='Yahoo Developer Network' mod='sociallogin'}</a>
			. {l s='Click on "My Projects" under your profile menu' mod='sociallogin'}.
		</li>
		<li>
			{l s='Click on the "Create a Project" button' mod='sociallogin'}.
		</li>
		<li>
			{l s='Fill out all the required fields and select' mod='sociallogin'}:
			<br />
			{l s='Under "Application Type"' mod='sociallogin'}: <i>{l s='Web-based' mod='sociallogin'}.</i>
			<br />
			{l s='Type in "Home Page URL"' mod='sociallogin'}: 
			<input class="fixed-width-xxl" type="text" readonly="readonly" onclick="this.focus();this.select()" value="{$link->getPageLink('index', false)|escape:'html'}"></input>
			<br />
			{l s='Under "Access Scopes" select' mod='sociallogin'}: <i>{l s='This app requires access to private user data' mod='sociallogin'}.</i>
			<br />
			{l s='Fill the "Callback Domain"' mod='sociallogin'}: 
			<input type="text" readonly="readonly" onclick="this.focus();this.select()" value="{$link->getModuleLink('sociallogin', 'login', ['p' => 'yahoo'], true)|escape:'html':'UTF-8'}"></input>
			<br />
			{l s='Under "Select APIs for private user data access" select' mod='sociallogin'}: {l s='"Contacts" and "Social Directory (Profiles)" with "Read" permission' mod='sociallogin'}.
			<br />
			{l s='Fill all the other required fields and then click on "Crate Project"' mod='sociallogin'}.
		</li>
		<li>
			{l s='In "APIs and Services" menu, under "Authentication Information: OAuth" copy and paste bellow the "Consumer Key" and "Consumer Secret"' mod='sociallogin'}.
		</li>
		<li>
			{l s='Follow the instructions in' mod='sociallogin'} <a target="_blank" href="https://developer.apps.yahoo.com/manage">https://developer.apps.yahoo.com/manage</a> {l s='to verify your domain' mod='sociallogin'}.
		</li>
	</ol>
	</div>
</div>