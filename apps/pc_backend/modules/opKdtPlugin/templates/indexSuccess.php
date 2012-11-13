<?php slot('submenu') ?>
<?php include_partial('submenu') ?>
<?php end_slot() ?>

<?php slot('title', __('Kdt Plugin Configuration')) ?>

<?php echo link_to(__('Generate Member'), '@kdt_generate_member') ?>
<?php echo link_to(__('Generate %Community%'), '@kdt_generate_community') ?>
