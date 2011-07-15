<?php

/**
 * @author Guillermo Vargas, http://joomla.vargas.co.cr
 * @email guille@vargas.co.cr
 * @version $Id: com_kunena.php 30 2011-04-09 23:38:58Z guille $
 * @package Xmap
 * @license GNU/GPL
 * @description Xmap plugin for Kunena Forum Component.
 */

/** Handles Kunena forum structure */
class xmap_com_kunena {
    /*
     * This function is called before a menu item is printed. We use it to set the
     * proper uniqueid for the item
     */

    static $profile;
    static $config;

    function prepareMenuItem(&$node, &$params)
    {
        $link_query = parse_url($node->link);
        parse_str(html_entity_decode($link_query['query']), $link_vars);
        $catid = intval(JArrayHelper::getValue($link_vars, 'catid', 0));
        $id = intval(JArrayHelper::getValue($link_vars, 'id', 0));
        $func = JArrayHelper::getValue($link_vars, 'func', '', '');
        if ($func = 'showcat' && $catid) {
            $node->uid = 'com_kunenac' . $catid;
            $node->expandible = false;
        } elseif ($func = 'view' && $id) {
            $node->uid = 'com_kunenaf' . $id;
            $node->expandible = false;
        }
    }

    function getTree(&$xmap, &$parent, &$params)
    {

        // Make sure that we can load the kunena api
        if (!xmap_com_kunena::loadKunenaApi()) {
            return false;
        }

        if (!self::$profile) {
            self::$config = KunenaFactory::getConfig ();;
            self::$profile = KunenaFactory::getUser ();
        }

        $user = JFactory::getUser();
        $catid = 0;

        $link_query = parse_url($parent->link);
        if (!isset($link_query['query'])) {
            return;
        }

        parse_str(html_entity_decode($link_query['query']), $link_vars);
        $view = JArrayHelper::getValue($link_vars, 'view', '');

        switch ($view){
            case 'showcat':
                $link_query = parse_url($parent->link);
                parse_str(html_entity_decode($link_query['query']), $link_vars);
                $catid = JArrayHelper::getValue($link_vars, 'catid', 0);
                break;
            case 'entrypage':
                $catid = 0;
                break;
            default:
                return true;   // Do not expand links to posts
        }

        $include_topics = JArrayHelper::getValue($params, 'include_topics', 1);
        $include_topics = ( $include_topics == 1
                || ( $include_topics == 2 && $xmap->view == 'xml')
                || ( $include_topics == 3 && $xmap->view == 'html')
                || $xmap->view == 'navigator');
        $params['include_topics'] = $include_topics;

        $priority = JArrayHelper::getValue($params, 'cat_priority', $parent->priority);
        $changefreq = JArrayHelper::getValue($params, 'cat_changefreq', $parent->changefreq);
        if ($priority == '-1')
            $priority = $parent->priority;
        if ($changefreq == '-1')
            $changefreq = $parent->changefreq;

        $params['cat_priority'] = $priority;
        $params['cat_changefreq'] = $changefreq;
        $params['groups'] = implode(',', $user->authorisedLevels());

        $priority = JArrayHelper::getValue($params, 'topic_priority', $parent->priority);
        $changefreq = JArrayHelper::getValue($params, 'topic_changefreq', $parent->changefreq);
        if ($priority == '-1')
            $priority = $parent->priority;

        if ($changefreq == '-1')
            $changefreq = $parent->changefreq;

        $params['topic_priority'] = $priority;
        $params['topic_changefreq'] = $changefreq;

        if ($include_topics) {
            $ordering = JArrayHelper::getValue($params, 'topics_order', 'ordering');
            $params['topics_order'] = 'modified desc';
            $params['include_pagination'] = ($xmap->view == 'xml');

            $params['limit'] = '';
            $params['days'] = '';
            $limit = JArrayHelper::getValue($params, 'max_topics', '');
            if (intval($limit))
                $params['limit'] = ' LIMIT ' . $limit;

            $days = JArrayHelper::getValue($params, 'max_age', '');
            if (intval($days))
                $params['days'] = ' AND time >=' . ($xmap->now - ($days * 86400)) . " ";
        }

        xmap_com_kunena::getCategoryTree($xmap, $parent, $params, $catid);
    }

    /* Return category/forum tree */

