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

/**
 * OSMap component helper.
 *
 * @package     OSMap
 * @subpackage  com_osmap
 * @since       2.0
 */
class OSMapHelper
{
    /**
     * Configure the Linkbar.
     *
     * @param    string  The name of the active view.
     */
    public static function addSubmenu($vName)
    {
        $version = new JVersion;

        if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
            JSubMenuHelper::addEntry(
                JText::_('OSMAP_SUBMENU_SITEMAPS'),
                'index.php?option=com_osmap',
                $vName == 'sitemaps'
            );
            JSubMenuHelper::addEntry(
                JText::_('OSMAP_SUBMENU_EXTENSIONS'),
                'index.php?option=com_plugins&view=plugins&filter_folder=osmap',
                $vName == 'extensions');
        } else {
            JHtmlSidebar::addEntry(
                JText::_('OSMAP_SUBMENU_SITEMAPS'),
                'index.php?option=com_osmap',
                $vName == 'sitemaps'
            );
            JHtmlSidebar::addEntry(
                JText::_('OSMAP_SUBMENU_EXTENSIONS'),
                'index.php?option=com_plugins&view=plugins&filter_folder=osmap',
                $vName == 'extensions');
        }
    }
}
