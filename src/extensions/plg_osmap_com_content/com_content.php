<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2015 Open Source Training, LLC. All rights reserved..
 * @author    Guillermo Vargas <guille@vargas.co.cr>
 * @author    Alledia <support@alledia.com>
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file is part of OSMap.
 *
 * OSMap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * OSMap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSMap. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/components/com_osmap/helpers/osmap.php';
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
class osmap_com_content
{
    private static $instance = null;

    public static function getInstance()
    {
        if (empty(static::$instance)) {
            $instance = new self;

            static::$instance = $instance;
        }

        return static::$instance;
    }

    /**
     * This function is called before a menu item is printed. We use it to set the
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
        $link_query = parse_url($node->link);

        if (!isset($link_query['query'])) {
            return;
        }

        parse_str(html_entity_decode($link_query['query']), $link_vars);

        $view   = JArrayHelper::getValue($link_vars, 'view', '');
        $layout = JArrayHelper::getValue($link_vars, 'layout', '');
        $id     = JArrayHelper::getValue($link_vars, 'id', 0);

        //----- Set add_images param
        $params['add_images'] = JArrayHelper::getValue($params, 'add_images', 0);

        //----- Set add pagebreaks param
        $add_pagebreaks = JArrayHelper::getValue($params, 'add_pagebreaks', 1);
        $params['add_pagebreaks'] = JArrayHelper::getValue($params, 'add_pagebreaks', 1);

        switch ($view) {
            case 'category':
                if ($id) {
                    $node->uid = 'com_contentc' . $id;

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
                } else {
                    $node->uid = 'com_content' . $layout;
                }

                $node->expandible = true;

                break;

            case 'article':
                $node->uid = 'com_contenta' . $id;
                $node->expandible = false;

                $query = $db->getQuery(true);

                $query->select($db->quoteName('created'))
                    ->select($db->quoteName('modified'))
                    ->select($db->quoteName('metadata'))
                    ->select($db->quoteName('attribs'))
                    ->from($db->quoteName('#__content'))
                    ->where($db->quoteName('id') . '=' . (int) $id);

                if ($params['add_pagebreaks'] || $params['add_images']) {
                    $query->select($db->quoteName('introtext'))
                        ->select($db->quoteName('fulltext'));
                }

                $db->setQuery($query);

                if (($row = $db->loadObject()) != null) {
                    $node->modified = $row->modified;

                    $row->params = $row->attribs;

                    if (OSMAP_LICENSE === 'pro') {
                        $content = new Alledia\OSMap\Pro\Joomla\Item($row);
                        if (!$content->isVisibleForRobots()) {
                            return false;
                        }
                    }

                    $text = @$item->introtext . @$item->fulltext;
                    if ($params['add_images']) {
                        if (OSMAP_LICENSE === 'pro') {
                            $node->images = Alledia\OSMap\Pro\Joomla\Helper::getImages($text, JArrayHelper::getValue($params, 'max_images', 1000));
                        } else {
                            $node->images = OSMapHelper::getImages($text, JArrayHelper::getValue($params, 'max_images', 1000));
                        }
                    }

                    if ($params['add_pagebreaks']) {
                        $node->subnodes   = OSMapHelper::getPagebreaks($text,$node->link);
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
                $node->uid        = 'com_contentfeatured';
                $node->expandible = false;
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
        $app    = JFactory::getApplication();
        $user   = JFactory::getUser();
        $result = null;

        $link_query = parse_url($parent->link);

        if (!isset($link_query['query'])) {
            return false;
        }

        parse_str(html_entity_decode($link_query['query']), $link_vars);
        $view = JArrayHelper::getValue($link_vars, 'view', '');
        $id   = intval(JArrayHelper::getValue($link_vars, 'id', ''));

        /*         * *
         * Parameters Initialitation
         * */
        //----- Set expand_categories param
        $expand_categories = JArrayHelper::getValue($params, 'expand_categories', 1);
        $expand_categories = ( $expand_categories == 1
            || ( $expand_categories == 2 && $osmap->view == 'xml')
            || ( $expand_categories == 3 && $osmap->view == 'html')
            || $osmap->view == 'navigator');
        $params['expand_categories'] = $expand_categories;

        //----- Set expand_featured param
        $expand_featured = JArrayHelper::getValue($params, 'expand_featured', 1);
        $expand_featured = ( $expand_featured == 1
            || ( $expand_featured == 2 && $osmap->view == 'xml')
            || ( $expand_featured == 3 && $osmap->view == 'html')
            || $osmap->view == 'navigator');
        $params['expand_featured'] = $expand_featured;

