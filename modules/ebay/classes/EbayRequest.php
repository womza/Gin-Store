<?php
/**
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (file_exists(dirname(__FILE__).'/EbayCountrySpec.php'))
	require_once(dirname(__FILE__).'/EbayCountrySpec.php');

class EbayRequest
{
	public $response;
	public $runame;
	public $itemID;
	public $error;
	public $itemConditionError;
	public $errorCode;

	private $devID;
	private $appID;
	private $certID;
	private $apiUrl;
	private $apiCall;
	private $loginUrl;
	private $compatibility_level;
	private $debug;
	private $dev = false;
	private $ebay_country;

	private $smarty_data;
	
	private $ebay_profile;
    
    private $context;
    
    private $write_api_logs;

	public function __construct($id_ebay_profile = null, $context = null)
	{
		/** Backward compatibility */
		require(dirname(__FILE__).'/../backward_compatibility/backward.php');

		$this->itemConditionError = false;
		$this->debug = (boolean)Configuration::get('EBAY_ACTIVATE_LOGS');

        if ($id_ebay_profile)
            $this->ebay_profile = new EbayProfile($id_ebay_profile);
        else
            $this->ebay_profile = EbayProfile::getCurrent();

        if ($this->ebay_profile)
            $this->ebay_country = EbayCountrySpec::getInstanceByKey($this->ebay_profile->getConfiguration('EBAY_COUNTRY_DEFAULT'), $this->dev);
        else
            $this->ebay_country = EbayCountrySpec::getInstanceByKey('gb');            

        if ($context)
            $this->context = $context;

		/**
		 * Sandbox params
		 **/

		$this->devID = '1db92af1-2824-4c45-8343-dfe68faa0280';

		if ($this->dev)
		{
			$this->appID = 'Prestash-2629-4880-ba43-368352aecc86';
			$this->certID = '6bd3f4bd-3e21-41e8-8164-7ac733218122';
			$this->apiUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
			$this->compatibility_level = 719;
			$this->runame = 'Prestashop-Prestash-2629-4-hpehxegu';
			$this->loginURL = $this->ebay_country->getSiteSignin();
		}
		else
		{
			$this->appID = 'Prestash-70a5-419b-ae96-f03295c4581d';
			$this->certID = '71d26dc9-b36b-4568-9bdb-7cb8af16ac9b';
			$this->apiUrl = 'https://api.ebay.com/ws/api.dll';
			$this->compatibility_level = 741;
			$this->runame = 'Prestashop-Prestash-70a5-4-pepwa';
			$this->loginURL = $this->ebay_country->getSiteSignin();
		}
        
        $this->write_api_logs = Configuration::get('EBAY_API_LOGS');

	}

	public function getLoginUrl()
	{
		return $this->loginURL;
	}

	public function login()
	{
		$response = $this->_makeRequest('GetSessionID', array(
			'version' => $this->compatibility_level,
			'ru_name' => $this->runame
		));

		if ($response === false)
			return false;

		return ($this->session = (string)$response->SessionID);
	}

	public function fetchToken($username, $session)
	{
		$response = $this->_makeRequest('FetchToken', array(
			'username' => $username,
			'session_id' => $session,
		));

		if ($response === false)
			return false;

		return (string)$response->eBayAuthToken;
	}

	/**
	 * Get User Profile Information
	 *
	 **/
	public function getUserProfile($username)
	{
		//Change API URL
		$apiUrl = $this->apiUrl;
		$this->apiUrl = ($this->dev) ? 'http://open.api.sandbox.ebay.com/shopping?' : 'http://open.api.ebay.com/shopping?';
		$response = $this->_makeRequest('GetUserProfile', array('user_id' => $username), true);

		if ($response === false)
			return false;

		$userProfile = array(
			'StoreUrl' => $response->User->StoreURL,
			'StoreName' => $response->User->StoreName,
			'SellerBusinessType' => $response->User->SellerBusinessType
		);

		$this->apiUrl = $apiUrl;

		return $userProfile;
	}

	public function getCategories()
	{
		$response = $this->_makeRequest('GetCategories', array(
			'version' => $this->compatibility_level,
			'category_site_id' => $this->ebay_country->getSiteID(),
		));

		if ($response === false)
			return false;

		$categories = array();

		foreach ($response->CategoryArray->Category as $cat)
		{
			$category = array();

			foreach ($cat as $key => $value)
				$category[(string)$key] = (string)$value;

			$categories[] = $category;
		}

		return $categories;
	}

	/**
	 * Returns what categories accept multi_sku
	 * Warning: no row is returned if the value is inherited from the parent category
	 *
	 **/
	public function getCategoriesSkuCompliancy()
	{
		$response = $this->_makeRequest('GetCategoryFeatures', array(
			'feature_id' => 'VariationsEnabled',
			'version' => $this->compatibility_level
		));

		if ($response === false)
			return false;

		$compliancies = array();

		foreach ($response->Category as $cat)
			$compliancies[(string)$cat->CategoryID] = ((string)$cat->VariationsEnabled === 'true' ? 1 : 0);

		return $compliancies;
	}

	public function GetCategoryFeatures($category_id)
	{
		$response = $this->_makeRequest('GetCategoryFeatures', array(
			'version' => $this->compatibility_level,
			'category_id' => $category_id
		));

		if ($response === false)
			return false;

		return $response;
	}

	public function GetCategorySpecifics($category_id)
	{
		$response = $this->_makeRequest('GetCategorySpecifics', array(
			'version' => $this->compatibility_level,
			'category_id' => $category_id
		));

		if ($response === false)
			return false;

		return $response;
	}

	public function getSuggestedCategory($query)
	{
		$response = $this->_makeRequest('GetSuggestedCategories', array(
			'version' => $this->compatibility_level,
			'query' => Tools::substr(Tools::strtolower($query), 0, 350)
		));

		if ($response === false)
			return false;

		if (isset($response->SuggestedCategoryArray->SuggestedCategory[0]->Category->CategoryID))
			return (int)$response->SuggestedCategoryArray->SuggestedCategory[0]->Category->CategoryID;

		return 0;
	}

	/**
	 * Methods to retrieve the eBay global returns policies
	 *
	 **/
	public function getReturnsPolicies()
	{
		$response = $this->_makeRequest('GeteBayDetails', array(
			'detail_name' => 'ReturnPolicyDetails',
		));

		if ($response === false)
			return false;

		$returns_policies = $returns_within = array();

		foreach ($response->ReturnPolicyDetails as $return_policy_details)
			foreach ($return_policy_details as $key => $returns) {
				if ($key == 'ReturnsAccepted')
					$returns_policies[] = array('value' => (string)$returns->ReturnsAcceptedOption, 'description' => (string)$returns->Description);
				else if ($key == 'ReturnsWithin') 
					$returns_within[] = array('value' => (string)$returns->ReturnsWithinOption, 'description' => (string)$returns->Description);
				else if ($key == 'ShippingCostPaidBy')
					$returns_whopays[] = array('value' => (string)$returns->ShippingCostPaidByOption, 'description' => (string)$returns->Description);
			}

		return array(
			'ReturnsAccepted' => $returns_policies,
			'ReturnsWithin' => $returns_within, 
			'ReturnsWhoPays' => $returns_whopays
		);
	}

	public function getInternationalShippingLocations()
	{
		$response = $this->_makeRequest('GeteBayDetails', array(
			'detail_name' => 'ShippingLocationDetails',
		));

		if ($response === false)
			return false;

		$shipping_locations = array();

		foreach ($response->ShippingLocationDetails as $line)
			$shipping_locations[] = array(
				'description' => strip_tags($line->Description->asXML()),
				'location' => strip_tags($line->ShippingLocation->asXML())
			);

		return $shipping_locations;
	}

	public function getExcludeShippingLocations()
	{
		$response = $this->_makeRequest('GeteBayDetails', array(
			'detail_name' => 'ExcludeShippingLocationDetails',
		));

		if ($response === false)
			return false;

		// Load xml in array
		$shipping_locations = array();

		foreach ($response->ExcludeShippingLocationDetails as $line)
		{
			$shipping_locations[] = array(
				'region' => strip_tags($line->Region->asXML()),
				'description' => strip_tags($line->Description->asXML()),
				'location' => strip_tags($line->Location->asXML())
			);
		}

		return $shipping_locations;
	}

	public function getCarriers()
	{
		$response = $this->_makeRequest('GeteBayDetails', array(
			'detail_name' => 'ShippingServiceDetails',
		));

		if ($response === false)
			return false;

		// Load xml in array
		$carriers = array();

		foreach ($response->ShippingServiceDetails as $carrier)
			$carriers[] = array(
				'description' => strip_tags($carrier->Description->asXML()),
				'shippingService' => strip_tags($carrier->ShippingService->asXML()),
				'shippingServiceID' => strip_tags($carrier->ShippingServiceID->asXML()),
				'ServiceType' => strip_tags($carrier->ServiceType->asXML()),
				'InternationalService' => (isset($carrier->InternationalService) ? strip_tags($carrier->InternationalService->asXML()) : false),
                'ebay_site_id' => (int)$this->ebay_profile->ebay_site_id
			);

		return $carriers;
	}

	public function getDeliveryTimeOptions()
	{
		$response = $this->_makeRequest('GeteBayDetails', array(
			'detail_name' => 'DispatchTimeMaxDetails',
		));

		if ($response === false)
			return false;

		$delivery_time_options = array();
		foreach ($response->DispatchTimeMaxDetails as $DeliveryTimeOption)
			$delivery_time_options[] = array(
				'DispatchTimeMax' => strip_tags($DeliveryTimeOption->DispatchTimeMax->asXML()),
				'description' => strip_tags($DeliveryTimeOption->Description->asXML())
			);

		array_multisort($delivery_time_options);

		return $delivery_time_options;
	}


	/**
	 * Add / Update / End Product Methods
	 *
	 **/
	public function addFixedPriceItem($data = array())
	{
		// Check data
		if (!$data)
			return false;
        
        $currency = new Currency($this->ebay_profile->getConfiguration('EBAY_CURRENCY'));

		$vars = array(
			'sku' => 'prestashop-'.$data['id_product'],
			'title' => Tools::substr(self::prepareTitle($data), 0, 80),
			'pictures' => isset($data['pictures']) ? $data['pictures'] : array(),
			'description' => $data['description'],
			'category_id' => $data['categoryId'],
			'condition_id' => $data['condition'],
			'price_update' => !isset($data['noPriceUpdate']),
			'start_price' => $data['price'],
			'country' => Tools::strtoupper($this->ebay_profile->getConfiguration('EBAY_SHOP_COUNTRY')),
			'country_currency' => $currency->iso_code,
			'dispatch_time_max' => $this->ebay_profile->getConfiguration('EBAY_DELIVERY_TIME'),
			'listing_duration' => $this->ebay_profile->getConfiguration('EBAY_LISTING_DURATION'),
			'pay_pal_email_address' => $this->ebay_profile->getConfiguration('EBAY_PAYPAL_EMAIL'),
			'postal_code' => $this->ebay_profile->getConfiguration('EBAY_SHOP_POSTALCODE'),
			'quantity' => $data['quantity'],
			'item_specifics' => $data['item_specifics'],
			'return_policy' => $this->_getReturnPolicy(),
			'shipping_details' => $this->_getShippingDetails($data),
			'buyer_requirements_details' => $this->_getBuyerRequirementDetails($data),
			'site' => $this->ebay_country->getSiteName(),
            'autopay' => $this->ebay_profile->getConfiguration('EBAY_IMMEDIATE_PAYMENT'),
		);
        
        if (isset($data['price_original']) && ($data['price_original'] > $data['price']))
            $vars['price_original'] = $data['price_original'];
        
        if (isset($data['ebay_store_category_id']) && $data['ebay_store_category_id'])
            $vars['ebay_store_category_id'] = $data['ebay_store_category_id'];

		$response = $this->_makeRequest('AddFixedPriceItem', $vars);

        $this->_logApiCall('addFixedPriceItem', $vars, $response, $data['id_product']);

		if ($response === false)
			return false;
        
		return $this->_checkForErrors($response);
	}

	public function reviseFixedPriceItem($data = array())
	{
		// Check data
		if (!$data)
			return false;

		$vars = array(
			'item_id' => $data['itemID'],
			'condition_id' => $data['condition'],
			'pictures' => isset($data['pictures']) ? $data['pictures'] : array(),
			'sku' => 'prestashop-'.$data['id_product'],
			'dispatch_time_max' => $this->ebay_profile->getConfiguration('EBAY_DELIVERY_TIME'),
			'listing_duration' => $this->ebay_profile->getConfiguration('EBAY_LISTING_DURATION'),
			'quantity' => $data['quantity'],
			'price_update' => !isset($data['noPriceUpdate']),
			'start_price' => $data['price'],
			'resynchronize' => ($this->ebay_profile->getConfiguration('EBAY_SYNC_OPTION_RESYNC') != 1),
			'title' => Tools::substr(self::prepareTitle($data), 0, 80),
			'description' => $data['description'],
			'shipping_details' => $this->_getShippingDetails($data),
			'buyer_requirements_details' => $this->_getBuyerRequirementDetails($data),
			'return_policy' => $this->_getReturnPolicy(),
			'item_specifics' => $data['item_specifics'],
            'country' => Tools::strtoupper($this->ebay_profile->getConfiguration('EBAY_SHOP_COUNTRY')),
            'autopay' => $this->ebay_profile->getConfiguration('EBAY_IMMEDIATE_PAYMENT')            
		);

	 if (isset($data['ebay_store_category_id']) && $data['ebay_store_category_id'])
	 	$vars['ebay_store_category_id'] = $data['ebay_store_category_id'];
        
        if (isset($data['price_original']) && ($data['price_original'] > $data['price']))
            $vars['price_original'] = $data['price_original'];        

		$response = $this->_makeRequest('ReviseFixedPriceItem', $vars);

        $this->_logApiCall('reviseFixedPriceItem', $vars, $response, $data['id_product']);

		if ($response === false)
			return false;

		return $this->_checkForErrors($response);
	}

	public function endFixedPriceItem($ebay_item_id, $id_product = null)
	{
		if (!$ebay_item_id)
			return false;

		$response_vars = array('item_id' => $ebay_item_id);

		if ($id_product)
			$response_vars['sku'] = 'prestashop-'.$id_product;

		$response = $this->_makeRequest('EndFixedPriceItem', $response_vars);
        
        $this->_logApiCall('endFixedPriceItem', $response_vars, $response, $id_product);

		if ($response === false)
			return false;

		return $this->_checkForErrors($response);
	}

	public function addFixedPriceItemMultiSku($data = array())
	{
		// Check data
		if (!$data)
			return false;
        
        $currency = new Currency($this->ebay_profile->getConfiguration('EBAY_CURRENCY'));

		// Build the request Xml string
		$vars = array(
			'country' => Tools::strtoupper($this->ebay_profile->getConfiguration('EBAY_SHOP_COUNTRY')),
			'country_currency' => $currency->iso_code,
			'description' => $data['description'],
			'condition_id' => $data['condition'],
			'dispatch_time_max' => $this->ebay_profile->getConfiguration('EBAY_DELIVERY_TIME'),
			'listing_duration' => $this->ebay_profile->getConfiguration('EBAY_LISTING_DURATION'),
			'pay_pal_email_address' => $this->ebay_profile->getConfiguration('EBAY_PAYPAL_EMAIL'),
			'postal_code' => $this->ebay_profile->getConfiguration('EBAY_SHOP_POSTALCODE'),
			'category_id' => $data['categoryId'],
			'title' => Tools::substr(self::prepareTitle($data), 0, 80),
			'pictures' => isset($data['pictures']) ? $data['pictures'] : array(),
			'return_policy' => $this->_getReturnPolicy(),
			'price_update' => !isset($data['noPriceUpdate']),
			'variations' => $this->_getVariations($data),
			'shipping_details' => $this->_getShippingDetails($data),
			'buyer_requirements_details' => $this->_getBuyerRequirementDetails($data),
			'site' => $this->ebay_country->getSiteName(),
			'item_specifics' => $data['item_specifics'],
            'autopay' => $this->ebay_profile->getConfiguration('EBAY_IMMEDIATE_PAYMENT')            
		);
        
        if (isset($data['ebay_store_category_id']) && $data['ebay_store_category_id'])
            $vars['ebay_store_category_id'] = $data['ebay_store_category_id'];        

		// Send the request and get response
		$response = $this->_makeRequest('AddFixedPriceItem', $vars);
        
        $this->_logApiCall('addFixedPriceItemMultiSku', $vars, $response);        

		if ($response === false)
			return false;

		return $this->_checkForErrors($response);
	}

	public function relistFixedPriceItem($item_id)
	{
		 // Check data
		if (!$item_id)
			return false;

		$response = $this->_makeRequest('RelistFixedPriceItem', array(
			'item_id' => (int)$item_id,
		));

		if ($response === false)
			return false;

		return $this->_checkForErrors($response);
	}

	public function reviseFixedPriceItemMultiSku($data = array())
	{
		// Check data
		if (!$data)
			return false;

		// Set Api Call
		$this->apiCall = 'ReviseFixedPriceItem';
        
        $currency = new Currency($this->ebay_profile->getConfiguration('EBAY_CURRENCY'));

		$vars = array(
			'item_id' => $data['itemID'],
			'country' => Tools::strtoupper($this->ebay_profile->getConfiguration('EBAY_SHOP_COUNTRY')),
			'country_currency' => $currency->iso_code,
			'condition_id' => $data['condition'],
			'dispatch_time_max' => $this->ebay_profile->getConfiguration('EBAY_DELIVERY_TIME'),
			'listing_duration' => $this->ebay_profile->getConfiguration('EBAY_LISTING_DURATION'),
			'listing_type' => 'FixedPriceItem',
			'payment_method' => 'PayPal',
			'pay_pal_email_address' => $this->ebay_profile->getConfiguration('EBAY_PAYPAL_EMAIL'),
			'postal_code' => $this->ebay_profile->getConfiguration('EBAY_SHOP_POSTALCODE'),
			'category_id' => $data['categoryId'],
			'pictures' => isset($data['pictures']) ? $data['pictures'] : array(),
			'return_policy' => $this->_getReturnPolicy(),
			'resynchronize' => ($this->ebay_profile->getConfiguration('EBAY_SYNC_OPTION_RESYNC') != 1),
			'title' => Tools::substr(self::prepareTitle($data), 0, 80),
			'description' => $data['description'],
			'shipping_details' => $this->_getShippingDetails($data),
			'buyer_requirements_details' => $this->_getBuyerRequirementDetails($data),
			'site' => $this->ebay_country->getSiteName(),
			'variations' => $this->_getVariations($data),
			'item_specifics' => $data['item_specifics'],
            'autopay' => $this->ebay_profile->getConfiguration('EBAY_IMMEDIATE_PAYMENT')            
		);
        
        if (isset($data['ebay_store_category_id']) && $data['ebay_store_category_id'])
            $vars['ebay_store_category_id'] = $data['ebay_store_category_id'];        

		$response = $this->_makeRequest('ReviseFixedPriceItem', $vars);

		$this->_logApiCall('reviseFixedPriceItem', $vars, $response, $data['id_product']);
		
		if ($response === false)
			return false;

		return $this->_checkForErrors($response);
	}

	public function getOrders($create_time_from, $create_time_to, $page)
	{
		// Check data
		if (!$create_time_from || !$create_time_to)
			return false;

		$vars = array(
			'create_time_from' => $create_time_from,
			'create_time_to' => $create_time_to,
			'page_number' => $page,
		);

		$response = $this->_makeRequest('GetOrders', $vars);

		if ($response === false)
			return false;

		// Checking Errors
		$this->error = '';

		if (isset($response->Errors) && isset($response->Ack) && (string)$response->Ack != 'Success' && (string)$response->Ack != 'Warning')
			foreach ($response->Errors as $e)
			{
				if ($this->error != '')
					$this->error .= '<br />';
				if ($e->ErrorCode == 932 || $e->ErrorCode == 931)
					Configuration::updateValue('EBAY_TOKEN_REGENERATE', true, false, 0, 0);
				$this->error .= (string)$e->LongMessage;
			}

		return isset($response->OrderArray->Order) ? $response->OrderArray->Order : array();
	}
    
	/**
	 * Get Store Categories
	 *
	 **/
	public function getStoreCategories()
	{

		// Set Api Call
		$this->apiCall = 'GetStore';
		$response = $this->_makeRequest('GetStore');
        return ($response === false) ? false : (isset($response->Store) ? $response->Store->CustomCategories->CustomCategory : false);
        
	}    
    
	/**
	 * Set order status to "shipped"
	 *
	 **/    
	public function orderHasShipped($id_order_ref)
	{
		if (!$id_order_ref)
			return false;

		// Set Api Call
		$this->apiCall = 'CompleteSale';

		$vars = array(
		    'id_order_ref' => $id_order_ref,
		);

		$response = $this->_makeRequest('CompleteSale', $vars);
        
        $this->_logApiCall('completeSale', $vars, $response);

		return ($response === false) ? false : $this->_checkForErrors($response);
	}
    
	/**
	 * Set order status to "shipped"
	 *
	 **/
	public function updateOrderTracking($id_order_ref, $tracking_number, $carrier_name)
	{
		// Check data
		if (!$id_order_ref)
			return false;

		// Set Api Call
		$this->apiCall = 'CompleteSale';

		$vars = array(
		    'id_order_ref' => $id_order_ref,
            'tracking_number' => $tracking_number,
            'carrier_name' => $carrier_name,
		);

		$response = $this->_makeRequest('CompleteSale', $vars);
        $this->context = 'ORDER_BACKOFFICE';
        $this->_logApiCall('completeSale', $vars, $response);
        
		return ($response === false) ? false : $this->_checkForErrors($response);
	}
    
	/**
	 * Add / Update / End Product Methods
	 *
	 **/
	public function uploadSiteHostedPicture($picture_url, $picture_name)
	{
		if (!$picture_url || !$picture_name)
			return false;

		$vars = array(
			'picture_url' => $picture_url,
			'picture_name' => $picture_name,
			'version' => $this->compatibility_level
		);

		$response = $this->_makeRequest('UploadSiteHostedPictures', $vars);

		if ($response === false)
			return false;

		if ($this->_checkForErrors($response))
			return (string)$response->SiteHostedPictureDetails->FullURL;

		return null;
	}

	private function _getShippingDetails($data)
	{
		$vars = array(
			'excluded_zones' => $data['shipping']['excludedZone'],
			'national_services' => $data['shipping']['nationalShip'],
			'international_services' => $data['shipping']['internationalShip'],
			'currency_id' => $this->ebay_country->getCurrency(),
		);

		$this->smarty->assign($vars);

		return $this->smarty->fetch(dirname(__FILE__).'/../ebay/api/GetShippingDetails.tpl');
	}

	private function _getBuyerRequirementDetails($datas)
	{
		$vars = array('has_excluded_zones' => (boolean)count($datas['shipping']['excludedZone']));
		$this->smarty->assign($vars);

		return $this->smarty->fetch(dirname(__FILE__).'/../ebay/api/GetBuyerRequirementDetails.tpl');
	}

	private function _getReturnPolicy()
	{
		$returns_policy_configuration = $this->ebay_profile->getReturnsPolicyConfiguration();
		$vars = array(
			'returns_accepted_option' => $returns_policy_configuration->ebay_returns_accepted_option,
			'description' => preg_replace('#<br\s*?/?>#i', "\n", $returns_policy_configuration->ebay_returns_description),
			'within' => $returns_policy_configuration->ebay_returns_within,
			'whopays' => $returns_policy_configuration->ebay_returns_who_pays
		);

		$this->smarty->assign($vars);

		return $this->smarty->fetch(dirname(__FILE__).'/../ebay/api/GetReturnPolicy.tpl');
	}

	private function _getVariations($data)
	{
		$variation_pictures = array();
		$variation_specifics_set = array();

		if (isset($data['variations']))
		{
			$last_specific_name = '';
			$attribute_used = array();

			foreach ($data['variations'] as $key => $variation)
			{
				if(isset($variation['variations']))
				{
					foreach ($variation['variations'] as $variation_key => $variation_element)
						if (!isset($attribute_used[md5($variation_element['name'].$variation_element['value'])]) && isset($variation['pictures'][$variation_key]))
						{
							if ($last_specific_name != $variation_element['name'])
								$variation_pictures[$key][$variation_key]['name'] = $variation_element['name'];

							$variation_pictures[$key][$variation_key]['value'] = $variation_element['value'];
							$variation_pictures[$key][$variation_key]['url'] = $variation['pictures'][$variation_key];

							$attribute_used[md5($variation_element['name'].$variation_element['value'])] = true;
							$last_specific_name = $variation_element['name'];
						}

					foreach ($variation['variation_specifics'] as $name => $value)
					{
						if (!isset($variation_specifics_set[$name]))
							$variation_specifics_set[$name] = array();

						if (!in_array($value, $variation_specifics_set[$name]))
							$variation_specifics_set[$name][] = $value;
					}
				}
			}
		}

		$vars = array(
			'variations' => isset($data['variations']) ? $data['variations'] : array(),
			'variations_pictures' => $variation_pictures,
			'price_update' => !isset($data['noPriceUpdate']),
			'variation_specifics_set' => $variation_specifics_set,
		);

		$this->smarty->assign($vars);

		return $this->smarty->fetch(dirname(__FILE__).'/../ebay/api/GetVariations.tpl');
	}

	private function _buildHeadersShopping($api_call)
	{
		$headers = array (
			'X-EBAY-API-APP-ID:'.$this->appID,
			'X-EBAY-API-VERSION:'.$this->compatibility_level,
			'X-EBAY-API-SITE-ID:'.$this->ebay_country->getSiteID(),
			'X-EBAY-API-CALL-NAME:'.$api_call,
			'X-EBAY-API-REQUEST-ENCODING:XML',

			//For api call on a different endpoint we need to add the content type
			'Content-type:text/xml;charset=utf-8'
		);

		return $headers;
	}

	private function _buildHeaders($api_call)
	{
		$headers = array (
			// Regulates versioning of the XML interface for the API
			'X-EBAY-API-COMPATIBILITY-LEVEL: '.$this->compatibility_level,

			// Set the keys
			'X-EBAY-API-DEV-NAME: '.$this->devID,
			'X-EBAY-API-APP-NAME: '.$this->appID,
			'X-EBAY-API-CERT-NAME: '.$this->certID,

			// The name of the call we are requesting
			'X-EBAY-API-CALL-NAME: '.$api_call,

			//SiteID must also be set in the Request's XML
			//SiteID = 0  (US) - UK = 3, Canada = 2, Australia = 15, ....
			//SiteID Indicates the eBay site to associate the call with
			'X-EBAY-API-SITEID: '.$this->ebay_country->getSiteID(),
		);

		return $headers;
	}

	private function _makeRequest($api_call, $vars = array(), $shoppingEndPoint = false)
	{
		$vars = array_merge($vars, array(
			'ebay_auth_token' => ($this->ebay_profile ? $this->ebay_profile->getToken() : ''),
			'error_language' => $this->ebay_country->getLanguage(),
		));

		$this->smarty->assign($vars);
		$request = $this->smarty->fetch(dirname(__FILE__).'/../ebay/api/'.$api_call.'.tpl');

		$connection = curl_init();
		curl_setopt($connection, CURLOPT_URL, $this->apiUrl);
		curl_setopt($connection, CURLINFO_HEADER_OUT, true);

		// Stop CURL from verifying the peer's certificate
		curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);

		// Set the headers (Different headers depending on the api call !)
		if ($shoppingEndPoint)
			curl_setopt($connection, CURLOPT_HTTPHEADER, $this->_buildHeadersShopping($api_call));
		else
			curl_setopt($connection, CURLOPT_HTTPHEADER, $this->_buildHeaders($api_call));

		curl_setopt($connection, CURLOPT_POST, 1);
		curl_setopt($connection, CURLOPT_POSTFIELDS, $request); // Set the XML body of the request
		curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1); // Set it to return the transfer as a string from curl_exec

		$response = curl_exec($connection); // Send the Request

		curl_close($connection); // Close the connection

		// Debug
		
		if ($this->debug)
		{
			if (!file_exists(dirname(__FILE__).'/../log/request.txt'))
				file_put_contents(dirname(__FILE__).'/../log/request.txt', "<?php\n\n", FILE_APPEND | LOCK_EX);

			file_put_contents(dirname(__FILE__).'/../log/request.txt', date('d/m/Y H:i:s')."\n\n HEADERS : \n".print_r($this->_buildHeaders($api_call), true), FILE_APPEND | LOCK_EX);

			file_put_contents(dirname(__FILE__).'/../log/request.txt', date('d/m/Y H:i:s')."\n\n".$request."\n\n".$response."\n\n-------------------\n\n", FILE_APPEND | LOCK_EX);
		}

		// Send the request and get response
		if (stristr($response, 'HTTP 404') || !$response)
		{
			$this->error = 'Error sending '.$api_call.' request';
			return false;
		}

		return simplexml_load_string($response);
	}

	private function _checkForErrors($response)
	{
		$this->error = '';
		$this->errorCode = '';

		if (isset($response->Errors) && isset($response->Ack) && (string)$response->Ack != 'Success' && (string)$response->Ack != 'Warning')
			foreach ($response->Errors as $e)
			{
				// if product no longer on eBay, we log the error code
				if ((int)$e->ErrorCode == 291)
					$this->errorCode = (int)$e->ErrorCode;
				elseif (in_array((int)$e->ErrorCode, array(21916883,21916884)))
					$this->itemConditionError = true;

				// We log error message
				if ($e->SeverityCode == 'Error')
				{
					if ($this->error != '')
						$this->error .= '<br />';

					$this->error .= (string)$e->LongMessage;

					if (isset($e->ErrorParameters->Value))
						$this->error .= '<br />'.(string)$e->ErrorParameters->Value;
				}
			}

		// Checking Success
		$this->itemID = 0;
		
		if (isset($response->Ack) && ((string)$response->Ack == 'Success' || (string)$response->Ack == 'Warning'))
			$this->itemID = (string)$response->ItemID;
		elseif (!$this->error)
			$this->error = 'Sorry, technical problem, try again later.';

		return empty($this->error);
	}
    
    private function _logApiCall($type, $data_sent, $response, $id_product = null, $id_order = null) {
        
        if (!$this->write_api_logs)
            return;
        
        $log = new EbayApiLog();
        
        $log->id_ebay_profile = $this->ebay_profile->id;
        $log->type = $type;
        $log->context = $this->context;

        $log->data_sent = Tools::jsonEncode($data_sent);
        $log->response = Tools::jsonEncode($response);
        
        if ($id_product)
            $log->id_product = (int)$id_product;

        if ($id_order)
            $log->id_order = (int)$id_order;
        
        return $log->save();
    }

	public function getDev() {
		return $this->dev;
	}

	public static function prepareTitle($data)
	{
		$product = new Product($data['real_id_product'], false, $data['id_lang']);
		$features = Feature::getFeatures($data['id_lang']);
		$features_product = $product->getFrontFeatures($data['id_lang']);
		$tags = array(
			'{TITLE}',
			'{BRAND}',
			'{REFERENCE}',
			'{EAN}',
		);
		$values = array(
			$data['name'],
			$data['manufacturer_name'],
			$data['reference'],
			$data['ean13'],
		);
		foreach ($features as $feature)
		{
			$tags[] = trim(str_replace(' ', '_', Tools::strtoupper('{FEATURE_'.$feature['name'].'}')));
			$hasFeature = array_map(array('EbayRequest', 'getValueOfFeature'), $features_product, $feature);
			if (isset($hasFeature[0]) &&$hasFeature[0])
				$values[] = $hasFeature[0];
			else
				$values[] = '';
		}
		
		return EbaySynchronizer::fillTemplateTitle($tags, $values, $data['titleTemplate']);
	}

	public static function getValueOfFeature($val, $feature)
	{
        if (!isset($feature['id_feature']))
            return false;
        
		return ((int)$val['id_feature'] == (int)$feature['id_feature'] ? $val['value'] : false);
	}

}