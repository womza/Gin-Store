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

{if !$logged && !isset($smarty.get.create_account) && !isset($smarty.get.token) && $show_authentication_block}
{literal}
<script type="text/javascript">
	$(document).ready(function() {
		var txt = '';
		var title = '';
		var box_header = '';
		var box_footer = '';
		var authentication = '';
		var only_buttons = '';
		box_header += '<div class="box col-xs-12 col-sm-12" style="float:left;display:block">';
		title += '<h2 class="page-subheading">';
		title += '{/literal}{l s='Register or login with your account:' mod='sociallogin'}{literal}';
		title += '</h2>';
		txt += '<div class="col-xs-12 col-sm-12">';
		only_buttons += '<div style="float:right;padding:4px 5px">'{/literal}
		{foreach from=$social_networks item=item key=k}
			{if $item.complete_config}
				{literal}
				txt += '<div class="col-xs-6 col-sm-4 col-md-2">';
				txt += '<br />';
				txt += createButton('{/literal}{$item.name|escape:'html':'UTF-8'}{literal}',
				'{/literal}{$item.connect|escape:'html':'UTF-8'}{literal}',
				'{/literal}{$size|escape:'html':'UTF-8'}{literal}',
				'{/literal}{$button|escape:'html':'UTF-8'}{literal}',
				{/literal}{if $popup}'_blank'{else}'_self'{/if}{literal},
				'{/literal}{if $sign_in}{l s='Sign in with' mod='sociallogin'}{/if}{literal}');
				txt += '<br />';
				txt += '</div>';

				only_buttons += createButton('{/literal}{$item.name|escape:'html':'UTF-8'}{literal}',
				'{/literal}{$item.connect|escape:'html':'UTF-8'}{literal}',
				'sm',
				'1',
				{/literal}{if $popup}'_blank'{else}'_self'{/if}{literal},
				'');{/literal}
			{/if}
		{/foreach}
		{literal}
		only_buttons += '</div>';
		txt += '</div>';
		box_footer += '</div>';
		box_footer += '<br /><p>* {/literal}{l s='Only ask permission to obtain your contact information: name, surname, email, gender, birth date. Never post in your name without your permission or your request.' mod='sociallogin'}{literal}</p>';
		box_footer += '<div class="clearfix visible-xs-block"></div>';

		authentication = box_header+title+txt+box_footer;

		{/literal}{if in_array('authentication', $positions)}{literal}
			$('#authentication #center_column').prepend(authentication);
			$('#new_account_form').prepend(authentication);
		{/literal}{/if}
		{if in_array('next_login', $positions)}{literal}
			$('.header_user_info').before(only_buttons);
		{/literal}{/if}
		{if in_array('product', $positions)}{literal}
			$('#product #usefull_link_block').after(box_header+title+only_buttons+box_footer);
		{/literal}{/if}{literal}
	});
</script>
{/literal}
{/if}