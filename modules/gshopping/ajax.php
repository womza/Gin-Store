<?php
ini_set('display_error', 'On');
include(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

if (Tools::getValue('action') == 'category')
{
	global $cookie;
	$values_category = array();
	$tmp = '
	<tr>
		<th>ID</th>
		<th>Name</th>
		<th>Google Product Category</th>
	</tr>';

	$cats = Db::getInstance()->ExecuteS('SELECT id_category, id_google_product_category FROM `'._DB_PREFIX_.'google_shopping`');
	foreach ($cats as $key => $value)
		$values_category[(int)$value['id_category']] = $value['id_google_product_category'];
	unset($cats, $key, $value);

	$shop_category = '';
	if (version_compare(_PS_VERSION_, '1.5', '>'))
	{
		$id_shop = (int)Tools::getValue('shop');
		$shop_category = ' AND cl.id_shop = '.$id_shop;
	}

	$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
	SELECT c.id_category, name
	FROM `'._DB_PREFIX_.'category` c
	LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (cl.id_category = c.id_category AND cl.id_lang = '.(int)$cookie->id_lang.$shop_category.')
	WHERE `active` = 1 LIMIT '.((int)Tools::getValue('page')*(int)Tools::getValue('packet')).', '.(int)Tools::getValue('packet'));

	$namefile = 'taxonomy.en-US.txt';
	if (!file_exists(dirname(__FILE__).'/'.$namefile))
	{
		$file = Tools::file_get_contents('http://www.google.com/basepages/producttype/taxonomy.en-US.txt', FILE_USE_INCLUDE_PATH);
		file_put_contents(dirname(__FILE__).'/'.$namefile, $file);
	}
	else
		$file = Tools::file_get_contents(dirname(__FILE__).'/'.$namefile);

	$google_product_categories = explode("\n", $file);
	$select_google_categories = array();

	foreach	($google_product_categories as $key_cat => $value_cat)
		$select_google_categories[$key_cat] = '<option value="'.(int)$key_cat.'" %select%>'.(($key_cat == 0) ? 'No default category' : $value_cat).'</option>';
	unset($google_product_categories, $key_cat, $value_cat);

	$irow = 0;
	foreach($result AS $category)
	{
		$id_category = intval($category['id_category']);

		$html = $select_google_categories;
		if (isset($values_category[(int)$id_category]) && isset($html[$values_category[(int)$id_category]]))
			$html[$values_category[(int)$id_category]] = str_replace('%select%', 'selected="selected"', $html[$values_category[(int)$id_category]]);
		$html = implode(' ', $html);
		$html = str_replace('%select%', '', $html);

		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$catname = $category['name'];
		else
			$catname = hideCategoryPosition($category['name']);

		$tmp .= '
		<tr class="'.($irow++ % 2 ? 'alt_row' : '').'">
		<td>'.$id_category.'</td>
		<td>
			<label for="categoryBox_'.$id_category.'" class="t">'.stripslashes($catname).'</label>
		</td>
		<td>
			<select class="selectGoogleCategory" name="category_'.$id_category.'_google" style="width:450px">'.$html.'</select>
		</td>
		</tr>';
	}
	unset($result, $category);
	die($tmp);
}

if (Tools::getValue('action') == 'updateCategory')
{
	$id_category = (int)Tools::getValue('idCategory');
	$count = Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'google_shopping` WHERE `id_category` = '.$id_category);
	$googleID = (int)Tools::getValue('idGoogle');
	if ($googleID > 0)
	{
		if ($count > 0)
		{
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'google_shopping`
				SET `id_google_product_category` = "'.$googleID.'"
				WHERE `id_category` = '.$id_category);
		}
		else
		{
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'google_shopping`
				(`id_category`, `id_google_product_category`)
				VALUES ("'.(int)$id_category.'", "'.$googleID.'")');
		}
	}
	else
		Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'google_shopping` WHERE id_category ='.$id_category);
}

function hideCategoryPosition($name)
{
	return preg_replace('/^[0-9]+\./', '', $name);
}

?>