        //----- Set expand_featured param
        $include_archived = JArrayHelper::getValue($params, 'include_archived', 2);
        $include_archived = ( $include_archived == 1
            || ( $include_archived == 2 && $osmap->view == 'xml')
            || ( $include_archived == 3 && $osmap->view == 'html')
            || $osmap->view == 'navigator');
        $params['include_archived'] = $include_archived;

        //----- Set show_unauth param
        $show_unauth = JArrayHelper::getValue($params, 'show_unauth', 1);
        $show_unauth = ( $show_unauth == 1
            || ( $show_unauth == 2 && $osmap->view == 'xml')
            || ( $show_unauth == 3 && $osmap->view == 'html'));
        $params['show_unauth'] = $show_unauth;

        //----- Set add_images param
        $add_images = JArrayHelper::getValue($params, 'add_images', 0) && $osmap->isImages;
        $add_images = ( $add_images && $osmap->view == 'xml');
        $params['add_images'] = $add_images;
        $params['max_images'] = JArrayHelper::getValue($params, 'max_images', 1000);

        //----- Set add pagebreaks param
        $add_pagebreaks = JArrayHelper::getValue($params, 'add_pagebreaks', 1);
        $add_pagebreaks = ( $add_pagebreaks == 1
            || ( $add_pagebreaks == 2 && $osmap->view == 'xml')
            || ( $add_pagebreaks == 3 && $osmap->view == 'html')
            || $osmap->view == 'navigator');
        $params['add_pagebreaks'] = $add_pagebreaks;

        if ($params['add_pagebreaks'] && !defined('_OSMAP_COM_CONTENT_LOADED')) {
            define('_OSMAP_COM_CONTENT_LOADED',1);  // Load it just once
            $lang = JFactory::getLanguage();
            $lang->load('plg_content_pagebreak');
        }

        //----- Set cat_priority and cat_changefreq params
        $priority = JArrayHelper::getValue($params, 'cat_priority', $parent->priority);
        $changefreq = JArrayHelper::getValue($params, 'cat_changefreq', $parent->changefreq);
        if ($priority == '-1')
            $priority = $parent->priority;
        if ($changefreq == '-1')
            $changefreq = $parent->changefreq;

        $params['cat_priority'] = $priority;
        $params['cat_changefreq'] = $changefreq;

        //----- Set art_priority and art_changefreq params
        $priority = JArrayHelper::getValue($params, 'art_priority', $parent->priority);
        $changefreq = JArrayHelper::getValue($params, 'art_changefreq', $parent->changefreq);
        if ($priority == '-1')
            $priority = $parent->priority;
        if ($changefreq == '-1')
            $changefreq = $parent->changefreq;

        $params['art_priority'] = $priority;
        $params['art_changefreq'] = $changefreq;

        $params['max_art'] = intval(JArrayHelper::getValue($params, 'max_art', 0));
        $params['max_art_age'] = intval(JArrayHelper::getValue($params, 'max_art_age', 0));

        $params['nullDate'] = $db->Quote($db->getNullDate());

        $params['nowDate'] = $db->Quote(JFactory::getDate()->toSql());
        $params['groups'] = implode(',', $user->getAuthorisedViewLevels());

        // Define the language filter condition for the query
        $params['language_filter'] = $app->getLanguageFilter();

