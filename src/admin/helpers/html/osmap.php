<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
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
