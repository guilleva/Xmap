<?php

/**
 * @version		$Id$
 * @copyright           Copyright (C) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @author		Guillermo Vargas (guille@vargas.co.cr)
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Xmap Component Controller
 *
 * @package		Xmap
 * @subpackage          com_xmap
 * @since		2.0
 */
class XmapController extends JController
{

    /**
     * Display the view
     */
    public function display($cachable = false, $urlparams = false)
    {
        // Initialise variables.
        $document = JFactory::getDocument();

        // Set the default view name and format from the Request.
        $vName = JRequest::getWord('view', 'html');
        $vFormat = $document->getType();
        $lName = JRequest::getWord('layout', 'default');

        // Get and render the view.
        if ($view = $this->getView($vName, $vFormat)) {
            // Get the model for the view.
            $model = $this->getModel('Sitemap');

            // Push the model into the view (as default).
            $view->setModel($model, true);
            $view->setLayout($lName);

            // Push document object into the view.
            $view->assignRef('document', $document);

            $view->display();
        }
    }

}
