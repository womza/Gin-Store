{*
 * Copyright 2014 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *	 http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 *  @author	   Ludovic Drin <ludovic@lengow.com> Romain Le Polh <romain@lengow.com>
 *  @copyright 2014 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}
 <script type="text/javascript" src="/modules/lengow/views/js/admin.js"></script>

{if isset($display_error)}
	{if $display_error}
		<div class="error">{l s='An error occured during the form validation' mod='lengow'}</div>
	{else}
		<div class="conf">{l s='Configuration updated' mod='lengow'}</div>
	{/if}
{/if}

<form id="_form" class="defaultForm lengow" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data" >
	<fieldset id="fieldset_0">
		<legend>{l s='Check configuration' mod='lengow'}</legend>
		<label>{l s='Checklist' mod='lengow'}</label>
		<div class="margin-form">
			{$checklist|escape:'string'}
		</div>
		<div class="clear"></div>
	</fieldset>
	<br />
	<fieldset id="fieldset_1">
		<legend>{l s='Account' mod='lengow'}</legend>
		<label>{l s='Customer ID' mod='lengow'}</label>
		<div class="margin-form">
			<input type="text" name="lengow_customer_id" id="lengow_customer_id" value="{$lengow_customer_id|escape:'str'}" class="" size="20" /> <sup>*</sup>
		</div>
		<div class="clear"></div>
		<label>{l s='Group ID' mod='lengow'}</label>
		<div class="margin-form">
			<input type="text" name="lengow_group_id" id="lengow_group_id" value="{$lengow_group_id|escape:'str'}" class="" size="20" /> <sup>*</sup>
		</div>
		<div class="clear"></div>
		<label>{l s='Token API' mod='lengow'}</label>
		<div class="margin-form">
			<input type="text" name="lengow_token" id="lengow_token" value="{$lengow_token|escape:'str'}" class="" size="32" /> <sup>*</sup>
		</div>
		<div class="clear"></div>
		<label>{l s='Help' mod='lengow'}</label>
		<div class="margin-form">
		{$help_credentials|escape:'str'}
		</div>
		<div class="clear"></div>
		<div class="small"><sup>*</sup> {l s='Required field' mod='lengow'}</div>
	</fieldset>
	<br />
	<fieldset id="fieldset_2">
		<legend>{l s='Security' mod='lengow'}</legend>
		<label>{l s='Authorized IP' mod='lengow'}</label>
		<div class="margin-form">
			<input type="text" name="lengow_authorized_ip" id="lengow_authorized_ip" value="{$lengow_authorized_ip|escape:'str'}" class="" size="100" />
		</div>
		<div class="clear"></div>
		<div class="small"><sup>*</sup> {l s='Required field' mod='lengow'}</div>
	</fieldset>
	<br />
	<fieldset id="fieldset_3">
		<legend>{l s='Tracking' mod='lengow'}</legend>
		<label>{l s='Tracker type choice' mod='lengow'}</label>
		<div class="margin-form">
			<select name="lengow_tracking" class="" id="lengow_tracking">
				{foreach from=$options.trackers item=option}
					<option value="{$option->id|escape:'str'}"{if $option->id == $lengow_tracking} selected="selected"{/if}>{$option->name|escape:'str'}</option>
				{/foreach}
			</select>
		</div>
		<div class="clear"></div>
		<div class="small"><sup>*</sup> {l s='Required field' mod='lengow'}</div>
	</fieldset>
	<br />
	<fieldset id="fieldset_4">
		<legend>{l s='Export parameters' mod='lengow'}</legend>
		<label>{l s='Export all products' mod='lengow'}</label>
		<div class="margin-form">
			<input type="radio" name="lengow_export_all" id="active_on" value="1" {if $lengow_export_all == 1} checked="checked"{/if} />
			<label class="t" for="active_on">
				<img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
			</label>
			<input type="radio" name="lengow_export_all" id="active_off" value="0" {if $lengow_export_all == 0} checked="checked"{/if} />
			<label class="t" for="active_off">
				<img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
			</label>
			<p class="preference_description">{l s='If don\'t want to export all your available products, click "no" and go onto Tab Prestashop to select yours products' mod='lengow'}</p>
		</div>
		<label>{l s='Export disabled products' mod='lengow'}</label>
		<div class="margin-form">
			<input type="radio" name="lengow_export_disabled" id="active_on" value="1" {if $lengow_export_disabled == 1} checked="checked"{/if} />
			<label class="t" for="active_on">
				<img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
			</label>
			<input type="radio" name="lengow_export_disabled" id="active_off" value="0" {if $lengow_export_disabled == 0} checked="checked"{/if} />
			<label class="t" for="active_off">
				<img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
			</label>
			<p class="preference_description">{l s='If you want to export disabled products, click "yes".' mod='lengow'}</p>
		</div>
		<div class="clear"></div>
		<label>{l s='Auto export of new product(s)' mod='lengow'}</label>
		<div class="margin-form">
			<input type="radio" name="lengow_export_new" id="active_on" value="1" {if $lengow_export_new == 1} checked="checked"{/if} />
			<label class="t" for="active_on">
				<img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
			</label>
			<input type="radio" name="lengow_export_new" id="active_off" value="0" {if $lengow_export_new == 0} checked="checked"{/if} />
			<label class="t" for="active_off">
				<img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
			</label>
			<p class="preference_description">{l s='If you click "yes" your new product(s) will be automatically exported on the next feed' mod='lengow'}</p>
		</div>
		<div class="clear"></div>
		<label>{l s='Export product variations' mod='lengow'}</label>
		<div class="margin-form">
			<input type="radio" name="lengow_export_all_attributes" id="active_on" value="1" {if $lengow_export_all_attributes == 1} checked="checked"{/if} />
			<label class="t" for="active_on">
				<img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
			</label>
			<input type="radio" name="lengow_export_all_attributes" id="active_off" value="0" {if $lengow_export_all_attributes == 0} checked="checked"{/if}/>
			<label class="t" for="active_off">
				<img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
			</label>
			<p class="preference_description">{l s='If don\'t want to export all your products\' variations, click "no"' mod='lengow'}</p>
		</div>
		<div class="clear"></div>
		<label>{l s='Export product features' mod='lengow'}</label>
		<div class="margin-form">
			<input type="radio" name="lengow_export_features" id="active_on" value="1" {if $lengow_export_features == 1} checked="checked"{/if} />
			<label class="t" for="active_on">
				<img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
			</label>
			<input type="radio" name="lengow_export_features" id="active_off" value="0" {if $lengow_export_features == 0} checked="checked"{/if}/>
			<label class="t" for="active_off">
				<img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
			</label>
			<p class="preference_description">{l s='If you click "yes", your product(s) will be exported with features.' mod='lengow'}</p>
		</div>
		<div class="clear"></div>
		<label>{l s='Title + attributes + features' mod='lengow'}</label>
		<div class="margin-form">
			<input type="radio" name="lengow_export_fullname" id="active_on" value="1" {if $lengow_export_fullname == 1} checked="checked"{/if} />
			<label class="t" for="active_on">
				<img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
			</label>
			<input type="radio" name="lengow_export_fullname" id="active_off" value="0" {if $lengow_export_fullname == 0} checked="checked"{/if}/>
			<label class="t" for="active_off">
				<img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
			</label>
			<p class="preference_description">{l s='Select this option if you want a variation product title as title + attributes + feature. By default the title will be the product name' mod='lengow'}</p>
		</div>
		<div class="clear"></div>
		<label>{l s='Export out of stock product' mod='lengow'}</label>
		<div class="margin-form">
			<input type="radio" name="lengow_export_out_stock" id="active_on" value="1" {if $lengow_export_out_stock == 1} checked="checked"{/if} />
			<label class="t" for="active_on">
				<img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
			</label>
			<input type="radio" name="lengow_export_out_stock" id="active_off" value="0" {if $lengow_export_out_stock == 0} checked="checked"{/if}/>
			<label class="t" for="active_off">
				<img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
			</label>
			<p class="preference_description">{l s='Select this option if you want to export out of stock products.' mod='lengow'}</p>
		</div>
		<div class="clear"></div>
		<label>{l s='Number of images to export' mod='lengow'}</label>
		<div class="margin-form">
			<select name="lengow_image_type" class="" id="lengow_image_type">
				{foreach from=$options.images item=option}
					<option value="{$option.id_image_type}"{if $option.id_image_type == $lengow_image_type} selected="selected"{/if}>{$option.name|escape:'str'}</option>
				{/foreach}
			</select>
		</div>
		<div class="clear"></div>
		<label>{l s='Number images to export' mod='lengow'}</label>
		<div class="margin-form">
			<select name="lengow_images_count" class="" id="lengow_images_count">
				{foreach from=$options.images_count item=option}
					<option value="{$option->id}"{if $option->id == $lengow_images_count} selected="selected"{/if}>{$option->name|escape:'str'}</option>
				{/foreach}
			</select>
		</div>
		<div class="clear"></div>
		<label>{l s='Export default format' mod='lengow'}</label>
		<div class="margin-form">
			<select name="lengow_export_format" class="" id="lengow_export_format">
				{foreach from=$options.formats item=option}
					<option value="{$option->id}"{if $option->id == $lengow_export_format} selected="selected"{/if}>{$option->name|escape:'str'}</option>
				{/foreach}
			</select>
		</div>
		<div class="clear"></div>
		<label>{l s='Export in a file' mod='lengow'}</label>
		<div class="margin-form">
			<input type="radio" name="lengow_export_file"id="active_on" value="1" {if $lengow_export_file}checked="checked"{/if} />
			<label class="t" for="active_on">
				<img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
			</label>
			<input type="radio" name="lengow_export_file"id="active_off" value="0" {if $lengow_export_file == 0}checked="checked"{/if}  />
			<label class="t" for="active_off">
				<img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
			</label>
			<p class="preference_description">{l s='You should use this option if you have more than 10,000 products' mod='lengow'}{$link_file_export}</p>
		</div>
		<div class="clear"></div>
		<label>{l s='Fields to export' mod='lengow'}</label>
		<div class="margin-form">
			<select name="lengow_export_fields[]" class="lengow-select" size="15" multiple="multiple">
				{foreach from=$options.export_fields item=field}
					<option value="{$field->id}"{if $field->id|in_array:$lengow_export_fields} selected="selected"{/if}>{$field->name|escape:'str'}</option>
				{/foreach}
			</select>
			<p class="preference_description">{l s='Maintain "control key or command key" to select fields.' mod='lengow'}{$link_file_export|escape:'str'}</p>
		</div>
		<div class="clear"></div>
		<label>{l s='Your export script' mod='lengow'}</label>
		<div class="margin-form">
			{$url_feed_export|escape:'str'}
		</div>
		<div class="clear"></div>
		<div class="small"><sup>*</sup> {l s='Required field' mod='lengow'}</div>
	</fieldset>
	<br />
	{if $lengow_feed_management}
	<fieldset id="fieldset_5"> <legend>{l s='Feeds' mod='lengow'}</legend>
		{$lengow_flow|escape:'str'}
		<p class="preference_description">{l s='If you use the backoffice of the Lengow module, migrate your feed when you are sure to be ready' mod='lengow'}<br />
			{l s='If you want to use the file export, don\'t use this fonctionality. Please contact Lengow Support Team' mod='lengow'}
		</p>
		<div class="clear"></div>
	</fieldset>
	<br />
	{/if}
	<fieldset id="fieldset_6"> <legend>{l s='Import parameters' mod='lengow'}</legend>
		<label>{l s='Status of process orders' mod='lengow'}</label>
		<div class="margin-form">
			<select name="lengow_order_process" class="" id="lengow_order_process">
				{foreach from=$options.states item=option}
					<option value="{$option.id_order_state}"{if $option.id_order_state == $lengow_order_process} selected="selected"{/if}>{$option.name|escape:'str'}</option>
				{/foreach}
			</select>
		</div>
		<div class="clear"></div>
		<label>{l s='Status of shipped orders' mod='lengow'}</label>
		<div class="margin-form">
			<select name="lengow_order_shipped" class="" id="lengow_order_shipped">
				{foreach from=$options.states item=option}
					<option value="{$option.id_order_state}"{if $option.id_order_state == $lengow_order_shipped} selected="selected"{/if}>{$option.name|escape:'str'}</option>
				{/foreach}
			</select>
		</div>
		<div class="clear"></div>
		<label>{l s='Status of cancelled orders' mod='lengow'}</label>
		<div class="margin-form">
			<select name="lengow_order_cancel" class="" id="lengow_order_cancel">
				{foreach from=$options.states item=option}
					<option value="{$option.id_order_state}"{if $option.id_order_state == $lengow_order_cancel} selected="selected"{/if}>{$option.name|escape:'str'}</option>
				{/foreach}
			</select>
		</div>
		<div class="clear"></div>
		<label>{l s='Associated payment method' mod='lengow'}</label>
		<div class="margin-form">
			<select name="lengow_method_name" class="" id="lengow_method_name">
				{foreach from=$options.shippings item=option}
					<option value="{$option->id}"{if $option->id == $lengow_method_name} selected="selected"{/if}>{$option->name|escape:'str'}</option>
				{/foreach}
			</select>
		</div>
		<div class="clear"></div>
		<label>{l s='Default carrier' mod='lengow'}</label>
		<div class="margin-form">
			<select name="lengow_carrier_default" class="" id="lengow_carrier_default">
				{foreach from=$options.carriers item=option}
					<option value="{$option.id_carrier}"{if $option.id_carrier == $lengow_carrier_default} selected="selected"{/if}>{$option.name|escape:'str'}</option>
				{/foreach}
			</select>
			<p class="preference_description">{l s='Your default carrier' mod='lengow'}</p>
		</div>
		<div class="clear"></div>
		<label>{l s='Import from x days' mod='lengow'}</label>
		<div class="margin-form">
			<input type="text" name="lengow_import_days" id="lengow_import_days" value="{$lengow_import_days|escape:'intval'}" class="" size="20" /> <sup>*</sup></div>
		<div class="clear"></div>
		<label>{l s='Forced price' mod='lengow'}</label>
		<div class="margin-form">
			<input type="radio" name="lengow_force_price"id="active_on" value="1" {if $lengow_force_price}checked="checked"{/if} />
			<label class="t" for="active_on">
				<img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
			</label>
			<input type="radio" name="lengow_force_price"id="active_off" value="0" {if $lengow_force_price == 0}checked="checked"{/if} />
			<label class="t" for="active_off">
				<img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
			</label>
			<p class="preference_description">{l s='This option allows to force the product prices of the marketplace orders during the import' mod='lengow'}</p>
		</div>
		<div class="clear"></div>
		<label>{l s='Force Products' mod='lengow'}</label>
		<div class="margin-form">
			<input type="radio" name="lengow_import_force_product"id="active_on" value="1" {if $lengow_import_force_product}checked="checked"{/if} />
			<label class="t" for="active_on">
				<img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
			</label>
			<input type="radio" name="lengow_import_force_product"id="active_off" value="0" {if $lengow_import_force_product == 0}checked="checked"{/if} />
			<label class="t" for="active_off">
				<img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
			</label>
			<p class="preference_description">{l s='Yes if you want to force import of disabled or out of stock product' mod='lengow'}</p>
		</div>
		<div class="clear"></div>
		<label>{l s='Import processing fee' mod='lengow'}</label>
		<div class="margin-form">
			<input type="radio" name="lengow_import_processing_fee"id="active_on" value="1" {if $lengow_import_processing_fee}checked="checked"{/if} />
			<label class="t" for="active_on">
				<img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
			</label>
			<input type="radio" name="lengow_import_processing_fee"id="active_off" value="0" {if $lengow_import_processing_fee == 0}checked="checked"{/if} />
			<label class="t" for="active_off">
				<img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
			</label>
			<p class="preference_description">{l s='Yes if you want have marketplace processing fee inside order' mod='lengow'}</p>
		</div>
		<label>{l s='Fictitious emails' mod='lengow'}</label>
		<div class="margin-form">
			<input type="radio" name="lengow_import_fake_email"id="active_on" value="1" {if $lengow_import_fake_email}checked="checked"{/if} />
			<label class="t" for="active_on">
				<img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
			</label>
			<input type="radio" name="lengow_import_fake_email"id="active_off" value="0" {if $lengow_import_fake_email == 0}checked="checked"{/if} />
			<label class="t" for="active_off">
				<img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
			</label>
			<p class="preference_description">{l s='Yes if you want to import orders with fictitious email' mod='lengow'}</p>
		</div>
		<div class="clear"></div>
		<div class="clear"></div>
		<label>{l s='Markeplace shipping method' mod='lengow'}</label>
		<div class="margin-form">
			<input type="radio" name="lengow_mp_shipping_method"id="active_on" value="1" {if $lengow_mp_shipping_method}checked="checked"{/if} />
			<label class="t" for="active_on">
				<img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
			</label>
			<input type="radio" name="lengow_mp_shipping_method"id="active_off" value="0" {if $lengow_mp_shipping_method == 0}checked="checked"{/if} />
			<label class="t" for="active_off">
				<img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
			</label>
			<p class="preference_description">{l s='Yes if you want your orders to use marketplace shipping method' mod='lengow'}</p>
		</div>
		<div class="clear"></div>
		<label>{l s='Force import of orders shipped by selected marketplaces' mod='lengow'}</label>
		<div class="margin-form">
			<select name="lengow_import_marketplaces[]" class="lengow-select" size="15" multiple="multiple">
				{foreach from=$options.import_marketplaces item=marketplace}
					<option value="{$marketplace->id}"{if $marketplace->id|in_array:$lengow_import_marketplaces} selected="selected"{/if}>
						{$marketplace->name|escape:'str'}
					</option>
				{/foreach}
			</select>
			<p class="preference_description">{l s='Please note that stock will be decremented. Maintain "control key or command key" to select multiple marketplaces.' mod='lengow'}{$link_file_export|escape:'str'}</p>
		</div>
		<label>{l s='Report email' mod='lengow'}</label>
		<div class="margin-form">
			<input type="radio" name="lengow_report_mail" id="active_on" value="1" {if $lengow_report_mail}checked="checked"{/if} />
			<label class="t" for="active_on">
				<img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
			</label>
			<input type="radio" name="lengow_report_mail" id="active_off" value="0" {if $lengow_report_mail == 0}checked="checked"{/if} />
			<label class="t" for="active_off">
				<img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
			</label>
			<p class="preference_description">{l s='If enabled, you will receive a report with every import on the email address configured.' mod='lengow'}</p>
		</div>
		<div class="clear"></div>
		<label>{l s='Send reports to' mod='lengow'}</label>
		<div class="margin-form">
			<input type="text" name="lengow_email_address" id="lengow_email_address" value="{$lengow_email_address|escape:'strval'}" class="" size="50" />
			<p class="preference_description">{l s='If report emails are activated, the reports will be send to the specified address. Otherwise it will be your default shop email address.' mod='lengow'}</p>
		</div>
		<div class="clear"></div>
		<div class="clear"></div>
		<div class="clear"></div>
		<label>{l s='Limit to one order per import process' mod='lengow'}</label>
		<div class="margin-form">
			<input type="radio" name="lengow_import_single" id="active_on" value="1" {if $lengow_import_single}checked="checked"{/if} />
			<label class="t" for="active_on">
				<img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
			</label>
			<input type="radio" name="lengow_import_single" id="active_off" value="0" {if $lengow_import_single == 0}checked="checked"{/if} />
			<label class="t" for="active_off">
				<img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
			</label>
			<p class="preference_description">{l s='Useful for prestashop versions from 1.5.2 to 1.5.4.* : avoids importing orders twice.' mod='lengow'}</p>
		</div>
		<div class="clear"></div>
		<div class="clear"></div>
		<label>{l s='Import state' mod='lengow'}</label>
		<div class="margin-form">
			{$lengow_is_import|escape:'str'}
		</div>
		<div class="clear"></div>
		<label>{l s='Your import script' mod='lengow'}</label>
		<div class="margin-form">
			{$url_feed_import|escape:'str'}
		</div>
		<div class="clear"></div>
		<div class="small"><sup>*</sup> {l s='Required field' mod='lengow'}</div>
	</fieldset>
	<br />
	<fieldset id="fieldset_7"><legend>{l s='Cron' mod='lengow'}</legend>
		{$lengow_cron|escape:'str'}
		<div class="clear"></div>
	</fieldset>
	<br />
	<fieldset id="fieldset_7"> <legend>{l s='Developer' mod='lengow'}</legend>
		<label>{l s='Debug mode' mod='lengow'}</label>
		<div class="margin-form">
			<input type="radio" name="lengow_debug"id="active_on" value="1" {if $lengow_debug}checked="checked"{/if} />
			<label class="t" for="active_on">
				<img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
			</label>
			<input type="radio" name="lengow_debug"id="active_off" value="0" {if $lengow_debug == 0}checked="checked"{/if} />
			<label class="t" for="active_off">
				<img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
			</label>
		</div>
		<div class=:"clear"></div>
		<label>{l s='Export timeout' mod='lengow'}</label>
		<div class="margin-form">
			<input type="text" name="lengow_export_timeout" id="lengow_export_timeout" value="{$lengow_export_timeout|escape:'intval'}" class="" size="20" /> <sup>*</sup></div>
		<div class="clear"></div>
		<label>{l s='Feed management' mod='lengow'}</label>
		<div class="margin-form">
			<input type="radio" name="lengow_feed_management"id="active_on" value="1" {if $lengow_feed_management}checked="checked"{/if} />
			<label class="t" for="active_on">
				<img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
			</label>
			<input type="radio" name="lengow_feed_management"id="active_off" value="0" {if $lengow_feed_management == 0}checked="checked"{/if} />
			<label class="t" for="active_off">
				<img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
			</label>
		</div>
		<div class=:"clear"></div>
		<label>{l s='Logs' mod='lengow'}</label>
		<div class="margin-form">
			{$log_files|escape:'str'}
		</div>
		<div class="margin-form">
			<input type="submit" id="_form_submit_btn" value="{l s='Save' mod='lengow'}" name="submitlengow" class="button" />
		</div>
		<div class="clear"></div>
		<div class="small"><sup>*</sup>{l s='Required field' mod='lengow'}</div>
	</fieldset>
</form>