<?php
/**
 * @version             $Id$
 * @copyright			Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');
jimport('joomla.client.helper');

class XmapControllerExtensions extends JController
{
	function __construct()
	{
		parent::__construct();
		$this->registerTask('add', 'edit');
	}

	function display()
	{
			parent::display();
	}

	function edit()
	{
			JRequest::setVar( 'layout', 'form' );
			JRequest::setVar( 'hidemainmenu', 1 );

			parent::display();
	}

	/**
	 * Install an extension
	 *
	 * @access      public
	 * @return      void
	 * @since       2.0
	 */
	function doInstall()
	{
			// Check for request forgeries
			JRequest::checkToken() or jexit('Invalid Token');

			$model  = &$this->getModel('Extensions');
			$view   = &$this->getView('Extensions','html');

			$model->install();

			$this->setRedirect('index.php?option=com_xmap&view=extensions');
	}

	/**
	 * Enable an extension
	 *
	 * @access      public
	 * @return      void
	 * @since       2.0
	 */
	function enable()
	{
			// Check for request forgeries
			JRequest::checkToken('request') or jexit('Invalid Token');

			$model  = &$this->getModel('Extensions');

			$ids = JRequest::getVar('eid',array(),'get','array');
			$model->enableDisable($ids,'1');

			$this->setRedirect('index.php?option=com_xmap&view=extensions');
	}

	/**
	 * Disable an extension
	 *
	 * @access      public
	 * @return      void
	 * @since       2.0
	 */
	function disable()
	{
			// Check for request forgeries
			JRequest::checkToken('request') or jexit('Invalid Token');

			$model  = &$this->getModel('Extensions');
			$ids = JRequest::getVar('eid',array(),'get','array');

			$model->enableDisable($ids,'0');

			$this->setRedirect('index.php?option=com_xmap&view=extensions');
	}
	
}
