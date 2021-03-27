<?php
/**
 * @version          $Id$
 * @copyright        Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license          GNU General Public License version 2 or later; see LICENSE.txt
 * @author           Guillermo Vargas (guille@vargas.co.cr)
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$app = Factory::getApplication();
$template = $app->getTemplate();

Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');

if ($this->fieldsets)
{
	HTMLHelper::_('bootstrap.framework');
}

$xml = $this->form->getXml();

?>

<script type="text/javascript">
    function submitbutton(task)
    {
        if (task == 'sitemap.cancel' || document.formvalidator.isValid($('adminForm'))) {
            submitform(task);
        }
    }
</script>
<form action="<?php echo JRoute::_('index.php?option=com_xmap&layout=edit&id='.$this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
    <div class="row-fluid">
        <!-- Begin Content -->
		<div class="col-md-12" id="config">
			<?php if ($this->fieldsets) : ?>
				<?php $opentab = 0; ?>

				<?php echo HTMLHelper::_('uitab.startTabSet', 'configTabs'); ?>

				<?php foreach ($this->fieldsets as $name => $fieldSet) : ?>
					<?php
					$hasChildren = $xml->xpath('//fieldset[@name="' . $name . '"]/fieldset');
					$hasParent = $xml->xpath('//fieldset/fieldset[@name="' . $name . '"]');
					$isGrandchild = $xml->xpath('//fieldset/fieldset/fieldset[@name="' . $name . '"]');
					?>

					<?php $dataShowOn = ''; ?>
					<?php if (!empty($fieldSet->showon)) : ?>
						<?php $wa->useScript('showon'); ?>
						<?php $dataShowOn = ' data-showon=\'' . json_encode(FormHelper::parseShowOnConditions($fieldSet->showon, $this->formControl)) . '\''; ?>
					<?php endif; ?>

					<?php $label = empty($fieldSet->label) ? 'XMAP_FIELDSET_' . strtoupper($name) : $fieldSet->label; ?>

					<?php if (!$isGrandchild && $hasParent) : ?>
						<fieldset id="fieldset-<?php echo $this->escape($name); ?>" class="options-menu options-form">
							<legend><?php echo Text::_($fieldSet->label); ?></legend>
							<div>
					<?php elseif (!$hasParent) : ?>
						<?php if ($opentab) : ?>

							<?php if ($opentab > 1) : ?>
								</div>
								</fieldset>
							<?php endif; ?>

							<?php echo HTMLHelper::_('uitab.endTab'); ?>

						<?php endif; ?>

						<?php echo HTMLHelper::_('uitab.addTab', 'configTabs', $name, Text::_($label)); ?>

						<?php $opentab = 1; ?>

						<?php if (!$hasChildren) : ?>

						<fieldset id="fieldset-<?php echo $this->escape($name); ?>" class="options-menu options-form">
							<legend><?php echo Text::_($fieldSet->label); ?></legend>
							<div>
						<?php $opentab = 2; ?>
						<?php endif; ?>
					<?php endif; ?>

					<?php if (!empty($fieldSet->description)) : ?>
						<div class="tab-description alert alert-info">
							<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
							<?php echo Text::_($fieldSet->description); ?>
						</div>
					<?php endif; ?>

					<?php if (!$hasChildren) : ?>
						<?php echo $this->form->renderFieldset($name, $name === 'permissions' ? ['hiddenLabel' => true, 'class' => 'revert-controls'] : []); ?>
					<?php endif; ?>

					<?php if (!$isGrandchild && $hasParent) : ?>
						</div>
					</fieldset>
					<?php endif; ?>
				<?php endforeach; ?>

				<?php if ($opentab) : ?>

					<?php if ($opentab > 1) : ?>
						</div>
						</fieldset>
					<?php endif; ?>
					<?php echo HTMLHelper::_('uitab.endTab'); ?>
				<?php endif; ?>

			<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
			<?php endif; ?>
        </div>
    </div>

    <input type="hidden" name="task" value="" />
    <?php echo $this->form->getInput('is_default'); ?>
    <?php echo JHtml::_('form.token'); ?>
</form>
<div class="clr"></div>
