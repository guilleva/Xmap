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

jimport('joomla.application.component.modeladmin');

/**
 * Sitemap model.
 *
 * @package       OSMap
 * @subpackage    com_osmap
 */
class OSMapModelSitemap extends JModelAdmin
{
    protected $_context = 'com_osmap';

    /**
     * Constructor.
     *
     * @param    array An optional associative array of configuration settings.
     * @see      JController
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->_item   = 'sitemap';
        $this->_option = 'com_osmap';
    }

    /**
     * Method to auto-populate the model state.
     */
    protected function _populateState()
    {
        $app = JFactory::getApplication('administrator');

        // Load the User state.
        if (!($pk = (int) $app->getUserState('com_osmap.edit.sitemap.id'))) {
            $pk = (int) JRequest::getInt('id');
        }
        $this->setState('sitemap.id', $pk);

        // Load the parameters.
        $params = JComponentHelper::getParams('com_osmap');
        $this->setState('params', $params);
    }

    /**
     * Returns a Table object, always creating it.
     *
     * @param    type                The table type to instantiate
     * @param    string              A prefix for the table class name. Optional.
     * @param    array               Configuration array for model. Optional.
     * @return   OSMapTableSitemap    A database object
    */
    public function getTable($type = 'Sitemap', $prefix = 'OSMapTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get a single record.
     *
     * @param    integer    The id of the primary key.
     *
     * @return   mixed      Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        // Initialise variables.
        $pk = (!empty($pk)) ? $pk : (int)$this->getState('sitemap.id');
        $false = false;

        // Get a row instance.
        $table = $this->getTable();

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
        $value = $table->getProperties(1);
        $value = JArrayHelper::toObject($value, 'JObject');

        // Convert the params field to an array.
        $registry = new JRegistry;
        $registry->loadString($table->attribs);
        $value->attribs = $registry->toArray();

        return $value;
    }

    /**
     * Method to get the record form.
     *
     * @param    array      $data        Data for the form.
     * @param    boolean    $loadData    True if the form is to load its own data (default case), false if not.
     * @return   mixed                   A JForm object on success, false on failure
     * @since    2.0
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_osmap.sitemap', 'sitemap', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     * @since    1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_osmap.edit.sitemap.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
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
        $table      = $this->getTable();
        $pk         = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('sitemap.id');
        $isNew      = true;

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
            $result = $this->getDefaultSitemapId();
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
            $query = $this->_db->getQuery(true)
               ->update($this->_db->quoteName('#__osmap_sitemap'))
               ->set($this->_db->quoteName('is_default').' = 0')
               ->where($this->_db->quoteName('id').' <> '.$table->id);

            $this->_db->setQuery($query);

            if (!$this->_db->query()) {
                $this->setError($table->_db->getErrorMsg());

                return false;
            }
        }

        // Clean the cache.
        $cache = JFactory::getCache('com_osmap');
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

    protected function _orderConditions($table = null)
    {
        $condition = array();

        return $condition;
    }

    public function setDefault($id)
    {
        $table = $this->getTable();
        if ($table->load($id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__osmap_sitemap'))
                ->set($db->quoteName('is_default').' = 0')
                ->where($db->quoteName('id').' <> '.$table->id);
            $this->_db->setQuery($query);

            if (!$this->_db->query()) {
                $this->setError($table->_db->getErrorMsg());

                return false;
            }

            $table->is_default = 1;
            $table->store();

            // Clean the cache.
            $cache = JFactory::getCache('com_osmap');
            $cache->clean();

            return true;
        }
    }

    /**
     * Override to avoid warnings
     *
     */
    public function checkout($pk = null)
    {
        return true;
    }

    private function getDefaultSitemapId()
    {
        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('id');
        $query->from($db->quoteName('#__osmap_sitemap'));
        $query->where('is_default=1');
        $db->setQuery($query);

        return $db->loadResult();
    }
}
