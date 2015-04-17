<?php
/**
* 2007-2014 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2014 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*/

class GShopping extends Module
{
	function __construct()
	{
		$this->name = 'gshopping';
	 	$this->tab = 'smart_shopping';
	 	$this->version = '2.1.12';
		$this->author = 'PrestaShop';
		$this->module_key = '7c35a8cd476e2dca4788a13911f45e13';
		$this->displayName = 'Google Shopping';

	 	parent::__construct();

		$this->description = $this->l('Export your catalog to Google Shopping (also called Google Base)');
	}

	public function getContent()
	{
		if (version_compare(_PS_VERSION_, '1.5.0.17') >= 0)
		{
			if (Shop::isFeatureActive())
			{
				$url = $this->context->shop->getUrls();
				if (empty($url))
				{
					$token_mod = '&token='.Tools::getAdminTokenLite('AdminShopGroup');
				$output = $this->displayError(
						'<div>
						<a href="index.php?controller=AdminShopGroup'.$token_mod.'" class="alert-link">'.$this->l('You should configure your URL').'</a>
					</div>');
				}
			}
		}
		$output = '';
		$output .= '<h2>Google Shopping</h2>';
		if (Tools::isSubmit('submitGShopping'))
		{

			Configuration::updateValue('GSHOPPING_ID_FEATURE_AUTHOR', intval(Tools::getValue('id_feature_author')));
			Configuration::updateValue('GSHOPPING_ID_FEATURE_YEAR', intval(Tools::getValue('id_feature_year')));
			Configuration::updateValue('GSHOPPING_ID_FEATURE_EDITION', intval(Tools::getValue('id_feature_edition')));
			Configuration::updateValue('GSHOPPING_ID_ATTRIBUTE_COLOR', intval(Tools::getValue('id_attribute_color')));
			Configuration::updateValue('GSHOPPING_ID_ATTRIBUTE_SIZE', intval(Tools::getValue('id_attribute_size')));
			Configuration::updateValue('GSHOPPING_COMBINATIONS', intval(Tools::getValue('combinations')));
			Configuration::updateValue('GSHOPPING_REFERENCES', intval(Tools::getValue('references')));
			Configuration::updateValue('GSHOPPING_SHIPPING', (float)Tools::getValue('shipping'));
			Configuration::updateValue('GSHOPPING_CARRIER', pSQL(Tools::getValue('carrier')));

			if (!Configuration::get('GSHOPPING_COMBINATIONS'))
				Configuration::updateValue('GSHOPPING_COMPLEMENT', 0);
			else
				Configuration::updateValue('GSHOPPING_COMPLEMENT', intval(Tools::getValue('complement')));

			$output .= $this->displayConfirmation($this->l('Settings updated'));
		}

		return $output.$this->displayForm();
	}

	public function install()
	{
		Configuration::updateValue('GSHOPPING_COMBINATIONS', 0);
		Configuration::updateValue('GSHOPPING_REFERENCES', 0);
		Configuration::updateValue('GSHOPPING_COMPLEMENT', 0);
		Configuration::updateValue('GSHOPPING_SHIPPING', 0);
		Configuration::updateValue('GSHOPPING_CARRIER', '');

		return parent::install() && $this->installDB();
	}

	public function installDB()
	{
		return Db::getInstance()->Execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'google_shopping` (
				`id_googleshopping_cat` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`id_category` INT UNSIGNED NOT NULL,
				`id_google_product_category` INT UNSIGNED NOT NULL,
				PRIMARY KEY (`id_googleshopping_cat`)
			) DEFAULT CHARSET=utf8 ;');
	}

	public function uninstall()
	{
		return (
			parent::uninstall() AND
			Configuration::deleteByName('GSHOPPING_ID_FEATURE_AUTHOR') AND
			Configuration::deleteByName('GSHOPPING_ID_FEATURE_YEAR') AND
			Configuration::deleteByName('GSHOPPING_ID_FEATURE_EDITION') AND
			Configuration::deleteByName('GSHOPPING_ID_ATTRIBUTE_COLOR') AND
			Configuration::deleteByName('GSHOPPING_ID_ATTRIBUTE_SIZE') AND
			Configuration::deleteByName('GSHOPPING_COMBINATIONS') AND
			Configuration::deleteByName('GSHOPPING_REFERENCES') AND
			Configuration::deleteByName('GSHOPPING_COMPLEMENT') AND
			Configuration::deleteByName('GSHOPPING_SHIPPING') AND
			Configuration::deleteByName('GSHOPPING_CARRIER') AND
			$this->uninstallDB()
		);
	}

