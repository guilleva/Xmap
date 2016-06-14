<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

require_once JPATH_SITE . '/components/com_content/helpers/route.php';
require_once JPATH_SITE . '/components/com_content/helpers/query.php';
require_once JPATH_SITE . '/components/com_osmap/helpers/osmap.php';

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

class PlgOSMapJoomla implements OSMap\PluginInterface
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
            $instance = new self;

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
        $db = JFactory::getDbo();

        $linkQuery = parse_url($node->link);

        if (!isset($linkQuery['query'])) {
            return;
        }

        parse_str(html_entity_decode($linkQuery['query']), $linkVars);

        $view   = JArrayHelper::getValue($linkVars, 'view', '');
        $id     = JArrayHelper::getValue($linkVars, 'id', 0);

        switch ($view) {
            case 'category':
                if ($id) {
                    $query = $db->getQuery(true);

                    if (OSMAP_LICENSE === 'pro') {
                        $query->select($db->quoteName('metadata'))
                            ->select($db->quoteName('params'))
                            ->from($db->quoteName('#__categories'))
                            ->where($db->quoteName('id') . '=' . (int) $id);
                        $db->setQuery($query);

                        if (($row = $db->loadObject()) != null) {

                            $category = new Alledia\OSMap\Pro\Joomla\Item($row);

                            if (!$category->isVisibleForRobots()) {
                                return false;
                            }
                        } else {
                            return false;
                        }
                    }

                    // Set the node UID
                    $node->uid = 'joomla.category.' . $id;
                }

                $node->expandible = true;

                break;

            case 'article':
                $node->expandible = false;

                $paramAddPageBreaks = $params->get('add_pagebreaks', 1);
                $paramAddImages     = $params->get('add_images', 1);
                $paramMaxImages     = $params->get('max_images', 1000);

                $query = $db->getQuery(true)
                    ->select($db->quoteName('created'))
                    ->select($db->quoteName('modified'))
                    ->select($db->quoteName('metadata'))
                    ->select($db->quoteName('attribs'))
                    ->from($db->quoteName('#__content'))
                    ->where($db->quoteName('id') . '=' . (int) $id);

                if ($paramAddPageBreaks || $paramAddImages) {
                    $query->select($db->quoteName('introtext'))
                        ->select($db->quoteName('fulltext'));
                }

                $db->setQuery($query);

                if (($row = $db->loadObject()) != null) {
                    // Set the node UID
                    $node->uid = 'joomla.article.' . $id;

                    // Check if we have a modification date
                    if (!OSMap\Helper::isEmptyDate($row->modified)) {
                        $node->modified = $row->modified;
                    }

                    // Make sure we have a modification date. If null, use the creation date
                    if (OSMap\Helper::isEmptyDate($node->modified)) {
                        if (isset($row->createdOn)) {
                            $node->modified = $row->createdOn;
                        } else {
                            $node->modified = $row->created;
                        }
                    }

                    $row->params = $row->attribs;

                    if (OSMAP_LICENSE === 'pro') {
                        $content = new Alledia\OSMap\Pro\Joomla\Item($row);
                        if (!$content->isVisibleForRobots()) {
                            return false;
                        }
                    }

                    $text = @$item->introtext . @$item->fulltext;
                    if ($paramAddImages) {
                        if (OSMAP_LICENSE === 'pro') {
                            $node->images = Alledia\OSMap\Pro\Joomla\Helper::getImages($text, $paramMaxImages);
                        } else {
                            $node->images = OSMap\Helper::getImages($text, $paramMaxImages);
                        }
                    }

                    if ($paramAddPageBreaks) {
                        $node->subnodes   = OSMap\Helper::getPagebreaks($text, $node->link, $node->uid);
                        $node->expandible = (count($node->subnodes) > 0); // This article has children
                    }
                } else {
                    return false;
                }

                break;

            case 'archive':
                $node->expandible = true;

                break;

            case 'featured':
                $node->expandible = false;

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
    public static function getTree($osmap, $parent, &$params)
    {
        $db     = JFactory::getDBO();
        $result = null;

        $linkQuery = parse_url($parent->link);

        if (!isset($linkQuery['query'])) {
            return false;
        }

        parse_str(html_entity_decode($linkQuery['query']), $linkVars);
        $view = JArrayHelper::getValue($linkVars, 'view', '');
        $id   = intval(JArrayHelper::getValue($linkVars, 'id', ''));

        /*
         * Parameters Initialitation
         */
        $paramExpandCategories = $params->get('expand_categories', 1);
        $paramExpandFeatured   = $params->get('expand_featured', 1);
        $paramIncludeArchived  = $params->get('include_archived', 2);

        $paramAddPageBreaks    = $params->get('add_pagebreaks', 1);

        $paramCatPriority      = $params->get('cat_priority', $parent->priority);
        $paramCatChangefreq    = $params->get('cat_changefreq', $parent->changefreq);

        if ($paramCatPriority == '-1') {
            $paramCatPriority = $parent->priority;
        }
        if ($paramCatChangefreq == '-1') {
            $paramCatChangefreq = $parent->changefreq;
        }
        $params->set('cat_priority', $paramCatPriority);
        $params->set('cat_changefreq', $paramCatChangefreq);

        $paramArtPriority = $params->get('art_priority', $parent->priority);
        $paramArtChangefreq = $params->get('art_changefreq', $parent->changefreq);

        if ($paramArtPriority == '-1') {
            $paramArtPriority = $parent->priority;
        }

        if ($paramArtChangefreq == '-1') {
            $paramArtChangefreq = $parent->changefreq;
        }

        $params->set('art_priority', $paramArtPriority);
        $params->set('art_changefreq', $paramArtChangefreq);

        // If enabled, loads the page break language
        if ($paramAddPageBreaks && !defined('OSMAP_PLUGIN_JOOMLA_LOADED')) {
            // Load it just once
            define('OSMAP_PLUGIN_JOOMLA_LOADED', 1);

            JFactory::getLanguage()->load('plg_content_pagebreak');
        }

        switch ($view) {
            case 'category':
                if (!$id) {
                    $id = intval($params->get('id', 0));
                }

                if ($paramExpandCategories && $id) {
                    $result = self::expandCategory($osmap, $parent, $id, $params, $parent->id);
                }

                break;

            case 'featured':
                if ($paramExpandFeatured) {
                    $result = self::includeCategoryContent($osmap, $parent, 'featured', $params, $parent->id);
                }

                break;

            case 'categories':
                if ($paramExpandCategories) {
                    $result = self::expandCategory($osmap, $parent, ($id ? $id : 1), $params, $parent->id);
                }

                break;

            case 'archive':
                if ($paramIncludeArchived) {
                    $result = self::includeCategoryContent($osmap, $parent, 'archived', $params, $parent->id);
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

                    $row = $db->loadObject();

                    // Make sure we have a modification date. If null, use the creation date
                    if (OSMap\Helper::isEmptyDate($row->modified)) {
                        $row->modified = $row->created;
                    }

                    // // Set the node UID
                    $row->uid = 'joomla.article.' . $id;

                    $parent->slug = $row->alias ? ($id . ':' . $row->alias) : $id;
                    $parent->link = ContentHelperRoute::getArticleRoute($parent->slug, $row->catid);

                    $subnodes = OSMap\Helper::getPagebreaks($row->introtext.$row->fulltext, $parent->link, $row->uid);
                    self::printNodes($osmap, $parent, $params, $subnodes);
                }
        }

        return $result;
    }

    /**
     * Get all content items within a content category.
     * Returns an array of all contained content items.
     *
     * @param object  $osmap
     * @param object  $parent   the menu item
     * @param int     $catid    the id of the category to be expanded
     * @param array   $params   an assoc array with the params for this plugin on Xmap
     * @param int     $itemid   the itemid to use for this category's children
     */
    public static function expandCategory($osmap, $parent, $catid, &$params, $itemid, $prevnode = null, $curlevel = 0)
    {
        $db = JFactory::getDBO();

        $where = array(
            'a.parent_id = ' . $catid,
            'a.published = 1',
            'a.extension=' . $db->quote('com_content')
        );

        if (!$params->get('show_unauth', 0)) {
            $where[] = 'a.access IN (' . OSMap\Helper::getAuthorisedViewLevels() . ') ';
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
                    'params',
                    'metadata'
                )
            )
            ->from('#__categories AS a')
            ->where($where);

        if ($osmap->view != 'xml') {
            $query->order('a.lft');
        }

        $db->setQuery($query);

        $items = $db->loadObjectList();
        $curlevel++;

        $node     = null;
        $maxLevel = $parent->params->get('maxLevel', -1);

        if ($curlevel <= $maxLevel || $maxLevel == -1) {
            if (count($items) > 0) {
                $osmap->changeLevel(1);
                foreach ($items as $item) {
                    if (OSMAP_LICENSE === 'pro') {
                        $content = new Alledia\OSMap\Pro\Joomla\Item($item);
                        if (!$content->isVisibleForRobots()) {
                            return false;
                        }
                    }

                    $node = new stdClass();
                    $node->id          = $parent->id;
                    $node->uid         = 'joomla.category.' . $item->id;
                    $node->browserNav  = $parent->browserNav;
                    $node->priority    = $params->get('cat_priority');
                    $node->changefreq  = $params->get('cat_changefreq');
                    $node->name        = $item->title;
                    $node->expandible  = true;
                    $node->secure      = $parent->secure;
                    // TODO: Should we include category name or metakey here?
                    // $node->keywords = $item->metakey;
                    $node->newsItem    = 0;

                    // For the google news we should use te publication date instead
                    // the last modification date. See
                    if (OSMap\Helper::isEmptyDate($item->modified)) {
                        $item->modified = $item->created;
                    }

                    $node->modified = $item->modified;

                    $node->slug = $item->route ? ($item->id . ':' . $item->route) : $item->id;
                    $node->link = ContentHelperRoute::getCategoryRoute($node->slug);

                    if (strpos($node->link, 'Itemid=')===false) {
                        $node->itemid = $itemid;
                        $node->link   .= '&Itemid='.$itemid;
                    } else {
                        $node->itemid = $itemid;
                        $node->link   = preg_replace('/Itemid=([0-9]+)/', 'Itemid='.$itemid, $node->link);
                    }

                    if ($osmap->printNode($node)) {
                        if ($curlevel <= $maxLevel || $maxLevel == -1) {
                            self::expandCategory($osmap, $parent, $item->id, $params, $node->itemid, $node, $curlevel);
                        }
                    }
                }

                $osmap->changeLevel(-1);
            }
        }

        // Include Category's content
        self::includeCategoryContent($osmap, $parent, $catid, $params, $itemid, $node);

        return true;
    }

    /**
     * Get all content items within a content category.
     * Returns an array of all contained content items.
     *
     * @since 2.0
     */
    public static function includeCategoryContent($osmap, $parent, $catid, &$params, $itemid, $prevnode = null)
    {
        $db = JFactory::getDBO();

        // We do not do ordering for XML sitemap.
        if ($osmap->view != 'xml') {
            $orderBy = self::buildContentOrderBy($parent->params, $parent->id, $itemid);
            //$orderBy = !empty($menuparams['orderby']) ? $menuparams['orderby'] : (!empty($menuparams['orderby_sec']) ? $menuparams['orderby_sec'] : 'rdate' );
            //$orderBy = self::orderby_sec($orderBy);
        }

        if ($params->get('include_archived', 2)) {
            $where = array('(a.state = 1 or a.state = 2)');
        } else {
            $where = array('a.state = 1');
        }

        if ($catid=='featured') {
            $where[] = 'a.featured=1';
        } elseif ($catid=='archived') {
            $where = array('a.state=2');
        } elseif (is_numeric($catid)) {
            $where[] = 'a.catid='.(int) $catid;
        }

        $maxArtAge = $params->get('max_art_age');
        if (!empty($maxArtAge) || $osmap->isNews) {
            $days = empty($maxArtAge) ? 2 : $maxArtAge;
            $where[] = "( a.created >= '"
                . date('Y-m-d H:i:s', time() - $days * 86400) . "' ) ";
        }

        if (!$params->get('show_unauth', 0)) {
            $where[] = 'a.access IN (' . OSMap\Helper::getAuthorisedViewLevels() . ') ';
        }

        $nullDate = $db->quote($db->getNullDate());
        $nowDate  = $db->quote(JFactory::getDate()->toSql());

        $query = $db->getQuery(true)
            ->select(
                array(
                    'a.id',
                    'a.title',
                    'a.alias',
                    'a.catid',
                    'a.created created',
                    'a.modified modified',
                    'attribs as params',
                    'metadata',
                    'a.language'
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
            ->join('LEFT', '#__content_frontpage AS fp ON (a.id = fp.content_id)')
            ->where($where)
            ->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
            ->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

        if ($osmap->view != 'xml') {
            $query->order($orderBy);
        }

        if (!empty($params->get('max_art'))) {
            $query->setLimit($params->get('max_art'));
        }

        $db->setQuery($query);

        $items = $db->loadObjectList();

        if (count($items) > 0) {
            $osmap->changeLevel(1);

            foreach ($items as $item) {

                if (OSMAP_LICENSE === 'pro') {
                    $content = new Alledia\OSMap\Pro\Joomla\Item($item);

                    if (!$content->isVisibleForRobots()) {
                        continue;
                    }
                }

                $node = new stdClass();
                $node->id          = $parent->id;
                $node->uid         = 'joomla.article.' . $item->id;
                $node->browserNav  = $parent->browserNav;
                $node->priority    = $params->get('art_priority');
                $node->changefreq  = $params->get('art_changefreq');
                $node->name        = $item->title;
                $node->modified    = OSMap\Helper::isEmptyDate($item->modified) ? $item->created : $item->modified;
                $node->expandible  = false;
                $node->secure      = $parent->secure;
                // TODO: Should we include category name or metakey here?
                // $node->keywords = $item->metakey;
                $node->newsItem    = 1;
                $node->language    = $item->language;

                $node->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
                //$node->catslug = $item->category_route ? ($catid . ':' . $item->category_route) : $catid;
                $node->catslug = $item->catid;
                $node->link    = ContentHelperRoute::getArticleRoute($node->slug, $node->catslug);

                if (strpos($node->link, 'Itemid=') === false) {
                    $node->itemid = $itemid;
                    $node->link   .= '&Itemid='.$parent->id;
                } else {
                    $node->itemid = $itemid;
                    $node->link   = preg_replace('/Itemid=([0-9]+)/', 'Itemid='.$parent->id, $node->link);
                }

                // Add images to the article
                $text = @$item->introtext . @$item->fulltext;

                if ($params->get('add_images', 1)) {
                    $maxImages = $params->get('max_images', 1000);

                    if (OSMAP_LICENSE === 'pro') {
                        $node->images = Alledia\OSMap\Pro\Joomla\Helper::getImages($text, $maxImages);
                    } else {
                        $node->images = OSMap\Helper::getImages($text, $maxImages);
                    }
                }

                if ($params->get('add_pagebreaks', 1)) {
                    $node->subnodes = OSMap\Helper::getPagebreaks($text, $node->link, $node->uid);
                    $node->expandible = (count($node->subnodes) > 0); // This article has children
                }

                if ($osmap->printNode($node) && $node->expandible) {
                    self::printNodes($osmap, $parent, $params, $node->subnodes);
                }
            }

            $osmap->changeLevel(-1);
        }

        return true;
    }

    private static function printNodes($osmap, $parent, &$params, &$subnodes)
    {
        $osmap->changeLevel(1);

        $i = 0;
        foreach ($subnodes as $subnode) {
            $i++;

            // $subnode->id         = $parent->id;
            $subnode->browserNav = $parent->browserNav;
            $subnode->priority   = $params->get('art_priority');
            $subnode->changefreq = $params->get('art_changefreq');
            $subnode->secure     = $parent->secure;

            $osmap->printNode($subnode);
        }

        $osmap->changeLevel(-1);
    }

    /**
     * Generates the order by part of the query according to the
     * menu/component/user settings. It checks if the current user
     * has already changed the article's ordering column in the frontend
     *
     * @param JRegistry $params
     * @param int $parentId
     * @param int $itemid
     * @return string
     */
    public static function buildContentOrderBy(&$params, $parentId, $itemid)
    {
        $app = JFactory::getApplication('site');

        // Case when the child gets a different menu itemid than it's parent
        if ($parentId != $itemid) {
            $menu       = $app->getMenu();
            $item       = $menu->getItem($itemid);
            $menuParams = clone($params);
            $itemParams = new JRegistry($item->params);

            $menuParams->merge($itemParams);
        } else {
            $menuParams =& $params;
        }

        $filterOrder = $app->getUserStateFromRequest(
            'com_content.category.list.' . $itemid . '.filter_order',
            'filter_order',
            '',
            'string'
        );
        $filterOrderDir = $app->getUserStateFromRequest(
            'com_content.category.list.' . $itemid . '.filter_order_Dir',
            'filter_order_Dir',
            '',
            'cmd'
        );
        $orderBy = ' ';

        if ($filterOrder && $filterOrderDir) {
            $orderBy .= $filterOrder . ' ' . $filterOrderDir . ', ';
        }

        $articleOrderby     = $menuParams->get('orderby_sec', 'rdate');
        $articleOrderDate   = $menuParams->get('order_date');
        //$categoryOrderby  = $menuParams->def('orderby_pri', '');
        $secondary      = ContentHelperQuery::orderbySecondary($articleOrderby, $articleOrderDate) . ', ';
        //$primary      = ContentHelperQuery::orderbyPrimary($categoryOrderby);

        //$orderBy .= $primary . ' ' . $secondary . ' a.created ';
        $orderBy .=  $secondary . ' a.created ';

        return str_replace('m.', 'a.', $orderBy);
    }
}
