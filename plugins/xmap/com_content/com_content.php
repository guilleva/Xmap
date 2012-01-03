<?php
/**
 * @version             $Id$
 * @copyright           Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
 */
defined('_JEXEC') or die;

require_once JPATH_SITE . DS . 'components' . DS . 'com_content' . DS . 'helpers' . DS . 'route.php';
require_once JPATH_SITE . DS . 'components' . DS . 'com_content' . DS . 'helpers' . DS . 'query.php';

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
class xmap_com_content
{
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
    static function prepareMenuItem($node, &$params)
    {
        $db = JFactory::getDbo();
        $link_query = parse_url($node->link);
        if (!isset($link_query['query'])) {
            return;
        }

        parse_str(html_entity_decode($link_query['query']), $link_vars);
        $view = JArrayHelper::getValue($link_vars, 'view', '');
        $layout = JArrayHelper::getValue($link_vars, 'layout', '');
        $id = JArrayHelper::getValue($link_vars, 'id', 0);

        //----- Set add_images param
        $params['add_images'] = JArrayHelper::getValue($params, 'add_images', 0);;

        //----- Set add pagebreaks param
        $add_pagebreaks = JArrayHelper::getValue($params, 'add_pagebreaks', 1);
        $params['add_pagebreaks'] = JArrayHelper::getValue($params, 'add_pagebreaks', 1);

        switch ($view) {
            case 'category':
                if ($id) {
                    $node->uid = 'com_contentc' . $id;
                } else {
                    $node->uid = 'com_content' . $layout;
                }
                $node->expandible = true;
                break;
            case 'article':
                $node->uid = 'com_contenta' . $id;
                $node->expandible = false;

                $query = 'SELECT UNIX_TIMESTAMP(a.created) as created,
                                 UNIX_TIMESTAMP(a.modified) as modified '
                       .(($params['add_images'] || $params['add_pagebreaks'])? ',`introtext`, `fulltext` ' : '')
                       . 'FROM `#__content` as a
                          WHERE id='.intval($id).'
                         ';
                $db->setQuery($query);
                if (($row = $db->loadObject()) != NULL) {
                    $node->modified = ($row->modified? $row->modified : $row->created);
                }
                break;
            case 'archive':
                $node->expandible = true;
                break;
            case 'featured':
                $node->uid = 'com_contentfeatured';
                $node->expandible = false;
        }
    }

