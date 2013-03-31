<?php
/**
* @author Martin Herbst - http://www.mherbst.de
* @email webmaster@mherbst.de
* @package Xmap
* @license GNU/GPL
* @description Xmap plugin for K2 component
*
* Changes:
* + 0.51   2009/08/21  Do not show deleted items resp. categories
* + 0.60   2009/08/21  New options "Show K2 Items" added
* # 0.65   2009/09/28  Correct modification date now shown in XML sitemap
* # 0.66   2009/10/07  Small bugfix to avoid PHP Notice:  Undefined variable
* # 0.67   2010/01/30  Small bugfix to avoid PHP warnings in case of null returned from queries
* + 0.80   2010/02/07  Support of new features of K2 2.2
* + 0.81   2010/02/19  Modified date was not correct for all items
* + 0.85   2010/04/11  New option to avoid duplicate items
*                      Change the date format if used together with SEFServiceMap
* # 0.86   2010/05/24  Expired items are no longer contained in the site map
* # 0.86   2010/05/24  Expired items are no longer contained in the site map
*                      Warnings regarding undefined properties solved
* # 0.90   2010/08/14  User rights are now taken into account (reported by http://walplanet.com)
* # 0.91   2010/08/21  Bugfix: wrong SQL statement created
* # 0.92   2010/10/13  Fixed a bug if last users or last categories has no entries
* + 0.93   2010/11/28  Add support for Google News sitemap
* # 0.94   2011/02/13  Small bugfix to avoid PHP warning
* # 0.95   2011/08/13  Bugfixes regarding empty categories and invalid SQL statements
* + 1.00   2011/09/22  Support of Joomla 1.7 and K2 2.5
* # 1.01   2011/09/27  XML sitemap did not show K2 items
* # 1.05   2011/11/02  Fixed some problems with menu items pointing to multiple categories
* # 1.06   2011/11/03  Fixed a bug with empty arrays
* # 1.07   2011/11/11  Follow subcategories did not work as expected
* # 1.2    2013/01/31  Comatiable with joomla 3.0 and k2 2.6.3 - Mohammad Hasani Eghtedar (m.h.eghtedar@gmail.com)
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

/** Adds support for K2  to Xmap */
class xmap_com_k2
{
    static $maxAccess = 0;
    static $suppressDups = false;
    static $suppressSub = false;

