<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

require_once JPATH_SITE . '/components/com_content/helpers/route.php';
require_once JPATH_SITE . '/components/com_content/helpers/query.php';

/**
 * Handles standard Joomla's Content articles/categories
 *
 * This plugin is able to expand the categories keeping the right order of the
 * articles acording to the menu settings and the user session data (user state).
 *
 * This is a very complex plugin, if you are trying to build your own plugin
 * for other component, I suggest you to take a look to another plugis as
 * they are usually most simple. ;)
 */

use Alledia\OSMap;
use Alledia\Framework;

class PlgOSMapJoomla extends OSMap\Plugin\Base implements OSMap\Plugin\ContentInterface
{
    private static $instance = null;

    /**
     * Returns the unique instance of the plugin
     *
     * @return object
     */
    public static function getInstance()
    {
        if (empty(static::$instance)) {
            $dispatcher = \JEventDispatcher::getInstance();
            $instance   = new self($dispatcher);

            static::$instance = $instance;
        }

        return static::$instance;
    }

    /**
     * Returns the element of the component which this plugin supports.
     *
     * @return string
     */
    public function getComponentElement()
    {
        return 'com_content';
    }

    /**
     * This function is called before a menu item is used. We use it to set the
     * proper uniqueid for the item
     *
     * @param object  Menu item to be "prepared"
     * @param array   The extension params
     *
     * @return void
     * @since  1.2
     */
    public static function prepareMenuItem($node, &$params)
    {
        $db        = OSMap\Factory::getDbo();
        $container = OSMap\Factory::getContainer();

        $linkQuery = parse_url($node->link);

        if (!isset($linkQuery['query'])) {
            return;
        }

        $node->pluginParams = &$params;

        parse_str(html_entity_decode($linkQuery['query']), $linkVars);

        $view = JArrayHelper::getValue($linkVars, 'view', '');
        $id   = JArrayHelper::getValue($linkVars, 'id', 0);

        switch ($view) {
            case 'archive':
                $node->adapterName = 'JoomlaCategory';
                $node->uid         = 'joomla.archive.' . $node->id;
                $node->expandible  = true;

                break;

            case 'featured':
                $node->adapterName = 'JoomlaCategory';
                $node->uid         = 'joomla.featured.' . $node->id;
                $node->expandible  = true;

                break;

            case 'categories':
            case 'category':
                $node->adapterName = 'JoomlaCategory';
                $node->uid         = 'joomla.category.' . $id;
                $node->expandible  = true;

                break;

            case 'article':
                $node->adapterName = 'JoomlaArticle';
                $node->expandible  = false;

                $paramAddPageBreaks = $params->get('add_pagebreaks', 1);
                $paramAddImages     = $params->get('add_images', 1);

                $query = $db->getQuery(true)
                    ->select($db->quoteName('created'))
                    ->select($db->quoteName('modified'))
                    ->select($db->quoteName('metadata'))
                    ->select($db->quoteName('attribs'))
                    ->from($db->quoteName('#__content'))
                    ->where($db->quoteName('id') . '=' . (int) $id);

                if ($paramAddPageBreaks || $paramAddImages) {
                    $query->select($db->quoteName('introtext'));
                    $query->select($db->quoteName('fulltext'));
                }

                $db->setQuery($query);

                if (($item = $db->loadObject()) != null) {
                    // Set the node UID
                    $node->uid = 'joomla.article.' . $id;

                    // Check if we have a modification date
                    if (!OSMap\Helper\General::isEmptyDate($item->modified)) {
                        $node->modified = $item->modified;
                    }

                    // Make sure we have a modification date. If null, use the creation date
                    if (OSMap\Helper\General::isEmptyDate($node->modified)) {
                        if (isset($item->createdOn)) {
                            $node->modified = $item->createdOn;
                        } else {
                            $node->modified = $item->created;
                        }
                    }

                    $item->params = $item->attribs;

                    $text = '';
                    if (isset($item->introtext) && isset($item->fulltext)) {
                        $text = $item->introtext . $item->fulltext;
                    }

                    if ($paramAddImages) {
                        $maxImages = $params->get('max_images', 1000);

                        $node->images = $container->imagesHelper->getImagesFromText($text, $maxImages);
                    }


                    if ($paramAddPageBreaks) {
                        $node->subnodes   = OSMap\Helper\General::getPagebreaks($text, $node->link, $node->uid);
                        $node->expandible = (count($node->subnodes) > 0); // This article has children
                    }
                } else {
                    return false;
                }

                break;
        }

        return true;
    }

