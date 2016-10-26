<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();
?>

<?php if ($this->debug) : ?>
    <div class="osmap-debug-sitemap">
        <h1><?php echo JText::_('COM_OSMAP_DEBUG_ALERT_TITLE'); ?></h1>
        <p><?php echo JText::_('COM_OSMAP_DEBUG_ALERT'); ?></p>
        <?php echo JText::_('COM_OSMAP_SITEMAP_ID'); ?>: <?php echo $this->sitemap->id; ?>
    </div>
<?php endif; ?>

<div class="osmap-items">
    <?php $this->sitemap->traverse(array($this, 'printNodeCallback')); ?>

    <?php if ($this->shouldCloseMenu) : ?>
        <?php $this->closeMenu(); ?>
    <?php endif; ?>
</div>

<?php // Make sure we close the stack of levels ?>
<?php if ($this->lastLevel > 0) : ?>
    <?php $this->closeLevels($this->lastLevel); ?>
<?php endif; ?>

<?php if ($this->debug) : ?>
    <div class="osmap-debug-items-count">
        <?php echo JText::_('COM_OSMAP_SITEMAP_ITEMS_COUNT'); ?>: <?php echo $this->count; ?>
    </div>
<?php endif; ?>
