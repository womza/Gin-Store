{if isset($advertisments) AND $advertisments}
<div class="block_google_adsense col-lg-12">
    {foreach from=$advertisments item=advertisment name=advertisments}
    <div class="block_content">
        <center>{$advertisment.content} </center>
    </div>
        {/foreach}
</div>
{/if}