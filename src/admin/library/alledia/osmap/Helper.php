<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap;


abstract class Helper
{
    /**
     * Build the submenu in admin if needed. Triggers the
     * onAdminSubmenu event for component addons to attach
     * their own admin screens.
     *
     * The expected response must be an array
     * [
     *    "text" => Static language string,
     *    "link" => Link to the screen
     *    "view" => unique view name
     * ]
     *
     * @param $vName
     *
     * @return void
     */
    public static function addSubmenu($vName)
    {
        \JHtmlSidebar::addEntry(
            \JText::_('COM_OSMAP_SUBMENU_SITEMAPS'),
            'index.php?option=com_osmap&view=sitemaps',
            $vName == 'sitemaps'
        );

        \JHtmlSidebar::addEntry(
            \JText::_('COM_OSMAP_SUBMENU_EXTENSIONS'),
            'index.php?option=com_plugins&view=plugins&filter_folder=osmap',
            $vName == 'extensions'
        );
    }
}
