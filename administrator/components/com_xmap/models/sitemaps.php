<?php
/**
 * @version       $Id$
 * @copyright     Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @author        Guillermo Vargas (guille@vargas.co.cr)
 */
// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');
jimport('joomla.database.query');

/**
 * Sitemaps Model Class
 *
 * @package         Xmap
 * @subpackage      com_xmap
 * @since           2.0
 */
class XmapModelSitemaps extends JModelList
{
    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see      JController
     * @since    1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'alias', 'a.alias',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'catid', 'a.catid', 'category_title',
                'state', 'a.state',
                'access', 'a.access', 'access_level',
                'created', 'a.created',
                'created_by', 'a.created_by',
                'ordering', 'a.ordering',
                'featured', 'a.featured',
                'language', 'a.language',
                'hits', 'a.hits',
                'publish_up', 'a.publish_up',
                'publish_down', 'a.publish_down',
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * @since       2.0
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) {
            $this->context .= '.'.$layout;
        }

        $access = $this->getUserStateFromRequest($this->context.'.filter.access', 'filter_access', 0, 'int');
        $this->setState('filter.access', $access);

        $published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        // List state information.
        parent::populateState('a.title', 'asc');
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string      $id A prefix for the store id.
     *
     * @return  string      A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':'.$this->getState('filter.search');
        $id .= ':'.$this->getState('filter.access');
        $id .= ':'.$this->getState('filter.published');

        return parent::getStoreId($id);
    }

    /**
     * @param       boolean True to join selected foreign information
     *
     * @return      string
     */
    protected function getListQuery($resolveFKs = true)
    {
        $db     = $this->getDbo();
        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
                $this->getState(
                          'list.select',
                          'a.*')
        );
        $query->from('#__xmap_sitemap AS a');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $query->where('a.access = ' . (int) $access);
        }

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('a.state = ' . (int) $published);
        } else if ($published === '') {
            $query->where('(a.state = 0 OR a.state = 1)');
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = '.(int) substr($search, 3));
            }
            else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(a.title LIKE '.$search.' OR a.alias LIKE '.$search.')');
            }
        }

        // Add the list ordering clause.
        $query->order($db->escape($this->state->get('list.ordering', 'a.title')) . ' ' . $db->escape($this->state->get('list.direction', 'ASC')));
        //echo nl2br(str_replace('#__','jos_',$query));
        return $query;
    }

    public function getExtensionsMessage()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('e.*');
        $query->from($db->quoteName('#__extensions'). 'AS e');
        $query->join('INNER', '#__extensions AS p ON e.element=p.element and p.enabled=0 and p.type=\'plugin\' and p.folder=\'xmap\'');
        $query->where('e.type=\'component\' and e.enabled=1');

        $db->setQuery($query);
        $extensions = $db->loadObjectList();
        if ( count($extensions) ) {
            $sep = $extensionsNameList = '';
            foreach ($extensions as $extension) {
                $extensionsNameList .= "$sep$extension->element";
                $sep = ', ';
            }

            return JText::sprintf('XMAP_MESSAGE_EXTENSIONS_DISABLED',$extensionsNameList);
        } else {
            return "";
        }
    }

}
