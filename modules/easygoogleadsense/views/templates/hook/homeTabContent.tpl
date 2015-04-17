{if isset($advertisments) AND $advertisments}
<ul id="advertisments" class="col-lg-12">
    {foreach from=$advertisments item=advertisment name=advertisments}
        <center>
            </br>{$advertisment.content}</br>
        </center>
    {/foreach}
</ul>
{/if}