	public function uninstallDB()
	{
		return Db::getInstance()->Execute('
				DROP TABLE IF EXISTS `'._DB_PREFIX_.'google_shopping`');
	}

	public function updateCatDb($categories, $google_product_categories)
	{
		$categories_selected = array();

		foreach ($categories as $key => $value)
		{
			$count = Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'google_shopping` WHERE `id_category` = '.(int)$value);

			if ($count > 0)
			{
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'google_shopping`
					SET `id_google_product_category` = "'.(int)$google_product_categories[(int)$value].'"
					WHERE `id_category` = '.(int)$value);
			}
			else
			{
				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'google_shopping`
					(`id_category`, `id_google_product_category`)
					VALUES ("'.(int)$value.'", "'.(int)$google_product_categories[(int)$value].'")');
			}

			$categories_selected[] = (int)$value;
		}

		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'google_shopping` WHERE id_category NOT IN ('.implode(',', $categories_selected).')');
	}

	public function getCatDb()
	{
		return 	Db::getInstance()->ExecuteS('SELECT id_category, id_google_product_category FROM `'._DB_PREFIX_.'google_shopping`');
	}

	public function getGoogleCatDb($id_category)
	{
		return 	Db::getInstance()->getValue('SELECT id_google_product_category FROM `'._DB_PREFIX_.'google_shopping` WHERE id_category = '.(int)$id_category);
	}

	// Parse the taxonomy file to find the google product category
	public function parseTaxonomyFile()
	{
		$file = Tools::file_get_contents('http://www.google.com/basepages/producttype/taxonomy.en-US.txt', FILE_USE_INCLUDE_PATH);
		$data = explode("\n", $file);
		return $data;
	}

	public function exportCatalog($langIsoCode, $currencyIsoCode)
	{
		$id_shop = 0;
		if (version_compare(_PS_VERSION_, '1.5.0.17') >= 0)
			$id_shop = $this->context->shop->id;
		$id_lang = Language::getIdByIso($langIsoCode);
		$id_currency = Currency::getIdByIsoCode($currencyIsoCode);
		$reference = Configuration::get('GSHOPPING_REFERENCES') ? 'supplier_reference' : 'reference';
		$oldVersion = version_compare(_PS_VERSION_, '1.4.0.2') == -1;

		$xmlString = <<<XML
<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"></rss>
XML;

		$xml = new SimpleXMLExtendedGShopping($xmlString);
		$channel = $xml->addChild('channel');
		$channel->addChild('title', 'Google Shopping export for PrestaShop');

		$weightUnit = Configuration::get('PS_WEIGHT_UNIT');
		$separator = Configuration::get('PS_NAVIGATION_PIPE');
		$lastQties = (int)Configuration::get('PS_LAST_QTIES');
		$id_attribute_color = (int)Configuration::get('GSHOPPING_ID_ATTRIBUTE_COLOR');
		$id_attribute_size = (int)Configuration::get('GSHOPPING_ID_ATTRIBUTE_SIZE');
		$combinationsEnabled = (int)Configuration::get('GSHOPPING_COMBINATIONS');
		$complementEnabled = (int)Configuration::get('GSHOPPING_COMPLEMENT');
		$link = new Link();

                $stockManagement = (int)Configuration::get('PS_STOCK_MANAGEMENT');

		$values_category = array();
		$categoriesDb = $this->getCatDb();

		if(!empty($categoriesDb))
		{
			foreach ($categoriesDb as $key => $value)
				$values_category[] = (int)$value['id_category'];
		}
		$google_file = $this->parseTaxonomyFile();

		$products = Db::getInstance()->ExecuteS('
		SELECT DISTINCT(p.id_product), '.(Configuration::get('GSHOPPING_COMBINATIONS') ? 'pa.id_product_attribute' : 'p.id_product').' id, pl.name title, pl.description_short description,
		pl.link_rewrite, '.(Configuration::get('GSHOPPING_COMBINATIONS') ? 'pa.weight' : 'p.weight').' shipping_weight, p.weight product_weight, m.name brand,
		'.(Configuration::get('GSHOPPING_COMBINATIONS') ? 'IF (IFNULL(pa.`'.$reference.'`, \'\') = \'\', p.`'.$reference.'`, pa.`'.$reference.'`)' : 'p.'.$reference.'').' mpn,
		cl.link_rewrite category_link, cl.name category_name,
		'.(Configuration::get('GSHOPPING_COMBINATIONS') ? 'IF (IFNULL(pa.`ean13`, \'\') = \'\', p.`ean13`, pa.`ean13`)' : 'p.ean13').' ean13,
		'.(!$oldVersion ? (Configuration::get('GSHOPPING_COMBINATIONS') ? 'IF (IFNULL(pa.`upc`, \'\') = \'\', p.`upc`, pa.`upc`)' : 'p.upc').' upc,' : '').'
		'.($oldVersion ? 'p.reduction_price, p.reduction_percent, p.reduction_from, p.reduction_to' : 'sp.reduction, sp.from, sp.to, p.online_only, p.condition').', '.($id_shop ? 'ps.id_category_default' : 'p.id_category_default').'
		FROM '._DB_PREFIX_.'product p
		LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = p.id_product)
		LEFT JOIN '._DB_PREFIX_.'lang l ON (l.id_lang = pl.id_lang)
		LEFT JOIN '._DB_PREFIX_.'manufacturer m ON (m.id_manufacturer = p.id_manufacturer)
		LEFT JOIN '._DB_PREFIX_.'category_lang cl ON (cl.id_category = p.id_category_default AND cl.id_lang = l.id_lang)
		LEFT JOIN '._DB_PREFIX_.'google_shopping gs ON p.id_category_default = gs.id_category
		'.($id_shop ?  'LEFT JOIN '._DB_PREFIX_.'product_shop ps ON ps.id_product = p.id_product' : '').'
		'.(!$oldVersion ? 'LEFT JOIN '._DB_PREFIX_.'specific_price sp ON (sp.id_product = p.id_product)' : '').'
		'.(Configuration::get('GSHOPPING_COMBINATIONS') ? '
		LEFT JOIN '._DB_PREFIX_.'product_attribute pa ON (pa.id_product = p.id_product)' : '').'
		WHERE p.active = 1 AND l.id_lang = '.(int)$id_lang.'
		GROUP BY p.id_product');

		$country = new Country((int)Configuration::get('PS_COUNTRY_DEFAULT'));
		/* Manage optional features */
		$id_feature_author = (int)Configuration::get('GSHOPPING_ID_FEATURE_AUTHOR');
		$id_feature_year = (int)Configuration::get('GSHOPPING_ID_FEATURE_YEAR');
		$id_feature_edition = (int)Configuration::get('GSHOPPING_ID_FEATURE_EDITION');
		$ps_legacy_images = (int)Configuration::get('PS_LEGACY_IMAGES');
		$service = Configuration::get('GSHOPPING_CARRIER');
		$service_price = Configuration::get('GSHOPPING_SHIPPING');

		foreach ($products AS &$product)
		{
			if (in_array($product['id_category_default'], $values_category))
			{
				/* Compatibility with versions < 1.4.0.2 */
				if ($oldVersion)
				{
					$product['upc'] = '';
					$product['online_only'] = 0;
					$product['condition'] = 'new';
				}

                                if ($stockManagement)
                                {
                                        if ($combinationsEnabled)
                                                $quantityInStock = (int)Product::getQuantity((int)$product['id_product'], (int)$product['id']);
                                        else
                                                $quantityInStock = (int)Product::getQuantity((int)$product['id_product']);
                                }
                                else
                                        $quantityInStock = 0;

				$item = $channel->addChild('item');
				$item->addCData('link', $link->getProductLink((int)$product['id_product'], $product['link_rewrite'], $product['category_link'], $product['ean13'], (int)$id_lang));
				$item->addCData('description', strip_tags($product['description']));
				$item->addChild('g:id', (int)$product['id'], 'http://base.google.com/ns/1.0');
				$specific_price = null;
                                
				if (version_compare(_PS_VERSION_, '1.5.0.17') >= 0)
				{
                                        $context = Context::getContext();
                                        $priceHT = Product::getPriceStatic($product['id_product'], false);
                                        $priceTTC = Product::getPriceStatic($product['id_product'], true);
				}
				else {
                                    $priceTTC = Product::priceCalculation(
                                        (int)Shop::getCurrentShop(),
                                        (int)$product['id_product'],
                                        ($combinationsEnabled ? 'id' : NULL),
                                        (int)$country->id,
                                        0,
                                        0,
                                        $id_currency,
                                        _PS_DEFAULT_CUSTOMER_GROUP_,
                                        1, // tax
                                        true,
                                        2,
                                        false,
                                        true,
                                        true,
                                        $specific_price,
                                        true
                                    );
                                    $priceHT = Product::priceCalculation(
                                        (int)Shop::getCurrentShop(),
                                        (int)$product['id_product'],
                                        ($combinationsEnabled ? 'id' : NULL),
                                        (int)$country->id,
                                        0,
                                        0,
                                        $id_currency,
                                        _PS_DEFAULT_CUSTOMER_GROUP_,
                                        0, // tax
                                        true,
                                        2,
                                        false,
                                        true,
                                        true,
                                        $specific_price,
                                        true
                                    );
				}
                                
                                if ($currencyIsoCode != 'USD')
                                   		$price = $priceTTC;
                                else
                                {
					$price = $priceHT;
                                       	$tax = $item->addChild('g:tax', null, 'http://base.google.com/ns/1.0');
                                       	$tax->addChild('g:country', 'US', 'http://base.google.com/ns/1.0');
                                       	$tax->addChild('g:rate', Tax::getProductTaxRate($product['id_product']), 'http://base.google.com/ns/1.0');
                                }
                                
				$item->addChild('g:price', $price, 'http://base.google.com/ns/1.0');
				$item->addCData('g:product_type', str_replace($separator, ' > ', strip_tags(Tools::getPath((int)$product['id_category_default'], $product['category_name']))), 'http://base.google.com/ns/1.0');
				// $item->addChild('g:quantity', (($quantityInStock > 0) ? $this->l('in stock') : ((Configuration::get('PS_ORDER_OUT_OF_STOCK') == '1') ? $this->l('available for order') : $this->l('out of stock'))), 'http://base.google.com/ns/1.0');
				$item->addChild('g:quantity', (int) $quantityInStock, 'http://base.google.com/ns/1.0');
				$item->addChild('g:shipping_weight', floatval($combinationsEnabled ? $product['shipping_weight'] + $product['product_weight'] : $product['shipping_weight']).' '.$weightUnit, 'http://base.google.com/ns/1.0');
				$item->addChild('g:online_only', $product['online_only'] ? 'y' : 'n', 'http://base.google.com/ns/1.0');
				$google_product_cat_id = $this->getGoogleCatDb((int)$product['id_category_default']);
				$item->addChild('g:google_product_category', (($google_product_cat_id != 0) ? htmlentities($google_file[(int)$google_product_cat_id]) : ''), 'http://base.google.com/ns/1.0');
				$item->addChild('g:condition', $product['condition'], 'http://base.google.com/ns/1.0');
				$item->addChild('g:brand', (($product['brand'] == '') ? 'Unknow' : $product['brand']), 'http://base.google.com/ns/1.0');

				if (!empty($product['mpn']))
					$item->addChild('g:mpn', !empty($product['mpn']) ? $product['mpn'] : '', 'http://base.google.com/ns/1.0');
				else
					$item->addChild('g:mpn', 'none', 'http://base.google.com/ns/1.0');

				if (!empty($product['ean13']) OR !empty($product['upc']))
					$item->addChild('g:gtin', !empty($product['ean13']) ? $product['ean13'] : (!empty($product['upc']) ? $product['upc'] : ''), 'http://base.google.com/ns/1.0');

				/* Manage featured products */
				if ($oldVersion)
				{
					if (($product['reduction_price'] OR $product['reduction_percent']) AND (strtotime($product['reduction_from']) < time() AND strtotime($product['reduction_to']) > time()))
						$item->addChild('g:featured_product', 'y', 'http://base.google.com/ns/1.0');
					else
						$item->addChild('g:featured_product', 'n', 'http://base.google.com/ns/1.0');
				}
				else
				{
					if ($product['reduction'] AND (strtotime($product['from']) < time() AND strtotime($product['to']) > time()))
						$item->addChild('g:featured_product', 'y', 'http://base.google.com/ns/1.0');
					else
						$item->addChild('g:featured_product', 'n', 'http://base.google.com/ns/1.0');
				}

				/* Include all the product pictures */
				$images = Db::getInstance()->ExecuteS('
				SELECT i.id_image
				FROM '._DB_PREFIX_.'image i
				WHERE i.id_product = '.(int)$product['id_product'].'
				ORDER BY i.cover DESC');
				$i = 0;
				foreach ($images AS $image)
				{
					if (version_compare(_PS_VERSION_, '1.5.0.17') >= 0)
					{
						if ($ps_legacy_images)
							$img_link = Tools::getShopDomain(true).'/img/p/'.$product['id_product'].'-'.$image['id_image'].'-large.jpg';
						else
							$img_link = 'http://'.$link->getImageLink($product['link_rewrite'], $image['id_image']);
					}
					else
						$img_link = $link->getImageLink($product['link_rewrite'], (int)$product['id_product'].'-'.(int)$image['id_image']);

					if ($i < 1)
						$item->addCData('g:image_link', $img_link, 'http://base.google.com/ns/1.0');
					elseif ($i < 10)
						$item->addCData('g:additional_image_link', $img_link, 'http://base.google.com/ns/1.0');

					++$i;
				}
				unset($images, $image);

				/* Determine availability */
				if (!$quantityInStock && $stockManagement)
					$item->addCData('g:availability', 'out of stock', 'http://base.google.com/ns/1.0');
				elseif (!$stockManagement)
				       $item->addCData('g:availability', 'preorder', 'http://base.google.com/ns/1.0');
				else
					$item->addCData('g:availability', 'in stock', 'http://base.google.com/ns/1.0');

				/* Manage combination details */
				$titleComplement = '';
				if ($id_attribute_color OR $id_attribute_size)
				{
					$attributes = Db::getInstance()->ExecuteS('
					SELECT al.id_attribute, a.id_attribute_group, al.name'.($combinationsEnabled ? ', agl.name group_name' : '').'
					FROM '._DB_PREFIX_.'attribute a
					LEFT JOIN '._DB_PREFIX_.'attribute_lang al ON (al.id_attribute = a.id_attribute)
					'.($combinationsEnabled ? 'LEFT JOIN '._DB_PREFIX_.'attribute_group_lang agl ON (agl.id_attribute_group = a.id_attribute_group)' : '').'
					LEFT JOIN '._DB_PREFIX_.'product_attribute_combination pac ON (pac.id_attribute = al.id_attribute)
					LEFT JOIN '._DB_PREFIX_.'product_attribute pa ON (pa.id_product_attribute = pac.id_product_attribute)
					WHERE '.($combinationsEnabled ? 'pac.id_product_attribute = '.(int)$product['id'] : 'pa.id_product = '.(int)$product['id']).'
					'.($combinationsEnabled ? 'AND agl.id_lang = '.(int)$id_lang : '').' AND al.id_lang = '.(int)$id_lang.'
					GROUP BY a.id_attribute');

					if (count($attributes))
					{
						if ($combinationsEnabled AND $complementEnabled)
							$titleComplement = '(';
						foreach ($attributes AS $attribute)
						{
							if ($attribute['id_attribute_group'] == $id_attribute_color)
								$item->addCData('g:color', $attribute['name'], 'http://base.google.com/ns/1.0');
							elseif ($attribute['id_attribute_group'] == $id_attribute_size)
								$item->addCData('g:size', $attribute['name'], 'http://base.google.com/ns/1.0');

							if ($combinationsEnabled AND $complementEnabled)
								$titleComplement .= $attribute['group_name'].$this->l(':').' '.$attribute['name'].', ';
						}
						if ($combinationsEnabled AND $complementEnabled)
						{
							$titleComplement = rtrim($titleComplement, ', ');
							$titleComplement .= ')';
						}
						unset($attributes, $attribute);
					}
				}

				$item->addCData('title', $product['title'].((!empty($titleComplement) AND $combinationsEnabled AND $complementEnabled) ? ' '.$titleComplement : ''));

				/* Manage features */
				$features = Product::getFrontFeaturesStatic((int)$id_lang, (int)$product['id_product']);
				$i = 0;
				foreach ($features AS $feature)
				{
					if ($i < 6)
				 	{
				 		$item->addCData('g:feature', $feature['name'].($langIsoCode == 'fr' ? ' : ' : ': ').$feature['value'], 'http://base.google.com/ns/1.0');
				 	}
				 	++$i;
				 }
				 unset($features, $feature);

				if ($id_feature_author OR $id_feature_year OR $id_feature_edition)
				{
					$featuresIds = array();
					if ($id_feature_author)
						$featuresIds[] = (int)$id_feature_author;
					if ($id_feature_year)
						$featuresIds[] = (int)$id_feature_year;
					if ($id_feature_edition)
						$featuresIds[] = (int)$id_feature_edition;

					$features = Db::getInstance()->ExecuteS('
					SELECT fp.id_feature, fvl.value
					FROM '._DB_PREFIX_.'feature_product fp
					LEFT JOIN '._DB_PREFIX_.'feature_value_lang fvl ON (fvl.id_feature_value = fp.id_feature_value)
					WHERE fp.id_product = '.(int)$product['id_product'].' AND fp.id_feature IN (0,'.implode(',', $featuresIds).')
					AND fvl.id_lang = '.(int)$id_lang);

					foreach ($features AS $feature)
					{
						if ($feature['id_feature'] == $id_feature_author)
							$item->addCData('g:author', $feature['value'], 'http://base.google.com/ns/1.0');
						elseif ($feature['id_feature'] == $id_feature_year)
							$item->addCData('g:year', $feature['value'], 'http://base.google.com/ns/1.0');
						elseif ($feature['id_feature'] == $id_feature_edition)
							$item->addCData('g:edition', $feature['value'], 'http://base.google.com/ns/1.0');
					}
					unset($features, $feature);
				}

				$ship = $item->addChild('g:shipping', null, 'http://base.google.com/ns/1.0');
				$ship->addCData('g:country', $country->iso_code,'http://base.google.com/ns/1.0');
				$ship->addCData('g:service', $service,'http://base.google.com/ns/1.0');
				$ship->addCData('g:price', $service_price,'http://base.google.com/ns/1.0');
			}
		}
		unset($products, $product, $country, $link);

		$dom = dom_import_simplexml($xml)->ownerDocument;
		$dom->formatOutput = true;
		echo $dom->saveXML();
		unset($dom);
	}

	private function _getFeaturesSelect($currentId)
	{
		global $cookie;

		$features = Db::getInstance()->ExecuteS('
		SELECT id_feature, name
		FROM '._DB_PREFIX_.'feature_lang
		WHERE id_lang = '.(int)$cookie->id_lang.'
		ORDER BY name ASC');

		$output = '<option value="-1">-- '.$this->l('Choose among existing features').' --</option>'."\n";
		foreach ($features AS $feature)
			$output .= '<option value="'.(int)$feature['id_feature'].'"'.($currentId == $feature['id_feature'] ? ' selected="selected"' : '').'>'.$feature['name'].'</option>'."\n";

		return $output;
	}

	private function _getAttributeGroupsSelect($currentId)
	{
		global $cookie;

		$attributes = Db::getInstance()->ExecuteS('
		SELECT id_attribute_group, name
		FROM '._DB_PREFIX_.'attribute_group_lang
		WHERE id_lang = '.(int)$cookie->id_lang.'
		ORDER BY name ASC');

		$output = '<option value="-1">-- '.$this->l('Choose among existing attribute groups').' --</option>'."\n";
		foreach ($attributes AS $attribute)
			$output .= '<option value="'.(int)$attribute['id_attribute_group'].'"'.($currentId == $attribute['id_attribute_group'] ? ' selected="selected"' : '').'>'.$attribute['name'].'</option>'."\n";

		return $output;
	}

	public function displayForm()
	{
		global $cookie;

		$countProducts = intval(Db::getInstance()->getValue('
		SELECT COUNT(id_product)
		FROM '._DB_PREFIX_.'product
		WHERE active = 1'));

		$countCombinations = intval(Db::getInstance()->getValue('
		SELECT COUNT(pa.id_product_attribute)
		FROM '._DB_PREFIX_.'product_attribute pa
		LEFT JOIN '._DB_PREFIX_.'product p ON (p.id_product = pa.id_product)
		WHERE p.active = 1'));
		$langs = Language::getLanguages();
		$curs = Currency::getCurrencies();

		$links = '';
		$base = Tools::getShopDomain(true).__PS_BASE_URI__;

		foreach ($langs as $lang)
			foreach ($curs as $cur)
				$links .= '<li><strong>'.$this->l('Export in').' <span style="color:#268CCD">'.$lang['name'].'</span>, with prices in <span style="color:#268CCD">'.$cur['name'].'</span> : </strong><br />
					<a href="'.$base.'modules/'.$this->name.'/export.php?lang='.$lang['iso_code'].'&amp;currency='.$cur['iso_code'].'" >'.$base.'modules/'.$this->name.'/export.php?lang='.$lang['iso_code'].'&amp;currency='.$cur['iso_code'].'</a></li>';

		$index = array();
		$categories = Category::getCategories(intval($cookie->id_lang), false);
		$categories[1]['infos'] = array('name' => $this->l('Home'), 'id_parent' => 0, 'level_depth' => 0);
		$carriers = Carrier::getCarriers($cookie->id_lang, true);

		$nb = Db::getInstance()->getValue('SELECT count(*) FROM '._DB_PREFIX_.'category');
		$packet = 15;
		$max = round($nb / $packet);

		$id_shop = 0;
		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$id_shop = (int)$this->context->shop->id;

		$output = '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset class="width4">
				<script type="text/javascript">
					var page = 0;
					var max = '.$max.';
					$("document").ready( function() {
						$("#prev").hide();
						$("#loader").show();

						// First load
						$.ajax({
							url : "../modules/gshopping/ajax.php",
							type : "post",
							async : false,
							data : {page : page, packet : '.$packet.', action : "category", shop : '.$id_shop.'},
							success : function (data){
								$("#category").html(data);
								$("#loader").hide();
							},
						});

						$(".selectGoogleCategory").live("change", function() {
							$.ajax({
								url : "../modules/gshopping/ajax.php",
								type : "post",
								async : false,
								data : {action : "updateCategory", idGoogle : $(this).val(), idCategory : parseInt($(this).parent().prev().prev().html())}
							});
						});

						$("#next").click(function() {
							page++;
							if (page <= '.$max.')
							{
								$("#next").show();
								$("#loader").show();
								$("#category").html("");
								$.ajax({
									url : "../modules/gshopping/ajax.php",
									type : "post",
									async : false,
									data : {page : page, packet : '.$packet.', action : "category", shop : '.$id_shop.'},
									success : function (data){
										$("#category").html(data);
										$("#loader").hide();
										$("#prev").show();
										if (page == '.$max.')
										{
											$("#next").hide();
										}
									},
								});
							}
							else
							{
								$("#next").hide();
							}
						});


						$("#prev").click(function() {
							page--;
							if (page > 0)
							{
								$("#prev").show();
								$("#loader").show();
								$("#category").html("");
								$.ajax({
									url : "../modules/gshopping/ajax.php",
									type : "post",
									async : false,
									data : {page : page, packet : '.$packet.', action : "category"},
									success : function (data){
										$("#category").append(data);
										$("#loader").hide();
										if (page != 0)
										{
											$("#next").show();
										}
									},
								});
							}
							else
							{
								$("#prev").hide();
							}
						});
					});
				</script>
				<legend><img src="../img/admin/cog.gif" alt="" class="middle" />'.$this->l('Settings').'</legend>
				<h2>'.$this->l('General settings').'</h2>
				<p><b>'.$this->l('Products references (Google asks for the manufacturer reference):').'</b><br /><br />
					<input type="radio" name="references" id="reference_off" value="0"'.(!Configuration::get('GSHOPPING_REFERENCES') ? ' checked="checked"' : '').' /> <label for="reference_off" style="font-weight: normal; float: none;">'.$this->l('use my references').'</label><br /><i>'.$this->l('or').'</i><br />
					<input type="radio" name="references" id="reference_on" value="1"'.(Configuration::get('GSHOPPING_REFERENCES') ? ' checked="checked"' : '').' /> <label for="reference_on" style="font-weight: normal; float: none;">'.$this->l('use my supplier references').'</label>
				</p>
				<p><b>'.$this->l('Default shipping cost :').'</b>
					<input type="text" name="shipping" value="'.Configuration::get('GSHOPPING_SHIPPING').'"/>
				</p>
				<p><b>'.$this->l('Default carrier :').'</b>
					<select name="carrier">';
		foreach ($carriers as $carrier)
					$output .= '<option value="'.$carrier['name'].'">'.$carrier['name'].'</option>';
		$output .='</select>
				</p>

				<hr size="1" />
				<h2>'.$this->l('Features (optional)').'</h2>
				<p><b>'.$this->l('If you sell books or multimedia, you can specify the fabrication/publication date, the author and the edition ("Collector" for instance).').'</b><br /><br />'.
				$this->l('For that, you\'ve to choose each corresponding feature among the existing features in your catalog:').'</p>
				<p>
					'.$this->l('Author:').'
					<select name="id_feature_author">
						'.$this->_getFeaturesSelect(intval(Configuration::get('GSHOPPING_ID_FEATURE_AUTHOR'))).'
					</select>
				</p>
				<p>
					'.$this->l('Fabrication/Publication year:').'
					<select name="id_feature_year">
						'.$this->_getFeaturesSelect(intval(Configuration::get('GSHOPPING_ID_FEATURE_YEAR'))).'
					</select>
				</p>
				<p>
					'.$this->l('Edition:').'
					<select name="id_feature_edition">
						'.$this->_getFeaturesSelect(intval(Configuration::get('GSHOPPING_ID_FEATURE_EDITION'))).'
					</select>
				</p>
				<hr size="1" />
				<h2>'.$this->l('Exported Categories :').'</h2>
				<div class="margin-form" style="padding-left:0;padding-right:0">';

				if ($nb > 15)
					$output .='<a href="#" id="prev"><b>'.$this->l('Previous Page').'</b></a><a href="#" id="next" style="margin-left:430px"><b>'.$this->l('Next Page').'</b></a>';

				$output .= '<br /><br />
				<table cellspacing="0" cellpadding="0" class="table" id="category">
					<tr>
						<th>'.$this->l('ID').'</th>
						<th>'.$this->l('Name').'</th>
						<th>'.$this->l('Google Product Category').'</th>
					</tr>
				</table>
				<center id="loader"><img src="../modules/gshopping/loader.gif" alt="Please wait..." /></center>
				</div>
				<center><input type="submit" name="submitGShopping" value="'.$this->l('Save settings').'" class="button" /></center>
			</fieldset>
			<p class="clear">&nbsp;</p>
			<fieldset class="width4">
				<legend>'.$this->l('Export links').'</legend>
				<p>'.$this->l('Depending on currencies and languages you\'ve installed on your shop, please find below an list of export links that you can submit to Google Merchant Center').'.</p>
				<p>'.$this->l('Create an feed on Google Merchant Center (e.g : export.xml), then create a new "Planning", and copy paste one of those links :').
				'<ul>'.$links.'</ul>
			</fieldset>
		</form>';

		return $output;
	}
}

class SimpleXMLExtendedGShopping extends SimpleXMLElement
{
	public function addCData($nodeName, $value, $ns = NULL)
	{
		$node = $this->addChild($nodeName, NULL, $ns);
		$node = dom_import_simplexml($node);
		$no = $node->ownerDocument;
		$node->appendChild($no->createCDATASection($value));
	}
}