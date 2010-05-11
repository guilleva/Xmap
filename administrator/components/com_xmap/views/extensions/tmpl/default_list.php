<?php
/**
 * @version             $Id$
 * @copyright			Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
 */

// no direct access
defined('_JEXEC') or die;

$user = JFactory::getUser();
$canChange  = $user->authorise('core.edit.state',    'com_plugins');
$canEdit    = $user->authorise('core.edit',          'com_plugins');
?>
<form action="index.php" method="post" name="adminForm">
	<?php if ($this->ftp) : ?>
		<?php echo $this->loadTemplate('ftp'); ?>
	<?php endif; ?>

	<table class="adminform">
		<tbody>
			<tr>
				<td width="100%"><?php echo JText::_('Xmap_Desc_Extensions'); ?></td>
			</tr>
		</tbody>
	</table>

	<?php if (count($this->items)) : ?>
	<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th class="title" width="10px"><?php echo JText::_('Num'); ?></th>
				<th class="title"><?php echo JText::_('Plugin'); ?></th>
				<th class="title" width="10%"><?php echo JText::_('Folder'); ?></th>
				<th class="title" width="10%"><?php echo JText::_('Published'); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_('Version'); ?></th>
				<th class="title" width="15%"><?php echo JText::_('Date'); ?></th>
				<th class="title" width="25%"><?php echo JText::_('Author'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="7"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item): ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td class="center">
                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                </td>
                <td>
                    <?php if ($canEdit) : ?>
                        <a href="<?php echo JRoute::_('index.php?option=com_xmap&task=extension.edit&id='.(int) $item->id); ?>">
                            <?php echo $item->name; ?></a>
                    <?php else : ?>
                            <?php echo $item->name; ?>
                    <?php endif; ?>
                </td>
                <td class="center">
                    <?php echo $this->escape($item->folder); ?>
                </td>
                <td class="center">
                    <?php echo JHtml::_('jgrid.published', $item->enabled, $i, 'extensions.', $canChange); ?>
                </td>
                <td class="nowrap center<?php if (@$item->legacy) echo ' legacy-mode'; ?>">
                    <?php echo @$item->version != '' ? $item->version : '&nbsp;'; ?>
                </td>
                <td class="nowrap center">
                    <?php echo @$item->creationDate != '' ? $item->creationDate : '&nbsp;'; ?>
                </td>
                <td class="center">
                    <?php echo @$item->authorEmail .'<br />'. @$item->authorUrl; ?>
                </td>
            </tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php else : ?>
		<?php echo JText::_('There are no custom extensions installed'); ?>
	<?php endif; ?>

	<input type="hidden" name="view" value="extensions" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="option" value="com_xmap" />
	<input type="hidden" name="type" value="xmap_ext" />
    <input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
