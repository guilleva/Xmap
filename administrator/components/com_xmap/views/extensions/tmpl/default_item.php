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
<tr class="<?php echo "row".$this->item->index % 2; ?>">
	<td><?php echo $this->pagination->getRowOffset($this->item->index); ?></td>
	<td>
		<input type="checkbox" id="cb<?php echo $this->item->index;?>" name="eid[<?php echo $this->item->id; ?>]" value="<?php echo $this->item->id; ?>" onclick="isChecked(this.checked);" />
		<span class="editlinktip hasTip" title="<?php echo JText::_( 'Edit Plugin' );?>::<?php echo $this->item->name; ?>">
                                <a href="<?php echo $this->item->link; ?>"><?php echo $this->item->name; ?></a></span>
	</td>
	<td><?php echo $this->item->folder; ?></td>
	<td align="center">
		<?php if (!$this->item->element) : ?>
		<strong>X</strong>
		<?php else : ?>
		<a href="index.php?option=com_xmap&amp;task=extensions.<?php echo $this->item->task; ?>&amp;eid[]=<?php echo $this->item->id; ?>&amp;limitstart=<?php echo $this->pagination->limitstart; ?>&amp;<?php echo JUtility::getToken();?>=1"><img src="images/<?php echo $this->item->img; ?>" border="0" title="<?php echo $this->item->action; ?>" alt="<?php echo $this->item->alt; ?>" /></a>
		<?php endif; ?>
	</td>
	<td align="center" <?php if (@$this->item->legacy) echo 'class="legacy-mode"'; ?>><?php echo @$this->item->version != '' ? $this->item->version : '&nbsp;'; ?></td>
	<td><?php echo @$this->item->creationdate != '' ? $this->item->creationdate : '&nbsp;'; ?></td>
	<td>
		<span class="editlinktip hasTip" title="<?php echo JText::_('Author Information');?>::<?php echo $this->item->author_info; ?>">
			<?php echo @$this->item->author != '' ? $this->item->author : '&nbsp;'; ?>
		</span>
	</td>
</tr>
