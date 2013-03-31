<?php

/**
 * @author Guillermo Vargas
 * @email guille@vargas.co.cr
 * @version $Id: com_weblinks.php
 * @package Xmap
 * @license GNU/GPL
 * @description Xmap plugin for Joomla's web links component
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

class xmap_com_weblinks
{

    static private $_initialized = false;
    /*
     * This function is called before a menu item is printed. We use it to set the
     * proper uniqueid for the item and indicate whether the node is expandible or not
     */

    static function prepareMenuItem($node, &$params)
    {
        $link_query = parse_url($node->link);
        parse_str(html_entity_decode($link_query['query']), $link_vars);
        $view = JArrayHelper::getValue($link_vars, 'view', '');
        if ($view == 'weblink') {
            $id = intval(JArrayHelper::getValue($link_vars, 'id', 0));
            if ($id) {
                $node->uid = 'com_weblinksi' . $id;
                $node->expandible = false;
            }
        } elseif ($view == 'categories') {
            $node->uid = 'com_weblinkscategories';
            $node->expandible = true;
        } elseif ($view == 'category') {
            $catid = intval(JArrayHelper::getValue($link_vars, 'id', 0));
            $node->uid = 'com_weblinksc' . $catid;
            $node->expandible = true;
        }
    }

    static function getTree($xmap, $parent, &$params)
    {
        self::initialize($params);

        $app = JFactory::getApplication();
        $weblinks_params = $app->getParams('com_weblinks');

        $link_query = parse_url($parent->link);
        parse_str(html_entity_decode($link_query['query']), $link_vars);
        $view = JArrayHelper::getValue($link_vars, 'view', 0);

        $app = JFactory::getApplication();
        $menu = $app->getMenu();
        $menuparams = $menu->getParams($parent->id);

        if ($view == 'category') {
            $catid = intval(JArrayHelper::getValue($link_vars, 'id', 0));
        } elseif ($view == 'categories') {
            $catid = 0;
        } else { // Only expand category menu items
            return;
        }

        $include_links = JArrayHelper::getValue($params, 'include_links', 1, '');
        $include_links = ( $include_links == 1
            || ( $include_links == 2 && $xmap->view == 'xml')
            || ( $include_links == 3 && $xmap->view == 'html')
            || $xmap->view == 'navigator');
        $params['include_links'] = $include_links;

        $priority = JArrayHelper::getValue($params, 'cat_priority', $parent->priority, '');
        $changefreq = JArrayHelper::getValue($params, 'cat_changefreq', $parent->changefreq, '');
        if ($priority == '-1')
            $priority = $parent->priority;
        if ($changefreq == '-1')
            $changefreq = $parent->changefreq;

        $params['cat_priority'] = $priority;
        $params['cat_changefreq'] = $changefreq;

        $priority = JArrayHelper::getValue($params, 'link_priority', $parent->priority, '');
        $changefreq = JArrayHelper::getValue($params, 'link_changefreq', $parent->changefreq, '');
        if ($priority == '-1')
            $priority = $parent->priority;

        if ($changefreq == '-1')
            $changefreq = $parent->changefreq;

        $params['link_priority'] = $priority;
        $params['link_changefreq'] = $changefreq;

        $options = array();
        $options['countItems'] = false;
        $options['catid'] = rand();
        $categories = JCategories::getInstance('Weblinks', $options);
        $category = $categories->get($catid? $catid : 'root', true);

        $params['count_clicks'] = $weblinks_params->get('count_clicks');

        xmap_com_weblinks::getCategoryTree($xmap, $parent, $params, $category);
    }

    static function getCategoryTree($xmap, $parent, &$params, $category)
    {
        $db = JFactory::getDBO();

        $children = $category->getChildren();
        $xmap->changeLevel(1);
        foreach ($children as $cat) {
            $node = new stdclass;
            $node->id = $parent->id;
            $node->uid = $parent->uid . 'c' . $cat->id;
            $node->name = $cat->title;
            $node->link = WeblinksHelperRoute::getCategoryRoute($cat);
            $node->priority = $params['cat_priority'];
            $node->changefreq = $params['cat_changefreq'];
            $node->expandible = true;
            if ($xmap->printNode($node) !== FALSE) {
                xmap_com_weblinks::getCategoryTree($xmap, $parent, $params, $cat);
            }
        }
        $xmap->changeLevel(-1);

        if ($params['include_links']) { //view=category&catid=...
            $linksModel = new WeblinksModelCategory();
            $linksModel->getState(); // To force the populate state
            $linksModel->setState('list.limit', JArrayHelper::getValue($params, 'max_links', NULL));
            $linksModel->setState('list.start', 0);
            $linksModel->setState('list.ordering', 'ordering');
            $linksModel->setState('list.direction', 'ASC');
            $linksModel->setState('category.id', $category->id);
            $links = $linksModel->getItems();
            $xmap->changeLevel(1);
            foreach ($links as $link) {
                $item_params = new JRegistry;
                $item_params->loadString($link->params);

                $node = new stdclass;
                $node->id = $parent->id;
                $node->uid = $parent->uid . 'i' . $link->id;
                $node->name = $link->title;

                // Find the Itemid
                $Itemid = intval(preg_replace('/.*Itemid=([0-9]+).*/','$1',WeblinksHelperRoute::getWeblinkRoute($link->id, $category->id)));

                if ($item_params->get('count_clicks', $params['count_clicks']) == 1) {
                    $node->link = 'index.php?option=com_weblinks&task=weblink.go&id='. $link->id.'&Itemid='.($Itemid ? $Itemid : $parent->id);
                } else {
                    $node->link = $link->url;
                }
                $node->priority = $params['link_priority'];
                $node->changefreq = $params['link_changefreq'];
                $node->expandible = false;
                $xmap->printNode($node);
            }
            $xmap->changeLevel(-1);
        }
    }

    static public function initialize(&$params)
    {
        if (self::$_initialized) {
            return;
        }

        self::$_initialized = true;
        require_once JPATH_SITE.'/components/com_weblinks/models/category.php';
        require_once JPATH_SITE.'/components/com_weblinks/helpers/route.php';
    }
}