<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2015 Alledia.com, All rights reserved.
 * @author    Guillermo Vargas <guille@vargas.co.cr>
 * @author    Alledia <support@alledia.com>
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 *
 * This file is part of OSMap.
 *
 * OSMap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
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

$includePath = __DIR__ . '/admin/library/Installer/include.php';
if (! file_exists($includePath)) {
    $includePath = __DIR__ . '/library/Installer/include.php';
}

require_once $includePath;

use Alledia\Installer\AbstractScript;

/**
 * OSMap Installer Script
 *
 * @since  2.4.0a1
 */
class Com_OSMapInstallerScript extends AbstractScript
{
    protected $isXmapDataFound = false;

    /**
     * @param string                     $type
     * @param JInstallerAdapterComponent $parent
     *
     * @return void
     */
    public function postFlight($type, $parent)
    {
        $this->isXmapDataFound = $this->lookForXmapData();

        parent::postFlight($type, $parent);
    }

    protected function lookForXmapData()
    {
        $db = JFactory::getDbo();

        // Do we have any Xmap sitemap?
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__xmap_sitemap');
        $db->setQuery($query);
        $total = (int) $db->loadResult();

        return $total > 0;
    }
}
