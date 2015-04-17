{if $page_name == 'index'}

<!-- Module DreamSlider -->
{if isset($dreamslider)}
<script type="text/javascript">
{if isset($dreamslider_slides) && $dreamslider_slides|@count > 1}
	{if $dreamslider.loop == 1}
		var dreamslider_loop = true;
	{else}
		var dreamslider_loop = false;
	{/if}
{else}
	var dreamslider_loop = false;
{/if}
var dreamslider_speed = {$dreamslider.speed};
var dreamslider_pause = {$dreamslider.pause};
</script>
{/if}
{if isset($dreamslider_slides)}
<ul id="dreamslider">
{foreach from=$dreamslider_slides item=slide}
	{if $slide.active}
		<li><a href="{$slide.url|escape:'htmlall':'UTF-8'}" title="{$slide.description|escape:'htmlall':'UTF-8'}"><img src="{$smarty.const._MODULE_DIR_}/dreamslider/img/images/{$slide.image|escape:'htmlall':'UTF-8'}" alt="{$slide.legend|escape:'htmlall':'UTF-8'}" title="{$slide.description|escape:'htmlall':'UTF-8'}" height="{$dreamslider.height|intval}" width="{$dreamslider.width|intval}" /></a></li>
	{/if}
{/foreach}
</ul>
{/if}
<!-- /Module DreamSlider -->

{/if}