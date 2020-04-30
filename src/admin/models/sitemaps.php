<?php
/**
 * @package   OSMap
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2020 Joomlashack.com. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSMap.
 *
 * OSMap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSMap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSMap.  If not, see <http://www.gnu.org/licenses/>.
 */

use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

class OSMapModelSitemaps extends JModelList
{
    public function __construct($config = array())
    {
        $config['filter_fields'] = array(
            'published',
            'default',
            'sitemap.published',
            'sitemap.name',
            'sitemap.id'
        );

        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select('sitemap.*')
            ->from('#__osmap_sitemaps sitemap');

        // Filter by publishing state
        $published = $this->getState('filter.published', '');

        if ($published != '*') {
            if ($published != '') {
                $query->where('sitemap.published = ' . $db->quote($published));
            } else {
                $query->where('sitemap.published >= 0');
            }
        } else {
            $query->where('(sitemap.published = 0 OR sitemap.published = 1)');
        }

        // Filter by default state
        $default = $this->getState('filter.default');
        if ($default != '') {
            $query->where('sitemap.is_default = ' . (int)$default);
        }

        $search = $this->getState('filter.search');
        if (!is_null($search)) {
            $query->where('sitemap.name LIKE ' . $db->quote('%' . $search . '%'));
        }

        $ordering  = $this->getState('list.ordering');
        $direction = $this->getState('list.direction');
        $query->order($ordering . ' ' . $direction);

        return $query;
    }

    protected function populateState($ordering = 'sitemap.id', $direction = 'asc')
    {
        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published');
        $this->setState('filter.published', $published);

        $default = $this->getUserStateFromRequest($this->context . '.filter.default', 'filter_default');
        $this->setState('filter.default', $default);

        parent::populateState($ordering, $direction);
    }

    public function getItems()
    {
        if ($items = parent::getItems()) {
            $siteApp = JApplicationCms::getInstance('site');
            $menus   = $siteApp->getMenu()->getItems('component', 'com_osmap');
            foreach ($items as $item) {
                $item->menuIdList = array();
                foreach ($menus as $menu) {
                    $view  = empty($menu->query['view']) ? null : $menu->query['view'];
                    $mapId = empty($menu->query['id']) ? null : $menu->query['id'];

                    if ($mapId == $item->id
                        && in_array($menu->query['view'], array('html', 'xml'))
                        && empty($item->menuIdList[$view])
                    ) {
                        $item->menuIdList[$view] = $menu->id;
                    }
                }
            }
        }

        return $items;
    }

    /**
     * Publish/Unpublish method
     *
     * @param int[] $pks
     * @param int   $value
     *
     * @return  bool
     */
    public function publish($pks, $value = 1)
    {
        $db = $this->getDbo();

        $pks = array_filter(array_map('intval', $pks));

        $query = $db->getQuery(true)
            ->set('published = ' . $db->quote($value))
            ->update('#__osmap_sitemaps')
            ->where(sprintf('id IN (%s)', join(',', $pks)));

        return $db->setQuery($query)->execute();
    }

    /**
     * @param int[] $ids
     *
     * @return bool
     * @throws Exception
     */
    public function delete($ids)
    {
        $ids = ArrayHelper::toInteger($ids);
        $db  = $this->getDbo();

        $query = $db->getQuery(true)
            ->delete('#__osmap_sitemaps')
            ->where(sprintf('id IN (%s)', join(',', $ids)));

        if ($db->setQuery($query)->execute()) {
            JFactory::getApplication()->enqueueMessage('SITEMAPS: ' . $db->getAffectedRows());
            $relatedTables = array(
                '#__osmap_sitemap_menus',
                '#__osmap_items_settings'
            );

            foreach ($relatedTables as $table) {
                $db->setQuery(
                    $db->getQuery(true)
                        ->delete($table)
                        ->where('sitemap_id NOT IN (SELECT id FROM #__osmap_sitemaps)')
                )->execute();
                JFactory::getApplication()->enqueueMessage($table . ':: ' . $db->getAffectedRows());
            }

            return true;
        }

        return false;
    }
}
