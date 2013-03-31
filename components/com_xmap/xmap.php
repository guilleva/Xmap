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

# For compatibility with older versions of Joola 2.5
if (!class_exists('JControllerLegacy')){
    class JControllerLegacy extends JController {

    }
}

require_once(JPATH_COMPONENT.'/displayer.php');

$controller = JControllerLegacy::getInstance('Xmap');
$controller->execute(JRequest::getVar('task'));
$controller->redirect();
