<?php
/**
 * @version             $Id$
 * @copyright			Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
*/

// no direct access
defined('_JEXEC') or die;
?>
<script language="javascript" type="text/javascript">
<!--
	function submitbutton3(pressbutton) {
		var form = document.installForm;

		// do field validation
		if (form.install_directory.value == ""){
			alert("<?php echo JText::_('Please select a directory', true); ?>");
		} else {
			form.installtype.value = 'folder';
			form.submit();
		}
	}

	function submitbutton4(pressbutton) {
		var form = document.installForm;

		// do field validation
		if (form.install_url.value == "" || form.install_url.value == "http://"){
			alert("<?php echo JText::_('Please enter a URL', true); ?>");
		} else {
			form.installtype.value = 'url';
			form.submit();
		}
	}

	function submitbutton5(pressbutton) {
		var form = document.installForm;

		// do field validation
		if (form.install_package.value == ""){
			alert("<?php echo JText::_('Please select a file to upload', true); ?>");
		} else {
			form.submit();
		}
	}
//-->
</script>
<h2><?php echo JText::_('Install new Extension'); ?></h2>
<form enctype="multipart/form-data" action="index.php" method="post" name="installForm">
	<?php if ($this->ftp) : ?>
		<?php echo $this->loadTemplate('ftp'); ?>
	<?php endif; ?>

	<table class="adminform">
	<tr>
		<th colspan="2"><?php echo JText::_('Upload Package File'); ?></th>
	</tr>
	<tr>
		<td width="120">
			<label for="install_package"><?php echo JText::_('Package File'); ?>:</label>
		</td>
		<td>
			<input class="input_box" id="install_package" name="install_package" type="file" size="57" />
			<input class="button" type="button" value="<?php echo JText::_('Upload File'); ?> &amp; <?php echo JText::_('Install'); ?>" onclick="submitbutton5()" />
		</td>
	</tr>
	</table>

	<table class="adminform">
	<tr>
		<th colspan="2"><?php echo JText::_('Install from directory'); ?></th>
	</tr>
	<tr>
		<td width="120">
			<label for="install_directory"><?php echo JText::_('Install directory'); ?>:</label>
		</td>
		<td>
			<input type="text" id="install_directory" name="install_directory" class="input_box" size="70" value="<?php echo $this->state->get('install.directory'); ?>" />
			<input type="button" class="button" value="<?php echo JText::_('Install'); ?>" onclick="submitbutton3()" />
		</td>
	</tr>
	</table>

	<table class="adminform">
	<tr>
		<th colspan="2"><?php echo JText::_('Install from URL'); ?></th>
	</tr>
	<tr>
		<td width="120">
			<label for="install_url"><?php echo JText::_('Install URL'); ?>:</label>
		</td>
		<td>
			<input type="text" id="install_url" name="install_url" class="input_box" size="70" value="http://" />
			<input type="button" class="button" value="<?php echo JText::_('Install'); ?>" onclick="submitbutton4()" />
		</td>
	</tr>
	</table>

	<input type="hidden" name="type" value="" />
	<input type="hidden" name="installtype" value="upload" />
	<input type="hidden" name="task" value="extensions.doInstall" />
	<input type="hidden" name="view" value="extensions" />
	<input type="hidden" name="option" value="com_xmap" />
	<?php echo JHtml::_('form.token'); ?>
</form>