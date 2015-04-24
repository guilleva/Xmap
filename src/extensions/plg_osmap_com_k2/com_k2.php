<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2015 Open Source Training, LLC. All rights reserved..
 * @author    Guillermo Vargas <guille@vargas.co.cr>
 * @author    Mohammad Hasani Eghtedar <m.h.eghtedar@gmail.com>
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

/** Adds support for K2  to OSMap */
class osmap_com_k2
{
    static $maxAccess = 0;
    static $suppressDups = false;
    static $suppressSub = false;

    /** Get the content tree for this kind of content */
    static function getTree( &$osmap, &$parent, &$params )
    {
        $tag=null;
        $limit=null;
        $id = null;
        $link_query = parse_url( $parent->link );
        parse_str( html_entity_decode($link_query['query']), $link_vars);
        $parm_vars = $parent->params->toArray();

        $option = osmap_com_k2::getParam($link_vars,'option',"");
        if ($option != "com_k2")
            return;

        $view = osmap_com_k2::getParam($link_vars,'view',"");
        $showMode = osmap_com_k2::getParam($params, 'showk2items', "always");

        if ($showMode == "never" || ($showMode == "xml" && $osmap->view == "html") || ($showMode == "html" && $osmap->view == "xml"))
            return;
        self::$suppressDups = (osmap_com_k2::getParam($params,'suppressdups', 'yes') == "yes");
        self::$suppressSub = (osmap_com_k2::getParam($params,'subcategories',"yes") != "yes");

        if ($view == "item")   // for Items the sitemap already contains the correct reference
        {
            if (!isset($osmap->IDS))
                $osmap->IDS = "";
            $osmap->IDS = $osmap->IDS."|".osmap_com_k2::getParam($link_vars, 'id', $id);
            return;
        }

        if ($osmap->view == "xml")
            self::$maxAccess = 1;   // XML sitemaps will only see content for guests
        else
            self::$maxAccess = implode(",", JFactory::getUser()->getAuthorisedViewLevels());

        switch(osmap_com_k2::getParam($link_vars,'task',""))
        {
            case "user":
                $tag = osmap_com_k2::getParam($link_vars, 'id', $id);
                $ids = array_key_exists('userCategoriesFilter',$parm_vars) ? $parm_vars['userCategoriesFilter'] : array("");
                $mode = "single user";
                break;
            case "tag":
                $tag = osmap_com_k2::getParam($link_vars, 'tag',"");
                $ids = array_key_exists('categoriesFilter',$parm_vars) ? $parm_vars['categoriesFilter'] : array("");
                $mode = "tag";
                break;
            case "category":
                $ids = explode("|", osmap_com_k2::getParam($link_vars, 'id',""));
                $mode = "category";
                break;
            case "":
                switch(osmap_com_k2::getParam($link_vars,'layout',""))
                {
                    case "category":
                        if(array_key_exists('categories', $parm_vars)) $ids = $parm_vars["categories"];
                        else $ids = '';
                        $mode = "categories";
                        break;
                    case "latest":
                        $limit = osmap_com_k2::getParam($parm_vars, 'latestItemsLimit', "");
                        if (osmap_com_k2::getParam($parm_vars, 'source', "") == "0")
                        {
                            $ids = array_key_exists("userIDs",$parm_vars) ? $parm_vars["userIDs"] : '';
                            $mode = "latest user";
                        }
                        else
                        {
                            $ids = array_key_exists("categoryIDs",$parm_vars) ? $parm_vars["categoryIDs"] : '';
                            $mode = "latest category";
                        }
                        break;
                    default:
                        return;
                }
                break;
            default:
                return;
        }
        $priority = osmap_com_k2::getParam($params,'priority',$parent->priority);
        $changefreq = osmap_com_k2::getParam($params,'changefreq',$parent->changefreq);
        if ($priority == '-1')
            $priority = $parent->priority;
        if ($changefreq == '-1')
            $changefreq = $parent->changefreq;

        $params['priority'] = $priority;
        $params['changefreq'] = $changefreq;

        $db = JFactory::getDBO();
        osmap_com_k2::processTree($db, $osmap, $parent, $params, $mode, $ids, $tag, $limit);

        return;
    }

    static function collectByCat($db, $catid, &$allrows)
    {
        if (trim($catid) == "") // in this case something strange went wrong
            return;
        $query = "select id,title,alias,UNIX_TIMESTAMP(created) as created, UNIX_TIMESTAMP(modified) as modified, metakey from #__k2_items where "
                ."published = 1 and trash = 0 and (publish_down = \"0000-00-00\" OR publish_down > NOW()) "
                ."and catid = ".$catid. " order by 1 desc";
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if ($rows != null)
            $allrows = array_merge($allrows, $rows);
        $query = "select id, name, alias  from #__k2_categories where published = 1 and trash = 0 and parent = ".$catid." order by id";
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if ($rows == null)
            $rows = array();

        foreach ($rows as $row)
        {
            osmap_com_k2::collectByCat($db, $row->id, $allrows);
        }
    }

