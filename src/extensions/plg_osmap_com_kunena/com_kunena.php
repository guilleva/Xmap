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

/** Handles Kunena forum structure */
class osmap_com_kunena
{
    /*
     * This function is called before a menu item is printed. We use it to set the
     * proper uniqueid for the item
     */
    static $profile;
    static $config;

    public static function prepareMenuItem($node, &$params)
    {
        $link_query = parse_url($node->link);
        parse_str(html_entity_decode($link_query['query']), $link_vars);

        $catid = intval(JArrayHelper::getValue($link_vars, 'catid', 0));
        $id    = intval(JArrayHelper::getValue($link_vars, 'id', 0));
        $func  = JArrayHelper::getValue($link_vars, 'func', '', '');

        if ($func = 'showcat' && $catid) {
            $node->uid        = 'com_kunenac' . $catid;
            $node->expandible = false;
        } elseif ($func = 'view' && $id) {
            $node->uid        = 'com_kunenaf' . $id;
            $node->expandible = false;
        }
    }

    public static function getTree($osmap, $parent, &$params)
    {
        // This component does not provide news content. don't waste time/resources
        if ($osmap->isNews) {
            return false;
        }

        // Make sure that we can load the kunena api
        if (!static::loadKunenaApi()) {
            return false;
        }

        if (!self::$profile) {
            self::$config = KunenaFactory::getConfig();

            self::$profile = KunenaFactory::getUser();
        }

        $user  = JFactory::getUser();
        $catid = 0;

        $link_query = parse_url($parent->link);
        if (!isset($link_query['query'])) {
            return;
        }

        parse_str(html_entity_decode($link_query['query']), $link_vars);


        // Kubik-Rubik Solution - get the correct view in Kunena >= 2.0.1 - START
        $view       = JArrayHelper::getValue($link_vars, 'view', '');
        $layout     = JArrayHelper::getValue($link_vars, 'layout', '');
        $catid_link = JArrayHelper::getValue($link_vars, 'catid', 0);

        if ($view == 'category' AND (!$layout OR 'list' == $layout)) {
            if (!empty($catid_link)) {
                $link_query = parse_url($parent->link);

                parse_str(html_entity_decode($link_query['query']), $link_vars);

                $catid = JArrayHelper::getValue($link_vars, 'catid', 0);
            } else {
                $catid = 0;
            }

            // Get ItemID of the main menu entry of the component
            $component = JComponentHelper::getComponent('com_kunena');
            $app       = JFactory::getApplication();

            $menus = $app->getMenu('site', array());
            $items = $menus->getItems('component_id', $component->id);

            foreach($items as $item) {
                if (@$item->query['view'] == 'home') {
                    $parent->id = $item->id;
                    break;
                }
            }
        } else {
            return true;
        }
        // Kubik-Rubik Solution - END

        $include_topics = JArrayHelper::getValue($params, 'include_topics', 1);
        $include_topics = ( $include_topics == 1
                || ( $include_topics == 2 && $osmap->view == 'xml')
                || ( $include_topics == 3 && $osmap->view == 'html')
                || $osmap->view == 'navigator');
        $params['include_topics'] = $include_topics;

        $priority   = JArrayHelper::getValue($params, 'cat_priority', $parent->priority);
        $changefreq = JArrayHelper::getValue($params, 'cat_changefreq', $parent->changefreq);
        if ($priority == '-1') {
            $priority = $parent->priority;
        }

        if ($changefreq == '-1') {
            $changefreq = $parent->changefreq;
        }

        $params['cat_priority']   = $priority;
        $params['cat_changefreq'] = $changefreq;
        $params['groups']         = implode(',', $user->getAuthorisedViewLevels());

        $priority   = JArrayHelper::getValue($params, 'topic_priority', $parent->priority);
        $changefreq = JArrayHelper::getValue($params, 'topic_changefreq', $parent->changefreq);
        if ($priority == '-1') {
            $priority = $parent->priority;
        }

        if ($changefreq == '-1') {
            $changefreq = $parent->changefreq;
        }

        $params['topic_priority']   = $priority;
        $params['topic_changefreq'] = $changefreq;

        if ($include_topics) {
            $ordering = JArrayHelper::getValue($params, 'topics_order', 'ordering');

            if (!in_array($ordering, array('id', 'ordering', 'time', 'subject', 'hits'))) {
                $ordering = 'ordering';
            }

            $params['topics_order']       = 't.`'.$ordering.'`';
            $params['include_pagination'] = ($osmap->view == 'xml');

            $params['limit'] = '';
            $params['days']  = '';

            // Kubik-Rubik Solution - limit must be only the number + check whether variable is numeric - START
            $limit = JArrayHelper::getValue($params, 'max_topics', '');

            if (is_numeric($limit)) {
                $params['limit'] = $limit;
            }

            $days           = JArrayHelper::getValue($params, 'max_age', '');
            $params['days'] = false;

            if (is_numeric($days)) {
                $params['days'] = ($osmap->now - (intval($days) * 86400));
            }
            // Kubik-Rubik Solution - END
        }

        $params['table_prefix'] = static::getTablePrefix();

        static::getCategoryTree($osmap, $parent, $params, $catid);
    }

