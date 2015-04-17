<?php

if (!defined('_PS_VERSION_'))
	exit;

	ini_set('allow_url_fopen',true);

class EasyCaptcha extends Module
{
	function __construct()
	{
		$this->name = 'easycaptcha';
		$this->tab = 'front_office_features';
		$this->author = 'Alfonso Soler Sanchis';		
		$this->version = '1.0';

		parent::__construct();		
		$this->displayName = $this->l('Easy Captcha');
		$this->description = $this->l('Añade un Captcha simple y eficaz - www.ecomm360.net');		
	}

	function install()
	{
		if (!parent::install()			)
			return false;	
		return true;
	}
	
	public function displayForm()
	{	
		global $cookie;	
		$link = Context::getContext()->shop->domain;
		$link = "http://".$link; 		
 		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();
		$iso = Language::getIsoById($defaultLanguage);
		$divLangName = 'link_label';				
   		$this->_html .= '    
    <form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="form">
		<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Configuración').'</legend>
			<p>'.$this->l('Ponga el siguiente código en el contact-form.tpl de su template donde desea que aparezca.
			').'</p>			
			<input type="text" size="120" name="image1" value=\'{include file="$tpl_dir./../../modules/easycaptcha/easycaptcha.tpl"}\' />
			<br/>
  			<p>'.$this->l('Ponga el siguiente código en el fichero ContactController.php, situado en /controllers/front. 
			').'</p>			
			<p>';
			$this->_html .= '<a href="'.$link.$this->_path.'ejemplo.txt" target="_blank">Descargar ejemplo</a>';
			$this->_html .= '
			<br/>
  		</fieldset>
  	</form>
	';	
 	return $this->_html;				
	}

	public function getContent()
	{
		global $cookie,$currentIndex;
		if (Tools::isSubmit('submitSlideCatpcha'))
		{
		$submitsc = Tools::getValue('submitsc');				
		Configuration::updateValue('SLIDECAPTCHA_SUBMITSC', $submitsc);
		$this->_html .= @$errors == '' ? $this->displayConfirmation('Settings updated successfully') : @$errors;					
		}
	
	return $this->displayForm();
	}

	
	 
}