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
class OSMapTableSitemapItems extends JTable
{
    /**
     * @var int Primary key
     */
    public $sitemap_id = null;

    /**
     * @var string
     */
    public $uid = null;

    /**
     * @var int
     */
    public $published = null;

    /**
     * @var string
     */
    public $changefreq = 'weekly';

    /**
     * @var int
     */
    public $priority = 5;

    /**
     * @param    JDatabase    A database connector object
     */
    public function __construct($db)
    {
        parent::__construct('#__osmap_items_settings', array('sitemap_id', 'uid'), $db);
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
        // The priority is stored as integer in the database to save memory
        if (isset($array['priority'])) {
            $array['priority'] = ((float)$array['priority']) * 10;
        }

        return parent::bind($array, $ignore);
    }
}
