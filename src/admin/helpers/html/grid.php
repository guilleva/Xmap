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

JTable::addIncludePath( JPATH_COMPONENT . '/views/sitemaps/tmpl/default.php' );


/**
 * @package       OSMap
 * @subpackage    com_osmap
 */
abstract class OSMapGrid
{
    public static function enabled($value, $i, $taskTrue, $taskFalse, $prefix='sitemaps.', $property = 'enabled', $img1 = 'tick.png', $img0 = 'publish_x.png')
    {
        if (is_object($value))
        {
            $value = $value->$property;
        }

        $img = $value ? $img1 : $img0;
                
        $task = $value ? $taskTrue : $taskFalse;
        $alt = $value ? JText::_('JPUBLISHED') : JText::_('JUNPUBLISHED');
        $action = $value ? JText::_('JLIB_HTML_UNPUBLISH_ITEM') : JText::_('JLIB_HTML_PUBLISH_ITEM');

        return '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $prefix . $task . '\')" title="' . $action . '">'
            . JHtml::_('image', 'admin/' . $img, $alt, null, true) . '</a>';
    }
}
