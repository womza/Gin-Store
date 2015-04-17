<?php

///////////////////////////////////////////////////////////////////
///////                  DialogKutesan                      ///////
///////             PrestaShop Tools Module                 ///////
///////                                                     ///////
///////               CREATED BY :  KUTESAN                 ///////
///////                  MADE IN : SPAIN                    ///////
///////  TESTED WITH THE VERSIONS: 1.2                      ///////
///////             CREATED DATE : 02 - JUNE - 2009         ///////
///////                  LICENSE : CopyLeft                 ///////
///////              AUTOR'S WEB :                          ///////
///////                                                     ///////
///////////////////////////////////////////////////////////////////
class dialogkutesan extends Module
{
	private $_html;
	private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'dialogkutesan';
		$this->tab = 'Tools';
		$this->version = 0.2;
		parent::__construct();

		$this->displayName = $this->l('Kutesan Pop-Up Dialog for access control');
		$this->description = $this->l('Adds a block with several information to access shop');
	}
	public function install()
	{
		if (!parent::install() OR !$this->registerHook('header') )
			return false;
		if (!Configuration::updateValue('DK_URL_EXIT', 'http://www.google.com'))
      return false;
    if (!Configuration::updateValue('DK_COOKIE_NAME', 'dialogkutesan'))
			return false;	
		if (!Configuration::updateValue('DK_ID_BLOCK', intval($this->id)))
			return false;	
		
		return true;
	}

	public function getContent()
	{
		global $cookie;
				
    $output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitDialogKutesan'))
		{
			$dk_id_block = intval(Tools::getValue('dk_id_block'));
			$dk_id_cms = intval(Tools::getValue('dk_id_cms'));
      $dk_url_exit = Tools::getValue('dk_url_exit');
			$dk_cookie_name = Tools::getValue('dk_cookie_name');
			
			
			
			
			Configuration::updateValue('DK_ID_CMS', $dk_id_cms);
      Configuration::updateValue('DK_URL_EXIT', $dk_url_exit);
			
			
		}
    $this-> displayForm();
		return $this->_html;
    
	}
	
	public function displayForm()
	{
	   global $cookie;
     
     $this->_html .=
     '<br />
          
     <form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
      	
        <legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
        
            
        <label>'.$this->l('Select CMS').'</label>
        <div class="margin-form">';
		        $cms = CMS::listCms($cookie->id_lang);
            $this->_html .= '
            <select name="dk_id_cms" id="dk_id_cms">';
             foreach($cms AS $row)
			       $this->_html .='
						 <option value='.$row['id_cms'] .(Configuration::get('DK_ID_CMS') == $row['id_cms'] ? ' selected >' : '>').$row['meta_title'].'</option>';
		        $this->_html .='
            </select>
            <p class="clear">'.$this->l('Select the CMS to show in Pop-up Dialog').'</p>
            <br />
				</div>
        
                
        <label>'.$this->l('URL exit button').'</label>
				<div class="margin-form">
					<input size="34" type="text" name="dk_url_exit"  value="'.(Tools::getValue('dk_url_exit', Configuration::get('DK_URL_EXIT'))).'" />
					<p class="clear">'.$this->l('Enter URL to clicked Exit Button').'</p>
				</div>   
             
        <center><input type="submit" name="submitDialogKutesan" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
		</form>';
     
	}
	

  
  
  public function hookHeader($params)
    {
		global $smarty;
    global $cookie;
    
    $dk_languages = Language::getLanguages();
    $dk_cookie_value = isset($_COOKIE[Configuration::get('DK_COOKIE_NAME')]) ? $_COOKIE[Configuration::get('DK_COOKIE_NAME')] : false;
      
      $result1 = Db::getInstance()->ExecuteS('
  		SELECT * FROM '._DB_PREFIX_.'cms_lang
  		WHERE  id_cms = '.intval(Configuration::get('DK_ID_CMS')).' AND id_lang='.intval($cookie->id_lang));
      foreach ($result1 as $row1)
      	{
  				  $dk_title = $row1['meta_title'];
            $dk_content = $row1['content'];     
                    
  			}
      $dk_button_enter =  $this->l('Enter');
      $dk_button_cancel =  $this->l('Cancel');  
  		$smarty->assign(array(
  			'dk_url_exit' =>	Configuration::get('DK_URL_EXIT'),
  			'dk_cookie_name' =>	Configuration::get('DK_COOKIE_NAME'),
  			'dk_id_block' =>	Configuration::get('DK_ID_BLOCK'),
  			'dk_id_cms' =>	Configuration::get('DK_ID_CMS'),
  			'cms_title' => $dk_title,
  			'cms_content' => $dk_content,
  			'dk_enter' => $dk_button_enter,
  			'dk_cancel' => $dk_button_cancel,
  			'languages' => $dk_languages
        ));
  		if (Configuration::get('DK_ID_BLOCK') == $dk_cookie_value)
  		{
        return ($this->display(__FILE__, '/dialogkutesan_pass.tpl'));
      }
      else
      {
        return ($this->display(__FILE__, '/dialogkutesan.tpl'));
      }
      
  		
		
	}
}	

