<div class="banner auto_clear">
	{if !$logged and $card->is_private}
		<span class="{if $card->is_light}lighter_text{else}darker_text{/if} fa fa-lock" title="private"></span>
	{/if}
	<h{$card->recursive_level+1}>
	<a href="{$current_app_virtual_url}{$card->name}" class="{if $card->is_light}white_text{else}black_text{/if} {if !$card->exists}striked light{/if}" title="{$card->display_name}">
		{$card->display_name}
	</a>
	</h{$card->recursive_level+1}>
	<div class="right align_right">
		{if !$card->exists}
			<a class="{if $card->is_light}lighter_text{else}darker_text{/if}" href="{$current_app_virtual_url}{$card->name}/edit" title="create">
				<span class="{if $card->is_light}lighter_text{else}darker_text{/if} fa fa-pencil" data-card-name="{$card->name}"></span>
			</a>
		{else}
			{if !$card->is_recursive}
				<span class="{if $card->is_light}white_text{else}black_text{/if}">{$card->last_edit}</span><br>
			{/if}
			<span class="starred {if $card->is_light}lighter_text{else}darker_text{/if} fa fa-star-o" data-card-name="{$card->name}" title="add in starred"></span>&nbsp;
			{if $logged || !$card->is_private}
				&nbsp;<a class="right {if $card->is_light}lighter_text{else}darker_text{/if} no-print" href="{$current_app_virtual_url}{$card->name}/edit" title="edit">
					<span class="{if $card->is_light}lighter_text{else}darker_text{/if} fa fa-pencil" data-card-name="{$card->name}"></span>
				</a>
			{/if}
		{/if}
	</div>
</div>