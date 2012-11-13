<?php slot('submenu') ?>
<?php include_partial('submenu') ?>
<?php end_slot() ?>

<?php slot('title', __('Kdt Plugin Configuration')) ?>
<h3><?php echo __('Generate Community') ?></h3>

<form action="<?php echo url_for('@kdt_generate_community') ?>" method="POST">
<table>
<?php echo $form ?>
<tr>
<td colspan="2"><input type="submit" value="<?php echo __('Save') ?>" /></td>
</tr>
</table>
</form>
