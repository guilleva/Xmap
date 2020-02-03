<?php
/**
 * @package   OSMap
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2020 Joomlashack.com. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSMap.
 *
 * OSMap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSMap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSMap.  If not, see <http://www.gnu.org/licenses/>.
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
        parent::__construct('#__osmap_items_settings', array('sitemap_id', 'uid', 'settings_hash'), $db);
    }
}
