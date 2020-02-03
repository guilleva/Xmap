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

defined('_JEXEC') or die();

JTable::addIncludePath(JPATH_COMPONENT . '/tables');

abstract class JHtmlOSMap
{
    /**
     * @param string $name
     * @param string $selected
     * @param int    $j
     *
     * @return string
     */
    public static function priorities($name, $selected = '0.5', $j = 0)
    {
        $options = array();
        foreach (static::priorityList() as $priority) {
            $options[] = JHTML::_('select.option', $priority, $priority);
        }

        return JHtml::_('select.genericlist', $options, $name, null, 'value', 'text', $selected, $name . $j);
    }

    /**
     * @param string $name
     * @param string $selected
     * @param int    $j
     *
     * @return string
     */
    public static function changefrequency($name, $selected = 'weekly', $j = 0)
    {
        $options = array();
        foreach (static::frequencyList() as $value => $text) {
            $options[] = JHTML::_('select.option', $value, $text);
        }

        return JHtml::_('select.genericlist', $options, $name, null, 'value', 'text', $selected, $name . $j);
    }

    /**
     * @return float[]
     */
    public static function priorityList()
    {
        $priorities = array();
        for ($i = 0.1; $i <= 1; $i += 0.1) {
            $priorities[] = sprintf('%03.1f', $i);
        }

        return $priorities;
    }

    /**
     * @return string[]
     */
    public static function frequencyList()
    {
        $frequencies = array(
            'always'  => JText::_('COM_OSMAP_ALWAYS'),
            'hourly'  => JText::_('COM_OSMAP_HOURLY'),
            'daily'   => JText::_('COM_OSMAP_DAILY'),
            'weekly'  => JText::_('COM_OSMAP_WEEKLY'),
            'monthly' => JText::_('COM_OSMAP_MONTHLY'),
            'yearly'  => JText::_('COM_OSMAP_YEARLY'),
            'never'   => JText::_('COM_OSMAP_NEVER')
        );

        return $frequencies;
    }
}
