<?php
/**
 * @version       $Id$
 * @copyright     Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @author        Guillermo Vargas (guille@vargas.co.cr)
 */

// no direct access
defined('_JEXEC') or die;

JTable::addIncludePath( JPATH_COMPONENT.'/tables' );

jimport('joomla.form.form');
JForm::addFieldPath( JPATH_COMPONENT.'/models/fields' );

// Register helper class
JLoader::register('XmapHelper', dirname(__FILE__) . '/helpers/xmap.php');

// Include dependancies
jimport('joomla.application.component.controller');

# For compatibility with older versions of Joola 2.5
if (!class_exists('JControllerLegacy')){
    class JControllerLegacy extends JController {

    }
}

$controller = JControllerLegacy::getInstance('Xmap');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();