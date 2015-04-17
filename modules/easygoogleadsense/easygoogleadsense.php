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

if (!defined('_PS_VERSION_'))
	exit;

class Easygoogleadsense extends Module
{
    protected $output = '';
    protected $hooks = array();

    public function __construct()
    {
        $this->name = 'easygoogleadsense';
        $this->tab = 'advertising_marketing';
        $this->version = '1.0.3';
        $this->author = 'Jose Aguilar';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->module_key = '057442542b52f87560e3e4ad5b97c40f';
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        parent::__construct();

        $this->displayName = $this->l('Easy Google Adsense');
        $this->description = $this->l('Add google adsense advertising in your shop.');
        
        include_once dirname(__FILE__).'/classes/Ega.php';
        
        $this->hooks = array(
            array('name' => $this->l('Top of page'), 'value' => 'displayTop'),
            array('name' => $this->l('Top banner'), 'value' => 'displayBanner'),
            array('name' => $this->l('Navigation'), 'value' => 'displayNav'),
            array('name' => $this->l('Left column blocks'), 'value' => 'displayLeftColumn'),
            array('name' => $this->l('Right column blocks'), 'value' => 'displayRightColumn'),
            array('name' => $this->l('Footer'), 'value' => 'displayFooter'),
            array('name' => $this->l('Homepage content'), 'value' => 'displayHome'),
            array('name' => $this->l('Home Page Tabs Content'), 'value' => 'displayHomeTabContent'),
            array('name' => $this->l('Customer account displayed in Front Office'), 'value' => 'displayCustomerAccount'),
            array('name' => $this->l('Customer account creation form'), 'value' => 'displayCustomerAccountForm'),
            array('name' => $this->l('Block above the form for create an account'), 'value' => 'displayCustomerAccountFormTop'),
            array('name' => $this->l('My account block'), 'value' => 'displayMyAccountBlock'),
            array('name' => $this->l('My account block in footer'), 'value' => 'displayMyAccountBlockfooter'),
            array('name' => $this->l('Product footer'), 'value' => 'displayFooterProduct'),
            array('name' => $this->l('New elements on the product page (left column)'), 'value' => 'displayLeftColumnProduct'),
            array('name' => $this->l('New elements on the product page (right column)'), 'value' => 'displayRightColumnProduct'),
            array('name' => $this->l('Product page actions'), 'value' => 'displayProductButtons'),
            array('name' => $this->l('Tabs content on the product page'), 'value' => 'displayProductTabContent'),
            array('name' => $this->l('Extra product comparison'), 'value' => 'displayProductComparison'),
            //array('name' => $this->l('Display new elements in the Front Office, products list'), 'value' => 'displayProductListFunctionalButtons'),
            //array('name' => $this->l('displayProductListReviews'), 'value' => 'displayProductListReviews'),
            array('name' => $this->l('Shopping cart'), 'value' => 'displayShoppingCart'),
            array('name' => $this->l('Shopping cart footer'), 'value' => 'displayShoppingCartFooter'),
            array('name' => $this->l('Before carriers list'), 'value' => 'displayBeforeCarrier'),
            //array('name' => $this->l('Payment'), 'value' => 'displayPayment'),
            //array('name' => $this->l('Payment return'), 'value' => 'displayPaymentReturn'),
            array('name' => $this->l('Top of payment page'), 'value' => 'displayPaymentTop'),
            array('name' => $this->l('Order confirmation page'), 'value' => 'displayOrderConfirmation'),
            //array('name' => $this->l('Order detail'), 'value' => 'displayOrderDetail'),
            array('name' => $this->l('Maintenance Page'), 'value' => 'displayMaintenance'),            
        );
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
                $this->registerHook('header') &&
                $this->registerHook('displayBanner') &&
                $this->registerHook('displayBeforeCarrier') &&
                $this->registerHook('displayCustomerAccount') &&
                $this->registerHook('displayCustomerAccountForm') &&
                $this->registerHook('displayCustomerAccountFormTop') &&
                $this->registerHook('displayFooter') &&
                $this->registerHook('displayFooterProduct') &&
                $this->registerHook('displayHome') &&
                $this->registerHook('displayHomeTab') &&
                $this->registerHook('displayHomeTabContent') &&
                $this->registerHook('displayLeftColumn') &&
                $this->registerHook('displayLeftColumnProduct') &&
                $this->registerHook('displayMaintenance') &&
                $this->registerHook('displayMyAccountBlock') &&
                $this->registerHook('displayMyAccountBlockfooter') &&
                $this->registerHook('displayNav') &&
                $this->registerHook('displayOrderConfirmation') &&
                //$this->registerHook('displayPayment') &&
                //$this->registerHook('displayPaymentReturn') &&
                $this->registerHook('displayPaymentTop') &&
                $this->registerHook('displayProductButtons') &&
                $this->registerHook('displayProductComparison') &&
                //$this->registerHook('displayProductListFunctionalButtons') &&
                //$this->registerHook('displayProductListReviews') &&
                $this->registerHook('displayProductTab') &&
                $this->registerHook('displayProductTabContent') &&
                $this->registerHook('displayRightColumn') &&
                $this->registerHook('displayRightColumnProduct') &&
                $this->registerHook('displayShoppingCart') &&
                $this->registerHook('displayShoppingCartFooter') &&
                $this->registerHook('displayTop');
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }
    
