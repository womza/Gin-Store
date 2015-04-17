{*
* 2015 Jorge Vargas
*
* NOTICE OF LICENSE
*
* This source file is subject to the End User License Agreement (EULA)
*
* See attachmente file LICENSE
*
* @author    Jorge Vargas <jorgevargaslarrota@hotmail.com>
* @copyright 2007-2015 Jorge Vargas
* @license   End User License Agreement (EULA)
* @package   sociallogin
* @version   1.0
*}

{literal}
<script type="text/javascript">
	$(function () {
		$('#myTab a[href=#{/literal}{$tab_active|escape:'htmlall':'UTF-8'}{literal}]').tab('show')
	});
	$('.carousel').carousel();
</script>
{/literal}