    /**
     * Expands a com_content menu item
     *
     * @return void
     * @since  1.0
     */
    static function getTree($xmap, $parent, &$params)
    {
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        $result = null;

        $link_query = parse_url($parent->link);
        if (!isset($link_query['query'])) {
            return;
        }

        parse_str(html_entity_decode($link_query['query']), $link_vars);
        $view = JArrayHelper::getValue($link_vars, 'view', '');
        $id = intval(JArrayHelper::getValue($link_vars, 'id', ''));

        /*         * *
         * Parameters Initialitation
         * */
        //----- Set expand_categories param
        $expand_categories = JArrayHelper::getValue($params, 'expand_categories', 1);
        $expand_categories = ( $expand_categories == 1
                || ( $expand_categories == 2 && $xmap->view == 'xml')
                || ( $expand_categories == 3 && $xmap->view == 'html')
                || $xmap->view == 'navigator');
        $params['expand_categories'] = $expand_categories;

        //----- Set expand_featured param
        $expand_featured = JArrayHelper::getValue($params, 'expand_featured', 1);
        $expand_featured = ( $expand_featured == 1
                || ( $expand_featured == 2 && $xmap->view == 'xml')
                || ( $expand_featured == 3 && $xmap->view == 'html')
                || $xmap->view == 'navigator');
        $params['expand_featured'] = $expand_featured;
        
        //----- Set expand_featured param
        $include_archived = JArrayHelper::getValue($params, 'include_archived', 2);
        $include_archived = ( $include_archived == 1
                || ( $include_archived == 2 && $xmap->view == 'xml')
                || ( $include_archived == 3 && $xmap->view == 'html')
                || $xmap->view == 'navigator');
        $params['include_archived'] = $include_archived;

        //----- Set show_unauth param
        $show_unauth = JArrayHelper::getValue($params, 'show_unauth', 1);
        $show_unauth = ( $show_unauth == 1
                || ( $show_unauth == 2 && $xmap->view == 'xml')
                || ( $show_unauth == 3 && $xmap->view == 'html'));
        $params['show_unauth'] = $show_unauth;

        //----- Set add_images param
        $add_images = JArrayHelper::getValue($params, 'add_images', 0);
        $add_images = ( $add_images == 1 && $xmap->view == 'xml');
        $params['add_images'] = $add_images;
        $params['max_images'] = JArrayHelper::getValue($params, 'max_images', 1000);

        //----- Set add pagebreaks param
        $add_pagebreaks = JArrayHelper::getValue($params, 'add_pagebreaks', 1);
        $add_pagebreaks = ( $add_pagebreaks == 1
                || ( $add_pagebreaks == 2 && $xmap->view == 'xml')
                || ( $add_pagebreaks == 3 && $xmap->view == 'html')
                || $xmap->view == 'navigator');
        $params['add_pagebreaks'] = $add_pagebreaks;

        if ($params['add_pagebreaks'] && !defined('_XMAP_COM_CONTENT_LOADED')) {
            define('_XMAP_COM_CONTENT_LOADED',1);  // Load it just once
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

        $params['nowDate'] = $db->Quote(JFactory::getDate()->toMySQL());
        $params['groups'] = implode(',', $user->authorisedLevels());

        // Define the language filter condition for the query
        $params['language_filter'] = $app->getLanguageFilter();

        switch ($view) {
            case 'category':
                if (!$id) {
                    $id = intval(JArrayHelper::getValue($params, 'id', 0));
                }
                if ($params['expand_categories'] && $id) {
                    $result = self::expandCategory($xmap, $parent, $id, $params, $parent->id);
                }
                break;
            case 'featured':
                if ($params['expand_featured']) {
                    $result = self::includeCategoryContent($xmap, $parent, 'featured', $params,$parent->id);
                }
                break;
            case 'categories':
                if ($params['expand_categories']) {
                    $result = self::expandCategory($xmap, $parent, 1, $params, $parent->id);
                }
                break;
            case 'archive':
                if ($params['expand_featured']) {
                    $result = self::includeCategoryContent($xmap, $parent, 'archived', $params,$parent->id);
                }
                break;
            case 'article':
                $db = JFactory::getDBO();
                $db->setQuery("SELECT UNIX_TIMESTAMP(modified) modified, UNIX_TIMESTAMP(created) created FROM #__content WHERE id=" . $id);
                $item = $db->loadObject();
                if ($item->modified) {
                    $item->modified = $item->created;
                }
                $result = true;
                break;
        }
        return $result;
    }

    /**
     * Get all content items within a content category.
     * Returns an array of all contained content items.
     *
     * @param object  $xmap
     * @param object  $parent   the menu item
     * @param int     $catid    the id of the category to be expanded
     * @param array   $params   an assoc array with the params for this plugin on Xmap
     * @param int     $itemid   the itemid to use for this category's children
     */
    static function expandCategory($xmap, $parent, $catid, &$params, $itemid)
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
               . 'UNIX_TIMESTAMP(a.created_time) created, UNIX_TIMESTAMP(a.modified_time) modified '
               . 'FROM #__categories AS a '
               . 'WHERE '. implode(' AND ',$where)
               . ( $xmap->view != 'xml' ? "\n ORDER BY " . $orderby . "" : '' );

        $db->setQuery($query);
        #echo nl2br(str_replace('#__','jos_',$db->getQuery()));exit;
        $items = $db->loadObjectList();

        if (count($items) > 0) {
            $xmap->changeLevel(1);
            foreach ($items as $item) {
                $node = new stdclass();
                $node->id = $parent->id;
                $node->uid = $parent->uid . 'c' . $item->id;
                $node->browserNav = $parent->browserNav;
                $node->priority = $params['cat_priority'];
                $node->changefreq = $params['cat_changefreq'];
                $node->name = $item->title;
                $node->expandible = true;
                $node->secure = $parent->secure;
                // TODO: Should we include category name or metakey here?
                // $node->keywords = $item->metakey;
                $node->newsItem = 0;

                // For the google news we should use te publication date instead
                // the last modification date. See
                if ($xmap->isNews || !$item->modified)
                    $item->modified = $item->created;

                $node->slug = $item->route ? ($item->id . ':' . $item->route) : $item->id;
                $node->link = ContentHelperRoute::getCategoryRoute($node->slug);
                if (strpos($node->link,'Itemid=')===false) {
                    $node->itemid = $itemid;
                    $node->link .= '&Itemid='.$itemid;
                } else {
                    $node->itemid = preg_replace('/.*Itemid=([0-9]+).*/','$1',$node->link);
                }
                if ($xmap->printNode($node)) {
                    self::expandCategory($xmap, $parent, $item->id, $params, $node->itemid);
                }
            }
            $xmap->changeLevel(-1);
        }

        // Include Category's content
        self::includeCategoryContent($xmap, $parent, $catid, $params, $itemid);
        return true;
    }

