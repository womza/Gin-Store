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

{if isset($customer_log)}
<div class="panel col-lg-6">
	<div class="panel-heading">
		<i class="icon-thumbs-up"></i> {l s='Social Login' mod='sociallogin'}
	</div>
	<div class="panel-body">
		<table class="table">
			<thead>
				<tr>
					<th>{l s='Network' mod='sociallogin'}</th>
					<th>{l s='Connected' mod='sociallogin'}</th>
					<th>{l s='Profile' mod='sociallogin'}</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$customer_log key=key item=item}
				<tr>
					<td>{$key|escape:'html'|capitalize}</td>
					<td>{if $item}<i class="icon-check"></i>{else}<i class="icon-remove"></i>{/if}</td>
					<td>
						{if $item}
							{if $key == 'facebook'}
								<a href="https://www.facebook.com/{$item|escape:'html'}" target="_blank">{l s='View profile' mod='sociallogin'}</a>
							{elseif $key == 'google'}
								<a href="https://plus.google.com/{$item|escape:'html'}" target="_blank">{l s='View profile' mod='sociallogin'}</a>
							{elseif $key == 'linkedin'}
								<a href="https://www.linkedin.com/profile/view?id={$item|escape:'html'}" target="_blank">{l s='View profile' mod='sociallogin'}</a>
							{elseif $key == 'twitter'}
								<a href="https://twitter.com/intent/user?user_id={$item|escape:'html'}" target="_blank">{l s='View profile' mod='sociallogin'}</a>
							{else}
								{$item|escape:'html'}
							{/if}
						{/if}
					</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</div>
{/if}