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
<br />
<fieldset>
	<legend><img src="../img/admin/tab-stats.gif" /> {l s='Import Lengow' mod='lengow'}</legend>
	<h4>{l s='This order has been imported from Lengow' mod='lengow'}</h4>
	<ul>
		<li>{l s='Lengow order ID' mod='lengow'} : <strong>{$id_order_lengow|escape:'str'}</strong></li>
		<li>{l s='Feed ID' mod='lengow'} : <strong>{$id_flux|escape:'str'}</strong></li>
		<li>{l s='Marketplace' mod='lengow'} : <strong>{$marketplace|escape:'str'}</strong></li>
		<li>{l s='Total amount paid on Marketplace' mod='lengow'} : <strong>{$total_paid|escape:'str'}</strong></li>
		<li>{l s='Carrier from marketplace' mod='lengow'} : <strong>{$carrier|escape:'str'}</strong></li>
		<li>{l s='Shipping method' mod='lengow'} : <strong>{$tracking_method|escape:"str"}</strong></li>
		<li>{l s='Message' mod='lengow'} : <strong>{$message|escape:'str'}</strong></li>
	</ul>
	<br />
	<div class"button-command-prev-next">
		<button id="reimport-order" class="button" data-url="{$action_reimport|escape:'str'}" data-orderid="{$order_id|escape:'str'}" data-lengoworderid="{$id_order_lengow|escape:'str'}" data-feedid="{$id_flux|escape:'str'}" data-version='{$version|escape:'str'}'>{l s='Cancel and re-import order' mod='lengow'}</button>
		<a class="button" href="{$action_synchronize|escape:'str'}">{l s='Synchronize ID' mod='lengow'}</a>
	</div>
</fieldset>
{if $add_script == true}
<script type="text/javascript" src="{$url_script|escape:'str'}"></script>
{/if}