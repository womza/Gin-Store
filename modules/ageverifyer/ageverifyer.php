<?php
/*
* 2015 Simon Agostini
*
* NOTICE OF LICENSE
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Simon Agostini webmaster@zutroll.de
*  @copyright  Simon Agostini webmaster@zutroll.de
*  @version  Release: 1.0
*/

if ( !defined( '_PS_VERSION_' ) )
  exit;

class ageverifyer extends Module
  {
	public function __construct()
	{
		$this->name = 'ageverifyer';
		$this->tab = 'front_office_features';
		$this->version = '1.0.1';
		$this->author = 'Simon Agostini';
		$this->need_instance = 0;
		$this->module_key = "2b62317205551f3bcf82cb1d2f941f02";
		
		parent::__construct();
		
		$this->displayName = $this->l( 'Age verification' );
		$this->description = $this->l( 'Adds the 18+ age verification to your shop' );
		
		/* Backward compatibility */
		if (_PS_VERSION_ < '1.5')
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
	}
	
	
		
	public function checkAge()
	{	
		if (isset( $this->context->cookie->over18 ))
		{
			return $this->context->cookie->over18;
		}
		else
		{
			return "";
		}
	} 
	
	public function install()
	{
		if ( parent::install() == false || !$this->registerHook('displayTop') )
			return false;
		return true;
	}
	
	public function uninstall()
	{
		self::deleteDBUninstall();
		return parent::uninstall();
	}
	
	//remove DB fields
	private function deleteDBUninstall()
	{
		Configuration::deleteByName($this->name.'_option');
		Configuration::deleteByName($this->name.'_url');
		return true;
	}
	
	  
	public function hookDisplayTop($params)
	{
		//check if cookie set
		$ageValid = $this->checkAge();
		if (Tools::usingSecureMode())
			$domain = Tools::getShopDomainSsl(true);
		else
			$domain = Tools::getShopDomain(true);

		$this->context->smarty->assign(array(
				'ageValid' => $ageValid,
				'token' => Tools::getToken(false), //token needed for validation
				'basis' => $domain.__PS_BASE_URI__,
				'currentpath' => $this->_path,
				'backURL' => Configuration::get($this->name.'_url')
			));
			
		if(_PS_VERSION_ >= 1.5 )
		{
			$this->context->controller->addjqueryPlugin('fancybox');
			$this->context->controller->addCSS(_PS_JS_DIR_.'jquery/plugins/fancybox/jquery.fancybox.css', 'all');
			$this->context->controller->addCSS(($this->_path).'views/css/design.css', 'all');
			//switch config option
			switch ( Configuration::get($this->name.'_option') )
			{
				case 0:  return $this->display( __FILE__, 'views/templates/front/ageverifyer.tpl' ); break;
				case 1:  return $this->display( __FILE__, 'views/templates/front/ageverifyer_light.tpl' ); break;
				default: return $this->display( __FILE__, 'views/templates/front/ageverifyer.tpl' ); break;
			}
		}
		else // old PS 1.4
		{
			Tools::addJS(_PS_JS_DIR_.'jquery/jquery.fancybox-1.3.4.js');	
			Tools::addCSS(_PS_CSS_DIR_.'jquery.fancybox-1.3.4.css', 'all');
			Tools::addCSS(($this->_path).'views/css/design.css', 'all');
			
			//switch config option
			switch ( Configuration::get($this->name.'_option') )
			{
				case 0:  return $this->display( __FILE__, 'views/templates/front/ageverifyer14.tpl' ); break;
				case 1:  return $this->display( __FILE__, 'views/templates/front/ageverifyer_light14.tpl' ); break;
				default: return $this->display( __FILE__, 'views/templates/front/ageverifyer14.tpl' ); break;
			}
		}
	}
	
	
	
	//#############################################################
	//Konfigurations Menu einblenden
	public function getContent()
	{
		if (Tools::isSubmit('submit'))
		{
			//update cms shipping id
			$option = (int)Tools::getValue('option');
			Configuration::updateValue($this->name.'_option', $option );
			
			$optionURL =  Tools::getValue('optionURL');
			Configuration::updateValue($this->name.'_url', $optionURL );
		}
		
		$this->_displayForm();
		return $this->_html;
	}
	
	//Formular anzeigen im Backoffice
	private function _displayForm()
	{
		$optionRadio = Configuration::get($this->name.'_option');
		$strHTMLchecked = '';
		$strHTMLchecked2 = '';
		if ($optionRadio == 0)
			$strHTMLchecked = ' checked="checked" ';
		else
			$strHTMLchecked2 = ' checked="checked" ';
		
		if(_PS_VERSION_ <= 1.5 )
		{	
			$this->_html = '<style>
			.panel{ 
				background-color: #ebedf4;
				border: 1px solid #ccced7;
				box-sizing: border-box;
				color: #585a69;
				float: left;
				margin: 0 0 10px;
				padding: 1em;
				width: 100%;
				}
			.form-group {
				clear: both;
				float: left;
				margin-bottom: 1em;
				width: 100%;
			}
			.form-group > div {
				float: left;
			}
			.radio label
			{
				clear: both;
				margin-right: 4px;
				width: auto;
			}
			label {
				color: #585a69;
				float: left;
				font-weight: bold;
				padding: 0.2em 0.5em 0 0;
				text-align: right;
				text-shadow: 0 1px 0 #fff;
				width: 250px;
			}
				</style>';
		}
		//HTML output
		$this->_html .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
		
		<div id="fieldset_0" class="panel">
			<div class="panel-heading">		
			</div>
			<div class="form-wrapper">
		';
			
		
		$this->_html .= '
			<div class="form-group" >
				<label class="control-label col-lg-3 required" for="date1">
				'.$this->l('Select which Module Type you want:').'
				</label>
				<div class="col-lg-9 ">
					<div class="radio radio">
					<label><input type="radio" '.$strHTMLchecked.' value="0" id="optionAge0" name="option"> '.$this->l('default').'</label>
					</div> 
					<div class="radio radio">
					<label><input type="radio" '.$strHTMLchecked2.' value="1"  id="optionAge1" name="option"> '.$this->l('light (no birthdate check)').'</label>
					</div>
				</div>
			</div
			<div class="form-group">
				<label class="control-label col-lg-3 required" for="date2">
				'.$this->l('Specify a return URL (empty value will disable option):').'
				</label>
				<div class="col-lg-9 ">
					<input type="text"  size="50" class="" value="'.Configuration::get($this->name.'_url').'" id="optionURL" name="optionURL">
				</div>
				
			</div>
	
			
			';
		
		//update BTN
		$this->_html .= '<div class="panel-footer" align="center">
							<button class="button" name="submit" id="configuration_form_submit_btn" value="1" type="submit">
								<i class="process-icon-save"></i> '.$this->l('save').'
							</button>
						</div>
				</div>
			</div>
		</form>';
		
		
		
	}	
	  
}

?>