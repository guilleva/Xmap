<?php
/**
 * @package   OSMap
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2020 Joomlashack.com. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSMap.
 *
 * OSMap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSMap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSMap.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die();

global $ignoreDuplicatedUIDs;

$ignoreDuplicatedUIDs = (int)$this->osmapParams->get('ignore_duplicated_uids', 1);

$printNodeCallback = function ($node) {
    global $ignoreDuplicatedUIDs;

    $display = !$node->ignore
        && $node->published
        && (!$node->duplicate || ($node->duplicate && !$ignoreDuplicatedUIDs))
        && $node->visibleForRobots
        && $node->parentIsVisibleForRobots
        && $node->visibleForXML
        && $node->isInternal
        && trim($node->fullLink) != '';

    if (!$node->hasCompatibleLanguage()) {
        $display = false;
    }

    // If the item would be displayed, but doesn't have images, we return true to still get it's child items images.
    if ($display && (!isset($node->images) || empty($node->images))) {
        return true;
    }

    if (!$display) {
        return false;
    }

    // Print the item
    echo '<url>';
    echo '<loc><![CDATA[' . $node->fullLink . ']]></loc>';

    foreach ($node->images as $image) {
        if (!empty($image->src)) {
            echo '<image:image>';
            // Link
            echo '<image:loc><![CDATA[' . $image->src . ']]></image:loc>';
            // Title
            echo '<image:title>';
            if (!empty($image->title)) {
                echo '<![CDATA[' . $image->title . ']]>';
            }
            echo '</image:title>';
            // License
            if (isset($image->license) && !empty($image->license)) {
                echo '<image:license><![CDATA[' . $image->license . ']]></image:license>';
            }

            echo '</image:image>';
        }
    }

    echo '</url>';

    return true;
};

// Do we need to apply XSL?
if ($this->params->get('add_styling', 1)) {
    $title = '';
    if ($this->params->get('show_page_heading', 1)) {
        $title = '&amp;title=' . urlencode($this->pageHeading);
    }

    echo '<?xml-stylesheet type="text/xsl" href="' . JUri::base() . 'index.php?option=com_osmap&amp;view=xsl&amp;format=xsl&amp;tmpl=component&amp;layout=images&amp;id=' . $this->sitemap->id . $title . '"?>';
}

echo '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

$this->sitemap->traverse($printNodeCallback);

echo '</urlset>';
