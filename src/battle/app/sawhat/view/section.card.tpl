{if isset($cards)}
<div class="content line">
	{include file="element.breadcrumbs.tpl"}
	<div id="search_result">
	{foreach from=$cards item=card}
	<section class="unit size1of3">
		<div class="smallermargin" id="{$card->name}">
			<style>{$card->style_definition}</style>
			{include file="element.card.banner.tpl"}
		</div>
	</section>
	{/foreach}
	<div class="clearer"></div>
	</div>
</div>
{elseif isset($card)}
<div class="content">
	{include file="element.breadcrumbs.tpl"}
	<section class="size1of1">
	{include file="element.card.tpl"}
	</section>
</div>
{else}
<div class="content">
	{include file="element.breadcrumbs.tpl"}
	<section class="size1of1">
	<div class="sawhat things">
	No card were found.
	</div>
	</section>
</div>
{/if}
{include file="element.footer.tpl"}