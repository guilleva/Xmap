<?php
/**
 * @version             $Id$
 * @copyright           Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/** Handles Mosets Tree component */
class xmap_com_mtree
{
    static function getTree( $xmap, $parent, &$params )
    {
        if ($xmap->isNews) // This component does not provide news content. don't waste time/resources
            return false;

        $db = JFactory::getDbo();

        $catid=0;
        if ( strpos($parent->link, 'task=listcats') ) {
            $link_query = parse_url( $parent->link );
            parse_str( html_entity_decode($link_query['query']), $link_vars);
            $catid = JArrayHelper::getValue($link_vars,'cat_id',0);
        }

        $include_links = JArrayHelper::getValue($params,'include_links',1);
        $include_links = ( $include_links == 1
            || ( $include_links == 2 && $xmap->view == 'xml')
            || ( $include_links == 3 && $xmap->view == 'html')
            ||   $xmap->view == 'navigator');
        $params['include_links'] = $include_links;

        $priority = JArrayHelper::getValue($params,'cat_priority',$parent->priority);
        $changefreq = JArrayHelper::getValue($params,'cat_changefreq',$parent->changefreq);
        if ($priority  == '-1')
            $priority = $parent->priority;
        if ($changefreq  == '-1')
            $changefreq = $parent->changefreq;

        $params['cat_priority'] = $priority;
        $params['cat_changefreq'] = $changefreq;

        $priority = JArrayHelper::getValue($params,'link_priority',$parent->priority);
        $changefreq = JArrayHelper::getValue($params,'link_changefreq',$parent->changefreq);
        if ($priority  == '-1')
            $priority = $parent->priority;

        if ($changefreq  == '-1')
            $changefreq = $parent->changefreq;

        $params['link_priority'] = $priority;
        $params['link_changefreq'] = $changefreq;

        $ordering = JArrayHelper::getValue($params,'cats_order','cat_name');
        $orderdir = JArrayHelper::getValue($params,'cats_orderdir','ASC');
        if ( !in_array($ordering,array('ordering','cat_name','cat_created')) )
            $ordering = 'cat_name';

        if ( !in_array($orderdir,array('ASC','DESC')) ){
            $orderdir = 'ASC';
        }

        $params['cats_order'] = $db->quoteName($ordering)." $orderdir";

        if ( $include_links ) {
            $ordering = JArrayHelper::getValue($params,'links_order','ordering');
            $orderdir = JArrayHelper::getValue($params,'links_orderdir','ASC');
            if ( !in_array($ordering,array('ordering','link_name','link_modified','link_created','link_hits')) )
                $ordering = 'ordering';

            if ( !in_array($orderdir,array('ASC','DESC')) ){
                $orderdir = 'ASC';
            }

            $params['links_order'] = $db->quoteName($ordering)." $orderdir";

            $params['limit'] = '';
            $params['days'] = '';
            $limit = JArrayHelper::getValue($params,'max_links',0);
            if ( intval($limit) )
                $params['limit'] = ' LIMIT '.intval($limit);

            $days = JArrayHelper::getValue($params,'max_age','');
            if ( intval($days) )
                $params['days'] = ' AND a.link_created >=\''.date('Y-m-d H:i:s',($xmap->now - ($days*86400))) ."' ";
        }

        xmap_com_mtree::getMtreeCategory($xmap,$parent,$params,$catid);
    }

    /* Returns URLs of all Categories and links in of one category using recursion */
    static function getMtreeCategory ($xmap, $parent, &$params, $catid )
    {
        $database =& JFactory::getDbo();

        $query = "SELECT cat_name, cat_id ".
             "FROM #__mt_cats WHERE cat_published='1' AND cat_approved='1' AND cat_parent = $catid " .
             "ORDER BY " . $params['cats_order'];

        $database->setQuery($query);
        $rows = $database->loadObjectList();

        $xmap->changeLevel(1);
        foreach($rows as $row) {
            $node = new stdclass;
            $node->name = $row->cat_name;
            $node->link = 'index.php?option=com_mtree&task=listcats&cat_id='.$row->cat_id.'&Itemid='.$parent->id;
            $node->id = $parent->id;
            $node->uid = $parent->uid .'c'.$row->cat_id;
            $node->browserNav = $parent->browserNav;
            $node->modified = NULL;
            $node->priority = $params['cat_priority'];
            $node->changefreq = $params['cat_changefreq'];
            $node->expandible = true;
            $node->secure = $parent->secure;

            if ( $xmap->printNode($node) !== FALSE) {
                xmap_com_mtree::getMtreeCategory($xmap,$parent,$params,$row->cat_id);
            }
        }

        /* Returns URLs of all listings in the current category */
        if ($params['include_links']) {
            $query = " SELECT a.link_name, a.link_id, a.link_created as created, a.link_modified as modified \n".
                 " FROM #__mt_links AS a, #__mt_cl as b \n".
                 " WHERE a.link_id = b.link_id \n".
                             " AND b.cat_id = $catid " .
                             " AND ( link_published='1' AND link_approved='1' ) " .
                 $params['days'] .
                 " ORDER BY " . $params['links_order'] .
                 $params['limit'];

            $database->setQuery($query);

            $rows = $database->loadObjectList();

            foreach($rows as $row) {
                if ( !$row->modified || ($row->modified == $database->getNullDate())) {
                    $row->modified = $row->created;
                }

                $node = new stdclass;
                $node->name = $row->link_name;
                $node->link = 'index.php?option=com_mtree&task=viewlink&link_id='.$row->link_id.'&Itemid='.$parent->id;
                $node->id = $parent->id;
                $node->uid = $parent->uid.'l'.$row->link_id;
                $node->browserNav = $parent->browserNav;
                $node->modified = $row->modified;
                $node->priority = $params['link_priority'];
                $node->changefreq = $params['link_changefreq'];
                $node->expandible = false;
                $node->secure = $parent->secure;
                $xmap->printNode($node);
            }
        }
        $xmap->changeLevel(-1);

    }
}
