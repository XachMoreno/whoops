<?php
/**
 * Whoops - php errors for cool kids
 * @author Filipe Dobreira <http://github.com/filp>
 * 
 * Layout template for the PrettyPageHandler error display.
 */
?>
<!DOCTYPE html>
<html class="no-js">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">

    <?php /* All stylesheets are compiled into a single block: */?>
    <style type="text/css"><?php echo $stylesheet ?></style>
  </head>
  <body>
    <div class="container">
      <div class="frames-container"></div>
      <div class="detail-container">

        <h2><code>(<?php echo $name ?>)</code> <?php echo $tpl->escape($message) ?></h2>
      </div>
    </div>
  </body>
</html>