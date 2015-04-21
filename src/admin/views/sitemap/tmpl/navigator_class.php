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

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_COMPONENT_SITE.'/displayer.php');

class OSMapNavigatorDisplayer extends OSMapDisplayer {

    function __construct(&$config, &$sitemap) {
        $this->_list=array();
        $this->view='navigator';

        parent::__construct( $config, $sitemap);
    }

    function printNode( &$node ) {
        if (!isset($node->selectable )) {
            $node->selectable=true;
        }
        // For extentions that doesn't set this property as this is new in OSMap 1.2.3
        if (!isset($node->expandible )) {
            $node->expandible = true;
        }
        if ( empty($this->_list[$node->uid]) ) { // Avoid duplicated items
            $this->_list[$node->uid] = $node;
        }
        return false;
    }

    function &expandLink(&$parent)    {
        $items = &JSite::getMenu();
        $extensions = &$this->_extensions;
        $rows = null;
        if (strpos($parent->link,'-menu-') === 0 ) {
            $menutype = str_replace('-menu-','',$parent->link);
            // Get Menu Items
            $rows = $items->getItems('menutype', $menutype);
        } elseif ($parent->id) {
            $rows = $items->getItems('parent_id', $parent->id);
        }

        if ( $rows ) {
            foreach ($rows as $item) {
                if ($item->parent_id == $parent->id) {
                    $node = new stdclass;
                    $node->name = $item->title;
                    $node->id   = $item->id;
                    $node->uid  = 'itemid'.$item->id;
                    $node->link = $item->link;
                    $node->expandible = true;
                    $node->selectable=true;

                    // Prepare the node link
                    if (false === OSMapHelper::prepareMenuItem($node)) {
                        continue;
                    }

                    if ( $item->home ) {
                        $node->link = JURI::root();
                    } elseif (substr($item->link,0,9) == 'index.php' && $item->type != 'url' ) {
                        if ($item->type == 'menulink') {// For Joomla 1.5 SEF compatibility
                            $params = new JParameter($item->params);
                            $node->link     = 'index.php?Itemid=' . $params->get('menu_item');
                        } elseif ( strpos($item->link,'Itemid=') === FALSE ){
                            $node->link     = 'index.php?Itemid=' . $node->id;
                        }
                    } elseif ($item->type == 'separator') {
                        $node->selectable=false;
                    }
                    $this->printNode($node);  // Add to the internal list
                }
            }

        }

        if ($parent->id) {
            $option = null;

            if (preg_match('#^/?index.php.*option=(com_[^&]+)#', $parent->link, $matches)) {
                $option = $matches[1];
            }

            $Itemid = JRequest::getInt('Itemid');

            if (!$option && $Itemid) {
                $item = $items->getItem($Itemid);
                $link_query = parse_url($item->link);
                parse_str( html_entity_decode($link_query['query']), $link_vars);

                $option = JArrayHelper::getValue($link_vars,'option','');

                if ($option) {
                    $parent->link = $item->link;
                }
            }

            if ($option && !empty($extensions[$option])) {
                $plugin = $extensions[$option];

                $methodParams = array(&$this, &$node, &$plugin->params);

                $result = Alledia\Framework\Helper::callMethod($plugin->className, 'getTree', $methodParams);

                $parent->uid = $option;
            }
        }

        return $this->_list;
    }

    function &getParam($arr, $name, $def) {
        $var = JArrayHelper::getValue($arr, $name, $def, '');

        return $var;
    }
}
