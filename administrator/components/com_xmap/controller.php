<?php
/**
 * @version     $Id$
 * @copyright   Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Guillermo Vargas (guille@vargas.co.cr)
 */
// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\MVC\Controller\BaseController as JControllerLegacy;

/**
 * Component Controller
 *
 * @package     Xmap
 * @subpackage  com_xmap
 */
class XmapController extends JControllerLegacy
{

    function __construct()
    {
        parent::__construct();

        $this->registerTask('navigator-links', 'navigatorLinks');
    }

    /**
     * Display the view
     */
    public function display($cachable = false, $urlparams = false)
    {
        require_once JPATH_COMPONENT . '/helpers/xmap.php';

        // Get the document object.
        $document = JFactory::getDocument();

        // Set the default view name and format from the Request.
        $vName = XmapHelper::getWord('view', 'sitemaps');
        $vFormat = $document->getType();
        $lName = XmapHelper::getWord('layout', 'default');

        // Get and render the view.
        if ($view = $this->getView($vName, $vFormat)) {
            // Get the model for the view.
            $model = $this->getModel($vName);

            // Push the model into the view (as default).
            $view->setModel($model, true);
            $view->setLayout($lName);

            // Push document object into the view.
            $view->document = $document;

            $view->display();

        }
    }

    function navigator()
    {
        $db = JFactory::getDBO();
        $document = JFactory::getDocument();
        $app = JFactory::getApplication('administrator');

        $id = XmapHelper::getInt('sitemap', 0);
        $link = urldecode(XmapHelper::getVar('link', ''));
        $name = XmapHelper::getCmd('e_name', '');
        if (!$id) {
            $id = $this->getDefaultSitemapId();
        }

        if (!$id) {
			JFactory::getApplication()->enqueueMessage(500, JText::_('Xmap_Not_Sitemap_Selected'), 'warning');
			return false;
        }

        $app->setUserState('com_xmap.edit.sitemap.id', $id);

        $view = $this->getView('sitemap', $document->getType());
        $model = $this->getModel('Sitemap');
        $view->setLayout('navigator');
        $view->setModel($model, true);

        // Push document object into the view.
        $view->assignRef('document', $document);

        $view->navigator();
    }

    function navigatorLinks()
    {

        $db = JFactory::getDBO();
        $document = JFactory::getDocument();
        $app = JFactory::getApplication('administrator');

        $id = XmapHelper::getInt('sitemap', 0);
        $link = urldecode(XmapHelper::getVar('link', ''));
        $name = XmapHelper::getCmd('e_name', '');
        if (!$id) {
            $id = $this->getDefaultSitemapId();
        }

        if (!$id) {
            JFactory::getApplication()->enqueueMessage(500, JText::_('Xmap_Not_Sitemap_Selected'), 'warning');
            return false;
        }

        $app->setUserState('com_xmap.edit.sitemap.id', $id);

        $view = $this->getView('sitemap', $document->getType());
        $model = $this->getModel('Sitemap');
        $view->setLayout('navigator');
        $view->setModel($model, true);

        // Push document object into the view.
        $view->assignRef('document', $document);

        $view->navigatorLinks();
    }

    private function getDefaultSitemapId()
    {
        $db = JFactory::getDBO();
        $query  = $db->getQuery(true);
        $query->select('id');
        $query->from($db->quoteName('#__xmap_sitemap'));
        $query->where('is_default=1');
        $db->setQuery($query);
        return $db->loadResult();
    }

}