    function getCategoryTree(&$xmap, &$parent, &$params, $parentCat)
    {
        $database = & JFactory::getDBO();

        $kunenaSession = KunenaFactory::getSession();
        $kunenaSession->updateAllowedForums();
        $catlist=$kunenaSession->allowed;

        $list = array();
        $query = "SELECT id as cat_id, name as cat_title, ordering FROM #__kunena_categories WHERE parent=$parentCat AND published=1 and id in ({$catlist}) ORDER BY name";
        $database->setQuery($query);
        $cats = $database->loadObjectList();

        /* get list of categories */
        $xmap->changeLevel(1);
        foreach ($cats as $cat) {
            $node = new stdclass;
            $node->id = $parent->id;
            $node->browserNav = $parent->browserNav;
            $node->uid = $parent->uid . 'c' . $cat->cat_id;
            $node->name = $cat->cat_title;
            $node->priority = $params['cat_priority'];
            $node->changefreq = $params['cat_changefreq'];
            $node->link = 'index.php?option=com_kunena&func=showcat&catid=' . $cat->cat_id;
            $node->expandible = true;
            if ($xmap->printNode($node) !== FALSE) {
                xmap_com_kunena::getCategoryTree($xmap, $parent, $params, $cat->cat_id);
            }
        }

        if ($params['include_topics']) {
            $access = KunenaFactory::getAccessControl();
            $hold = $access->getAllowedHold(self::$profile, $parentCat);

            $query = "SELECT t.id, t.catid as cat_id, t.subject as forum_name, max(m.time) as modified, count(m.id) as msgcount " .
                    "FROM #__kunena_messages AS t " .
                    "INNER JOIN #__kunena_messages AS m ON t.id = m.thread " .
                    "WHERE t.catid=$parentCat " .
                    "AND t.hold in ({$hold}) " .
                    "AND t.parent=0 " .
                    $params['days'] .
                    "GROUP BY m.`thread`" .
                    "ORDER BY " . $params['topics_order'] .
                    $params['limit'];

            $database->setQuery($query);
            #echo str_replace('#__','jos_',$database->getQuery());

            $forums = $database->loadObjectList();

            //get list of forums
            foreach ($forums as $forum) {
                $node = new stdclass;
                $node->id = $parent->id;
                $node->browserNav = $parent->browserNav;
                $node->uid = $parent->uid . 't' . $forum->id;
                $node->name = $forum->forum_name;
                $node->priority = $params['topic_priority'];
                $node->changefreq = $params['topic_changefreq'];
                $node->modified = intval($forum->modified);
                $node->link = 'index.php?option=com_kunena&func=view&catid=' . $forum->cat_id.'&id=' . $forum->id;
                $node->expandible = false;
                if ($xmap->printNode($node) !== FALSE) {
                    if ($params['include_pagination'] && $forum->msgcount > self::$config->messages_per_page ){
                        $msgPerPage = self::$config->messages_per_page;
                        $threadPages = ceil ( $forum->msgcount / $msgPerPage );
                        for ($i=2;$i<=$threadPages;$i++) {
                            $subnode = new stdclass;
                            $subnode->id = $node->id;
                            $subnode->uid = $node->uid.'p'.$i;
                            $subnode->name = "[$i]";
                            $subnode->seq = $i;
                            $subnode->link = $node->link.'&limit='.$msgPerPage.'&limitstart='.(($i-1)*$msgPerPage);
                            $subnode->browserNav = $node->browserNav;
                            $subnode->priority = $node->priority;
                            $subnode->changefreq = $node->changefreq;
                            $subnode->modified = $node->modified;
                            $xmap->printNode($subnode);
                        }
                    }
                }
            }
        }
        $xmap->changeLevel(-1);
    }

    private static function loadKunenaApi()
    {
        if (!defined('KUNENA_LOADED')) {
            jimport ( 'joomla.application.component.helper' );
            // Check if Kunena component is installed/enabled
            if (! JComponentHelper::isEnabled ( 'com_kunena', true )) {
                    return false;
            }

            // Check if Kunena API exists
            $kunena_api = JPATH_ADMINISTRATOR . '/components/com_kunena/api.php';
            if (! is_file ( $kunena_api ))
                    return false;

            // Load Kunena API
            require_once ($kunena_api);
        }
        return true;
    }

}