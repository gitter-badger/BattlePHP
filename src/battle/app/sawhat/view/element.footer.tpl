<footer class="content auto_clear">
	<div class="left">
		<span class="fa fa-caret-right"></span>&nbsp;<a href="{$current_app_virtual_url}all_cards">See all cards</a>
		<br>
		<span class="fa fa-caret-right"></span>&nbsp;<a href="{$current_app_virtual_url}starred">See starred cards</a>
		<br>
		<span class="fa fa-caret-right"></span>&nbsp;<a href="{$current_app_virtual_url}help">Help</a>
	</div>
	<form method="POST" class="right form-inline">
		<div class="input-group">
			<input type="text" class="form-control" name="search" placeholder="keywords go here" value="" required="required" pattern="[a-zA-Z0-9 _-]+">
			<span class="input-group-btn">
				<button class="btn btn-default" type="submit" name="submit" value="search" title="search"><span class="fa fa-search"></span></button> 
			</span>
		</div>
	</form>
</footer>