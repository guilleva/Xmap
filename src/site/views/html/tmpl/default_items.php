<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

$printNodeCallback = function ($node) {
    if (!$node->ignore) {
        echo '<li>';
        echo '<a href="' . $node->fullLink . '" target="_blank">';
        echo htmlspecialchars($node->name);
        echo '</a>';
        echo '</li>';
    }
};
?>
<ul>
    <?php $this->sitemap->traverse($printNodeCallback); ?>
</ul>
