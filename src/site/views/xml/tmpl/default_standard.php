<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

global $showExternalLinks;

$showExternalLinks = (int)$this->osmapParams->get('show_external_links', 0);

$printNodeCallback = function ($node) {
    global $showExternalLinks;

    $display = !$node->ignore
        && $node->published
        && !$node->duplicate
        && $node->visibleForRobots
        && trim($node->fullLink) != '';

    // Check if is external URL and if should be ignored
    if ($display && !$node->isInternal) {
        $display = $showExternalLinks === 1;
    }

    if (!$display) {
        return false;
    }

    // Print the item
    echo '<url>';
    echo '<loc><![CDATA[' . $node->fullLink . ']]></loc>';
    echo '<lastmod>' . $node->modified . '</lastmod>';
    echo '<changefreq>' . $node->changefreq . '</changefreq>';
    echo '<priority>' . $node->priority . '</priority>';
    echo '</url>';

    return true;
};

/*
echo '<?xml-stylesheet type="text/xsl" href="' . JUri::base() . 'index.php?option=com_osmap&amp;view=xsl&amp;format=xsl&amp;tmpl=component&amp;id=' . $this->sitemap->id . '"?>';
*/

echo '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

$this->sitemap->traverse($printNodeCallback);

echo '</urlset>';
