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

class xmap_com_weblinks
{
    private static $views = array('categories', 'category');

    private static $enabled = false;

    public function __construct()
    {
        self::$enabled = JComponentHelper::isEnabled('com_weblinks');

        if (self::$enabled) {
            JLoader::register('WeblinksHelperRoute', JPATH_SITE . '/components/com_weblinks/helpers/route.php');
        }
    }

    public static function getTree($xmap, $parent, &$params)
    {
        $uri = new JUri($parent->link);

        if (!self::$enabled || !in_array($uri->getVar('view'), self::$views)) {
            return;
        }

        $params['groups']              = implode(',', JFactory::getUser()->getAuthorisedViewLevels());

        $params['language_filter']     = JFactory::getApplication()->getLanguageFilter();

        $params['include_links']       = JArrayHelper::getValue($params, 'include_links', 1);
        $params['include_links']       = ($params['include_links'] == 1 || ($params['include_links'] == 2 && $xmap->view == 'xml') || ($params['include_links'] == 3 && $xmap->view == 'html'));

        $params['show_unauth']         = JArrayHelper::getValue($params, 'show_unauth', 0);
        $params['show_unauth']         = ($params['show_unauth'] == 1 || ($params['show_unauth'] == 2 && $xmap->view == 'xml') || ($params['show_unauth'] == 3 && $xmap->view == 'html'));

        $params['category_priority']   = JArrayHelper::getValue($params, 'category_priority', $parent->priority);
        $params['category_changefreq'] = JArrayHelper::getValue($params, 'category_changefreq', $parent->changefreq);

        if ($params['category_priority'] == -1) {
            $params['category_priority'] = $parent->priority;
        }

        if ($params['category_changefreq'] == -1) {
            $params['category_changefreq'] = $parent->changefreq;
        }

        $params['link_priority']   = JArrayHelper::getValue($params, 'link_priority', $parent->priority);
        $params['link_changefreq'] = JArrayHelper::getValue($params, 'link_changefreq', $parent->changefreq);

        if ($params['link_priority'] == -1) {
            $params['link_priority'] = $parent->priority;
        }

        if ($params['link_changefreq'] == -1) {
            $params['link_changefreq'] = $parent->changefreq;
        }

        switch ($uri->getVar('view')) {
            case 'categories':
                self::getCategoryTree($xmap, $parent, $params, $uri->getVar('id'));
                break;

            case 'category':
                self::getlinks($xmap, $parent, $params, $uri->getVar('id'));
                break;
        }
    }

    private static function getCategoryTree(&$xmap, &$parent, &$params, $parent_id)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select(array('c.id', 'c.alias', 'c.title', 'c.parent_id'))
            ->from('#__categories AS c')
            ->where('c.parent_id = ' . $db->quote($parent_id ? $parent_id : 1))
            ->where('c.extension = ' . $db->quote('com_weblinks'))
            ->where('c.published = 1')
            ->order('c.lft');

        if (!$params['show_unauth']) {
            $query->where('c.access IN(' . $params['groups'] . ')');
        }

        if ($params['language_filter']) {
            $query->where('c.language IN(' . $db->quote(JFactory::getLanguage()->getTag()) . ', ' . $db->quote('*') . ')');
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        if (empty($rows)) {
            return;
        }

        $xmap->changeLevel(1);

        foreach ($rows as $row) {
            $node = new stdclass;
            $node->id         = $parent->id;
            $node->name       = $row->title;
            $node->uid        = $parent->uid . '_cid_' . $row->id;
            $node->browserNav = $parent->browserNav;
            $node->priority   = $params['category_priority'];
            $node->changefreq = $params['category_changefreq'];
            $node->pid        = $row->parent_id;
            $node->link       = WeblinksHelperRoute::getCategoryRoute($row->id);

            if ($xmap->printNode($node) !== false) {
                self::getlinks($xmap, $parent, $params, $row->id);
            }
        }

        $xmap->changeLevel(-1);
    }

    private static function getlinks(&$xmap, &$parent, &$params, $catid)
    {
        self::getCategoryTree($xmap, $parent, $params, $catid);

        if (!$params['include_links']) {
            return;
        }

        $db  = JFactory::getDbo();
        $now = JFactory::getDate('now', 'UTC')->toSql();

        $query = $db->getQuery(true)
            ->select(array('w.id', 'w.alias', 'w.title'))
            ->from('#__weblinks AS w')
            ->where('w.catid = ' . $db->Quote($catid))
            ->where('w.state = 1')
            ->where('(w.publish_up = ' . $db->quote($db->getNullDate()) . ' OR w.publish_up <= ' . $db->quote($now) . ')')
            ->where('(w.publish_down = ' . $db->quote($db->getNullDate()) . ' OR w.publish_down >= ' . $db->quote($now) . ')')
            ->order('w.ordering');

        if (!$params['show_unauth']) {
            $query->where('w.access IN(' . $params['groups'] . ')');
        }

        if ($params['language_filter']) {
            $query->where('w.language IN(' . $db->quote(JFactory::getLanguage()->getTag()) . ', ' . $db->quote('*') . ')');
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        if (empty($rows)) {
            return;
        }

        $xmap->changeLevel(1);

        foreach ($rows as $row) {
            $node = new stdclass;
            $node->id         = $parent->id;
            $node->name       = $row->title;
            $node->uid        = $parent->uid . '_' . $row->id;
            $node->browserNav = $parent->browserNav;
            $node->priority   = $params['link_priority'];
            $node->changefreq = $params['link_changefreq'];
            $node->link       = WeblinksHelperRoute::getWeblinkRoute($row->id . ':' . $row->alias, $catid);

            $xmap->printNode($node);
        }

        $xmap->changeLevel(-1);
    }
}
