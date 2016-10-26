<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Component;

defined('_JEXEC') or die();


abstract class Helper extends \JComponentHelper
{
    public static function getParams($option = 'com_osmap', $strict = false)
    {
        return parent::getParams($option, $strict);
    }

    public static function getComponent($option = 'com_osmap', $strict = false)
    {
        return parent::getComponent($option, $strict);
    }
}
