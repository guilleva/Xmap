<?php
/**
 * @version     $Id$
 * @copyright   Copyright (C) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Guillermo Vargas (guille@vargas.co.cr)
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Include dependencies
jimport('joomla.application.component.controller');
use Joomla\CMS\MVC\Controller\BaseController as JControllerLegacy;

require_once(JPATH_COMPONENT.'/displayer.php');
require_once(JPATH_COMPONENT.'/helpers/xmap.php');

$controller = JControllerLegacy::getInstance('Xmap');
$controller->execute(XmapHelper::getVar('task'));
$controller->redirect();
