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

/**
 * @package         OSMap
 * @subpackage      com_osmap
 */
class OSMapTableSitemap extends JTable
{
    /**
     * @var int Primary key
     */
    var $id = null;

    /**
     * @var string
     */
    var $title = null;

    /**
     * @var string
     */
    var $introtext = null;

    /**
     * @var string
     */
    var $metakey = null;

    /**
     * @var string
     */
    var $attribs = null;

    /**
     * @var string
     */
    var $menus = null;

    /**
     * @var string
     */
    var $created = null;

    /**
     * @var string
     */
    var $metadesc = null;

    /**
     * @var int
     */
    var $is_default = 0;

    /**
     * @var int
     */
    var $state = 1; //JPUBLISHED's value is 1

    /**
     * @var int
     */
    var $access = 0;

    /**
     * @var int
     */
    var $links_count = 0;

    /**
     * @param    JDatabase    A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__osmap_sitemap', 'id', $db);
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
        if (isset($array['attribs']) && is_array($array['attribs'])) {
            $registry = new JRegistry();
            $registry->loadArray($array['attribs']);
            $array['attribs'] = $registry->toString();
        }

        if (isset($array['selections']) && is_array($array['selections'])) {
            $selections = array();
            foreach ($array['selections'] as $i => $menu) {
                $selections[$menu] = array(
                    'priority'   => $array['selections_priority'][$i],
                    'changefreq' => $array['selections_changefreq'][$i],
                    'ordering'   => $i
                );
            }

            $registry = new JRegistry();
            $registry->loadArray($selections);
            $array['selections'] = $registry->toString();
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
        if (empty($this->title)) {
            $this->setError(JText::_('COM_OSMAP_MSG_SITEMAP_MUST_HAVE_TITLE'));

            return false;
        }

        if (trim(str_replace('-', '', $this->alias)) == '') {
            $datenow = OSMap\Factory::getDate();
            $this->alias = $datenow->format("Y-m-d-H-i-s");
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
        $date = JFactory::getDate();

        if (!$this->id) {
            $this->created = $date->toSql();
        }

        return parent::store($updateNulls);
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
            ->update($this->_db->quoteName('#__osmap_sitemap'))
            ->set($this->_db->quoteName('state').' = '. (int) $state)
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
}
