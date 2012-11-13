<h2><?php echo __('Kdt Plugin Configuration') ?></h2>
<h3><?php echo __('Generate Member') ?></h3>

<form action="<?php echo url_for('@kdt_generate_member') ?>" method="POST">
<table>
<?php echo $form ?>
<tr>
<td colspan="2"><input type="submit" value="<?php echo __('Save') ?>" /></td>
</tr>
</table>
</form>