    /**
     * Expands a com_content menu item
     *
     * @return void
     * @since  1.0
     */
    public static function getTree($collector, $menuItem, &$params)
    {
        $db = OSMap\Factory::getDBO();

        $result = null;

        $linkQuery = parse_url($menuItem->link);

        if (!isset($linkQuery['query'])) {
            return false;
        }

        parse_str(html_entity_decode($linkQuery['query']), $linkVars);
        $view = JArrayHelper::getValue($linkVars, 'view', '');
        $id   = intval(JArrayHelper::getValue($linkVars, 'id', ''));

        /*
         * Parameters Initialisation
         */
        $paramExpandCategories = $params->get('expand_categories', 1) > 0;
        $paramExpandFeatured   = $params->get('expand_featured', 1);
        $paramIncludeArchived  = $params->get('include_archived', 2);

        $paramAddPageBreaks    = $params->get('add_pagebreaks', 1);

        $paramCatPriority      = $params->get('cat_priority', $menuItem->priority);
        $paramCatChangefreq    = $params->get('cat_changefreq', $menuItem->changefreq);

        if ($paramCatPriority == '-1') {
            $paramCatPriority = $menuItem->priority;
        }
        if ($paramCatChangefreq == '-1') {
            $paramCatChangefreq = $menuItem->changefreq;
        }
        $params->set('cat_priority', $paramCatPriority);
        $params->set('cat_changefreq', $paramCatChangefreq);

        $paramArtPriority   = $params->get('art_priority', $menuItem->priority);
        $paramArtChangefreq = $params->get('art_changefreq', $menuItem->changefreq);

        if ($paramArtPriority == '-1') {
            $paramArtPriority = $menuItem->priority;
        }

        if ($paramArtChangefreq == '-1') {
            $paramArtChangefreq = $menuItem->changefreq;
        }

        $params->set('art_priority', $paramArtPriority);
        $params->set('art_changefreq', $paramArtChangefreq);

        // If enabled, loads the page break language
        if ($paramAddPageBreaks && !defined('OSMAP_PLUGIN_JOOMLA_LOADED')) {
            // Load it just once
            define('OSMAP_PLUGIN_JOOMLA_LOADED', 1);

            OSMap\Factory::getLanguage()->load('plg_content_pagebreak');
        }

        switch ($view) {
            case 'category':
                if (empty($id)) {
                    $id = intval($params->get('id', 0));
                }

                if ($paramExpandCategories && $id) {
                    $result = self::expandCategory($collector, $menuItem, $id, $params, $menuItem->id);
                }

                break;

            case 'featured':
                if ($paramExpandFeatured) {
                    $result = self::includeCategoryContent($collector, $menuItem, 'featured', $params, $menuItem->id);
                }

                break;

            case 'categories':
                if ($paramExpandCategories) {
                    if (empty($id)) {
                        $id = 1;
                    }

                    $result = self::expandCategory($collector, $menuItem, $id, $params, $menuItem->id);
                }

                break;

            case 'archive':
                if ($paramIncludeArchived) {
                    $result = self::includeCategoryContent($collector, $menuItem, 'archived', $params, $menuItem->id);
                }

                break;

            case 'article':
                // if it's an article menu item, we have to check if we have to expand the
                // article's page breaks
                if ($paramAddPageBreaks) {
                    $query = $db->getQuery(true)
                        ->select($db->quoteName('introtext'))
                        ->select($db->quoteName('fulltext'))
                        ->select($db->quoteName('alias'))
                        ->select($db->quoteName('catid'))
                        ->select($db->quoteName('attribs') . ' AS params')
                        ->select($db->quoteName('metadata'))
                        ->select($db->quoteName('modified'))
                        ->select($db->quoteName('created'))
                        ->from($db->quoteName('#__content'))
                        ->where($db->quoteName('id') . '=' . (int) $id);
                    $db->setQuery($query);

                    $item = $db->loadObject();

                    // Make sure we have a modification date. If null, use the creation date
                    if (OSMap\Helper\General::isEmptyDate($item->modified)) {
                        $item->modified = $item->created;
                    }

                    // // Set the node UID
                    $item->uid = 'joomla.article.' . $id;

                    $menuItem->slug = $item->alias ? ($id . ':' . $item->alias) : $id;
                    $menuItem->link = ContentHelperRoute::getArticleRoute($menuItem->slug, $item->catid);

                    $menuItem->subnodes = OSMap\Helper\General::getPagebreaks($item->introtext . $item->fulltext, $menuItem->link, $item->uid);
                    self::printNodes($collector, $menuItem, $params, $menuItem->subnodes, $item);
                }
        }

        return $result;
    }

