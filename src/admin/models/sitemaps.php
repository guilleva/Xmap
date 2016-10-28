<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap;

defined('_JEXEC') or die();


class OSMapModelSitemaps extends JModelList
{
    public function __construct($config = array())
    {
        $config['filter_fields'] = array(
            'published', 'sitemap.published',
            'default', 'sitemap.default'
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
            $query->where('sitemap.is_default = ' . (int) $default);
        }

        $search = $this->getState('filter.search');
        if (!is_null($search)) {
            $query->where('sitemap.name LIKE ' . $db->quote('%' . $search . '%'));
        }

        $listOrder = $this->getState($this->context . '.list.ordering', 'sitemap.id');
        $listDir   = $this->getState($this->context . '.list.direction', 'ASC');
        $query->order($listOrder . ' ' . $listDir);

        return $query;
    }

    protected function populateState($ordering = null, $direction = null)
    {
        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published');
        $this->setState('filter.published', $published);

        $default = $this->getUserStateFromRequest($this->context . '.filter.default', 'filter_default');
        $this->setState('filter.default', $default);

        parent::populateState('sitemap.id', 'ASC');
    }

    public function getItems()
    {
        $items = parent::getItems();

        // For each item, check if there is a menu setup to change the url
        $db = $this->getDbo();

        foreach ($items as &$item) {
            $query = $db->getQuery(true)
                ->select('id')
                ->select('link')
                ->from('#__menu')
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
                ->where('published = 1')
                ->where('(
                    link LIKE ' . $db->quote('index.php?option=com_osmap&view=xml&id=' . $item->id)
                    . ' OR link LIKE ' . $db->quote('index.php?option=com_osmap&view=html&id=' . $item->id) . ')');
            $menus = $db->setQuery($query)->loadObjectList();

            if (!empty($menus)) {
                $item->menuIdList= array();

                foreach ($menus as $menu) {
                    preg_match('#view=(xml|html)#', $menu->link, $matches);

                    // Check if we already found a menu for the view type
                    if (isset($matches[1]) && !isset($item->menuIdList[$matches[1]])) {
                        // Stores the menu id for the link
                        $item->menuIdList[$matches[1]] = $menu->id;
                    }
                }
            }
        }

        return $items;
    }

    /**
     * Method to change the published state of one or more records.
     *
     * @param   array    &$pks   A list of the primary keys to change.
     * @param   integer  $value  The value of the published state.
     *
     * @return  boolean  True on success.
     *
     * @since   12.2
     */
    public function publish(&$pks, $value = 1)
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->set('published = ' . $db->quote($value))
            ->update('#__osmap_sitemaps')
            ->where('id IN (' . implode(',', $pks) . ')');

        return $db->setQuery($query)->execute();
    }
}
