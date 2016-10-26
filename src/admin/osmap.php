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

require_once JPATH_COMPONENT_ADMINISTRATOR . '/include.php';

// Access check
if (!OSMap\Factory::getUser()->authorise('core.manage', 'com_osmap')) {
    throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
}

$input = OSMap\Factory::getApplication()->input;

$controller = OSMap\Controller\Base::getInstance('OSMap');
$controller->execute($input->getCmd('task'));
$controller->redirect();
