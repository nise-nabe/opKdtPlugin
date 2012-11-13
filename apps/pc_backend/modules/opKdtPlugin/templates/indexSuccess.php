<?php slot('submenu') ?>
<?php include_partial('submenu') ?>
<?php end_slot() ?>

<?php slot('title', __('Kdt Plugin Configuration')) ?>

<?php foreach ($tasks as $task): ?>
<?php echo link_to(__($task), '@kdt_task?task='.$task) ?><br />
<?php endforeach; ?>
