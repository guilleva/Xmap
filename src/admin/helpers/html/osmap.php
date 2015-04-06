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

JTable::addIncludePath( JPATH_COMPONENT . '/tables' );


/**
 * @package       OSMap
 * @subpackage    com_osmap
 */
abstract class JHtmlOSMap
{

    /**
     * @param    string  $name
     * @param    string  $value
     * @param    int     $j
     */
    public static function priorities($name, $value = '0.5', $j)
    {
        // Array of options
        for ($i = 0.1; $i <= 1; $i += 0.1) {
            $options[] = JHTML::_('select.option', $i, $i);
        }

        return JHtml::_('select.genericlist', $options, $name, null, 'value', 'text', $value, $name . $j);
    }

    /**
     * @param    string  $name
     * @param    string  $value
     * @param    int     $j
     */
    public static function changefrequency($name, $value = 'weekly', $j)
    {
        // Array of options
        $options[] = JHTML::_('select.option', 'hourly', 'hourly');
        $options[] = JHTML::_('select.option', 'daily', 'daily');
        $options[] = JHTML::_('select.option', 'weekly', 'weekly');
        $options[] = JHTML::_('select.option', 'monthly', 'monthly');
        $options[] = JHTML::_('select.option', 'yearly', 'yearly');
        $options[] = JHTML::_('select.option', 'never', 'never');

        return JHtml::_('select.genericlist', $options, $name, null, 'value', 'text', $value, $name . $j);
    }
}
