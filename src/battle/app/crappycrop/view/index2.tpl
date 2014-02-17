<!DOCTYPE html>
<html>
<head>
  <title>jQuery-cropbox</title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1"/>
  <style type="text/css">
    body {
      font-family : sans-serif;
      font-size   : 13px;
    }
    .results {
      font-family : monospace;
      font-size   : 20px;
    }
  </style>
  <!-- 
  <script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
   -->
  <script type="text/javascript" src="{$root_url}lib/jquery/jquery-1.10.2.min.js"></script>
  <script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/hammer.js/1.0.5/hammer.js"></script>
  <script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/jquery-mousewheel/3.1.6/jquery.mousewheel.js"></script>
  <script type="text/javascript" src="{$root_url}lib/cropbox/jquery.cropbox.js"></script>
  <link rel="stylesheet" href="{$root_url}lib/cropbox/jquery.cropbox.css">
  {literal}
  <script type="text/javascript" defer>
    $( function () {
      $( '.cropimage' ).each( function () {
        var image = $(this),
            cropwidth = image.attr('cropwidth'),
            cropheight = image.attr('cropheight'),
            results = image.next('.results' ),
            x       = $('.cropX', results),
            y       = $('.cropY', results),
            w       = $('.cropW', results),
            h       = $('.cropH', results),
            download = results.next('.download').find('a');

          image.cropbox( {width: cropwidth, height: cropheight, showControls: 'auto'} )
            .on('cropbox', function( event, results, img ) {
              x.text( results.cropX );
              y.text( results.cropY );
              w.text( results.cropW );
              h.text( results.cropH );
              download.attr('href', img.getDataURL());
            });
      } );

      $('#select').on('change', function () {
          var size = parseInt(this.value);
          $('.cropimage').each(function () {
            $(this).cropbox({width: size, height: size})
          });
      });

    } );
  </script>
  {/literal}
</head>
<body>

  <img class="cropimage" alt="" src="{$current_app_url}public/images/img.jpg" cropwidth="200" cropheight="200"/>
  <div class="results">
    <b>X</b>: <span class="cropX"></span>
    <b>Y</b>: <span class="cropY"></span>
    <b>W</b>: <span class="cropW"></span>
    <b>H</b>: <span class="cropH"></span>
  </div>
  <div class="download">
    <a href="#" download="crop.png">Download</a>
  </div>

  <br/>

  <img class="cropimage" alt="" src="{$current_app_url}public/images/img.jpg" cropwidth="300" cropheight="200"/>
  <div class="results">
    <b>X</b>: <span class="cropX"></span>
    <b>Y</b>: <span class="cropY"></span>
    <b>W</b>: <span class="cropW"></span>
    <b>H</b>: <span class="cropH"></span>
  </div>

  <div class="download">
    <a href="#" download="crop.png">Download</a>
  </div>

  <br/>

  <h4>Change size of every cropbox</h4>

  <select id="select">
    <option value="200" selected>200</option>
    <option value="300">300</option>
  </select>
</body>
