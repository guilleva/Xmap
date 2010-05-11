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
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('add', 'edit');
        $this->registerTask('unpublish',        'publish');
        $this->registerTask('publish',          'publish');
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
			$model->publish($ids,'1');

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

			$model->publish($ids,'0');

			$this->setRedirect('index.php?option=com_xmap&view=extensions');
	}
	
    /**
     * Enable/Disable an extension (If supported)
     */
    public function publish()
    {
        // Check for request forgeries.
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        $user    = JFactory::getUser();
        $ids    = JRequest::getVar('cid', array(), '', 'array');
        $values    = array('publish' => 1, 'unpublish' => 0);
        $task    = $this->getTask();
        $value    = JArrayHelper::getValue($values, $task, 0, 'int');

        if (empty($ids)) {
            JError::raiseWarning(500, JText::_('XMAP_ERROR_NO_EXTENSIONS_SELECTED'));
        } else {
            // Get the model.
            $model    = $this->getModel('extensions');

            // Change the state of the records.
            if (!$model->publish($ids, $value)) {
                JError::raiseWarning(500, implode('<br />', $model->getErrors()));
            }
            else
            {
                if ($value == 1) {
                    $ntext = 'XMAP_N_EXTENSIONS_PUBLISHED';
                } else if ($value == 0) {
                    $ntext = 'XMAP_N_EXTENSIONS_UNPUBLISHED';
                }
                $this->setMessage(JText::__($ntext, count($ids)));
            }
        }

        $this->setRedirect(JRoute::_('index.php?option=com_xmap&view=extensions',false));
    }
    
    /**
     * Refreshes the cached metadata about an extension
     * Useful for debugging and testing purposes when the XML file might change
     */
    function refresh()
    {
        // Check for request forgeries
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $model    = &$this->getModel('extensions');
        $uid = JRequest::getVar('cid', array(), '', 'array');
        JArrayHelper::toInteger($uid, array());
        $result = $model->refresh($uid);
        $this->setRedirect(JRoute::_('index.php?option=com_xmap&view=extensions',false));
    }
}
