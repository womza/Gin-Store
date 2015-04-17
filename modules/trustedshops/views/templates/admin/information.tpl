{**
* 2014 silbersaiten The module is based on the trustedshops module originally developed by PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to support@silbersaiten.de so we can send you a copy immediately.
*
* @author    silbersaiten www.silbersaiten.de <info@silbersaiten.de>
* @copyright 2014 silbersaiten
* @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
*}
<div class="panel">
    <div id="trustedshops_info">
        <div class="row">
            <div class="col-md-9">
                <div class="row">
                    <strong>{l s='Trustmark with Buyer Protection and Customer Reviews' mod='trustedshops'}</strong>
                </div>
                <div class="row">
                    {l s='Trusted Shops is the well-known internet Trustmark for online shops which also offers customers a Buyer Protection. During the audit, your online shop is subjected to extensive and thorough tests. This audit, consisting of over 100 individual criteria, is based on the requirements of consumer protection, national and European legislation.' mod='trustedshops'}
                </div>
            </div>
            <div class="col-md-3">
                <img src="{$_path|escape}img/ts_logo.jpg" alt=""/>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <img src="{$ts_rating_image|escape}" alt=""/>
            </div>
            <div class="col-md-8">
                <div class="row">
                    <strong>{l s='More trust leads to more sales!' mod='trustedshops'}</strong>
                </div>
                <div class="row">
                    {l s='The Trusted Shops Trustmark is the optimal way to increase the trust of your online customers. Trust increases customers\' willingness to buy from you.' mod='trustedshops'}
                </div>
                <div class="row">
                    <strong>{l s='Less abandoned purchases' mod='trustedshops'}</strong>
                </div>
                <div class="row">
                    {l s='Give your online customers a strong reason to buy proposing the Trusted Shops Buyer Protection. This additional security leads to less shopping basket abandonment.' mod='trustedshops'}
                </div>
                <div class="row">
                    <strong>{l s='Your Customers become Sellers' mod='trustedshops'}</strong>
                </div>
                <div class="row">
                    {l s='Use our retailer evaluation with integrated customer opinions as an important marketing tool to increase the trust of your customers. Display your positive evaluation at Google Shopping and increase your traffic.' mod='trustedshops'}
                </div>
            </div>
        </div>
        <div class="row text-center">
            <a href="{$applynow_link|escape}" target="_blank" class="btn btn-primary">{l s='Apply Now!' mod='trustedshops'}</a>
        </div>
    </div>
</div>