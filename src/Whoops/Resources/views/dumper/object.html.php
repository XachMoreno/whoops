<?php /** Generic dumper for all objects */ ?>
<?php $properties = get_object_vars($variable); ?>
<h1><?php echo get_class($variable) ?></h1>

<h2>Properties (<?php echo count($properties) ?>):</h2>
<?php foreach($properties as $property): ?>
  <?php $tpl->dump($property) ?>
<?php endforeach ?>
