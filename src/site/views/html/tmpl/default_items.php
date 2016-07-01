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
global $lastMenuId, $lastLevel, $debug, $count, $showExternalLinks;

$lastMenuId        = 0;
$lastLevel         = 0;
$count             = 0;
$debug             = $this->debug;
$showExternalLinks = (int)$this->osmapParams->get('show_external_links', 0);

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
    echo '<ul class="level_0">';
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
    echo '<div><span>' . JText::_('COM_OSMAP_MODIFIED') . ':</span>&nbsp;' . htmlspecialchars($node->modified) . '</div>';
    echo '<div><span>' . JText::_('COM_OSMAP_LEVEL') . ':</span>&nbsp;' . $node->level . '</div>';
    echo '<div><span>' . JText::_('COM_OSMAP_DUPLICATE') . ':</span>&nbsp;' . JText::_($node->duplicate ? 'JYES' : 'JNO') . '</div>';
    echo '<div><span>' . JText::_('COM_OSMAP_VISIBLE_FOR_ROBOTS') . ':</span>&nbsp;' . JText::_($node->visibleForRobots ? 'JYES' : 'JNO') . '</div>';
    echo '<div><span>' . JText::_('COM_OSMAP_ADAPTER_CLASS') . ':</span>&nbsp;' . get_class($node->adapter) . '</div>';

    $adminNotes = $node->getAdminNotesString();
    if (!empty($adminNotes)) {
        echo '<div><span>' . JText::_('COM_OSMAP_ADMIN_NOTES') . ':</span>&nbsp;' . nl2br($adminNotes) . '</div>';
    }
    echo '</div>';
}

function printItem($node, $debug, $count)
{
    $liClass = $debug ? 'osmap-debug-item' : '';
    $liClass .= $count % 2 == 0 ? ' even' : '';

    echo "<li class=\"{$liClass}\">";

    // Some items are just separator, without a link. Do not print as link then
    if (trim($node->fullLink) === '') {
        $type = isset($node->type) ? $node->type : 'separator';
        echo '<span class="osmap-item-' . $type . '">';
        echo htmlspecialchars($node->name);
        echo '</span>';
    } else {
        echo '<a href="' . $node->fullLink . '" target="_blank" class="osmap-link">';
        echo htmlspecialchars($node->name);
        echo '</a>';
    }

    // Debug box
    if ($debug) {
        printDebugInfo($node, $count);
    }

    echo '</li>';
}

$printNodeCallback = function ($node) {
    global $lastMenuId, $lastLevel, $debug, $count, $showExternalLinks;

    $display = !$node->ignore
        && $node->published
        && $node->visibleForHTML;

    // Check if is external URL and if should be ignored
    if ($display && !$node->isInternal) {
        $display = $showExternalLinks > 0;
    }

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
