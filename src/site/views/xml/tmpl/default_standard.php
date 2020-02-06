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

use Alledia\OSMap\Helper\General;
use Alledia\OSMap\Sitemap\Item;

defined('_JEXEC') or die();

$showExternalLinks    = (int)$this->osmapParams->get('show_external_links', 0);
$ignoreDuplicatedUIDs = (int)$this->osmapParams->get('ignore_duplicated_uids', 1);
$debug                = $this->params->get('debug', 0) ? "\n" : '';

$printNodeCallback = function (Item $node) use ($showExternalLinks, $ignoreDuplicatedUIDs, $debug) {
    $display = !$node->ignore
        && $node->published
        && (!$node->duplicate || ($node->duplicate && !$ignoreDuplicatedUIDs))
        && $node->visibleForRobots
        && $node->parentIsVisibleForRobots
        && $node->visibleForXML
        && trim($node->fullLink) != '';

    // Check if is external URL and if should be ignored
    if ($display && !$node->isInternal) {
        $display = $showExternalLinks === 1;
    }

    if (!$node->hasCompatibleLanguage()) {
        $display = false;
    }

    if (!$display) {
        return false;
    }

    echo $debug;

    echo '<url>';
    echo '<loc><![CDATA[' . $node->fullLink . ']]></loc>';

    if (!General::isEmptyDate($node->modified)) {
        echo '<lastmod>' . $node->modified . '</lastmod>';
    }

    echo '<changefreq>' . $node->changefreq . '</changefreq>';
    echo '<priority>' . $node->priority . '</priority>';
    echo '</url>';

    echo $debug;

    return true;
};

echo $this->addStylesheet();

echo $debug . '<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">' . $debug;

$this->sitemap->traverse($printNodeCallback);

echo '</urlset>';
