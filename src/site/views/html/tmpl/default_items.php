<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

// Declares global variables to be used into the callback
global $lastMenuId, $lastLevel, $debug, $count;

$lastMenuId = 0;
$lastLevel  = 0;
$count      = 0;
$debug      = $this->debug;

function closeLevels($offset)
{
    if ($offset > 0) {
        for ($i = 0; $i < $offset; $i++) {
            echo '</ul>';
        }
    }
}

function openMenu($node, $debug)
{
    echo '<h2>' . $node->menu->name;

    if ($debug) {
        echo '<div><span>' . JText::_('COM_OSMAP_MENUTYPE') . ':</span>&nbsp;' . $node->menu->id . ': ' . $node->menu->menutype . '</div>';
    }

    echo '</h2>';
    echo '<ul>';
}

function closeMenu()
{
    echo '</ul>';
}

function openSubLevel($node)
{
    echo '<ul class="level_' . $node->level . '">';
}

function printDebugInfo($node, $count)
{
    echo '<div class="osmap-debug-box">';
    echo '<div><span>#:</span>&nbsp;' . $count . '</div>';
    echo '<div><span>' . JText::_('COM_OSMAP_UID') . ':</span>&nbsp;' . $node->uid . '</div>';
    echo '<div><span>' . JText::_('COM_OSMAP_FULL_LINK') . ':</span>&nbsp;' . htmlspecialchars($node->fullLink) . '</div>';
    echo '<div><span>' . JText::_('COM_OSMAP_LINK') . ':</span>&nbsp;' . htmlspecialchars($node->link) . '</div>';
    echo '<div><span>' . JText::_('COM_OSMAP_LEVEL') . ':</span>&nbsp;' . $node->level . '</div>';
    echo '<div><span>' . JText::_('COM_OSMAP_DUPLICATE') . ':</span>&nbsp;' . JText::_($node->duplicate ? 'JYES' : 'JNO') . '</div>';
    echo '</div>';
}

function printItem($node, $debug, $count)
{
    $liClass = $debug ? 'osmap-debug-item' : '';
    $liClass .= $count % 2 == 0 ? ' even' : '';

    echo "<li class=\"{$liClass}\">";
    echo '<a href="' . $node->fullLink . '" target="_blank" class="osmap-link">';
    echo htmlspecialchars($node->name);
    echo '</a>';

    // Debug box
    if ($debug) {
        printDebugInfo($node, $count);
    }
    echo '</li>';
}

$printNodeCallback = function ($node) {
    global $lastMenuId, $lastLevel, $debug, $count;

    $display = !$node->ignore && $node->published;

    if (!$display) {
        return false;
    }

    $count++;

    if ($lastMenuId !== $node->menu->id) {
        // Make sure we need to close the last menu
        if ($lastMenuId > 0) {
            closeLevels($lastLevel);
            closeMenu();
        }

        openMenu($node, $debug);
        $lastLevel = 0;
    }

    // Check if we have a different level to start or close tags
    if ($lastLevel !== $node->level) {
        if ($node->level > $lastLevel) {
            openSubLevel($node);
        }

        if ($node->level < $lastLevel) {
            // Make sure we close the stack of prior levels
            closeLevels($lastLevel - $node->level);
        }
    }

    printItem($node, $debug, $count);

    $lastLevel  = $node->level;
    $lastMenuId = $node->menu->id;

    return true;
};
?>

<?php if ($this->debug) : ?>
    <div class="osmap-debug-sitemap">
        <h1><?php echo JText::_('COM_OSMAP_DEBUG_ALERT_TITLE'); ?></h1>
        <p><?php echo JText::_('COM_OSMAP_DEBUG_ALERT'); ?></p>
        <?php echo JText::_('COM_OSMAP_SITEMAP_ID'); ?>: <?php echo $this->sitemap->id; ?>
    </div>
<?php endif; ?>

<div class="osmap-items">
    <?php $this->sitemap->traverse($printNodeCallback); ?>
</div>

<?php // Make sure we close the stack of levels ?>
<?php if ($lastLevel > 0) : ?>
    <?php closeLevels($lastLevel); ?>
<?php endif; ?>

<?php closeMenu(); ?>

<?php if ($this->debug) : ?>
    <div class="osmap-debug-items-count">
        <?php echo JText::_('COM_OSMAP_SITEMAP_ITEMS_COUNT'); ?>: <?php echo $count; ?>
    </div>
<?php endif; ?>