    /**
     * Get all content items within a content category.
     * Returns an array of all contained content items.
     *
     * @since 2.0
     */
    static function includeCategoryContent($xmap, $parent, $catid, &$params,$Itemid)
    {
        $db = JFactory::getDBO();

        // We do not do ordering for XML sitemap.
        if ($xmap->view != 'xml') {
            $orderby = self::buildContentOrderBy($parent->params,$parent->id,$Itemid);
            //$orderby = !empty($menuparams['orderby']) ? $menuparams['orderby'] : (!empty($menuparams['orderby_sec']) ? $menuparams['orderby_sec'] : 'rdate' );
            //$orderby = self::orderby_sec($orderby);
        }

        if ($params['include_archived']) {
            $where = array('(a.state = 1 || a.state = 2)');
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

        if ($params['max_art_age'] || $xmap->isNews) {
            $days = (($xmap->isNews && ($params['max_art_age'] > 3 || !$params['max_art_age'])) ? 3 : $params['max_art_age']);
            $where[] = "( a.created >= '"
                      . date('Y-m-d H:i:s', time() - $days * 86400) . "' ) ";
        }

        if ($params['language_filter'] ) {
            $where[] = 'a.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')';
        }

        if (!$params['show_unauth'] ){
            $where[] = 'a.access IN (' . $params['groups'] . ') ';
        }

        $query = 'SELECT a.id, a.title, a.alias, a.title_alias, a.catid, '
               . 'UNIX_TIMESTAMP(a.created) created, UNIX_TIMESTAMP(a.modified) modified'
               . ',a.language'
               . (($params['add_images'] || $params['add_pagebreaks']) ? ',a.introtext, a.fulltext ' : ' ')
               . 'FROM #__content AS a '
               . ($catid =='featured'? 'LEFT JOIN #__content_frontpage AS fp ON a.id = fp.content_id ' : ' ')
               . 'WHERE ' . implode(' AND ',$where) . ' AND '
               . '      (a.publish_up = ' . $params['nullDate']
               . ' OR a.publish_up <= ' . $params['nowDate'] . ') AND '
               . '      (a.publish_down = ' . $params['nullDate']
               . ' OR a.publish_down >= ' . $params['nowDate'] . ') '
               . ( $xmap->view != 'xml' ? "\n ORDER BY $orderby  " : '' )
               . ( $params['max_art'] ? "\n LIMIT {$params['max_art']}" : '');

        $db->setQuery($query);
        //echo nl2br(str_replace('#__','mgbj2_',$db->getQuery()));
        $items = $db->loadObjectList();

        if (count($items) > 0) {
            $xmap->changeLevel(1);
            foreach ($items as $item) {
                $node = new stdclass();
                $node->id = $parent->id;
                $node->uid = $parent->uid . 'a' . $item->id;
                $node->browserNav = $parent->browserNav;
                $node->priority = $params['art_priority'];
                $node->changefreq = $params['art_changefreq'];
                $node->name = $item->title;
                $node->modified = $item->modified;
                $node->expandible = false;
                $node->secure = $parent->secure;
                // TODO: Should we include category name or metakey here?
                // $node->keywords = $item->metakey;
                $node->newsItem = 1;
                $node->language = $item->language;

                // For the google news we should use te publication date instead
                // the last modification date. See
                if ($xmap->isNews || !$node->modified)
                    $node->modified = $item->created;

                $node->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
                //$node->catslug = $item->category_route ? ($catid . ':' . $item->category_route) : $catid;
                $node->catslug = $item->catid;
                $node->link = ContentHelperRoute::getArticleRoute($node->slug, $node->catslug);

                // Add images to the article
                $text = @$item->introtext . @$item->fulltext;
                if ($params['add_images']) {
                    $node->images = XmapHelper::getImages($text,$params['max_images']);
                }

                if ($params['add_pagebreaks']) {
                    $subnodes = XmapHelper::getPagebreaks($text,$node->link);
                    $node->expandible = (count($subnodes) > 0); // This article has children
                }

                if ($xmap->printNode($node) && $node->expandible) {
                    $xmap->changeLevel(1);
                    $i=0;
                    foreach ($subnodes as $subnode) {
                        $i++;
                        //var_dump($subnodes);
                        $subnode->id = $parent->id;
                        $subnode->uid = $parent->uid.'p'.$i;
                        $subnode->browserNav = $parent->browserNav;
                        $subnode->priority = $params['art_priority'];
                        $subnode->changefreq = $params['art_changefreq'];
                        $subnode->secure = $parent->secure;
                        $xmap->printNode($subnode);
                    }
                    $xmap->changeLevel(-1);
                }
            }
            $xmap->changeLevel(-1);
        }
        return true;
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
        $app    = JFactory::getApplication('site');

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

        $filter_order = $app->getUserStateFromRequest('com_content.category.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
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