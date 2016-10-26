<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JTable::addIncludePath(JPATH_COMPONENT . '/tables');


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
    public static function priorities($name, $value = '0.5', $j = 0)
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
    public static function changefrequency($name, $value = 'weekly', $j = 0)
    {
        // Array of options
        $options[] = JHTML::_('select.option', 'always', JText::_('COM_OSMAP_ALWAYS'));
        $options[] = JHTML::_('select.option', 'hourly', JText::_('COM_OSMAP_HOURLY'));
        $options[] = JHTML::_('select.option', 'daily', JText::_('COM_OSMAP_DAILY'));
        $options[] = JHTML::_('select.option', 'weekly', JText::_('COM_OSMAP_WEEKLY'));
        $options[] = JHTML::_('select.option', 'monthly', JText::_('COM_OSMAP_MONTHLY'));
        $options[] = JHTML::_('select.option', 'yearly', JText::_('COM_OSMAP_YEARLY'));
        $options[] = JHTML::_('select.option', 'never', JText::_('COM_OSMAP_NEVER'));

        return JHtml::_('select.genericlist', $options, $name, null, 'value', 'text', $value, $name . $j);
    }
}
