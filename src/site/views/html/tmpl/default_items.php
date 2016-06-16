<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

$lastLevel = 0;

$printNodeCallback = function ($node) {
    global $lastLevel;

    if (!$node->ignore && $node->published) {

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

        echo '<li>';
        echo '<a href="' . $node->fullLink . '" target="_blank">';
        echo htmlspecialchars($node->name);
        echo '</a>';
        echo '</li>';

        $lastLevel = $node->level;
    }

    return !$node->ignore;
};
?>
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
