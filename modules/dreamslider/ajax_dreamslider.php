<?php
include_once('../../config/config.inc.php');
include_once('../../init.php');
include_once('dreamslider.php');

$context = Context::getContext();
$dream_slider = new DreamSlider();
$slides = array();

if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $dream_slider->secure_key || !Tools::getValue('action'))
	die(1);

if (Tools::getValue('action') == 'updateSlidesPosition' && Tools::getValue('slides'))
{

	$slides = Tools::getValue('slides');

	foreach ($slides as $position => $id_slide)
	{
		$res = Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'dreamslider_slides` SET `position` = '.(int)$position.'
			WHERE `id_dreamslider_slides` = '.(int)$id_slide
		);

	}
}