    /** Get the content tree for this kind of content */
    static function getTree( &$xmap, &$parent, &$params )
    {
        $tag=null;
        $limit=null;
        $id = null;
        $link_query = parse_url( $parent->link );
        parse_str( html_entity_decode($link_query['query']), $link_vars);
        $parm_vars = $parent->params->toArray();

        $option = xmap_com_k2::getParam($link_vars,'option',"");
        if ($option != "com_k2")
            return;

        $view = xmap_com_k2::getParam($link_vars,'view',"");
        $showMode = xmap_com_k2::getParam($params, 'showk2items', "always");

        if ($showMode == "never" || ($showMode == "xml" && $xmap->view == "html") || ($showMode == "html" && $xmap->view == "xml"))
            return;
        self::$suppressDups = (xmap_com_k2::getParam($params,'suppressdups', 'yes') == "yes");
        self::$suppressSub = (xmap_com_k2::getParam($params,'subcategories',"yes") != "yes");
            
        if ($view == "item")   // for Items the sitemap already contains the correct reference
        {
            if (!isset($xmap->IDS))
                $xmap->IDS = "";
            $xmap->IDS = $xmap->IDS."|".xmap_com_k2::getParam($link_vars, 'id', $id);
            return;
        }

        if ($xmap->view == "xml")
            self::$maxAccess = 1;   // XML sitemaps will only see content for guests
        else
            self::$maxAccess = implode(",", JFactory::getUser()->getAuthorisedViewLevels());

        switch(xmap_com_k2::getParam($link_vars,'task',""))
        {
            case "user":
                $tag = xmap_com_k2::getParam($link_vars, 'id', $id);
                $ids = array_key_exists('userCategoriesFilter',$parm_vars) ? $parm_vars['userCategoriesFilter'] : array("");
                $mode = "single user";
                break;
            case "tag":
                $tag = xmap_com_k2::getParam($link_vars, 'tag',"");
                $ids = array_key_exists('categoriesFilter',$parm_vars) ? $parm_vars['categoriesFilter'] : array("");
                $mode = "tag";
                break;
            case "category":
                $ids = explode("|", xmap_com_k2::getParam($link_vars, 'id',""));
                $mode = "category";
                break;
            case "":
                switch(xmap_com_k2::getParam($link_vars,'layout',""))
                {
                    case "category":
                        if(array_key_exists('categories', $parm_vars)) $ids = $parm_vars["categories"];
                        else $ids = '';
                        $mode = "categories";
                        break;
                    case "latest":
                        $limit = xmap_com_k2::getParam($parm_vars, 'latestItemsLimit', "");
                        if (xmap_com_k2::getParam($parm_vars, 'source', "") == "0")
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
        $priority = xmap_com_k2::getParam($params,'priority',$parent->priority);
        $changefreq = xmap_com_k2::getParam($params,'changefreq',$parent->changefreq);
        if ($priority == '-1')
            $priority = $parent->priority;
        if ($changefreq == '-1')
            $changefreq = $parent->changefreq;

        $params['priority'] = $priority;
        $params['changefreq'] = $changefreq;

        $db = JFactory::getDBO();
        xmap_com_k2::processTree($db, $xmap, $parent, $params, $mode, $ids, $tag, $limit);

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
            xmap_com_k2::collectByCat($db, $row->id, $allrows);
        }
    }

    static function processTree($db, &$xmap, &$parent, &$params, $mode, $ids, $tag, $limit)
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
                            xmap_com_k2::collectByCat($db, $id, $allrows);
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

        $xmap->changeLevel(1);
        $node = new stdclass ();
        $node->id = $parent->id;

        if ($rows == null)
        {
            $rows = array();
        }
        foreach ($rows as $row )
        {
            if (!(self::$suppressDups && isset($xmap->IDS) && strstr($xmap->IDS, "|".$row->id)))
                xmap_com_k2::addNode($xmap, $node, $row, false, $parent, $params);
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
                if (!isset($xmap->IDS))
                        $xmap->IDS = "";
                if (!(self::$suppressDups && strstr($xmap->IDS, "|c".$row->id)))
                {
                    xmap_com_k2::addNode($xmap, $node, $row, true, $parent, $params);
                    $newID = array();
                    $newID[0] = $row->id;
                    xmap_com_k2::processTree($db, $xmap, $parent, $params, $mode, $newID, "", "");
                }
            }
        }
        $xmap->changeLevel (-1);
    }

    static function addNode($xmap, $node, $row, $iscat, &$parent, &$params)
    {
        $sef = ($_REQUEST['option'] == "com_sefservicemap"); // verallgemeinern

        if ($xmap->isNews && ($row->modified ? $row->modified : $row->created) > ($xmap->now - (2 * 86400)))
        {
            $node->newsItem = 1;
            $node->keywords = $row->metakey;
        }
        else
        {
            $node->newsItem = 0;
            $node->keywords = "";
        }
        if (!isset($xmap->IDS))
            $xmap->IDS = "";

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
            $xmap->IDS .= "|c".$row->id;
            $node->link = 'index.php?option=com_k2&view=itemlist&task=category&id='.$row->id.':'.$row->alias.'&Itemid='.$parent->id;
            $node->expandible = true;
        }
        else
        {
            $xmap->IDS .= "|".$row->id;
            $node->link = 'index.php?option=com_k2&view=item&id='.$row->id.':'.$row->alias.'&Itemid='.$parent->id;
            $node->expandible = false;
        }
        $node->tree = array ();
        $xmap->printNode($node);

    }

    static function &getParam($arr, $name, $def)
    {
        $var = JArrayHelper::getValue( $arr, $name, $def, '' );
        return $var;
    }

}
?>