    /*
     * Builds the Kunena's tree
     */
    protected static function getCategoryTree($osmap, $parent, &$params, $parentCat)
    {
        $db = JFactory::getDBO();

        // Load categories
        if (self::getKunenaMajorVersion() >= '2.0') {
            // Kunena 2.0+
            $catlink = 'index.php?option=com_kunena&amp;view=category&amp;catid=%s&Itemid='.$parent->id;
            $toplink = 'index.php?option=com_kunena&amp;view=topic&amp;catid=%s&amp;id=%s&Itemid='.$parent->id;

            $categories = KunenaForumCategoryHelper::getChildren($parentCat);
        } else {
            $catlink = 'index.php?option=com_kunena&amp;func=showcat&amp;catid=%s&Itemid='.$parent->id;
            $toplink = 'index.php?option=com_kunena&amp;func=view&amp;catid=%s&amp;id=%s&Itemid='.$parent->id;

            if (self::getKunenaMajorVersion() >= '1.6') {
                // Kunena 1.6+
                kimport('session');
                $session = KunenaFactory::getSession();
                $session->updateAllowedForums();
                $allowed = $session->allowed;
                $query = "SELECT id, name FROM `#__kunena_categories` WHERE parent={$parentCat} AND id IN ({$allowed}) ORDER BY ordering";
            } else {
                // Kunena 1.0+
                $query = "SELECT id, name FROM `{$params['table_prefix']}_categories` WHERE parent={$parentCat} AND published=1 AND pub_access=0 ORDER BY ordering";
            }

            $db->setQuery($query);

            $categories = $db->loadObjectList();
        }

        /* get list of categories */
        $osmap->changeLevel(1);
        foreach($categories as $cat) {
            $node = new stdclass;
            $node->id         = $parent->id;
            $node->browserNav = $parent->browserNav;
            $node->uid        = 'com_kunenac'.$cat->id;
            $node->name       = $cat->name;
            $node->priority   = $params['cat_priority'];
            $node->changefreq = $params['cat_changefreq'];
            $node->link       = sprintf($catlink, $cat->id);
            $node->expandible = true;
            $node->secure     = $parent->secure;

            if ($osmap->printNode($node) !== FALSE) {
                static::getCategoryTree($osmap, $parent, $params, $cat->id);
            }
        }

        if ($params['include_topics']) {
            if (self::getKunenaMajorVersion() >= '2.0') {
                // Kunena 2.0+
                // TODO: orderby parameter is missing:
                $topics = KunenaForumTopicHelper::getLatestTopics($parentCat, 0, ($params['limit'] ? (int)$params['limit'] : PHP_INT_MAX), array('starttime', $params['days']));
            } else {
                $access = KunenaFactory::getAccessControl();
                $hold   = $access->getAllowedHold(self::$profile, $parentCat);
                // Kunena 1.0+
                $query = "SELECT t.id, t.catid, t.subject, max(m.time) as time, count(m.id) as msgcount
                    FROM {$params['table_prefix']}_messages t
                    INNER JOIN {$params['table_prefix']}_messages AS m ON t.id = m.thread
                    WHERE t.catid=$parentCat AND t.parent=0
                        AND t.hold in ({$hold})
                    GROUP BY m.`thread`
                    ORDER BY {$params['topics_order']} DESC";

                if ($params['days']) {
                    $query = "SELECT * FROM ($query) as topics WHERE time >= {$params['days']}";
                }

                #echo str_replace('#__','mgbj2_',$query);
                $db->setQuery($query, 0, $params['limit']);
                $topics = $db->loadObjectList();
            }

            // Kubik-Rubik Solution - call the array item 1, because 0 only contains the number of topics in this category - START
            foreach($topics[1] as $topic) {
            // Kubik-Rubik Solution - END
                $node = new stdclass;
                $node->id         = $parent->id;
                $node->browserNav = $parent->browserNav;
                $node->uid        = 'com_kunenat'.$topic->id;
                $node->name       = $topic->subject;
                $node->priority   = $params['topic_priority'];
                $node->changefreq = $params['topic_changefreq'];

                // Kubik-Rubik Solution - names have been changed - START
                $node->modified = intval($topic->last_post_time);
                $node->link     = sprintf($toplink, $topic->category_id, $topic->id);
                // Kubik-Rubik Solution - END

                $node->expandible = false;
                $node->secure     = $parent->secure;

                if ($osmap->printNode($node) !== false) {
                    // Pagination will not work with K2.0, revisit this when that version is out and stable
                    if ($params['include_pagination'] && isset($topic->msgcount) && $topic->msgcount > self::$config->messages_per_page) {
                        $msgPerPage  = self::$config->messages_per_page;
                        $threadPages = ceil($topic->msgcount / $msgPerPage);

                        for($i = 2; $i <= $threadPages; $i++) {
                            $subnode = new stdclass;
                            $subnode->id         = $node->id;
                            $subnode->uid        = $node->uid.'p'.$i;
                            $subnode->name       = "[$i]";
                            $subnode->seq        = $i;
                            $subnode->link       = $node->link.'&limit='.$msgPerPage.'&limitstart='.(($i - 1) * $msgPerPage);
                            $subnode->browserNav = $node->browserNav;
                            $subnode->priority   = $node->priority;
                            $subnode->changefreq = $node->changefreq;
                            $subnode->modified   = $node->modified;
                            $subnode->secure     = $node->secure;
                            $osmap->printNode($subnode);
                        }
                    }
                }
            }
        }

        $osmap->changeLevel(-1);
    }

