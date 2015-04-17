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

{capture name=path}
	<a href="{$link->getPageLink('my-account', true)|escape:'html'}" title="{l s='My account' mod='sociallogin'}" rel="nofollow">
		{l s='My account' mod='sociallogin'}
	</a>
	<span class="navigation-pipe">
		{$navigationPipe|escape:'html':'UTF-8'}
	</span>
	{l s='Social Account Linking' mod='sociallogin'}
{/capture}

{assign var=configure_url value=[
	'facebook' => 'https://www.facebook.com/settings?tab=applications',
	'google' => 'https://plus.google.com/apps',
	'linkedin' => 'https://www.linkedin.com/secure/settings?userAgree',
	'microsoft' => 'https://account.live.com/consent/Manage',
	'twitter' => 'https://twitter.com/settings/applications',
	'yahoo' => 'https://api.login.yahoo.com/WSLogin/V1/unlink']}

<h1 class="page-subheading">{l s='Social Account Linking' mod='sociallogin'}</h1>
<p>{l s='Here you will connect your social accounts to login easily in our shop.' mod='sociallogin'}</p>
<div class="box">
	<div class="panel panel-default">
		<table class="table">
			<thead>
				<tr>
					<th>{l s='Network'  mod='sociallogin'}</th>
					<th>{l s='Action'  mod='sociallogin'}</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$social_networks item=item key=k}
					{if $item.complete_config}
					<tr>
						<td>
							<p class="btn btn-social-icon btn-{if $item.name == 'google'}google-plus{else}{$item.name|escape:'html':'UTF-8'}{/if}">
								<i class="fa fa-{if $item.name == 'microsoft'}windows{elseif $item.name == 'google'}google-plus{else}{$item.name|escape:'html':'UTF-8'}{/if}"></i>
							</p>
						</td>
						<td>
							{if !$customer_log[$item.name]}
								<button class="btn btn-success" onclick="connectSocial('{$item.connect|escape:'html':'UTF-8'}', '{l s='Confirm that you want to connect %s to your account' sprintf=$item.name|escape:'html':'UTF-8'|capitalize mod='sociallogin'}', {if isset($popup) && $popup}'_blank'{else}'_self'{/if})">
									<i class="icon-ok"></i> {l s='Connect' mod='sociallogin'}
								</button>
							{else}
								<button class="btn btn-danger" onclick="deleteSocial('{$item.delete|escape:'html':'UTF-8'}', '{l s='Confirm that you want to disconnet %s from your account' sprintf=$item.name|escape:'html':'UTF-8'|capitalize mod='sociallogin'}')">
									<i class="icon-remove"></i> {l s='Disconnect' mod='sociallogin'}
								</button>
							{/if}
							{if isset($configure_url[$item.name])}
							<a class="btn btn-link" href="{$configure_url[$item.name]|escape:'html':'UTF-8'}" title="{l s='Configure' mod='sociallogin'} {$item.name|escape:'html':'UTF-8'|capitalize}" target="_blank">
								<i class="icon-cog"></i>
							</a>
							{/if}
						</td>
					</tr>
					{/if}
				{/foreach}
			</tbody>
		</table>
	</div>
	<p>&nbsp;</p>
	<p class="alert alert-warning">{l s='You can connect your social accounts, but if you have your email in other registered customer account you will login in the other account. Remember that duplicated account is prohibited in our terms and conditions.' mod='sociallogin'}</p>
</div>
<ul class="footer_links clearfix">
	<li>
		<a class="btn btn-defaul button button-small" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
			<span><i class="icon-chevron-left"></i> {l s='Back to your account' mod='sociallogin'}</span>
		</a>
	</li>
	<li>
		<a class="btn btn-defaul button button-small" href="{$base_dir|escape:'html':'UTF-8'}">
			<span><i class="icon-chevron-left"></i> {l s='Home' mod='sociallogin'}</span>
		</a>
	</li>
</ul>