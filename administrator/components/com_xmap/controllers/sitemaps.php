<?php
/**
 * @version		$Id$
 * @copyright   Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @author		Guillermo Vargas (guille@vargas.co.cr)
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * @package	     Xmap
 * @subpackage  com_xmap
 * @since	       2.0
 */
class XmapControllerSitemaps extends JController
{
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();

		$this->registerTask('unpublish',	'publish');
		$this->registerTask('trash',	    'publish');
		$this->registerTask('unfeatured',       'featured');
	}

	/**
	 * Display the view
	 */
	function display()
	{
	}
		
	/**
	 * Proxy for getModel
	 */
	function getModel($name = 'Sitemap', $prefix = 'XmapModel')
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}
		
	/**
	 * Removes one or more sitemaps
		 *
	 * @return      void
	 * @since       2.0
	 */
	function delete()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('Invalid Token');

		// Get items to remove from the request.
		$ids    = JRequest::getVar('cid', array(), '', 'array');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('Select an item to delete'));
		}
		else {
			// Get the model.
			$model = $this->getModel();

			// Remove the items.
			if (!$model->delete($ids)) {
				JError::raiseWarning(500, $model->getError());
			}
		}

		$this->setRedirect('index.php?option=com_xmap&view=sitemaps');
	}

	/**
	 * Method to publish a list of sitemaps.
	 *
	 * @return      void
	 * @since       2.0
	 */
	function publish()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('Invalid Token');

		// Get items to publish from the request.
		$ids    = JRequest::getVar('cid', array(), '', 'array');
		$values = array('publish' => 1, 'unpublish' => 0, 'trash' => -1);
		$task   = $this->getTask();
		$value  = JArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('Select an item to publish'));
		}
		else
		{
			// Get the model.
			$model  = $this->getModel();

			// Publish the items.
			if (!$model->publish($ids, $value)) {
				JError::raiseWarning(500, $model->getError());
			}
		}

		$this->setRedirect('index.php?option=com_xmap&view=sitemaps');
	}
		
	/**
	 * Method to toggle the default sitemap.
	 *
	 * @return      void
	 * @since       2.0
	 */
	function setDefault()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('Invalid Token');

		// Get items to publish from the request.
		$cid    = JRequest::getVar('cid', 0, '', 'array');
		$id	= @$cid[0];

		if (!$id) {
			JError::raiseWarning(500, JText::_('Select an item to set as default'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Publish the items.
			if (!$model->setDefault($id)) {
				JError::raiseWarning(500, $model->getError());
			}
		}

		$this->setRedirect('index.php?option=com_xmap&view=sitemaps');
	}
}