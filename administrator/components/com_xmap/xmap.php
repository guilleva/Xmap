<?php
/**
 * @version		$Id$
 * @copyright   	Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @author		Guillermo Vargas (guille@vargas.co.cr)
 */

// no direct access
defined('_JEXEC') or die;

JTable::addIncludePath( JPATH_COMPONENT.DS.'tables' );

jimport('joomla.form.form');
JForm::addFieldPath( JPATH_COMPONENT.DS.'models'.DS.'fields' );
//JForm::addFieldPath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_menus'.DS.'models'.DS.'fields' );

// Include dependancies
jimport('joomla.application.component.controller');

$controller = JController::getInstance('Xmap');
$controller->execute(JRequest::getVar('task'));
$controller->redirect();
