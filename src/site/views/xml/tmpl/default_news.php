<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap;

defined('_JEXEC') or die();

$params = $this->params;

$printNodeCallback = function ($node) use ($params) {
    $display = !$node->ignore
        && $node->published
        && (!$node->duplicate || ($node->duplicate && !$this->osmapParams->get('ignore_duplicated_uids', 1)))
        && isset($node->newsItem)
        && !empty($node->newsItem)
        && $node->visibleForRobots
        && $node->visibleForXML
        && $node->isInternal
        && trim($node->fullLink) != '';

    if (!$node->hasCompatibleLanguage()) {
        $display = false;
    }

    if (!$display) {
        return false;
    }

    // Publication name
    $publicationName = $params->get('news_publication_name', '');

    // Print the item
    echo '<url>';
    echo '<loc>' . htmlspecialchars($node->fullLink) . '</loc>';
    echo "<news:news>";
    echo '<news:publication>';
    echo '<news:name>' . htmlspecialchars($publicationName) . '</news:name>';

    // Language
    if (!isset($node->language) || $node->language == '*') {
        $defaultLanguage = strtolower(JFactory::getLanguage()->getTag());

        // Legacy code. Not sure why the hardcoded zh-cn and zh-tw
        if (preg_match('/^([a-z]+)-.*/', $defaultLanguage, $matches)

           && !in_array($defaultLanguage, array(' zh-cn', ' zh-tw'))) {
            $defaultLanguage = $matches[1];
        }

        $node->language = $defaultLanguage;
    }

    echo '<news:language>' . $node->language . '</news:language>';

    echo '</news:publication>';

    // Publication date
    $publicationDate = (
        isset($node->modified)
        && !empty($node->modified)
        && $node->modified != OSMap\Factory::getDbo()->getNullDate()
        && $node->modified != -1
    ) ? $node->modified : null;

    if (empty($publicationDate)) {
        $publicationDate = time();
    }

    if ($publicationDate && !is_numeric($publicationDate)) {
        $date            = new JDate($publicationDate);
        $publicationDate = $date->toUnix();
    }

    if ($publicationDate) {
        $publicationDate = gmdate('Y-m-d\TH:i:s\Z', $publicationDate);
    }

    echo '<news:publication_date>' . $publicationDate . '</news:publication_date>';

    // Title
    echo '<news:title>' . htmlspecialchars($node->name) . '</news:title>';

    // Keywords
    if (isset($node->keywords)) {
        echo '<news:keywords>' . htmlspecialchars($node->keywords) . '</news:keywords>';
    }

    echo "</news:news>";
    echo '</url>';

    return true;
};

// Do we need to apply XSL?
if ($this->params->get('add_styling', 1)) {
    $title = '';
    if ($this->params->get('show_page_heading', 1)) {
        $title = '&amp;title=' . urlencode($this->pageHeading);
    }

    echo '<?xml-stylesheet type="text/xsl" href="' . JUri::base() . 'index.php?option=com_osmap&amp;view=xsl&amp;format=xsl&amp;tmpl=component&amp;layout=news&amp;id=' . $this->sitemap->id . $title . '"?>';
}

echo '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">';

$this->sitemap->traverse($printNodeCallback);

echo '</urlset>';