    static function processTree($db, &$osmap, &$parent, &$params, $mode, $ids, $tag, $limit)
    {
        $baseQuery = "select id,title,alias,UNIX_TIMESTAMP(created) as created, UNIX_TIMESTAMP(modified) as modified, metakey from  #__k2_items where "
                    ."published = 1 and trash = 0 and (publish_down = \"0000-00-00\" OR publish_down > NOW()) and "
                    ."access in (".self::$maxAccess.") and ";

        switch($mode)
        {
            case "single user":
                $query = $baseQuery."created_by = ".$tag." ";
                if ($ids[0] != "")
                    $query .= " and catid in (".implode(",", $ids).")";
                $query .= " order by 1 DESC ";
                $db->setQuery($query);
                $rows = $db->loadObjectList();
                break;
            case "tag":
                $query = "SELECT c.id, title, alias, UNIX_TIMESTAMP(c.created) as created, UNIX_TIMESTAMP(c.modified) as modified FROM #__k2_tags a, #__k2_tags_xref b, #__k2_items c where "."c.published = 1 and c.trash = 0 and (c.publish_down = \"0000-00-00\" OR c.publish_down > NOW()) "
                         ."and a.Name = '".$tag."' and a.id =  b.tagId and c.id = b.itemID and c.access in (".self::$maxAccess.")";
                if ($ids[0] != "")
                    $query .= " and c.catid in (".implode(",", $ids).")";
                $query .= " order by 1 DESC ";
                $db->setQuery($query);
                $rows = $db->loadObjectList();
                break;
            case "category":
                $query = $baseQuery."catid = ".$ids[0]." order by 1 DESC ";
                $db->setQuery($query);
                $rows = $db->loadObjectList();
                break;
            case "categories":
                if (!self::$suppressSub)
                {
                    if($ids) $query = $baseQuery."catid in (".implode(",", $ids).") order by 1 DESC ";
                    else $query = $baseQuery."1 order by 1 DESC ";
                    $db->setQuery($query);
                    $rows = $db->loadObjectList ();
                }
                else
                {
                    $rows = array();
                    if (is_array($ids))
                    {
                        foreach($ids as $id)
                        {
                            $allrows = array();
                            osmap_com_k2::collectByCat($db, $id, $allrows);
                            $rows = array_merge($rows, $allrows);
                        }
                    }
                }
                break;
            case "latest user":
                $rows = array();
                if (is_array($ids))
                {
                    foreach ($ids as $id)
                    {
                        $query = $baseQuery."created_by = ".$id." order by 1 DESC LIMIT ".$limit;
                        $db->setQuery($query);
                        $res = $db->loadObjectList();
                        if ($res != null)
                            $rows = array_merge($rows, $res);
                    }
                }
                break;
            case "latest category":
                $rows = array();
                if (is_array($ids))
                {
                    foreach ($ids as $id)
                    {
                        $query = $baseQuery."catid = ".$id." order by 1 DESC LIMIT ".$limit;
                        $db->setQuery($query);
                        $res = $db->loadObjectList();
                        if ($res != null)
                            $rows = array_merge($rows, $res);
                    }
                }
                break;
            default:
                return;
        }

        $osmap->changeLevel(1);
        $node = new stdclass ();
        $node->id = $parent->id;

        if ($rows == null)
        {
            $rows = array();
        }
        foreach ($rows as $row )
        {
            if (!(self::$suppressDups && isset($osmap->IDS) && strstr($osmap->IDS, "|".$row->id)))
                osmap_com_k2::addNode($osmap, $node, $row, false, $parent, $params);
        }

        if ($mode == "category" && !self::$suppressSub)
        {
            $query = "select id, name, alias  from #__k2_categories where published = 1 and trash = 0 and parent = ".$ids[0]
                    ." and access in (".self::$maxAccess.") order by id";
            $db->setQuery($query);
            $rows = $db->loadObjectList();
            if ($rows == null)
            {
                $rows = array();
            }

            foreach ($rows as $row)
            {
                if (!isset($osmap->IDS))
                        $osmap->IDS = "";
                if (!(self::$suppressDups && strstr($osmap->IDS, "|c".$row->id)))
                {
                    osmap_com_k2::addNode($osmap, $node, $row, true, $parent, $params);
                    $newID = array();
                    $newID[0] = $row->id;
                    osmap_com_k2::processTree($db, $osmap, $parent, $params, $mode, $newID, "", "");
                }
            }
        }
        $osmap->changeLevel (-1);
    }

    static function addNode($osmap, $node, $row, $iscat, &$parent, &$params)
    {
        $sef = ($_REQUEST['option'] == "com_sefservicemap"); // verallgemeinern

        if ($osmap->isNews && ($row->modified ? $row->modified : $row->created) > ($osmap->now - (2 * 86400)))
        {
            $node->newsItem = 1;
            $node->keywords = $row->metakey;
        }
        else
        {
            $node->newsItem = 0;
            $node->keywords = "";
        }
        if (!isset($osmap->IDS))
            $osmap->IDS = "";

        $node->browserNav = $parent->browserNav;
        $node->pid = $row->id;
        $node->uid = $parent->uid . 'item' . $row->id;
        if (isset($row->modified) || isset($row->created))
            $node->modified = (isset($row->modified) ? $row->modified : $row->created);

        if ($sef)
            $node->modified = date('Y-m-d',$node->modified);

        $node->name = ($iscat ? $row->name : $row->title);

        $node->priority = $params['priority'];
        $node->changefreq = $params['changefreq'];

        if ($iscat)
        {
            $osmap->IDS .= "|c".$row->id;
            $node->link = 'index.php?option=com_k2&view=itemlist&task=category&id='.$row->id.':'.$row->alias.'&Itemid='.$parent->id;
            $node->expandible = true;
        }
        else
        {
            $osmap->IDS .= "|".$row->id;
            $node->link = 'index.php?option=com_k2&view=item&id='.$row->id.':'.$row->alias.'&Itemid='.$parent->id;
            $node->expandible = false;
        }
        $node->tree = array ();
        $osmap->printNode($node);

    }

    static function &getParam($arr, $name, $def)
    {
        $var = JArrayHelper::getValue( $arr, $name, $def, '' );
        return $var;
    }

}
