<?php
/**
 * @version     $Id$
 * @copyright   Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Guillermo Vargas (guille@vargas.co.cr)
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table as JTable; 	 
use Joomla\CMS\HTML\HTMLHelper as JHTML;

JTable::addIncludePath( JPATH_COMPONENT . '/tables' );
JHTML::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_xmap' . DS . 'helpers' .DS . 'html');

/**
 * @package       Xmap
 * @subpackage    com_xmap
 */
abstract class HTMLHelperXmap
{

    /**
     * @param    string  $name
     * @param    string  $value
     * @param    int     $j
     */
    public static function priorities($name, $value = '0.5', $j)
    {
        // Array of options
        for ($i=0.1; $i<=1;$i+=0.1) {
            $options[] = JHTML::_('select.option',$i,$i);;
        }
        return JHtml::_('select.genericlist', $options, $name, null, 'value', 'text', $value, $name.$j);
    }

    /**
     * @param    string  $name
     * @param    string  $value
     * @param    int     $j
     */
    public static function changefrequency($name, $value = 'weekly', $j)
    {
        // Array of options
        $options[] = JHTML::_('select.option','hourly','hourly');
        $options[] = JHTML::_('select.option','daily','daily');
        $options[] = JHTML::_('select.option','weekly','weekly');
        $options[] = JHTML::_('select.option','monthly','monthly');
        $options[] = JHTML::_('select.option','yearly','yearly');
        $options[] = JHTML::_('select.option','never','never');
        return JHtml::_('select.genericlist', $options, $name, null, 'value', 'text', $value, $name.$j);
    }
}


if (!JHTML::isRegistered('xmap.priorities')) {
    JHTML::register('xmap.priorities', ['HTMLHelperXmap', 'priorities']);
}

if (!JHTML::isRegistered('xmap.changefrequency')) {
    JHTML::register('xmap.changefrequency', ['HTMLHelperXmap', 'priorities']);
}
