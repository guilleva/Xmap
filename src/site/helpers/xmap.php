<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Alledia\OSMap;

/**
 * OSMap Component Controller
 *
 * @package        OSMap
 * @subpackage     com_osmap
 */
if (!class_exists('XmapHelper')) {
    class XMapHelper extends OSMap\Helper\General
    {
    }
}
