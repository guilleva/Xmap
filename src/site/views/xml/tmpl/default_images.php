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
    $display = !$node->ignore
        && $node->published
        && (!$node->duplicate || ($node->duplicate && !$this->osmapParams->get('ignore_duplicated_uids', 1)))
        && isset($node->images)
        && !empty($node->images)
        && $node->visibleForRobots
        && $node->visibleForXML
        && $node->isInternal
        && trim($node->fullLink) != '';

    if (!$display) {
        return false;
    }

    // Print the item
    echo '<url>';
    echo '<loc>' . $node->fullLink . '</loc>';

    foreach ($node->images as $image) {
        if (!empty($image->src)) {
            echo '<image:image>';
            // Link
            echo '<image:loc>' . $image->src . '</image:loc>';
            // Title
            echo '<image:title>';
            if (!empty($image->title)) {
                echo '<![CDATA[' . $image->title . ']]>';
            }
            echo '</image:title>';
            // Lincense
            if (isset($image->license)) {
                echo '<image:license><![CDATA[' . $image->license . ']]></image:license>';
            }

            echo '</image:image>';
        }
    }

    echo '</url>';

    return true;
};

echo '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

$this->sitemap->traverse($printNodeCallback);

echo '</urlset>';