    /**
     * Get all content items within a content category.
     * Returns an array of all contained content items.
     *
     * @param object  $collector
     * @param object  $parent   the menu item
     * @param int     $catid    the id of the category to be expanded
     * @param array   $params   an assoc array with the params for this plugin on Xmap
     * @param int     $itemid   the itemid to use for this category's children
     */
    public static function expandCategory($collector, $parent, $catid, &$params, $itemid, $prevnode = null, $curlevel = 0)
    {
        $paramExpandCategories = $params->get('expand_categories', 1);

        $db = OSMap\Factory::getDBO();

        $where = array(
            'a.parent_id = ' . $catid,
            'a.published = 1',
            'a.extension=' . $db->quote('com_content')
        );

        if (!$params->get('show_unauth', 0)) {
            $where[] = 'a.access IN (' . OSMap\Helper\General::getAuthorisedViewLevels() . ') ';
        }

        $query = $db->getQuery(true)
            ->select(
                array(
                    'a.id',
                    'a.title',
                    'a.alias',
                    'a.access',
                    'a.path AS route',
                    'a.created_time created',
                    'a.modified_time modified',
                    'a.params',
                    'a.metadata',
                    'a.metakey'
                )
            )
            ->from('#__categories AS a')
            ->where($where);

        $query->order('a.lft');

        $db->setQuery($query);

        $items = $db->loadObjectList();

        $curlevel++;

        $node     = null;
        $maxLevel = $parent->params->get('max_category_level', 100);

        if ($curlevel <= $maxLevel) {
            if (count($items) > 0) {
                $collector->changeLevel(1);

                foreach ($items as $item) {
                    $node               = new stdClass;
                    $node->id           = $item->id;
                    $node->uid          = 'joomla.category.' . $item->id;
                    $node->browserNav   = $parent->browserNav;
                    $node->priority     = $params->get('cat_priority');
                    $node->changefreq   = $params->get('cat_changefreq');
                    $node->name         = $item->title;
                    $node->expandible   = true;
                    $node->secure       = $parent->secure;
                    $node->newsItem     = 0;
                    $node->adapterName  = 'JoomlaCategory';
                    $node->pluginParams = &$params;

                    // Keywords
                    $paramKeywords = $params->get('keywords', 'metakey');
                    $keywords      = null;
                    if ($paramKeywords !== 'none') {
                        $keywords = $item->metakey;
                    }
                    $node->keywords = $keywords;

                    // For the google news we should use te publication date instead
                    // the last modification date
                    $node->modified     = OSMap\Helper\General::isEmptyDate($item->modified)
                        ? $item->created : $item->modified;

                    $node->slug = $item->route ? ($item->id . ':' . $item->route) : $item->id;
                    $node->link = ContentHelperRoute::getCategoryRoute($node->slug);

                    $node->itemid = $itemid;
                    if (strpos($node->link, 'Itemid=')===false) {
                        $node->link .= '&Itemid=' . $itemid;
                    } else {
                        $node->link = preg_replace('/Itemid=([0-9]+)/', 'Itemid=' . $itemid, $node->link);
                    }

                    if ($collector->printNode($node)) {
                        self::expandCategory($collector, $parent, $item->id, $params, $node->itemid, $node, $curlevel);
                    }
                }

                $collector->changeLevel(-1);
            }
        }

        // Include Category's content
        self::includeCategoryContent($collector, $parent, $catid, $params, $itemid, $node);

        return true;
    }

