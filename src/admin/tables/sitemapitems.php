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
}
