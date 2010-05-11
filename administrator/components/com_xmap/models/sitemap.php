<?php
/**
 * @version        $Id$
 * @copyright    Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Article model.
 *
 * @package        Joomla.Administrator
 * @subpackage    com_content
 */
class XmapModelSitemap extends JModelAdmin
{
    protected $_context = 'com_xmap';

    /**
     * Constructor.
     *
     * @param    array An optional associative array of configuration settings.
     * @see        JController
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->_item = 'sitemap';
        $this->_option = 'com_xmap';
    }
    
    /**
     * Method to auto-populate the model state.
     */
    protected function _populateState()
    {
        $app = JFactory::getApplication('administrator');

        // Load the User state.
        if (!($pk = (int) $app->getUserState('com_xmap.edit.sitemap.id'))) {
            $pk = (int) JRequest::getInt('id');
        }
        $this->setState('sitemap.id', $pk);

        // Load the parameters.
        $params    = JComponentHelper::getParams('com_xmap');
        $this->setState('params', $params);
    }

    /**
     * Returns a Table object, always creating it.
     *
     * @param    type    The table type to instantiate
     * @param    string    A prefix for the table class name. Optional.
     * @param    array    Configuration array for model. Optional.
     * @return    JTable    A database object
    */
    public function getTable($type = 'Sitemap', $prefix = 'XmapTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get a single record.
     *
     * @param    integer    The id of the primary key.
     *
     * @return    mixed    Object on success, false on failure.
     */
    public function &getItem($pk = null)
    {
        // Initialise variables.
        $pk = (!empty($pk)) ? $pk : (int)$this->getState('sitemap.id');
        $false    = false;

        // Get a row instance.
        $table = &$this->getTable();

        // Attempt to load the row.
        $return = $table->load($pk);

        // Check for a table object error.
        if ($return === false && $table->getError()) {
            $this->setError($table->getError());
            return $false;
        }

        // Prime required properties.
        if (empty($table->id))
        {
            // Prepare data for a new record.
        }

        // Convert to the JObject before adding other data.
        $value = JArrayHelper::toObject($table->getProperties(1), 'JObject');

        // Convert the params field to an array.
        $registry = new JRegistry;
        $registry->loadJSON($table->attribs);
        $value->attribs = $registry->toArray();

        return $value;
    }

    /**
     * Method to get the record form.
     *
     * @return    mixed    JForm object on success, false on failure.
     * @since    1.6
     */
    public function getForm()
    {
        // Initialise variables.
        $app    = JFactory::getApplication();

        // Get the form.
        $form = parent::getForm('com_xmap.sitemap', 'sitemap', array('control' => 'jform'));

        // Check for an error.
        if (JError::isError($form)) {
            $this->setError($form->getMessage());
            return false;
        }

        // Check the session for previously entered form data.
        $data = $app->getUserState('com_xmap.edit.sitemap.data', array());

        // Bind the form data if present.
        if (!empty($data)) {
            $form->bind($data);
        }

        return $form;
    }

    /**
     * Method to save the form data.
     *
     * @param    array    The form data.
     * @return    boolean    True on success.
     * @since    1.6
     */
    public function save($data)
    {
        // Initialise variables;
        $dispatcher = JDispatcher::getInstance();
        $table        = $this->getTable();
        $pk            = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('sitemap.id');
        $isNew        = true;

        // Load the row if saving an existing record.
        if ($pk > 0) {
            $table->load($pk);
            $isNew = false;
        }

        // Bind the data.
        if (!$table->bind($data)) {
            $this->setError(JText::sprintf('JERROR_TABLE_BIND_FAILED', $table->getError()));
            return false;
        }

        // Prepare the row for saving
        $this->_prepareTable($table);

        // Check the data.
        if (!$table->check()) {
            $this->setError($table->getError());
            return false;
        }

        if (!$table->is_default) {
            // Check if there is no default sitemap. Then, set it as default if not
            $query = 'SELECT COUNT(id) FROM `#__xmap_sitemap` where is_default=1'.($table->id? ' AND id<>'.$table->id:'');
            $this->_db->setQuery($query);
            $result = $this->_db->loadResult();
            if (!$result) {
                $table->is_default=1;
            }
        }

        // Store the data.
        if (!$table->store()) {
            $this->setError($table->getError());
            return false;
        }

        if ($table->is_default) {
            $query = 'UPDATE `#__xmap_sitemap` set is_default=0 where id <> '.$table->id;
            $this->_db->setQuery($query);
            if (!$this->_db->query()) {
                $this->setError($table->_db->getErrorMsg());
                return false;
            }
        }

        // Clean the cache.
        $cache = JFactory::getCache('com_xmap');
        $cache->clean();

        $this->setState('sitemap.id', $table->id);

        return true;
    }

    /**
     * Prepare and sanitise the table prior to saving.
     */
    protected function _prepareTable(&$table)
    {
        // TODO.
    }

    
    function _orderConditions($table = null)
    {
        $condition = array();
        return $condition;
    }
}