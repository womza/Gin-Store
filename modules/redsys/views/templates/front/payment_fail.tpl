{capture name=path}{l s='Pago no completado' mod='redsys'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Pago no completado' mod='redsys'}</h2>

<table width="100%" border="0">
	<tr><td><img src="{$this_path}views/img/error.gif" width="301" height="197" alt="Pago pago no completado" longdesc="Pago no completado" /></td>
    <td>{l s='Lo sentimos. Su pago no se ha completado. Puede intentarlo de nuevo o escoger otro medio de pago. Recuerde que puede usar tarjetas adheridas al sistema de pago seguro de Visa, denominado "Verified by Visa", o de MasterCard, denominado "MasterCard SecureCode".'  mod='redsys'}</td></tr>
</table>
<ul class="footer_links">
	<li><a href="{$link->getPageLink('my-account')}"><img src="{$this_path}views/img/my-account.gif" alt="" class="icon" /></a><a href="{$link->getPageLink('my-account')}">{l s='Volver a su cuenta'  mod='redsys'}</a></li>
	<li><a href="{$link->getPageLink('order',false, NULL,'step=3')}" title="{l s='Pagos'  mod='redsys'}"><img src="{$this_path}views/img/cart.gif" alt="{l s='Pagos' mod='redsys'}" class="icon" /></a><a href="{$link->getPageLink('order',false, NULL,'step=3')}" title="{l s='Pagos'  mod='redsys'}">{l s='Volver a elegir medio de pago'  mod='redsys'}</a></li>
	<li><a href="{$base_dir}"><img src="{$this_path}views/img/home.gif" alt="" class="icon" /></a><a href="{$base_dir}">{l s='Inicio'  mod='redsys'}</a></li>
</ul>