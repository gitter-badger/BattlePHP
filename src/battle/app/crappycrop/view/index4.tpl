<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
		<meta name="viewport" content="user-scalable=0, width=device-width, initial-scale=1, maximum-scale=1">
		<meta name="description" content="CrappyCrop">
		<meta name="author" content="label carrote">
		<meta name="keywords" content="label carrote, BattlePHP, jon, potiron, dalton, touchypunchy, moustachu, mitch">
		<title>CrappyCrop / PROTOCROP</title>
		<link href='http://fonts.googleapis.com/css?family=Raleway' rel='stylesheet' type='text/css'>
		<link href="http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
		<script type="text/javascript" src="{$root_url}lib/jquery/jquery-1.10.2.min.js"></script>
		<script type="text/javascript" src="{$root_url}lib/hammer/jquery.hammer-full.min.js"></script>
		<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/jquery-mousewheel/3.1.6/jquery.mousewheel.js"></script>
		<script type="text/javascript" src="{$current_app_url}public/jquery.cropicious.js"></script>
		<style>

		/* ---- General ---- */
		body { font: 16px/1.5 'Raleway', Arial, Verdana, sans-serif; }
		#upload_form a{ font: 16px/1.5  Arial, Verdana, sans-serif !important; }

		.left{ float: left; }
		.right{ float: right; }
		.clear{ clear: both; }
		
		.content{
			max-width: 960px;
			margin: auto;
			text-align: center;
			margin-top: 24px;
		}
		.content img{
			width: 100%;
			height: auto;
		}

		/* --- CrappyCrop / Protocrop / Cropicious ---- */
		.crop_container{
			-ms-touch-action: none;
			touch-action: none;
			width: 100%;
			height: 500px;
			overflow: hidden;
			position: relative;
			border: 1px solid #dedede;
			background-color: #000;
		}
		.crop_container img{
			position: absolute;
			height: auto;
		}
		
		</style>
		{literal}
		<script type="text/javascript">
		$(window).load(function(){
			var crops = $(".crop_container");
			// Cropicious the image container
			crops.Cropicious();

			// Get the first Cropicious
			var cropy = crops.first().getCropicious();
			
			// Try the public methods
			$('.fit_in').on('click', function(){
				cropy.fit_in();
			});
			$('.fit_out').on('click', function(){
				cropy.fit_out();
			});
			$('.zoom_in').on('click', function(){
				cropy.zoom_in();
			});
			$('.zoom_out').on('click', function(){
				cropy.zoom_out();
			});
			$(".upload").on('click', function (){
				var submit_url = $('#upload_form').attr("action"),
					dataString = JSON.stringify({submit: "save", image: cropy.getDataURL()});
				$.post(submit_url, {data : dataString}, function(){ location.reload(true)});
			});
		});
		</script>
		{/literal}
	</head>
	<body>
		<div class="content">
			<div>
				<div class="left"><strong>PROTOCROP</strong></div>
				<div class="right"><a href="#">FORK / DOWNLOAD</a></div>
				<div class="clear"></div>
			</div>
			
			<form id="upload_form" action="{$current_app_virtual_url}">
				<div>
					<a class="fit_in" href="#">FIT IN</a>
					<a class="fit_out" href="#">FIT OUT</a>
					<a class="zoom_in" href="#">ZOOM IN</a>
					<a class="zoom_out" href="#">ZOOM OUT</a>
					<a class="upload" href="#">UPLOAD CROP</a>
				</div>
				<!-- Crop Container -->
				<div class="crop_container">
					<img alt="" src="{$current_app_url}public/images/lechat.jpg"/>
				</div>
				<!-- END Crop Container -->
			</form>

			<p>
				<i class="fa fa-caret-down fa-lg"></i>
				&nbsp;&nbsp;&nbsp;&nbsp;RESULT / CROPPED PICTURE&nbsp;&nbsp;&nbsp;&nbsp;
				<i class="fa fa-caret-down fa-lg"></i>
			</p>
			
			<a href="{$current_app_url}public/images/result.jpg">
				<img class="result" alt="" src="{$current_app_url}public/images/result.jpg">
			</a>
			
			<p>Label Carrote 2014</p>
		</div>
	</body>
</html>