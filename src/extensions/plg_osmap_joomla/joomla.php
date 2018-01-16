<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved.
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
use Alledia\OSMap\Sitemap\Collector;
use Alledia\OSMap\Sitemap\Item;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class PlgOSMapJoomla extends OSMap\Plugin\Base implements OSMap\Plugin\ContentInterface
{
    /**
     * @var PlgOSMapJoomla
     */
    private static $instance = null;

    /**
     * Returns the unique instance of the plugin
     *
     * @return PlgOSMapJoomla
     */
    public static function getInstance()
    {
        if (empty(static::$instance)) {
            $dispatcher       = \JEventDispatcher::getInstance();
            static::$instance = new self($dispatcher);
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
     * @param Item     $node   Menu item to be "prepared"
     * @param Registry $params The extension params
     *
     * @return bool
     * @throws Exception
     */
    public static function prepareMenuItem($node, $params)
    {
        static::checkMemory();

        $db        = OSMap\Factory::getDbo();
        $container = OSMap\Factory::getContainer();

        $linkQuery = parse_url($node->link);

        if (!isset($linkQuery['query'])) {
            return false;
        }

        $node->pluginParams = $params;

        parse_str(html_entity_decode($linkQuery['query']), $linkVars);

        $view = ArrayHelper::getValue($linkVars, 'view', '');
        $id   = ArrayHelper::getValue($linkVars, 'id', 0);

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
                    ->select(
                        array(
                            $db->quoteName('created'),
                            $db->quoteName('modified'),
                            $db->quoteName('publish_up'),
                            $db->quoteName('metadata'),
                            $db->quoteName('attribs')
                        )
                    )
                    ->from($db->quoteName('#__content'))
                    ->where($db->quoteName('id') . '=' . (int)$id);

                if ($paramAddPageBreaks || $paramAddImages) {
                    $query->select(
                        array(
                            $db->quoteName('introtext'),
                            $db->quoteName('fulltext'),
                            $db->quoteName('images')
                        )
                    );
                }

                $db->setQuery($query);

                if (($item = $db->loadObject()) != null) {
                    // Set the node UID
                    $node->uid = 'joomla.article.' . $id;

                    // Set dates
                    $node->modified  = $item->modified;
                    $node->created   = $item->created;
                    $node->publishUp = $item->publish_up;

                    $item->params = $item->attribs;

                    $text = '';
                    if (isset($item->introtext) && isset($item->fulltext)) {
                        $text = $item->introtext . $item->fulltext;
                    }

                    if ($paramAddImages) {
                        $maxImages = $params->get('max_images', 1000);

                        $node->images = array();

                        // Images from text
                        $node->images = array_merge(
                            $node->images,
                            (array)$container->imagesHelper->getImagesFromText($text, $maxImages)
                        );

                        // Images from params
                        if (!empty($item->images)) {
                            $node->images = array_merge(
                                $node->images,
                                (array)$container->imagesHelper->getImagesFromParams($item)
                            );
                        }
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
     * @param Collector $collector
     * @param Item      $menuItem
     * @param Registry  $params
     *
     * @return void
     * @throws Exception
     */
    public static function getTree($collector, $menuItem, $params)
    {
        $db = OSMap\Factory::getDbo();

        $result = null;

        $linkQuery = parse_url($menuItem->link);

        if (!isset($linkQuery['query'])) {
            return;
        }

        parse_str(html_entity_decode($linkQuery['query']), $linkVars);
        $view = ArrayHelper::getValue($linkVars, 'view', '');
        $id   = intval(ArrayHelper::getValue($linkVars, 'id', ''));

        /*
         * Parameters Initialisation
         */
        $paramExpandCategories = $params->get('expand_categories', 1) > 0;
        $paramExpandFeatured   = $params->get('expand_featured', 1);
        $paramIncludeArchived  = $params->get('include_archived', 2);

        $paramAddPageBreaks = $params->get('add_pagebreaks', 1);

        $paramCatPriority   = $params->get('cat_priority', $menuItem->priority);
        $paramCatChangefreq = $params->get('cat_changefreq', $menuItem->changefreq);

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
                    self::expandCategory($collector, $menuItem, $id, $params, $menuItem->id);
                }

                break;

            case 'featured':
                if ($paramExpandFeatured) {
                    self::includeCategoryContent($collector, $menuItem, 'featured', $params, $menuItem->id);
                }

                break;

            case 'categories':
                if ($paramExpandCategories) {
                    if (empty($id)) {
                        $id = 1;
                    }

                    self::expandCategory($collector, $menuItem, $id, $params, $menuItem->id);
                }

                break;

            case 'archive':
                if ($paramIncludeArchived) {
                    self::includeCategoryContent($collector, $menuItem, 'archived', $params, $menuItem->id);
                }

                break;

            case 'article':
                // if it's an article menu item, we have to check if we have to expand the
                // article's page breaks
                if ($paramAddPageBreaks) {
                    $query = $db->getQuery(true)
                        ->select(
                            array(
                                $db->quoteName('introtext'),
                                $db->quoteName('fulltext'),
                                $db->quoteName('alias'),
                                $db->quoteName('catid'),
                                $db->quoteName('attribs') . ' AS params',
                                $db->quoteName('metadata'),
                                $db->quoteName('created'),
                                $db->quoteName('modified'),
                                $db->quoteName('publish_up')
                            )
                        )
                        ->from($db->quoteName('#__content'))
                        ->where($db->quoteName('id') . '=' . (int)$id);
                    $db->setQuery($query);

                    $item = $db->loadObject();

                    $item->uid = 'joomla.article.' . $id;

                    $menuItem->slug = $item->alias ? ($id . ':' . $item->alias) : $id;
                    $menuItem->link = ContentHelperRoute::getArticleRoute($menuItem->slug, $item->catid);

                    $menuItem->subnodes = OSMap\Helper\General::getPagebreaks(
                        $item->introtext . $item->fulltext,
                        $menuItem->link,
                        $item->uid
                    );
                    self::printSubNodes($collector, $menuItem, $params, $menuItem->subnodes, $item);
                }
        }
    }

    /**
     * Get all content items within a content category.
     * Returns an array of all contained content items.
     *
     * @param Collector $collector
     * @param Item      $parent the menu item
     * @param int       $catid  the id of the category to be expanded
     * @param Registry  $params parameters for this plugin on Xmap
     * @param int       $itemid the itemid to use for this category's children
     * @param int       $curlevel
     *
     * @return void
     * @throws Exception
     */
    protected static function expandCategory(
        $collector,
        $parent,
        $catid,
        $params,
        $itemid,
        $curlevel = 0
    ) {
        static::checkMemory();

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
                    'a.created_time AS created',
                    'a.modified_time AS modified',
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
                    $node = (object)array(
                        'id'                       => $item->id,
                        'uid'                      => 'joomla.category.' . $item->id,
                        'browserNav'               => $parent->browserNav,
                        'priority'                 => $params->get('cat_priority'),
                        'changefreq'               => $params->get('cat_changefreq'),
                        'name'                     => $item->title,
                        'expandible'               => true,
                        'secure'                   => $parent->secure,
                        'newsItem'                 => 0,
                        'adapterName'              => 'JoomlaCategory',
                        'pluginParams'             => &$params,
                        'parentIsVisibleForRobots' => $parent->visibleForRobots,
                        'created'                  => $item->created,
                        'modified'                 => $item->modified,
                        'publishUp'                => $item->created
                    );

                    // Keywords
                    $paramKeywords = $params->get('keywords', 'metakey');
                    $keywords      = null;
                    if ($paramKeywords !== 'none') {
                        $keywords = $item->metakey;
                    }
                    $node->keywords = $keywords;

                    $node->slug   = $item->route ? ($item->id . ':' . $item->route) : $item->id;
                    $node->link   = ContentHelperRoute::getCategoryRoute($node->slug);
                    $node->itemid = $itemid;

                    if ($collector->printNode($node)) {
                        self::expandCategory($collector, $parent, $item->id, $params, $node->itemid, $curlevel);
                    }
                }

                $collector->changeLevel(-1);
            }
        }

        // Include Category's content
        self::includeCategoryContent($collector, $parent, $catid, $params, $itemid, $node);
    }

    /**
     * Get all content items within a content category.
     * Returns an array of all contained content items.
     *
     * @since 2.0
     *
     * @param Collector  $collector
     * @param Item       $parent
     * @param int|string $catid
     * @param Registry   $params
     * @param int        $itemid
     * @param object     $prevnode
     *
     * @return void
     * @throws Exception
     */
    public static function includeCategoryContent($collector, $parent, $catid, $params, $itemid, $prevnode = null)
    {
        static::checkMemory();

        $db        = OSMap\Factory::getDbo();
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
                    'a.publish_up',
                    'a.attribs AS params',
                    'a.metadata',
                    'a.language',
                    'a.metakey',
                    'a.images',
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
        $orderOptions    = array(
            'a.created',
            'a.modified',
            'a.publish_up',
            'a.hits',
            'a.title',
            'a.ordering'
        );
        $orderDirOptions = array(
            'ASC',
            'DESC'
        );

        $order    = ArrayHelper::getValue($orderOptions, $params->get('article_order', 0), 0);
        $orderDir = ArrayHelper::getValue($orderDirOptions, $params->get('article_orderdir', 0), 0);

        $orderBy = ' ' . $order . ' ' . $orderDir;
        $query->order($orderBy);

        $maxArt = (int)$params->get('max_art');
        $db->setQuery($query, 0, $maxArt);

        $items = $db->loadObjectList();

        if (count($items) > 0) {
            $collector->changeLevel(1);

            $paramExpandCategories = $params->get('expand_categories', 1);
            $paramExpandFeatured   = $params->get('expand_featured', 1);
            $paramIncludeArchived  = $params->get('include_archived', 2);

            foreach ($items as $item) {
                $node = (object)array(
                    'id'                       => $item->id,
                    'uid'                      => 'joomla.article.' . $item->id,
                    'browserNav'               => $parent->browserNav,
                    'priority'                 => $params->get('art_priority'),
                    'changefreq'               => $params->get('art_changefreq'),
                    'name'                     => $item->title,
                    'created'                  => $item->created,
                    'modified'                 => $item->modified,
                    'publishUp'                => $item->publish_up,
                    'expandible'               => false,
                    'secure'                   => $parent->secure,
                    'newsItem'                 => 1,
                    'language'                 => $item->language,
                    'adapterName'              => 'JoomlaArticle',
                    'pluginParams'             => &$params,
                    'parentIsVisibleForRobots' => $parent->visibleForRobots
                );

                $keywords = array();

                $paramKeywords = $params->get('keywords', 'metakey');
                if ($paramKeywords !== 'none') {
                    if (in_array($paramKeywords, array('metakey', 'both'))) {
                        $keywords[] = $item->metakey;
                    }

                    if (in_array($paramKeywords, array('category', 'both'))) {
                        $keywords[] = $item->categMetakey;
                    }
                }
                $node->keywords = join(',', $keywords);

                $node->slug    = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
                $node->catslug = $item->catid;
                $node->link    = ContentHelperRoute::getArticleRoute($node->slug, $node->catslug);

                // Set the visibility for XML or HTML sitempas
                if ($catid == 'featured') {
                    // Check if the item is visible in the XML or HTML sitemaps
                    $node->visibleForXML  = in_array($paramExpandFeatured, array(1, 2));
                    $node->visibleForHTML = in_array($paramExpandFeatured, array(1, 3));
                } elseif ($catid == 'archived') {
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

                    $node->images = array();

                    // Images from text
                    $node->images = array_merge(
                        $node->images,
                        (array)$container->imagesHelper->getImagesFromText($text, $maxImages)
                    );

                    // Images from params
                    if (!empty($item->images)) {
                        $node->images = array_merge(
                            $node->images,
                            (array)$container->imagesHelper->getImagesFromParams($item)
                        );
                    }
                }

                if ($params->get('add_pagebreaks', 1)) {
                    $node->subnodes = OSMap\Helper\General::getPagebreaks($text, $node->link, $node->uid);
                    // This article has children
                    $node->expandible = (count($node->subnodes) > 0);
                }

                if ($collector->printNode($node) && $node->expandible) {
                    self::printSubNodes($collector, $parent, $params, $node->subnodes, $node);
                }
            }

            $collector->changeLevel(-1);
        }
    }

    /**
     * @param Collector $collector
     * @param Item      $parent
     * @param Registry  $params
     * @param array     $subnodes
     * @param object    $item
     *
     * @return void
     * @throws Exception
     */
    protected static function printSubNodes($collector, $parent, $params, $subnodes, $item)
    {
        static::checkMemory();

        $collector->changeLevel(1);

        $i = 0;
        foreach ($subnodes as $subnode) {
            $i++;

            $subnode->browserNav = $parent->browserNav;
            $subnode->priority   = $params->get('art_priority');
            $subnode->changefreq = $params->get('art_changefreq');
            $subnode->secure     = $parent->secure;
            $subnode->created    = $item->created;
            $subnode->modified   = $item->modified;
            $subnode->publishUp  = isset($item->publish_up) ? $item->publish_up : $item->created;

            $collector->printNode($subnode);

            $subnode = null;
            unset($subnode);
        }

        $collector->changeLevel(-1);
    }
}