    public function postProcess() {
        
        if (Tools::isSubmit('submitAddAdvertisment')) {
            $errors = '';
            
            if (Tools::getValue('id_easy_google_adsense'))
                $advertisment = new Ega(Tools::getValue('id_easy_google_adsense'));
            else 
                $advertisment = new Ega();
            
            if (!Tools::getValue('title'))
                $errors .= $this->l('The title advertisment is empty.').'<br/>'; 
            
            if (!Tools::getValue('advertisment'))
                $errors .= $this->l('The content advertisment is empty.').'<br/>'; 
            
            if ($errors == '') {
                
                $advertisment->active = (int)Tools::getValue('active');
                $advertisment->id_shop = (int)$this->context->shop->id;
                $advertisment->title = (string)Tools::getValue('title');
                $advertisment->show_title = (string)Tools::getValue('show_title');
                $advertisment->content = Tools::getValue('advertisment');
                $advertisment->hook = (string)Tools::getValue('hook');
                
                if (Tools::getValue('id_easy_google_adsense'))
                    $advertisment->update();
                else 
                    $advertisment->add();      
                
                Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminModules').'&configure='.$this->name.'&conf=4');
            }
            else {
                $this->output .= $this->displayError($errors);   
            }  
        }
        
        if (Tools::isSubmit('statuseasy_google_adsense')) {
            
            $advertisment = new Ega(Tools::getValue('id_easy_google_adsense'));
            if ($advertisment->active == 1)
                $advertisment->active = 0;
            else
                $advertisment->active = 1;
            $advertisment->update();
            Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminModules').'&configure='.$this->name.'&conf=4');
        }
        
        if (Tools::isSubmit('deleteeasy_google_adsense')) {
            $advertisment = new Ega(Tools::getValue('id_easy_google_adsense'));
            $advertisment->delete();
            Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminModules').'&configure='.$this->name.'&conf=4');
        }
        
    }

    public function getContent() {
        $this->postProcess();
        
        if (Tools::isSubmit('updateeasy_google_adsense') || Tools::isSubmit('addeasy_google_adsense')) {
            $this->output .= $this->displayForm();
        }
        else {
            $this->context->smarty->assign('module_dir', $this->_path);
            $this->output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
            $this->output .= $this->renderList();
            $this->output .= $this->displayForm();
            
        }
        
        return $this->output;
    }
    
