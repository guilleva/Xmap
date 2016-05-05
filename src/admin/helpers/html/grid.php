<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved..
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

JLoader::register('JHtmlGrid', JPATH_LIBRARIES . '/joomla/html/html/grid.php');
/**
 * @package       OSMap
 * @subpackage    com_osmap
 */
abstract class OSMapGrid extends JHtmlGrid
{
    /**
     * Toggles attirbs items on admin view
     *
     * @param    string  $value           Value of current item state.
     * @param    string  $i               The index of the sitemap.
     * @param    string  $taskDisable     If value is true run this task.
     * @param    string  $taskEnable      If value is false run this task.
     * @param    string  $jLegacy         If user has older version of Joomla!.
     * @param    string  $prefix          The controller to run the task.
     * @param    string  $property        If value is an object get its property.
     * @return   string                   Returns the link with the state of the item.
    */
    public static function enabled(
        $value,
        $i,
        $taskDisable,
        $taskEnable,
        $jLegacy = false,
        $prefix = 'sitemaps.',
        $property = 'enabled'
    ) {
        if (is_object($value)) {
            $value = $value->$property;
        }

        $task = $value ? $taskDisable : $taskEnable;
        $alt = $value ? JText::_('COM_OSMAP_ENABLED') : JText::_('COM_OSMAP_DISABLED');
        $action = $value ? JText::_('COM_OSMAP_DISABLE_TOOLTIP') : JText::_('COM_OSMAP_ENABLE_TOOLTIP');

        if ($jLegacy) {
            $classes = "";
            $title = 'title="' . $action . '"';
            $img = $value ? 'tick.png' : 'publish_x.png';
            $img = JHtml::_('image', 'admin/' . $img, $alt, null, true);
        } else {
            $classes = $value ? 'btn btn-micro active hasTooltip' : 'btn btn-micro hasTooltip';
            $title = 'data-original-title="' . $action . '"';
            $img = $value ? 'icon-publish' : 'icon-unpublish';
            $img = '<span class="' . $img . '"></span>';
        }

        return '<a class="' . $classes . '" href="#" onclick="return listItemTask(\'cb' . $i . '\',\''
            . $prefix . $task . '\')"' . $title . '>'
            . $img . '</a>';
    }
}
