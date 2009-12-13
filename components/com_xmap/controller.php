<?php
/**
 * @version		$Id$
 * @copyright   Copyright (C) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @author		Guillermo Vargas (guille@vargas.co.cr)
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Content Component Controller
 *
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since		1.5
 */
class XmapController extends JController
{
	/**
	 * Display the view
	 */
	function display()
	{
		// Initialise variables.
		$document	= &JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getWord('view', 'html');
		$vFormat	= $document->getType();
		$lName		= JRequest::getWord('layout', 'default');

		// Get and render the view.
		if ($view = &$this->getView($vName, $vFormat))
		{
			// Get the model for the view.
			$model	= &$this->getModel('Sitemap');

			// Push the model into the view (as default).
			$view->setModel($model, true);
			$view->setLayout($lName);

			// Push document object into the view.
			$view->assignRef('document', $document);

			$view->display();
		}
	}

	public function editElement()
	{

                jimport('joomla.utilities.date');
                jimport('joomla.user.helper');
                $user =& JFactory::getUser();
                $groups = array_keys(JUserHelper::getUserGroups($user->get('id')));
	 	$registry = new JRegistry('_default');

		if (!in_array(8,$groups)) {
			$registry->setValue('result','KO');
			$registry->setValue('message','You are not authorized to perform this action!');
		} else {
			$model = $this->getModel('sitemap');
			if ( $model->getItem() ) {
				$action = JRequest::getCmd('action','');
				$uid = JRequest::getCmd('uid','');
				$itemid = JRequest::getInt('itemid','');
				switch (  $action ) {
					case 'toggleElement':
						if ($uid && $itemid) {
							$state = $model->toggleItem($uid,$itemid);
						}
						break;
					case 'changeProperty':
						$uid      = JRequest::getCmd('uid','');
						$property = JRequest::getCmd('property','');
						$value = JRequest::getCmd('value','');
						if ( $uid && $itemid && $uid && $property ) {
								$state = $model->chageItemPropery($uid,$itemid,'xml',$property,$value);
						}
						break;
				}
			}
			$registry->setValue('result','OK');
			$registry->setValue('state',$state);
			$registry->setValue('message','');
		}
		ob_end_clean();
		echo $registry->toString();
		exit;	
	}
}
