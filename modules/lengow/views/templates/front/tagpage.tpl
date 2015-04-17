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
<!-- Tag_Lengow -->
<img src="https://tracking.lengow.com/lead.php?idClient={$id_customer|escape:'intval'}&idGroup={$id_group|escape:'intval'}&price={$order_total|escape:'intval'}&idCommande={$id_order|escape:'intval'}&modePaiement={$mode_payment|escape:'htmlall'}&listingProduit={$ids_products|escape:'str'}" alt="" style="width: 1px; height: 1px; border: none;" />
<img src="https://tracking.lengow.com/leadValidation.php?idClient={$id_customer|escape:'intval'}&idGroup={$id_group|escape:'intval'}&idCommande={$id_order|escape:'intval'}" alt="" style="width: 1px; height: 1px; border: none;" />
<!-- /Tag_Lengow -->';