    public function renderList() {
        $advertisments = Ega::getAdvertisments($this->context->shop->id);

        $fields_list = array(
                'title' => array(
                        'title' => $this->l('Title'),
                        'type' => 'text',
                ),
                'hook' => array(
                        'title' => $this->l('Hook'),
                        'type' => 'text',
                ),
                'active' => array(
                        'title' => $this->l('Status'),
                        'active' => 'status',
                        'type' => 'bool',
                ),
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = true;
        $helper->toolbar_btn['new'] = array(
                'href' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&module_name='.$this->name.'&addeasy_google_adsense',
                'desc' => $this->l('Add New Advertisment', null, null, false)
        );
        $helper->module = $this;
        $helper->identifier = 'id_easy_google_adsense';
        $helper->title = $this->l('Google Adsense Advertisments created');
        $helper->table = 'easy_google_adsense';
        $helper->listTotal = count($advertisments);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        return $helper->generateList($advertisments, $fields_list);
    }

    public function displayForm() {
        
        if (Tools::getValue('id_easy_google_adsense') > 0) {
            $advertisment = new Ega(Tools::getValue('id_easy_google_adsense'));
        }
        
        if (version_compare(_PS_VERSION_, '1.6', '<'))
            $type = 'radio';
        else
            $type = 'switch';
        
        $languages = Language::getLanguages(false);
        foreach ($languages as $k => $language)
            $languages[$k]['is_default'] = (int)$language['id_lang'] == Configuration::get('PS_LANG_DEFAULT');

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = 'easygoogleadsense';
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->languages = $languages;
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = true;
        $helper->toolbar_scroll = true;
        $helper->toolbar_btn = $this->initToolbar();
        $helper->title = $this->displayName;
        $helper->submit_action = 'submitAddAdvertisment';

        $this->fields_form[0]['form'] = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->l('Add advertisment'),
                'image' => $this->_path.'logo.gif'
            ),
            'submit' => array(
                'name' => 'submitAddAdvertisment',
                'title' => $this->l('Save'),
                'class' => 'button pull-right'
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'id_easy_google_adsense',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Title'),
                    'name' => 'title',
                    'required' => true,
                    'lang' => false,
                    'col' => 5,
                ),
                array(
                    'type' => $type,
                    'label' => $this->l('Show title'),
                    'name' => 'show_title',
                    'class' => 't',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                        ),
                        array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Content Advertisment'),
                    'name' => 'advertisment',
                    'lang' => false,
                    'autoload_rte' => false,
                    'desc' => $this->l('Please copy code of google adsense advertisment here.'),
                    'cols' => 20,
                    'rows' => 10,
                ),
                array(
                  'type' => 'select',
                  'label' => $this->l('Position'),
                  'name' => 'hook',
                  'required' => false,
                  'options' => array(
                        'query' => $this->hooks,
                        'id' => 'value',
                        'name' => 'name'
                  )
                ),
                array(
                    'type' => $type,
                    'label' => $this->l('Active'),
                    'name' => 'active',
                    'class' => 't',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                        ),
                        array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                        )
                    ),
                ),
            )
        );
        
        if (!Tools::getValue('id_easy_google_adsense')) {
            $helper->fields_value['id_easy_google_adsense'] = '';
            $helper->fields_value['title'] = '';
            $helper->fields_value['show_title'] = 1;
            $helper->fields_value['advertisment'] = '';
            $helper->fields_value['hook'] = '';
            $helper->fields_value['active'] = 1;
        }
        else {
            $helper->fields_value['id_easy_google_adsense'] = $advertisment->id;
            $helper->fields_value['title'] = $advertisment->title;
            $helper->fields_value['show_title'] = $advertisment->show_title;
            $helper->fields_value['advertisment'] = $advertisment->content;
            $helper->fields_value['hook'] = $advertisment->hook;
            $helper->fields_value['active'] = $advertisment->active; 
        }

        return $helper->generateForm($this->fields_form);
    }

    private function initToolbar() {
        $this->toolbar_btn['save'] = array(
            'href' => '#',
            'desc' => $this->l('Save')
        );

        return $this->toolbar_btn;
    }
	
    public function hookHeader()
    {
        $this->context->controller->addCSS($this->_path.'/css/front.css');
    }
    
    public function getAdevertisments($hook) {
        $advertisments = Ega::getAdvertisments($this->context->shop->id, $hook);

        if (count($advertisments) > 0) {
            
            $this->context->smarty->assign(array(
                'advertisments' => $advertisments,
            ));
            
            return $this->display(__FILE__, 'easygoogleadsense.tpl');
        }
    }
    
    public function hookDisplayTop()
    {   
        return $this->getAdevertisments('displayTop');
    }
    
    public function hookDisplayBanner()
    {
        return $this->getAdevertisments('displayBanner');
    }
    
    public function hookDisplayNav()
    {
        return $this->getAdevertisments('displayNav');
    }
    
    public function hookDisplayFooter()
    {
        return $this->getAdevertisments('displayFooter');
    }
    
    public function hookDisplayLeftColumn()
    {
        return $this->getAdevertisments('displayLeftColumn');
    }
    
    public function hookDisplayRightColumn()
    {
        return $this->getAdevertisments('displayRightColumn');
    }
    
    public function hookDisplayHome()
    {
        return $this->getAdevertisments('displayHome');
    }

    public function hookDisplayHomeTab()
    {
        $advertisments = Ega::getAdvertisments($this->context->shop->id, 'displayHomeTabContent');
        if (count($advertisments) > 0) {
            
            $this->smarty->assign(array(
                'advertisments' => $advertisments,
            ));
            
            return $this->display(__FILE__, 'homeTab.tpl');
        }
    }

    public function hookDisplayHomeTabContent()
    {
        $advertisments = Ega::getAdvertisments($this->context->shop->id, 'displayHomeTabContent');
        if (count($advertisments) > 0) {
            
            $this->smarty->assign(array(
                'advertisments' => $advertisments,
            ));
            
            return $this->display(__FILE__, 'homeTabContent.tpl');
        }
    }
    
    public function hookDisplayCustomerAccount()
    {
        return $this->getAdevertisments('displayCustomerAccount');
    }

    public function hookDisplayCustomerAccountForm()
    {
        return $this->getAdevertisments('displayCustomerAccountForm');
    }

    public function hookDisplayCustomerAccountFormTop()
    {
        return $this->getAdevertisments('displayCustomerAccountFormTop');
    }
    
    public function hookDisplayMyAccountBlock()
    {
        return $this->getAdevertisments('displayMyAccountBlock');
    }

    public function hookDisplayMyAccountBlockfooter()
    {
        return $this->getAdevertisments('displayMyAccountBlockfooter');
    }
    
    public function hookDisplayFooterProduct()
    {
        return $this->getAdevertisments('displayFooterProduct');
    }
    
    public function hookDisplayLeftColumnProduct()
    {
        return $this->getAdevertisments('displayLeftColumnProduct');
    }

    public function hookDisplayRightColumnProduct()
    {
        return $this->getAdevertisments('displayRightColumnProduct');
    }
    
    public function hookDisplayProductButtons()
    {
        return $this->getAdevertisments('displayProductButtons');
    }
    
    public function hookDisplayProductTab()
    {
        $advertisments = Ega::getAdvertisments($this->context->shop->id, 'displayProductTabContent');
        if (count($advertisments) > 0) {
            
            $this->smarty->assign(array(
                'advertisments' => $advertisments,
            ));
            
            return $this->display(__FILE__, 'productTab.tpl');
        }
    }

    public function hookDisplayProductTabContent()
    {
        return $this->getAdevertisments('displayProductTabContent');
    }

    public function hookDisplayProductComparison()
    {
        return $this->getAdevertisments('displayProductComparison');
    }
    
    /*public function hookDisplayProductListFunctionalButtons()
    {
        return $this->getAdevertisments('displayProductListFunctionalButtons');
    }

    public function hookDisplayProductListReviews()
    {
        return $this->getAdevertisments('displayProductListReviews');
    }*/
    
    public function hookDisplayShoppingCart()
    {
        return $this->getAdevertisments('displayShoppingCart');
    }

    public function hookDisplayShoppingCartFooter()
    {
        return $this->getAdevertisments('displayShoppingCartFooter');
    }
    
    public function hookDisplayBeforeCarrier()
    {
        return $this->getAdevertisments('displayBeforeCarrier');
    }
    
    public function hookDisplayPayment()
    {
        return $this->getAdevertisments('displayBeforeCarrier');
    }

    /*public function hookDisplayPaymentReturn()
    {
        return $this->getAdevertisments('displayBeforeCarrier');
    }*/

    public function hookDisplayPaymentTop()
    {
        return $this->getAdevertisments('displayPaymentTop');
    }    
    
    public function hookDisplayOrderConfirmation()
    {
        return $this->getAdevertisments('displayOrderConfirmation');
    }
    
    /*public function hookDisplayOrderDetail()
    {
        return $this->getAdevertisments('displayOrderDetail');
    }*/

    public function hookDisplayMaintenance()
    {
        return $this->getAdevertisments('displayMaintenance');
    }  
}
