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

/**
 * @package         OSMap
 * @subpackage      com_osmap
 */
class OSMapTableSitemap extends JTable
{
    /**
     * @var int Primary key
     */
    public $id = null;

    /**
     * @var string
     */
    public $name = null;

    /**
     * @var string
     */
    public $params = null;

    /**
     * @var string
     */
    public $created_on = null;

    /**
     * @var int
     */
    public $is_default = 0;

    /**
     * @var int
     */
    public $published = 1; //JPUBLISHED's value is 1

    /**
     * @var int
     */
    public $links_count = 0;

    /**
     * @var array
     */
    public $menus = array();

    /**
     * @var array
     */
    public $menus_priority = array();

    /**
     * @var array
     */
    public $menus_changefreq = array();

    /**
     * @var string
     */
    public $menus_ordering = '';

    /**
     * @param    JDatabase    A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__osmap_sitemaps', 'id', $db);
    }

    /**
     * Overloaded bind function
     *
     * @access      public
     * @param       array $hash named array
     * @return      null|string  null is operation was satisfactory, otherwise returns an error
     * @see         JTable:bind
     * @since       2.0
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['params']) && is_array($array['params'])) {
            $registry = new JRegistry();
            $registry->loadArray($array['params']);
            $array['params'] = $registry->toString();
        }

        if (isset($array['metadata']) && is_array($array['metadata'])) {
            $registry = new JRegistry();
            $registry->loadArray($array['metadata']);
            $array['metadata'] = $registry->toString();
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Overloaded check function
     *
     * @access      public
     * @return      boolean
     * @see         JTable::check
     * @since       2.0
     */
    public function check()
    {
        if (empty($this->name)) {
            $this->setError(JText::_('COM_OSMAP_MSG_SITEMAP_MUST_HAVE_NAME'));

            return false;
        }

        return true;
    }

    /**
     * Overriden JTable::store to set modified data and user id.
     *
     * @param       boolean True to update fields even if they are null.
     * @return      boolean True on success.
     * @since       2.0
     */
    public function store($updateNulls = false)
    {
        $db   = OSMap\Factory::getDbo();
        $date = JFactory::getDate();

        if (!$this->id) {
            $this->created_on = $date->toSql();
        }

        // Make sure we have only one default sitemap
        if ((bool)$this->is_default) {
            // Set as not default any other sitemap
            $query = $db->getQuery(true)
                ->update('#__osmap_sitemaps')
                ->set('is_default = 0');
            $db->setQuery($query)->execute();
        } else {
            // Check if we have another default sitemap. If not, force this as default
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from('#__osmap_sitemaps')
                ->where('is_default = 1')
                ->where('id <> ' . $db->quote($this->id));
            $count = (int)$db->setQuery($query)->loadResult();

            if ($count === 0) {
                // Force as default
                $this->is_default = 1;

                OSMap\Factory::getApplication()->enqueueMessage(
                    JText::_('COM_OSMAP_MSG_SITEMAP_FORCED_AS_DEFAULT'),
                    'info'
                );
            }
        }

        // Get the menus
        $menus           = $this->menus;
        $menusPriority   = $this->menus_priority;
        $menusChangeFreq = $this->menus_changefreq;
        $menusOrdering   = explode(',', $this->menus_ordering);

        unset($this->menus, $this->menus_priority, $this->menus_changefreq, $this->menus_ordering);

        // Store the sitemap data
        $result = parent::store($updateNulls);

        if ($result) {
            // Remove the current menus
            $this->removeMenus();

            if (!empty($menus)) {
                $ordering = 0;

                // Store the menus for this sitemap
                foreach ($menus as $menuId) {
                    // Get the index of the selected menu in the ordering array
                    $index = array_search('menu_' . $menuId, $menusOrdering);

                    $query = $db->getQuery(true)
                        ->insert('#__osmap_sitemap_menus')
                        ->set('sitemap_id = ' . $db->quote($this->id))
                        ->set('menutype_id = ' . $db->quote($menuId))
                        ->set('priority = ' . $db->quote($menusPriority[$index]))
                        ->set('changefreq = ' . $db->quote($menusChangeFreq[$index]))
                        ->set('ordering = ' . $ordering);
                    $db->setQuery($query)->execute();

                    $ordering++;
                }
            }
        }

        return $result;
    }

    /**
     * Method to set the publishing state for a row or list of rows in the database
     * table.
     *
     * @param       mixed   An optional array of primary key values to update.  If not
     *                      set the instance property value is used.
     * @param       integer The publishing state. eg. [0 = unpublished, 1 = published]
     * @param       integer The user id of the user performing the operation.
     * @return      boolean True on success.
     * @since       2.0
     */
    public function publish($pks = null, $state = 1, $userId = 0)
    {
        // Initialize variables.
        $k = $this->_tbl_key;

        // Sanitize input.
        JArrayHelper::toInteger($pks);
        $userId = (int) $userId;
        $state  = (int) $state;

        // If there are no primary keys set check to see if the instance key is set.
        if (empty($pks)) {
            if ($this->$k) {
                $pks = array($this->$k);
            } else {
                // Nothing to set publishing state on, return false.
                $this->setError(JText::_('NO_ROWS_SELECTED'));

                return false;
            }
        }

        // Build the WHERE clause for the primary keys.
        $where = $k . '=' . implode(' OR ' . $k . '=', $pks);

        // Update the publishing state for rows with the given primary keys.
        $query =  $this->_db->getQuery(true)
            ->update($this->_db->quoteName('#__osmap_sitemaps'))
            ->set($this->_db->quoteName('state') . ' = ' . (int) $state)
            ->where($where);

        $this->_db->setQuery($query);
        $this->_db->query();

        // Check for a database error.
        if ($this->_db->getErrorNum()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // If the JTable instance value is in the list of primary keys that were set, set the instance.
        if (in_array($this->$k, $pks)) {
            $this->state = $state;
        }

        $this->setError('');

        return true;
    }

    /**
     * Remove all the menus for the given sitemap
     *
     * @param int $sitemapId
     *
     * @return void
     */
    public function removeMenus()
    {
        if (!empty($this->id)) {
            $db    = OSMap\Factory::getDbo();
            $query = $db->getQuery(true)
                ->delete('#__osmap_sitemap_menus')
                ->where('sitemap_id = ' . $db->quote($this->id));
            $db->setQuery($query)->execute();
        }
    }

    /**
     * Method to load a row from the database by primary key and bind the fields to the JTable instance properties.
     *
     * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.
     *                           If not set the instance property value is used.
     * @param   boolean  $reset  True to reset the default values before loading the new row.
     *
     * @return  boolean  True if successful. False if row not found.
     *
     * @since   11.1
     * @throws  InvalidArgumentException
     * @throws  RuntimeException
     * @throws  UnexpectedValueException
     */
    public function load($keys = null, $reset = true)
    {
        $success = parent::load($keys, $reset);

        if ($success) {
            // Load the menus information
            $db       = OSMap\Factory::getDbo();
            $ordering = array();

            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__osmap_sitemap_menus')
                ->where('sitemap_id = ' . $db->quote($this->id))
                ->order('ordering');
            $menusRows = $db->setQuery($query)->loadObjectList();

            if (!empty($menusRows)) {
                foreach ($menusRows as $menu) {
                    $this->menus[]            = $menu->menutype_id;
                    $this->menus_priority[]   = $menu->priority;
                    $ordering[]               = 'menu_' . $menu->menutype_id;
                    $this->menus_changefreq[] = $menu->changefreq;
                }
            }

            $this->menus_ordering = implode(',', $ordering);
        }

        return $success;
    }
}
