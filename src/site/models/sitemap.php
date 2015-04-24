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

jimport('joomla.application.component.modelitem');
jimport('joomla.database.query');
require_once(JPATH_COMPONENT . '/helpers/osmap.php');

/**
 * OSMap Component Sitemap Model
 *
 * @package        OSMap
 * @subpackage     com_osmap
 * @since          2.0
 */
class OSMapModelSitemap extends JModelItem
{

    /**
     * Model context string.
     *
     * @var        string
     */
    protected $_context = 'com_osmap.sitemap';
    protected $_extensions = null;

    static $items = array();
    /**
     * Method to auto-populate the model state.
     *
     * @return     void
     */
    protected function populateState()
    {
        $app = JFactory::getApplication('site');

        // Load state from the request.
        $pk = JRequest::getInt('id');

        // If not sitemap specified, select the default one
        if (!$pk) {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('id')->from('#__osmap_sitemap')->where('is_default=1');
            $db->setQuery($query);
            $pk = $db->loadResult();
        }

        $this->setState('sitemap.id', $pk);

        $offset = JRequest::getInt('limitstart');
        $this->setState('list.offset', $offset);

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);

        // TODO: Tune these values based on other permissions.
        $this->setState('filter.published', 1);
        $this->setState('filter.access', true);
    }

    /**
     * Method to get sitemap data.
     *
     * @param    integer    The id of the article.
     *
     * @return   mixed      Menu item data object on success, false on failure.
     */
    public function &getItem($pk = null)
    {
        // Initialize variables.
        $db = $this->getDbo();
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('sitemap.id');

        if ($this->_item === null) {
            $this->_item = array();
        }

        if (!isset($this->_item[$pk])) {
            try {
                $query = $db->getQuery(true);

                $query->select($this->getState('item.select', 'a.*'));
                $query->from('#__osmap_sitemap AS a');

                $query->where('a.id = ' . (int) $pk);

                // Filter by published state.
                $published = $this->getState('filter.published');
                if (is_numeric($published)) {
                    $query->where('a.state = ' . (int) $published);
                }

                // Filter by access level.
                if ($access = $this->getState('filter.access')) {
                    $user = JFactory::getUser();
                    $groups = implode(',', $user->getAuthorisedViewLevels());
                    $query->where('a.access IN (' . $groups . ')');
                }

                $this->_db->setQuery($query);

                $data = $this->_db->loadObject();

                if ($error = $this->_db->getErrorMsg()) {
                    throw new Exception($error);
                }

                if (empty($data)) {
                    throw new Exception(JText::_('COM_OSMAP_ERROR_SITEMAP_NOT_FOUND'));
                }

                // Check for published state if filter set.
                if (is_numeric($published) && $data->state != $published) {
                    throw new Exception(JText::_('COM_OSMAP_ERROR_SITEMAP_NOT_FOUND'));
                }

                // Convert parameter fields to objects.
                $registry = new JRegistry('_default');
                $registry->loadString($data->attribs);
                $data->params = clone $this->getState('params');
                $data->params->merge($registry);

                // Convert the selections field to an array.
                $registry = new JRegistry('_default');
                $registry->loadString($data->selections);
                $data->selections = $registry->toArray();

                // Compute access permissions.
                if ($access) {
                    // If the access filter has been set, we already know this user can view.
                    $data->params->set('access-view', true);
                } else {
                    // If no access filter is set, the layout takes some responsibility for display of limited information.
                    $user = &JFactory::getUser();
                    $groups = $user->authorisedLevels();

                    $data->params->set('access-view', in_array($data->access, $groups));
                }
                // TODO: Type 2 permission checks?

                $this->_item[$pk] = $data;
            } catch (Exception $e) {
                $this->setError($e->getMessage());
                $this->_item[$pk] = false;
            }
        }

        return $this->_item[$pk];
    }

    public function getItems()
    {
        if ($item = $this->getItem()) {
            return OSMapHelper::getMenuItems($item->selections);
        }
        return false;
    }

    function getExtensions()
    {
        return OSMapHelper::getExtensions();
    }

    /**
     * Increment the hit counter for the sitemap.
     *
     * @param    int        Optional primary key of the sitemap to increment.
     *
     * @return   boolean    True if successful; false otherwise and internal error set.
     */
    public function hit($count)
    {
        // Initialize variables.
        $pk = (int) $this->getState('sitemap.id');

        $view = JRequest::getCmd('view', 'html');
        if ($view != 'xml' && $view != 'html') {
            return false;
        }

        $this->_db->setQuery(
            'UPDATE #__osmap_sitemap' .
            ' SET views_' . $view . ' = views_' . $view . ' + 1, count_' . $view . ' = ' . $count . ', lastvisit_' . $view . ' = ' . JFactory::getDate()->toUnix() .
            ' WHERE id = ' . (int) $pk
        );

        if (!$this->_db->query()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        return true;
    }

    public function getSitemapItems($view=null)
    {
        if (!isset($view)) {
            $view = JRequest::getCmd('view');
        }
        $db = JFactory::getDBO();
        $pk = (int) $this->getState('sitemap.id');

        if (self::$items !== NULL && isset(self::$items[$view])) {
            return;
        }
        $query = "select * from #__osmap_items where view='$view' and sitemap_id=" . $pk;
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        self::$items[$view] = array();
        foreach ($rows as $row) {
            self::$items[$view][$row->itemid] = array();
            self::$items[$view][$row->itemid][$row->uid] = array();
            $pairs = explode(';', $row->properties);
            foreach ($pairs as $pair) {
                if (strpos($pair, '=') !== FALSE) {
                    list($property, $value) = explode('=', $pair);
                    self::$items[$view][$row->itemid][$row->uid][$property] = $value;
                }
            }
        }
        return self::$items;
    }

    function chageItemPropery($uid, $itemid, $view, $property, $value)
    {
        $items = $this->getSitemapItems($view);
        $db = JFactory::getDBO();
        $pk = (int) $this->getState('sitemap.id');

        $isNew = false;
        if (empty($items[$view][$itemid][$uid])) {
            $items[$view][$itemid][$uid] = array();
            $isNew = true;
        }
        $items[$view][$itemid][$uid][$property] = $value;
        $sep = $properties = '';
        foreach ($items[$view][$itemid][$uid] as $k => $v) {
            $properties .= $sep . $k . '=' . $v;
            $sep = ';';
        }
        if (!$isNew) {
            $query = 'UPDATE #__osmap_items SET properties=\'' . $db->escape($properties) . "' where uid='" . $db->escape($uid) . "' and itemid=$itemid and view='$view' and sitemap_id=" . $pk;
        } else {
            $query = 'INSERT #__osmap_items (uid,itemid,view,sitemap_id,properties) values ( \'' . $db->escape($uid) . "',$itemid,'$view',$pk,'" . $db->escape($properties) . "')";
        }
        $db->setQuery($query);
        //echo $db->getQuery();exit;
        if ($db->query()) {
            return true;
        } else {
            return false;
        }
    }

    function toggleItem($uid, $itemid)
    {
        $app = JFactory::getApplication('site');
        $sitemap = $this->getItem();

        $displayer = new OSMapDisplayer($app->getParams(), $sitemap);

        $excludedItems = $displayer->getExcludedItems();
        if (isset($excludedItems[$itemid])) {
            $excludedItems[$itemid] = (array) $excludedItems[$itemid];
        }
        if (!$displayer->isExcluded($itemid, $uid)) {
            $excludedItems[$itemid][] = $uid;
            $state = 0;
        } else {
            if (is_array($excludedItems[$itemid]) && count($excludedItems[$itemid])) {
                $excludedItems[$itemid] = array_filter($excludedItems[$itemid], create_function('$var', 'return ($var != \'' . $uid . '\');'));
            } else {
                unset($excludedItems[$itemid]);
            }
            $state = 1;
        }

        $registry = new JRegistry('_default');
        $registry->loadArray($excludedItems);
        $str = $registry->toString();

        $db = JFactory::getDBO();
        $query = "UPDATE #__osmap_sitemap set excluded_items='" . $db->escape($str) . "' where id=" . $sitemap->id;
        $db->setQuery($query);
        $db->query();
        return $state;
    }

}
