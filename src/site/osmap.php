<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap;

defined('_JEXEC') or die();

jimport('legacy.controller.legacy');

// Load the frontend language
JFactory::getLanguage()->load('com_osmap', JPATH_SITE . '/components/com_osmap');

require_once JPATH_ADMINISTRATOR . '/components/com_osmap/include.php';

$task = JFactory::getApplication()->input->getCmd('task');

$controller = JControllerLegacy::getInstance('OSMap');
$controller->execute($task);
$controller->redirect();
