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
    echo '<url>';
    echo '<loc><![CDATA[' . $node->fullLink . ']]></loc>';
    echo '<lastmod>' . $node->modifiedOn . '</lastmod>';
    echo '<changefreq>' . $node->changeFreq . '</changefreq>';
    echo '<priority>' . $node->priority . '</priority>';
    echo '</url>';
};

echo '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

$this->sitemap->traverse($printNodeCallback);

echo '</urlset>';
