/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*	@author    PrestaShop SA <contact@prestashop.com>
*	@copyright	2007-2014 PrestaShop SA
*	@license   http://opensource.org/licenses/afl-3.0.php	Academic Free License (AFL 3.0)
*	International Registered Trademark & Property of PrestaShop SA
*/

function loadCategoryMatch(id_category) {
	$.ajax({
		async: false,
		type: "POST",
		url: module_dir + 'ebay/ajax/loadCategoryMatch.php?token=' + ebay_token + '&id_category=' + id_category + '&time=' + module_time + '&ch_cat_str=' + categories_ebay_l['no category selected'] + "&profile=" + id_ebay_profile,
		success: function(data) { $("#categoryPath" + id_category).html(); }
	});
}

function changeCategoryMatch(level, id_category) {
	var levelParams = "&level1=" + $("#categoryLevel1-" + id_category).val();

	if (level > 1) levelParams += "&level2=" + $("#categoryLevel2-" + id_category).val();
	if (level > 2) levelParams += "&level3=" + $("#categoryLevel3-" + id_category).val();
	if (level > 3) levelParams += "&level4=" + $("#categoryLevel4-" + id_category).val();
	if (level > 4) levelParams += "&level5=" + $("#categoryLevel5-" + id_category).val();

	$.ajax({
		type: "POST",
		url: module_dir + 'ebay/ajax/changeCategoryMatch.php?token=' + ebay_token + '&id_category=' + id_category + '&time=' + module_time + '&level=' + level + levelParams + '&ch_cat_str=' + categories_ebay_l['no category selected'] + '&profile=' + id_ebay_profile,
		success: function(data) { $("#categoryPath" + id_category).html(data); }
	});
}

var loadedCategories = new Array();

function showProducts(id_category) {
	var elem = $('#show-products-switch-' + id_category);
	var elem_string = $('#show-products-switch-string' + id_category);

	if (elem.attr('showing') == true) 
	{
		$('.product-row[category=' + id_category +']').hide();
		elem.attr('showing', 0);
		elem.html('&#9654;');
		elem_string.html(categories_ebay_l['Unselect products']);
	} 
	else 
	{
		elem.attr('showing', 1);
		elem.html('&#9660;');
		elem_string.html(categories_ebay_l['Unselect products clicked']);

		if (loadedCategories[id_category])
			$('.product-row[category=' + id_category +']').show();
		else
		{
			$('<img src="' + module_path + 'img/loading-small.gif" id="loading-' + id_category +'" alt="" />').insertAfter(elem);

			$.ajax({
				dataType: 'json',
				type: "POST",
				url: module_dir + 'ebay/ajax/getProducts.php?category=' + id_category + '&token=' + ebay_token + '&id_ebay_profile='+id_ebay_profile,
				success: function(products) { 
					loadedCategories[id_category] = true;
					for (var i in products)
					{
						product = products[i];

						$('#category-' + id_category).after('<tr class="product-row ' + (i%2 == 0 ? 'alt_row':'') + '" category="' + id_category + '"> \
							<td>' + product.name + '</td> \
							<td></td> \
							<td></td> \
							<td class="center"> \
								<input name="showed_products[' + product.id + ']" type="hidden" value="1" /> \
								<input onchange="toggleSyncProduct(this)" class="sync-product" product="' + product.id + '" name="to_synchronize[' + product.id + ']" type="checkbox" ' + (product.blacklisted == 1 ? '' : 'checked') + ' /> \
							</td> \
						</tr>');
					}
					$('#loading-' + id_category).remove();
				}
			});
		}
	}
}

function toggleSyncProduct(obj)
{
	var product_id = $(obj).attr('product');
}

$(document).ready(function() {
  
	$("#pagination").children('li').click(function(){
		var p = $(this).html();
		var li = $("#pagination").children('li.current');
		if ($(this).attr('class') == 'prev')
		{
			var liprev = li.prev();
			if (!liprev.hasClass('prev'))
			{
				liprev.trigger('click');
			}
			return false;
		}
		if ($(this).attr('class') == 'next')
		{
			var linext = li.next();
			if (!linext.hasClass('next'))
			{
				linext.trigger('click');
			}
			return false;
		}
		$("#pagination").children('li').removeClass('current');
		$(this).addClass('current');
		$("#textPagination").children('span').html(p);
		$.ajax({
			type: "POST",
			dataType: "json",
			url: module_dir + "ebay/ajax/saveCategories.php?token=" + ebay_token + "&profile=" + id_ebay_profile,
			data: $('#configForm2').serialize()+"&ajax=true",
			success : function(data)
			{
				if (data.valid)
				{
					$.ajax({
						type: "POST",
						url: module_dir + "ebay/ajax/loadTableCategories.php?token=" + ebay_token + "&p=" + p + "&profile=" + id_ebay_profile + "&id_lang=" + id_lang + "&ch_cat_str=" + categories_ebay_l["no category selected"] + "&ch_no_cat_str=" + categories_ebay_l["no category found"] + "&not_logged_str=" + categories_ebay_l["You are not logged in"] + "&unselect_product=" + categories_ebay_l["Unselect products"]  ,
						success : function(data) {
							$("form#configForm2 table tbody #removeRow").remove(); $("form#configForm2 table tbody").html(data);
						}
					});
				}
			}
		});
	})  
  
	$.ajax({
		type: "POST",
		url: module_dir + "ebay/ajax/loadTableCategories.php?token=" + ebay_token + "&id_lang=" + id_lang + "&profile=" + id_ebay_profile + '&ch_cat_str=' + categories_ebay_l['no category selected'] + '&ch_no_cat_str=' + categories_ebay_l['no category found'] + '&not_logged_str=' + categories_ebay_l['You are not logged in'] + '&unselect_product=' + categories_ebay_l['Unselect products'],
		success : function(data) { $("form#configForm2 table tbody #removeRow").remove(); $("form#configForm2 table tbody").html(data); }
	});
	
	$("#configForm2SuggestedCategories input[type=submit]").click(function(){
		$('<div class="center"><img src="' + module_path + 'img/loading-small.gif" alt="" />' + categories_ebay_l['thank you for waiting'] + '</div>').insertAfter($(this));
		$(this).fadeOut();
		$.ajax({
			type: "POST",
			url: module_dir + "ebay/ajax/suggestCategories.php?token=" + ebay_token + "&id_lang=" + id_lang + "&profile=" + id_ebay_profile + '&not_logged_str=' + categories_ebay_l['You are not logged in'] + '&settings_updated_str=' + categories_ebay_l['Settings updated'],
			success : function(data) { window.location.href = window.location.href + "&conf=6"; }
		});
		return false;
	});

	$('#update-all-extra-images').click(function() {
		var val = $('#all-extra-images-selection').val();
		$('#all-extra-images-value').val(val);
	})
	
	if ($("#menuTab1").hasClass('success') && $("#menuTab2").hasClass('wrong') && $("#configForm2SuggestedCategories input[type=submit]").length == 1)
	{
		//$("#configForm2SuggestedCategories input[type=submit]").trigger("click");
	}
});
