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
global $lastLevel, $debug, $count;

$lastLevel = 0;
$count     = 0;
$debug     = $this->debug;

$printNodeCallback = function ($node) {
    global $lastLevel, $debug, $count;

    if (!$node->ignore && $node->published) {

        $count++;

        // Check if we have a different level to start or close tags
        if ($lastLevel !== $node->level) {
            if ($node->level > $lastLevel) {
                echo '<ul class="level_' . $node->level . '">';
            }

            if ($node->level < $lastLevel) {
                // Make sure we close the stack of prior levels
                $offset = $lastLevel - $node->level;
                if ($offset > 0) {
                    for ($i = 0; $i < $offset; $i++) {
                        echo '</ul>';
                    }
                }
            }
        }

        $liClass = $debug ? 'class="osmap-debug"' : '';
        echo "<li {$liClass}>";
        echo '<a href="' . $node->fullLink . '" target="_blank">';
        echo htmlspecialchars($node->name);
        echo '</a>';

        // Debug box
        if ($debug) {
            echo '<div class="osmap-debug-box">';
            echo '<div><span>#:</span> ' . $count . '</div>';
            echo '<div><span>' . JText::_('COM_OSMAP_UID') . ':</span> ' . $node->uid . '</div>';
            echo '<div><span>' . JText::_('COM_OSMAP_FULL_LINK') . ':</span> ' . htmlspecialchars($node->fullLink) . '</div>';
            echo '<div><span>' . JText::_('COM_OSMAP_LINK') . ':</span> ' . htmlspecialchars($node->link) . '</div>';
            echo '<div><span>' . JText::_('COM_OSMAP_LEVEL') . ':</span> ' . $node->level . '</div>';
            echo '</div>';
        }
        echo '</li>';

        $lastLevel = $node->level;
    }

    return !$node->ignore;
};
?>

<?php if ($this->debug) : ?>
    <div class="osmap-debug-sitemap">
        <?php echo JText::_('COM_OSMAP_SITEMAP_ID'); ?>: <?php echo $this->sitemap->id; ?>
    </div>
<?php endif; ?>

<ul>
    <?php $this->sitemap->traverse($printNodeCallback); ?>

    <?php
    // Make sure we close the stack of levels
    if ($lastLevel > 0) {
        for ($i = 0; $i < $lastLevel; $i++) {
            echo '</ul>';
        }
    }
    ?>
</ul>

<?php if ($this->debug) : ?>
    <div class="osmap-debug-sitemap-count">
        <?php echo JText::_('COM_OSMAP_SITEMAP_ITEMS_COUNT'); ?>: <?php echo $count; ?>
    </div>
<?php endif; ?>
