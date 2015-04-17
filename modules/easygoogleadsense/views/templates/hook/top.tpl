{if isset($advertisments) AND $advertisments}
<div class="block_google_adsense col-lg-12">
    {foreach from=$advertisments item=advertisment name=advertisments}
    <div class="block_content">
        <center>
            </br>{$advertisment.content}</br>
        </center>
    </div>
        {/foreach}
</div>
{/if}