        switch ($view) {
            case 'category':
                if (!$id) {
                    $id = intval(JArrayHelper::getValue($params, 'id', 0));
                }
                if ($params['expand_categories'] && $id) {
                    $result = self::expandCategory($osmap, $parent, $id, $params, $parent->id);
                }
                break;
            case 'featured':
                if ($params['expand_featured']) {
                    $result = self::includeCategoryContent($osmap, $parent, 'featured', $params,$parent->id);
                }
                break;
            case 'categories':
                if ($params['expand_categories']) {
                    $result = self::expandCategory($osmap, $parent, ($id ? $id : 1), $params, $parent->id);
                }
                break;
            case 'archive':
                if ($params['expand_featured']) {
                    $result = self::includeCategoryContent($osmap, $parent, 'archived', $params,$parent->id);
                }
                break;
            case 'article':
                // if it's an article menu item, we have to check if we have to expand the
                // article's page breaks
                if ($params['add_pagebreaks']){
                    $query = $db->getQuery(true);

                    $query->select($db->quoteName('introtext'))
                          ->select($db->quoteName('fulltext'))
                          ->select($db->quoteName('alias'))
                          ->select($db->quoteName('catid'))
                          ->select($db->quoteName('attribs') . ' AS params')
                          ->select($db->quoteName('metadata'))
                          ->from($db->quoteName('#__content'))
                          ->where($db->quoteName('id') . '=' . (int) $id);
                    $db->setQuery($query);

                    $row = $db->loadObject();

                    if (OSMAP_LICENSE === 'pro') {
                        $content = new Alledia\OSMap\Pro\Joomla\Item($row);
                        if (!$content->isVisibleForRobots()) {
                            return false;
                        }
                    }

                    $parent->slug = $row->alias ? ($id . ':' . $row->alias) : $id;
                    $parent->link = ContentHelperRoute::getArticleRoute($parent->slug, $row->catid);

                    $subnodes = OSMapHelper::getPagebreaks($row->introtext.$row->fulltext,$parent->link);
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
    public static function expandCategory($osmap, $parent, $catid, &$params, $itemid)
    {
        $db = JFactory::getDBO();

        $where = array('a.parent_id = ' . $catid . ' AND a.published = 1 AND a.extension=\'com_content\'');

        if ($params['language_filter'] ) {
            $where[] = 'a.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')';
        }

        if (!$params['show_unauth']) {
            $where[] = 'a.access IN (' . $params['groups'] . ') ';
        }

        $orderby = 'a.lft';
        $query = 'SELECT a.id, a.title, a.alias, a.access, a.path AS route, '
               . 'a.created_time created, a.modified_time modified, params, metadata '
               . 'FROM #__categories AS a '
               . 'WHERE '. implode(' AND ',$where)
               . ( $osmap->view != 'xml' ? "\n ORDER BY " . $orderby . "" : '' );

        $db->setQuery($query);

        $items = $db->loadObjectList();

        if (count($items) > 0) {
            $osmap->changeLevel(1);
            foreach ($items as $item) {
                if (OSMAP_LICENSE === 'pro') {
                    $content = new Alledia\OSMap\Pro\Joomla\Item($item);
                    if (!$content->isVisibleForRobots()) {
                        return false;
                    }
                }

                $node = new stdclass();
                $node->id          = $parent->id;
                $node->uid         = $parent->uid . 'c' . $item->id;
                $node->browserNav  = $parent->browserNav;
                $node->priority    = $params['cat_priority'];
                $node->changefreq  = $params['cat_changefreq'];
                $node->name        = $item->title;
                $node->expandible  = true;
                $node->secure      = $parent->secure;
                // TODO: Should we include category name or metakey here?
                // $node->keywords = $item->metakey;
                $node->newsItem    = 0;

                // For the google news we should use te publication date instead
                // the last modification date. See
                if ($osmap->isNews || !$item->modified)
                    $item->modified = $item->created;

                $node->slug = $item->route ? ($item->id . ':' . $item->route) : $item->id;
                $node->link = ContentHelperRoute::getCategoryRoute($node->slug);
                if (strpos($node->link,'Itemid=')===false) {
                    $node->itemid = $itemid;
                    $node->link .= '&Itemid='.$itemid;
                } else {
                    $node->itemid = preg_replace('/.*Itemid=([0-9]+).*/','$1',$node->link);
                }
                if ($osmap->printNode($node)) {
                    self::expandCategory($osmap, $parent, $item->id, $params, $node->itemid);
                }
            }

            $osmap->changeLevel(-1);
        }

        // Include Category's content
        self::includeCategoryContent($osmap, $parent, $catid, $params, $itemid);

        return true;
    }

    /**
     * Get all content items within a content category.
     * Returns an array of all contained content items.
     *
     * @since 2.0
     */
    public static function includeCategoryContent($osmap, $parent, $catid, &$params,$Itemid)
    {
        $db = JFactory::getDBO();

        // We do not do ordering for XML sitemap.
        if ($osmap->view != 'xml') {
            $orderby = self::buildContentOrderBy($parent->params,$parent->id,$Itemid);
            //$orderby = !empty($menuparams['orderby']) ? $menuparams['orderby'] : (!empty($menuparams['orderby_sec']) ? $menuparams['orderby_sec'] : 'rdate' );
            //$orderby = self::orderby_sec($orderby);
        }

        if ($params['include_archived']) {
            $where = array('(a.state = 1 or a.state = 2)');
        } else {
            $where = array('a.state = 1');
        }

        if ($catid=='featured') {
            $where[] = 'a.featured=1';
        } elseif ($catid=='archived') {
            $where = array('a.state=2');
        } elseif(is_numeric($catid)) {
            $where[] = 'a.catid='.(int) $catid;
        }

        if ($params['max_art_age'] || $osmap->isNews) {
            $days = (($osmap->isNews && ($params['max_art_age'] > 3 || !$params['max_art_age'])) ? 3 : $params['max_art_age']);
            $where[] = "( a.created >= '"
                . date('Y-m-d H:i:s', time() - $days * 86400) . "' ) ";
        }

        if ($params['language_filter'] ) {
            $where[] = 'a.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')';
        }

        if (!$params['show_unauth'] ){
            $where[] = 'a.access IN (' . $params['groups'] . ') ';
        }

        $query = 'SELECT a.id, a.title, a.alias, a.catid, '
               . 'a.created created, a.modified modified, attribs as params, metadata'
               . ',a.language'
               . (($params['add_images'] || $params['add_pagebreaks']) ? ',a.introtext, a.fulltext ' : ' ')
               . 'FROM #__content AS a '
               . 'LEFT JOIN #__content_frontpage AS fp ON a.id = fp.content_id '
               . 'WHERE ' . implode(' AND ',$where) . ' AND '
               . '      (a.publish_up = ' . $params['nullDate']
               . ' OR a.publish_up <= ' . $params['nowDate'] . ') AND '
               . '      (a.publish_down = ' . $params['nullDate']
               . ' OR a.publish_down >= ' . $params['nowDate'] . ') '
               . ( $osmap->view != 'xml' ? "\n ORDER BY $orderby  " : '' )
               . ( $params['max_art'] ? "\n LIMIT {$params['max_art']}" : '');

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

                $node = new stdclass();
                $node->id          = $parent->id;
                $node->uid         = $parent->uid . 'a' . $item->id;
                $node->browserNav  = $parent->browserNav;
                $node->priority    = $params['art_priority'];
                $node->changefreq  = $params['art_changefreq'];
                $node->name        = $item->title;
                $node->modified    = $item->modified;
                $node->expandible  = false;
                $node->secure      = $parent->secure;
                // TODO: Should we include category name or metakey here?
                // $node->keywords = $item->metakey;
                $node->newsItem    = 1;
                $node->language    = $item->language;

                // For the google news we should use te publication date instead
                // the last modification date. See
                if ($osmap->isNews || !$node->modified)
                    $node->modified = $item->created;

                $node->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
                //$node->catslug = $item->category_route ? ($catid . ':' . $item->category_route) : $catid;
                $node->catslug = $item->catid;
                $node->link    = ContentHelperRoute::getArticleRoute($node->slug, $node->catslug);

                // Add images to the article
                $text = @$item->introtext . @$item->fulltext;

                if ($params['add_images']) {
                    if (OSMAP_LICENSE === 'pro') {
                        $node->images = Alledia\OSMap\Pro\Joomla\Helper::getImages($text, JArrayHelper::getValue($params, 'max_images', 1000));
                    } else {
                        $node->images = OSMapHelper::getImages($text, JArrayHelper::getValue($params, 'max_images', 1000));
                    }
                }

                if ($params['add_pagebreaks']) {
                    $subnodes = OSMapHelper::getPagebreaks($text,$node->link);
                    $node->expandible = (count($subnodes) > 0); // This article has children
                }

                if ($osmap->printNode($node) && $node->expandible) {
                    self::printNodes($osmap, $parent, $params, $subnodes);
                }
            }