    private static function loadKunenaApi()
    {
        if (!defined('KUNENA_LOADED')) {
            jimport('joomla.application.component.helper');

            // Check if Kunena component is installed/enabled
            if (!JComponentHelper::isEnabled('com_kunena', true)) {
                return false;
            }

            // Check if Kunena API exists
            $kunena_api = JPATH_ADMINISTRATOR . '/components/com_kunena/api.php';

            if (!is_file($kunena_api)) {
                return false;
            }

            // Load Kunena API
            require_once ($kunena_api);
        }

        return true;
    }

    /**
     * Based on Matias' version (Thanks)
     * See: http://docs.kunena.org/index.php/Developing_Kunena_Router
     */
    protected static function getKunenaMajorVersion()
    {
        static $version;

        if (!$version) {
            if (class_exists('KunenaForum')) {
                $version = KunenaForum::versionMajor();
            } elseif (class_exists('Kunena')) {
                $version = substr(Kunena::version(), 0, 3);
            } elseif (is_file(JPATH_ROOT.'/components/com_kunena/lib/kunena.defines.php')) {
                $version = '1.5';
            } elseif (is_file(JPATH_ROOT.'/components/com_kunena/lib/kunena.version.php')) {
                $version = '1.0';
            }
        }

        return $version;
    }

    protected static function getTablePrefix()
    {
        $version = self::getKunenaMajorVersion();

        if ($version <= 1.5) {
            return '#__fb';
        }

        return '#__kunena';
    }
}
