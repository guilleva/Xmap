<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap;

defined('_JEXEC') or die();


class OSMapModelSitemaps extends JModelList
{
    public function __construct($config = array())
    {
        $config['filter_fields'] = array(
            'access', 'sitemap.code',
            'published', 'sitemap.published'
        );

        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select('sitemap.*')
            ->from('#__osmap_sitemap sitemap');

        $published = $this->getState('filter.published');
        if ($published != '') {
            $query->where('sitemap.published = ' . $db->quote($published));
        }

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $query->where('a.access = ' . (int) $access);
        }

        $listOrder = $this->getState('list.ordering', 'sitemap.id');
        $listDir   = $this->getState('list.direction', 'ASC');
        $query->order($listOrder . ' ' . $listDir);

        return $query;
    }

    protected function populateState($ordering = null, $direction = null)
    {
        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published');
        $this->setState('filter.published', $published);

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
        $this->setState('filter.access', $access);

        parent::populateState('sitemap.id', 'ASC');
    }
}