    /**
     * Get all content items within a content category.
     * Returns an array of all contained content items.
     *
     * @since 2.0
     */
    public static function includeCategoryContent($collector, $parent, $catid, &$params, $itemid, $prevnode = null)
    {
        $db        = OSMap\Factory::getDBO();
        $container = OSMap\Factory::getContainer();

        if ($params->get('include_archived', 2)) {
            $where = array('(a.state = 1 or a.state = 2)');
        } else {
            $where = array('a.state = 1');
        }

        if ($catid == 'featured') {
            $where[] = 'a.featured=1';
        } elseif ($catid == 'archived') {
            $where = array('a.state=2');
        } elseif (is_numeric($catid)) {
            $where[] = 'a.catid=' . (int)$catid;
        }

        $maxArtAge = $params->get('max_art_age');
        if (!empty($maxArtAge) || $collector->isNews) {
            $days    = empty($maxArtAge) ? 2 : $maxArtAge;
            $where[] = "(a.created >= '"
                . date('Y-m-d H:i:s', time() - $days * 86400) . "' ) ";
        }

        if (!$params->get('show_unauth', 0)) {
            $where[] = 'a.access IN (' . OSMap\Helper\General::getAuthorisedViewLevels() . ') ';
        }

        $nullDate = $db->quote($db->getNullDate());
        $nowDate  = $db->quote(OSMap\Factory::getDate()->toSql());

        $query = $db->getQuery(true)
            ->select(
                array(
                    'a.id',
                    'a.title',
                    'a.alias',
                    'a.catid',
                    'a.created',
                    'a.modified',
                    'a.attribs AS params',
                    'a.metadata',
                    'a.language',
                    'a.metakey',
                    'c.title AS categMetakey'
                )
            );

        if ($params->get('add_images', 1) || $params->get('add_pagebreaks', 1)) {
            $query->select(
                array(
                    'a.introtext',
                    'a.fulltext'
                )
            );
        }

        $query
            ->from('#__content AS a')
            //@todo: Do we need this join for frontpage?
            ->join('LEFT', '#__content_frontpage AS fp ON (a.id = fp.content_id)')
            ->join('LEFT', '#__categories AS c ON (a.catid = c.id)')
            ->where($where)
            ->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
            ->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');


        // Ordering
        $orderOptions = array(
            'a.created',
            'a.modified',
            'a.publish_up',
            'a.hits',
            'a.title'
        );
        $orderDirOptions = array(
            'ASC',
            'DESC'
        );
        $order    = JArrayHelper::getValue($orderOptions, $params->get('article_order', 0), 0);
        $orderDir = JArrayHelper::getValue($orderDirOptions, $params->get('article_orderdir', 0), 0);

        $orderBy = ' ' . $order . ' ' . $orderDir;
        $query->order($orderBy);

        $maxArt = $params->get('max_art');
        if (!empty($maxArt)) {
            $query->setLimit($maxArt);
        }

        $db->setQuery($query);

        $items = $db->loadObjectList();

        if (count($items) > 0) {
            $collector->changeLevel(1);

            $paramExpandCategories = $params->get('expand_categories', 1);
            $paramExpandFeatured   = $params->get('expand_featured', 1);
            $paramIncludeArchived  = $params->get('include_archived', 2);

            foreach ($items as $item) {
                $node               = new stdClass();
                $node->id           = $item->id;
                $node->uid          = 'joomla.article.' . $item->id;
                $node->browserNav   = $parent->browserNav;
                $node->priority     = $params->get('art_priority');
                $node->changefreq   = $params->get('art_changefreq');
                $node->name         = $item->title;
                $node->modified     = OSMap\Helper\General::isEmptyDate($item->modified) ? $item->created : $item->modified;
                $node->expandible   = false;
                $node->secure       = $parent->secure;
                $node->newsItem     = 1;
                $node->language     = $item->language;
                $node->adapterName  = 'JoomlaArticle';
                $node->pluginParams = &$params;

                // Keywords
                $paramKeywords = $params->get('keywords', 'metakey');
                $keywords      = '';
                if ($paramKeywords !== 'none') {
                    if (in_array($paramKeywords, array('metakey', 'both'))) {
                        $keywords = $item->metakey;
                    }

                    if (in_array($paramKeywords, array('category', 'both'))) {
                        if (!empty($keywords)) {
                            $keywords .= ',';
                        }

                        $keywords .= $item->categMetakey;
                    }
                }
                $node->keywords = trim($keywords);

                $node->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
                //$node->catslug = $item->category_route ? ($catid . ':' . $item->category_route) : $catid;
                $node->catslug = $item->catid;
                $node->link    = ContentHelperRoute::getArticleRoute($node->slug, $node->catslug);

                // Set the visibility for XML or HTML sitempas
                if ($catid=='featured') {
                    // Check if the item is visible in the XML or HTML sitemaps
                    $node->visibleForXML  = in_array($paramExpandFeatured, array(1, 2));
                    $node->visibleForHTML = in_array($paramExpandFeatured, array(1, 3));
                } elseif ($catid=='archived') {
                    // Check if the item is visible in the XML or HTML sitemaps
                    $node->visibleForXML  = in_array($paramIncludeArchived, array(1, 2));
                    $node->visibleForHTML = in_array($paramIncludeArchived, array(1, 3));
                } elseif (is_numeric($catid)) {
                    // Check if the item is visible in the XML or HTML sitemaps
                    $node->visibleForXML  = in_array($paramExpandCategories, array(1, 2));
                    $node->visibleForHTML = in_array($paramExpandCategories, array(1, 3));
                }

                // Add images to the article
                $text = '';
                if (isset($item->introtext) && isset($item->fulltext)) {
                    $text = $item->introtext . $item->fulltext;
                }

                if ($params->get('add_images', 1)) {
                    $maxImages = $params->get('max_images', 1000);

                    $node->images = $container->imagesHelper->getImagesFromText($text, $maxImages);
                }

                if ($params->get('add_pagebreaks', 1)) {
                    $node->subnodes = OSMap\Helper\General::getPagebreaks($text, $node->link, $node->uid);
                    // This article has children
                    $node->expandible = (count($node->subnodes) > 0);
                }

                if ($collector->printNode($node) && $node->expandible) {
                    self::printNodes($collector, $parent, $params, $node->subnodes, $node);
                }
            }

            $collector->changeLevel(-1);
        }

        return true;
    }

    private static function printNodes($collector, $parent, &$params, &$subnodes, $item)
    {
        $collector->changeLevel(1);

        $i = 0;
        foreach ($subnodes as $subnode) {
            $i++;

            $subnode->browserNav = $parent->browserNav;
            $subnode->priority   = $params->get('art_priority');
            $subnode->changefreq = $params->get('art_changefreq');
            $subnode->secure     = $parent->secure;

            // Check if the child item has modified date
            if (isset($item->modified)) {
                $subnode->modified = $item->modified;
            } else {
                $subnode->modified = $item->created;
            }

            $collector->printNode($subnode);

            $subnode = null;
            unset($subnode);
        }

        $collector->changeLevel(-1);
    }
}
