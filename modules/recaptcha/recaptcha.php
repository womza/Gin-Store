<?php
/*
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
*
*  @author José Manuel Bermudo Ancio (soy.amarillo@gmail.com)
*  @copyright  2013
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

if (!defined('_PS_VERSION_')){
    exit;
}

require_once dirname(__FILE__) . '/lib/recaptchalib.php';

class reCaptcha extends Module
{
    public function __construct() 
    {
        $this->name = 'recaptcha';
        $this->tab = 'front_office_features';
        $this->version = '0.1';
        $this->author = 'José Manuel Bermudo Ancio';
        $this->need_instance = 0;
        //$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.5');
        $this->dependencies = array();

        parent::__construct();

        $this->displayName = $this->l('Módulo ReCaptcha');
        $this->description = $this->l('Módulo para añadir ReCaptcha al formulario de creación de cuentas.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

        if (!Configuration::get('reCaptcha_private_key')){
            $this->warning = $this->l('Private key not found!');
        }
        
        if (!Configuration::get('reCaptcha_public_key')){
            $this->warning = $this->l('Public key not found!');
        }
    }
    
    public function install() 
    {
        $return = true;
        
        if (Shop::isFeatureActive()){
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        $return &= parent::install();        
        //To show the recaptcha challenge at the registration form
        $return &= $this->registerHook('createAccountForm');
        //To check the recaptcha code inserted by the customer
        $return &= $this->registerHook('actionBeforeSubmitAccount');
        $return &= Configuration::updateValue('reCaptcha_private_key', $this->l('Write your private key here'));
        $return &= Configuration::updateValue('reCaptcha_public_key', $this->l('Write your public key here'));
        
        return $return;
    }
    
    public function uninstall() 
    {
        $return = true;
        
        if (parent::uninstall() == false){
            $return = false;
        }
        
        $return &= Configuration::deleteByName('reCaptcha_private_key');
        $return &= Configuration::deleteByName('reCaptcha_public_key');
        
        return $return;
    }
    
    public function hookDisplayHeader()
    {
        
    }
    
    
    /**
     * This method shows the challenge at the registration form
     * @param type $params
     * @return type
     */
    public function hookDisplayCustomerAccountForm($params)
    {
        $this->context->controller->addCSS($this->_path . 'css/recaptcha.css', 'all');
                
        //We get the public key for getting the recaptcha form field
        $html = recaptcha_get_html(Configuration::get('reCaptcha_public_key'));
        
        $this->context->smarty->assign(
            array(
                'recaptcha' => $html
            )
        );
                
        return $this->display(__FILE__, 'recaptcha.tpl');
    }
    
    /**
     * This method checks if the code entered was valid or not
     * @param type $params
     */
    public function hookActionBeforeSubmitAccount($params)
    {
        $challenge = Tools::getValue('recaptcha_challenge_field');
        $respuesta = Tools::getValue('recaptcha_response_field');
        
        $privatekey = Configuration::get('reCaptcha_private_key');
        
        //Call to the recaptcha library
        $resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $challenge, $respuesta);

        if (!$resp->is_valid) {
            // What happens when the CAPTCHA was entered incorrectly
            $this->context->controller->errors[] = $this->l('El texto de la imagen de seguridad no fue introducido correctamente. Por favor, inténtelo de nuevo');
        }
    }
    
    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit' . $this->name)) {
            
            $private_key = strval(Tools::getValue('reCaptcha_private_key'));
            $public_key = strval(Tools::getValue('reCaptcha_public_key'));
            
            $errors = false;
            
            if (!$private_key || empty($private_key)){
                $output .= $this->displayError($this->l('Invalid private key'));
                $errors = true;
            }
            
            if (!$public_key || empty($public_key)){
                $output .= $this->displayError($this->l('Invalid public key'));
                $errors = true;
            }
            
            if (!$errors) {
                Configuration::updateValue('reCaptcha_private_key', $private_key);
                Configuration::updateValue('reCaptcha_public_key', $public_key);
                
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        return $output . $this->displayForm();
    }
    
    public function displayForm() 
    {
        // Get default Language
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Private key'),
                    'name' => 'reCaptcha_private_key',
                    'size' => 70,
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Public key'),
                    'name' => 'reCaptcha_public_key',
                    'size' => 70,
                    'required' => true
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'button'
            )
        );

        $helper = new HelperForm();

        // Module, t    oken and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = array(
            'save' =>
            array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        // Load current value
        $helper->fields_value['reCaptcha_public_key'] = Configuration::get('reCaptcha_public_key');
        $helper->fields_value['reCaptcha_private_key'] = Configuration::get('reCaptcha_private_key');

        return $helper->generateForm($fields_form);
    }

}