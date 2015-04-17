<link rel="stylesheet" href="{$base_dir}modules/dialogkutesan/css/jqModal.css" type="text/css" media="all" charset="utf-8" />
<link rel="stylesheet" href="{$base_dir}modules/dialogkutesan/css/dialogkutesan.css" type="text/css" media="all" charset="utf-8" />
<script type="text/javascript" src="{$base_dir}modules/dialogkutesan/js/jqModal.js"></script>
<script src="{$base_dir}modules/dialogkutesan/js/jquery.blockUI.js" type="text/javascript"></script>
<script src="{$base_dir}modules/dialogkutesan/js/dialogkutesan.js" type="text/javascript"></script>

<script LANGUAGE="JavaScript">
    var page="{$dk_url_exit}";
    var cookie_name ="{$dk_cookie_name}";
    var cookie_value ="{$dk_id_block}"; 
    var cookie_get_value =null;
</script>
 
<div id="question" style="display:none; cursor: default"> 

  <table width="800" height="613px" border="0" BACKGROUND="{$base_dir}modules/dialogkutesan/img/fondo.gif">
    <tr height = "102px">
      <td width="18%">
        <div id="dk_lang">
            
            <div id="dk_languages_block">
            	<ul id="dk_languages">
            		{foreach from=$languages key=k item=language name="languages"}
            			<li {if $language.iso_code == $lang_iso}class="selected_language"{/if}>
            				{if $language.iso_code != $lang_iso}<a href="{$link->getLanguageLink($language.id_lang, $language.name)}" title="{$language.name}">{/if}
            					<img src="{$img_lang_dir}{$language.id_lang}.jpg" alt="{$language.name}" />
            				{if $language.iso_code != $lang_iso}</a>{/if}
            			</li>
            		{/foreach}
            	</ul>
            </div>
            <script type="text/javascript">
            	$('ul#dk_languages li:not(.selected_language)').css('opacity', 0.3);
            	$('ul#dk_languages li:not(.selected_language)').hover(function(){ldelim}
            		$(this).css('opacity', 1);
            	{rdelim}, function(){ldelim}
            		$(this).css('opacity', 0.3);
            	{rdelim});
            </script>
            
              
        </div     
      </td>
      <td width="65%" colspan="3">
        
        <div id="dk_logo">
          <id="logo"><a href="{$base_dir}" title="{$shop_name|escape:'htmlall':'UTF-8'}"><img src="{$img_ps_dir}logo.jpg" alt="{$shop_name|escape:'htmlall':'UTF-8'}" /></a>
        </div>
          
      </td>
      <td width="17%"></td>
    </tr>
    <tr height = "83px">
      <td colspan="5">
        
        <div id="dk_title">
          <strong>{$cms_title}</strong>
        </div>
        
      </td>
    </tr>
    <tr height = "330px">
      <td>&nbsp;</td>
      <td colspan="3" align="left" valign="top">
        
        <div id="dk_content" style="height:330px;overflow:auto">
          <p>{$cms_content}</p>
        </div>
          
      </td>
      <td>&nbsp;</td>
    </tr>
    <tr height = "38px">
      <td>&nbsp;</td>
      <td>
        <input type="button" id="yes" value="{$dk_enter}"  />
      </td>
      <td>
        
        <input type="button" id="no" value="{$dk_cancel}" />
      </td>
      <td colspan="2"></td>
    </tr>
    <tr height = "38px">
      <td colspan="5">
        <div id="dk_footer">
        
        </div>
      </td>
    </tr>
  </table>

</div> 
