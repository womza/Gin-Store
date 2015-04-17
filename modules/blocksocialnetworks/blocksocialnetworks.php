<?php

/**

	Block Social Networks
    Copyright (C) 2010  Valentín Matilla Milán <valtor.gpl@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

  */

class blocksocialnetworks extends Module
{
	/**
	* Construct
	**/
	function __construct()
	{
		$this->name = 'blocksocialnetworks';
		$this->tab = 'Blocks';
		$this->version = '0.2.5';

		parent::__construct();

		if (!Configuration::get('TWITTER_USER') 
			AND !Configuration::get('FACEBOOK_PAGE')
			AND !Configuration::get('TUENTI_PAGE')
			AND !Configuration::get('HI5_USER'))
				$this->warning = $this->l('You don\'t have any configuration for Block Social Network');
		$this->displayName = $this->l('Block Social Networks');
		$this->description = $this->l('Add a block to give access to your social networks');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
	}

	/**
	* Install
	**/
	function install()
	{
		if (!parent::install())
			return false;
		if (!$this->registerHook('leftColumn'))
			return false;
		return true;
	}
	
	/**
	* Uninstall
	**/
	function uninstall()
	{
		if (!Configuration::deleteByName('TWITTER_USER')
			OR !Configuration::deleteByName('FACEBOOK_PAGE') 
			OR !Configuration::deleteByName('TUENTI_PAGE') 
			OR !Configuration::deleteByName('HI5_USER') OR !parent::uninstall())
			return false;
		return true;
	}

	/**
	* GetContent
	**/
	public function getContent()
	{
		$output = '<h2>Social Networks</h2>';
		if (Tools::isSubmit('submitSocialNetworks')) {
			if ($twitter = Tools::getValue('twitter_user')) {
				Configuration::updateValue('TWITTER_USER', $twitter);
			} else {
				Configuration::updateValue('TWITTER_USER', '');
			}
			if($facebook = Tools::getValue('facebook_page')) {
				Configuration::updateValue('FACEBOOK_PAGE', $facebook);
			} else {
				Configuration::updateValue('FACEBOOK_PAGE', '');
			}
			if($tuenti = Tools::getValue('tuenti_page')) {
				Configuration::updateValue('TUENTI_PAGE', $tuenti);
			} else {
				Configuration::updateValue('TUENTI_PAGE', '');
			}
			if ($hi5 = Tools::getValue('hi5_user')) {
				Configuration::updateValue('HI5_USER', $hi5);
			} else {
				Configuration::updateValue('HI5_USER', '');
			}

			$output .= '
			<div class="conf confirm">
				<img src="../img/admin/ok.gif" alt="" title="" />
				'.$this->l('Settings updated').'
			</div>';
		}
		return $output.$this->displayForm();
	}

	/**
	* DisplayForm
	**/
	public function displayForm()
	{
		$output = '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset class="width2">
				<legend><img src="../img/admin/cog.gif" alt="" class="middle" />'.$this->l('Settings').'</legend>
				
				<label>'.$this->l('Twitter user').'</label>
				<div class="margin-form">
					<input type="text" name="twitter_user" value="'.Tools::getValue('twitter_user', Configuration::get('TWITTER_USER')).'" />
					<p class="clear">'.$this->l('Example:').' user (http://twitter.com/<b>user</b>)</p>
					<p class="clear">'.$this->l('Leave this blank if you don\'t have a twitter account.').'</p>
				</div>
				
				<label>'.$this->l('Facebook page').'</label>
				<div class="margin-form">
					<input type="text" name="facebook_page" value="'.Tools::getValue('facebook_page', Configuration::get('FACEBOOK_PAGE')).'" />
					<p class="clear">'.$this->l('Example:').' page_name/000000000000000 (http://www.facebook.com/pages/<b>page_name/000000000000000</b>)</p>
					<p class="clear">'.$this->l('Leave this blank if you don\'t have a facebook page.').'</p>
				</div>
				
				<label>'.$this->l('Hi5 user').'</label>
				<div class="margin-form">
					<input type="text" name="hi5_user" value="'.Tools::getValue('hi5_user', Configuration::get('HI5_USER')).'" />
					<p class="clear">'.$this->l('Example:').' user (http://<b>user</b>.hi5.com)</p>
					<p class="clear">'.$this->l('Leave this blank if you don\'t have a Hi5 account.').'</p>
				</div>
				
				<label>'.$this->l('Tuenti Id').'</label>
				<div class="margin-form">
					<input type="text" name="tuenti_page" value="'.Tools::getValue('tuenti_page', Configuration::get('TUENTI_PAGE')).'" />
					<p class="clear">'.$this->l('Example:').' 000000 (http://www.tuenti.com/#m=Profile&func=index&user_id=<b>00000</b>)</p>
					<p class="clear">'.$this->l('Leave this blank if you don\'t have a Tuenti account.').'</p>
				</div>
				
				<center><input type="submit" name="submitSocialNetworks" value="'.$this->l('Update').'" class="button" /></center>
			</fieldset>
		</form>';
		
		return $output;
	}

	function hookRightColumn($params)
	{
		global $smarty;
	
		Tools::addCSS(($this->_path).'blocksocialnetworks.css', 'all');
		if(Configuration::get('TWITTER_USER')) {
			$smarty->assign('imageTwitter', '/img/twitter.gif');
			$smarty->assign('TwitterUrl', "http://twitter.com/".Configuration::get('TWITTER_USER'));
		}

		if(Configuration::get('TUENTI_PAGE')) {
			$smarty->assign('imageTuenti', '/img/tuenti.png');
			$smarty->assign('TuentiUrl', "http://www.tuenti.com/#m=Profile&func=index&user_id=".Configuration::get('TUENTI_PAGE'));
		}

		if(Configuration::get('FACEBOOK_PAGE')) {
			$smarty->assign('imageFacebook', '/img/facebook.gif');
			$smarty->assign('FacebookUrl', "http://www.facebook.com/pages/".Configuration::get('FACEBOOK_PAGE'));
		}

		if(Configuration::get('HI5_USER')) {
			$smarty->assign('imageHi5', '/img/hi5.gif');
			$smarty->assign('Hi5Url', "http://".Configuration::get('HI5_USER').".hi5.com");
		}
		return $this->display(__FILE__, 'blocksocialnetworks.tpl');
	}

	function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}
}

?>
