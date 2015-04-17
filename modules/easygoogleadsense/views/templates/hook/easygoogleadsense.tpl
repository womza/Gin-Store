{if isset($advertisments) AND $advertisments}
    <div class="block block_google_adsense">
        {foreach from=$advertisments item=advertisment name=advertisments}
            {if $advertisment.show_title == 1}<h4>{$advertisment.title}</h4>{/if}
            <div class="block_content">
                <center>{$advertisment.content}</center>
            </div>
        {/foreach}
    </div>
{/if}