            $osmap->changeLevel(-1);
        }

        return true;
    }

    static private function printNodes($osmap, $parent, &$params, &$subnodes)
    {
        $osmap->changeLevel(1);
        $i=0;
        foreach ($subnodes as $subnode) {
            $i++;
            $subnode->id = $parent->id;
            $subnode->uid = $parent->uid.'p'.$i;
            $subnode->browserNav = $parent->browserNav;
            $subnode->priority = $params['art_priority'];
            $subnode->changefreq = $params['art_changefreq'];
            $subnode->secure = $parent->secure;
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
    static function buildContentOrderBy(&$params,$parentId,$itemid)
    {
        $app = JFactory::getApplication('site');

        // Case when the child gets a different menu itemid than it's parent
        if ($parentId != $itemid) {
            $menu = $app->getMenu();
            $item = $menu->getItem($itemid);
            $menuParams = clone($params);
            $itemParams = new JRegistry($item->params);
            $menuParams->merge($itemParams);
        } else {
            $menuParams =& $params;
        }

        $filter_order     = $app->getUserStateFromRequest('com_content.category.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
        $filter_order_Dir = $app->getUserStateFromRequest('com_content.category.list.' . $itemid . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
        $orderby = ' ';

        if ($filter_order && $filter_order_Dir) {
            $orderby .= $filter_order . ' ' . $filter_order_Dir . ', ';
        }

        $articleOrderby     = $menuParams->get('orderby_sec', 'rdate');
        $articleOrderDate   = $menuParams->get('order_date');
        //$categoryOrderby  = $menuParams->def('orderby_pri', '');
        $secondary      = ContentHelperQuery::orderbySecondary($articleOrderby, $articleOrderDate) . ', ';
        //$primary      = ContentHelperQuery::orderbyPrimary($categoryOrderby);

        //$orderby .= $primary . ' ' . $secondary . ' a.created ';
        $orderby .=  $secondary . ' a.created ';

        return $orderby;
    }
}
