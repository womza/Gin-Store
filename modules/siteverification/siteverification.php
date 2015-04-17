<?php

class SiteVerification extends Module
{
	function __construct()
	{
		$this->name = 'siteverification';
		$this->tab = version_compare(_PS_VERSION_, '1.4.0.0', '>=')?'seo':'Mediacom87';
		$this->version = 1.3;
		$this->need_instance = 1;
		$this->author = 'Mediacom87';

		parent::__construct();

		$this->displayName = $this->l('Site Verification');
		$this->description = $this->l('Enter your site identification for Google, Bing and Alexa');
		
		if (!Configuration::get('BLOCK_GOOGLE_CODE') AND !Configuration::get('BLOCK_BING_CODE') AND !Configuration::get('BLOCK_ALEXA_CODE'))
			$this->warning = $this->l('You have not yet set your Google Webmaster Tools ID or Bing Webmaster Center ID or Alexa ID');
		
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
	}

	function install()
	{
		if (!parent::install() 
			OR !$this->registerHook('header')
		)
			return false;
		return true;
	}
	
	function uninstall()
	{
		if (!Configuration::deleteByName('BLOCK_GOOGLE_CODE') OR !Configuration::deleteByName('BLOCK_BING_CODE') OR !Configuration::deleteByName('BLOCK_ALEXA_CODE') OR !parent::uninstall())
			return false;
		return true;
	}

	public function getContent()
	{
		global $cookie;
		$iso = Language::getIsoById((int)$cookie->id_lang);
		$output = '<h2><a href="http://www.prestatoolbox.'.(($iso != 'fr')?'com':'fr').'/?utm_source=module&utm_medium=cpc&utm_campaign='.$this->name.'"><img src="http://netdna.prestatoolbox.net/images/valide-32x32.png" alt="" /></a>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitSiteVerification'))
		{
			Configuration::updateValue('BLOCK_GOOGLE_CODE', Tools::getValue('google'));
			Configuration::updateValue('BLOCK_BING_CODE', Tools::getValue('bing'));
			Configuration::updateValue('BLOCK_ALEXA_CODE', Tools::getValue('alexa'));
			$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		global $cookie;
		$iso = Language::getIsoById((int)$cookie->id_lang);
				
		return '
		<fieldset style="float: right; width: 255px">
			<legend><a href="https://www.paypal.com/fr/mrb/pal=LG5H4P9K8K6FC" target="_blank"><img src="http://netdna.prestatoolbox.net/images/paypal-16x16.png" alt="" /></a>'.$this->l('Donation').'</legend>
			<p style="font-size: 1.5em; font-weight: bold; padding-bottom: 0">'.$this->displayName.'</p>
			<p style="clear: both">
			'.$this->l('Thanks for installing this module on your website.').'
			</p>
			<p>
			'.$this->description.'
			</p>
			<p>
			'.$this->l('Developped by').' <a style="color: #900; text-decoration: underline;" href="http://www.prestatoolbox.'.(($iso != 'fr')?'com':'fr').'/?utm_source=module&utm_medium=cpc&utm_campaign='.$this->name.'">Mediacom87</a>'.$this->l(', which helps you develop your e-commerce site.').'
			</p>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="text-align:center" target="_blank">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="2BJGYCJS357VN">
			<input type="image" src="https://www.paypalobjects.com/WEBSCR-640-20110429-1/fr_FR/FR/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !">
			<img alt="" border="0" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
			</form>

		</fieldset>
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset class="width3">
				<legend><a href="http://www.prestatoolbox.'.(($iso != 'fr')?'com':'fr').'/?utm_source=module&utm_medium=cpc&utm_campaign='.$this->name.'"><img src="'.$this->_path.'logo.gif" alt="" /></a>'.$this->l('Settings').'</legend>
				
				<label><a href="https://www.google.com/webmasters/tools/dashboard" target="_blank"><img src="http://netdna.prestatoolbox.net/images/google-83x34.png" alt="'.$this->l('Google Webmaster Tools ID').'" /></a></label>
				<div class="margin-form">
					&lt;meta name=&quot;google-site-verification&quot; content=&quot;<input type="text" name="google" value="'.Configuration::get('BLOCK_GOOGLE_CODE').'" />&quot; /&gt;
					<p class="clear">'.$this->l('Enter the identification code of your site').'</p>
				</div>
				
				<label><a href="http://webmaster.live.com/" target="_blank"><img src="http://netdna.prestatoolbox.net/images/bing-85x37.png" alt="'.$this->l('Bing Webmaster Center ID').'" /></a></label>
				<div class="margin-form">
					&lt;meta name=&quot;msvalidate.01&quot; content=&quot;<input type="text" name="bing" value="'.Configuration::get('BLOCK_BING_CODE').'" />&quot; /&gt;
					<p class="clear">'.$this->l('Enter the identification code of your site').'</p>
				</div>
				
				<label><a href="http://www.alexa.com/" target="_blank"><img src="http://netdna.prestatoolbox.net/images/alexa-85x22.png" alt="'.$this->l('Alexa Verify ID').'" /></a></label>
				<div class="margin-form">
					&lt;meta name="alexaVerifyID" content=&quot;<input type="text" name="alexa" value="'.Configuration::get('BLOCK_ALEXA_CODE').'" />&quot; /&gt;
					<p class="clear">'.$this->l('Enter the identification code of your site').'</p>
				</div>
				
				<center><input type="submit" name="submitSiteVerification" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
		
			<fieldset style="clear: both" class="space">
					<legend><img src="http://netdna.prestatoolbox.net/images/google-icon-16x16.png" alt="" height="16" width="16" /> '.$this->l('Ads').'</legend>
					<p style="text-align:center"><script type="text/javascript"><!--
					google_ad_client = "ca-pub-1663608442612102";
					/* Siteverification 728x90 */
					google_ad_slot = "7415064332";
					google_ad_width = 728;
					google_ad_height = 90;
					//-->
					</script>
					<script type="text/javascript"
					src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
					</script></p>
			</fieldset>
		</form>
		';
	}

	function hookHeader($params)
	{
		$this->_code = Configuration::getMultiple(array(
			'BLOCK_GOOGLE_CODE',
			'BLOCK_BING_CODE',
			'BLOCK_ALEXA_CODE'
		));
		$output = '';
		if (isset($this->_code['BLOCK_GOOGLE_CODE']) && !empty($this->_code['BLOCK_GOOGLE_CODE'])) { 
			$output .= '
				<meta name="google-site-verification" content="'.$this->_code['BLOCK_GOOGLE_CODE'].'" />';
		}
		if (isset($this->_code['BLOCK_BING_CODE']) && !empty($this->_code['BLOCK_BING_CODE'])) {
			$output .= '
				<meta name="msvalidate.01" content="'.$this->_code['BLOCK_BING_CODE'].'" />';
		}
		if (isset($this->_code['BLOCK_ALEXA_CODE']) && !empty($this->_code['BLOCK_ALEXA_CODE'])) {
			$output .= '
				<meta name="alexaVerifyID" content="'.$this->_code['BLOCK_ALEXA_CODE'].'" />';
		}
		return $output;
	}
}

?>
