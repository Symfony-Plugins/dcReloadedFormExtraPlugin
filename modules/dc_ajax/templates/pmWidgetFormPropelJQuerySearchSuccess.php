<?php use_helper("I18N", "JavascriptBase") ?>

<?php if ($total_objects): ?>

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
  
  <?php if (count($objects) < $total_objects): ?>
    <ul id="jquery_search_navigation">
      <?php if ($previous_page >= 0): ?>
        <li id="jquery_search_navigation_previous">
          <?php echo link_to_function(__("Previous"), "$js_var_name.search($previous_page)") ?>
        </li>
      <?php endif ?>
      <?php if (count($results) == $limit): ?>
        <li id="jquery_search_navigation_next">
          <?php echo link_to_function(__("Next"), "$js_var_name.search($next_page)") ?>
        </li>
      <?php endif ?>
    </ul>
  <?php endif ?>

<?php else: ?>
  
  <script>
    <?php echo $js_var_name ?>.displayNoResultsFoundLabel();
  </script>
  
<?php endif ?>