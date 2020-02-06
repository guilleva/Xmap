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

use Alledia\OSMap\Factory;
use Alledia\OSMap\Sitemap\Item;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

$debug = $this->params->get('debug', 0) ? "\n" : '';

$printNodeCallback = function (Item $node) {
    $display = !$node->ignore
        && $node->published
        && (!$node->duplicate || ($node->duplicate && !$this->osmapParams->get('ignore_duplicated_uids', 1)))
        && isset($node->newsItem)
        && !empty($node->newsItem)
        && $node->visibleForRobots
        && $node->parentIsVisibleForRobots
        && $node->visibleForXML
        && $node->isInternal
        && trim($node->fullLink) != ''
        && $node->hasCompatibleLanguage();

    /** @var DateTime $publicationDate */
    $publicationDate = $this->isNewsPublication($node);
    if ($display && $publicationDate) {
        echo '<url>';
        echo '<loc><![CDATA[' . $node->fullLink . ']]></loc>';
        echo '<news:news>';

        echo '<news:publication>';
        echo ($publicationName = $this->params->get('news_publication_name', ''))
            ? '<news:name><![CDATA[' . $publicationName . ']]></news:name>'
            : '<news:name/>';

        if (empty($node->language) || $node->language == '*') {
            $node->language = $this->language;
        }
        echo '<news:language>' . $node->language . '</news:language>';
        echo '</news:publication>';

        echo '<news:publication_date>' . $publicationDate->format('Y-m-d\TH:i:s\Z') . '</news:publication_date>';
        echo '<news:title><![CDATA[' . $node->name . ']]></news:title>';

        if (!empty($node->keywords)) {
            echo '<news:keywords><![CDATA[' . $node->keywords . ']]></news:keywords>';
        }

        echo "</news:news>";
        echo '</url>';
    }

    return $display;
};

echo $this->addStylesheet();

$attribs = array(
    'xmlns'      => 'https://www.sitemaps.org/schemas/sitemap/0.9',
    'xmlns:news' => 'https://www.google.com/schemas/sitemap-news/0.9'
);

echo sprintf($debug . '<urlset %s>' . $debug, ArrayHelper::toString($attribs));

$this->sitemap->traverse($printNodeCallback);

echo '</urlset>';
