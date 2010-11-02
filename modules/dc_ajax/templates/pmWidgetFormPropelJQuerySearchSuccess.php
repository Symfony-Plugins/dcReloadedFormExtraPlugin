<?php use_helper("JavascriptBase") ?>

<?php if (count($objects)): ?>

  <dl>
    <?php foreach ($objects as $object): ?>
      <dt id="result_<?php echo $object->$methodKey() ?>">
        <?php echo $object->$methodValue() ?>

        <script>
          <?php echo $js_var_name ?>.getSelectLink(<?php echo $object->$methodKey() ?>, "<?php echo $object->$methodValue() ?>");
        </script>
    
      </dt>
    <?php endforeach ?>
  </dl>

<?php else: ?>
  
  <script>
    <?php echo $js_var_name ?>.displayNoResultsFoundLabel();
  </script>
  
<?php endif ?>