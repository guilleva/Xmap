<?php
/**
 * @version             $Id$
 * @copyright		Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

jimport('joomla.html.pane');
$pane = &JPane::getInstance('sliders', array('allowAllClose' => true));

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>

<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'sitemap.cancel' || document.formvalidator.isValid($('item-form'))) {
			<?php //echo $this->form->fields['introtext']->editor->save('jform[introtext]'); ?>
			submitform(task);
		}
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_xmap'); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<div class="width-60 fltlft">
		<fieldset class="adminform">
                <legend><?php echo JText::_('Xmap_Sitemap_Details_Fieldset'); ?></legend>
		<?php echo $this->form->getLabel('title'); ?>
		<?php echo $this->form->getInput('title'); ?>

		<?php echo $this->form->getLabel('alias'); ?>
		<?php echo $this->form->getInput('alias'); ?>

                <?php echo $this->form->getLabel('state'); ?>
		<?php echo $this->form->getInput('state'); ?>

                <?php echo $this->form->getLabel('access'); ?>
		<?php echo $this->form->getInput('access'); ?>

		<div class="clr"></div>
		<?php echo $this->form->getLabel('introtext'); ?><br />
		<div class="clr"></div>
		<?php echo $this->form->getInput('introtext'); ?>
		</fieldset>
	</div>

	<div class="width-40" style="float:left">
		<?php echo $pane->startPane('xmap-pane'); ?>

		<?php echo $pane->startPanel(JText::_('Xmap_Fieldset_Menus'), 'menus-details'); ?>
				<?php echo $this->form->getInput('selections'); ?>
		<?php echo $pane->endPanel(); ?>

		<?php echo $pane->startPanel(JText::_('Xmap_Fieldset_Options'), 'basic-options'); ?>
		<table>
			<tr>
				<td class="paramlist_key" width="40%">
					<?php echo $this->form->getLabel('access'); ?>
				</td>
				<td class="paramlist_value">
					<?php echo $this->form->getInput('access'); ?>
				</td>
			</tr>
		<?php foreach($this->form->getFields('attribs') as $field): ?>
			<?php if ($field->hidden): ?>
				<?php echo $field->input; ?>
			<?php else: ?>
				<tr>
					<td class="paramlist_key" width="40%">
						<?php echo $field->label; ?>
					</td>
					<td class="paramlist_value">
						<?php echo $field->input; ?>
					</td>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
		</table>
		<?php echo $pane->endPanel(); ?>

		<?php echo $pane->startPanel(JText::_('Xmap_Fieldset_Metadata'), 'meta-options'); ?>
		<ol>
			<li>
				<?php echo $this->form->getLabel('metadesc'); ?><br />
				<?php echo $this->form->getInput('metadesc'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('metakey'); ?><br />
				<?php echo $this->form->getInput('metakey'); ?>
			</li>
		</ol>
		<?php echo $pane->endPanel(); ?>

		<?php echo $pane->endPane(); ?>
	</div>




	<input type="hidden" name="task" value="" />
	<?php echo $this->form->getInput('is_default'); ?>
	<?php echo JHtml::_('form.token'); ?>
</form>
<div class="clr"></div>
