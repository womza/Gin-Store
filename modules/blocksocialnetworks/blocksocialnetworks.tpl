<!-- MODULE block social networks -->
{if $FacebookUrl != '' or $TwitterUrl != '' or $Hi5Url != ''}
	<div id="blocksocialnetworks" class="block">
		<h4>{l s='Follow us...' mod='blocksocialnetworks'}</h4>
		<div class="block_content">
			{if $FacebookUrl != ''}
				<a href="{$FacebookUrl}" title="Facebook" target="_blank"><img src="{$module_dir}{$imageFacebook}" alt="Facebook" /></a>
			{/if}
			{if $TwitterUrl != ''}
				<a href="{$TwitterUrl}" title="Twitter" target="_blank"><img src="{$module_dir}{$imageTwitter}" alt="Twitter" /></a>
			{/if}
			{if $Hi5Url != ''}
				<a href="{$Hi5Url}" title="Hi5" target="_blank"><img src="{$module_dir}{$imageHi5}" alt="Hi5" /></a><br />
			{/if}
			{if $TuentiUrl != ''}
				<a href="{$TuentiUrl}" title="Tuenti" target="_blank"><img src="{$module_dir}{$imageTuenti}" alt="Tuenti" /></a><br />
			{/if}
		</div>
	</div>
{/if}
<!-- /MODULE block social networks -->
