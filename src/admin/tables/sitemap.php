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
// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * @package         OSMap
 * @subpackage      com_osmap
 * @since           2.0
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
    var $alias = null;

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
    var $selections = null;

    /**
     * @var string
     */
    var $created = null;

    /**
     * @var string
     */
    var $metadesc = null;

    /**
     * @var string
     */
    var $excluded_items = null;

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
    var $count_xml = 0;

    /**
     * @var int
     */
    var $count_html = 0;

    /**
     * @var int
     */
    var $views_xml = 0;

    /**
     * @var int
     */
    var $views_html = 0;

    /**
     * @var int
     */
    var $lastvisit_xml = 0;

    /**
     * @var int
     */
    var $lastvisit_html = 0;


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
            $this->setError(JText::_('Sitemap must have a title'));

            return false;
        }

        if (empty($this->alias)) {
            $this->alias = $this->title;
        }

        $this->alias = JApplication::stringURLSafe($this->alias);

        if (trim(str_replace('-', '', $this->alias)) == '') {
            $datenow = &JFactory::getDate();
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
        $state = (int) $state;

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
