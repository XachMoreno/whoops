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

    <?php foreach($stylesheets as $stylesheet): ?>
      <style type="text/css"><?php echo $stylesheet ?></style>
    <?php endforeach ?>
  </head>
  <body>
    <div class="container">
      <h1>Whoops!</h1>
    </div>
  </body>
</html>