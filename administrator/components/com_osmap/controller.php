<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2015 Alledia.com, All rights reserved.
 * @author    Guillermo Vargas <guille@vargas.co.cr>
 * @author    Alledia <support@alledia.com>
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 *
 * This file is part of OSMap.
 *
 * OSMap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OSMap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSMap. If not, see <http://www.gnu.org/licenses/>.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * Component Controller
 *
 * @package     OSMap
 * @subpackage  com_osmap
 */
class OSMapController extends JControllerLegacy
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
        require_once JPATH_COMPONENT . '/helpers/osmap.php';

        // Get the document object.
        $document = JFactory::getDocument();

        // Set the default view name and format from the Request.
        $vName = JRequest::getWord('view', 'sitemaps');
        $vFormat = $document->getType();
        $lName = JRequest::getWord('layout', 'default');

        // Get and render the view.
        if ($view = $this->getView($vName, $vFormat)) {
            // Get the model for the view.
            $model = $this->getModel($vName);

            // Push the model into the view (as default).
            $view->setModel($model, true);
            $view->setLayout($lName);

            // Push document object into the view.
            $view->assignRef('document', $document);

            $view->display();

        }
    }

    function navigator()
    {
        $db = JFactory::getDBO();
        $document = JFactory::getDocument();
        $app = JFactory::getApplication('administrator');

        $id = JRequest::getInt('sitemap', 0);
        $link = urldecode(JRequest::getVar('link', ''));
        $name = JRequest::getCmd('e_name', '');
        if (!$id) {
            $id = $this->getDefaultSitemapId();
        }

        if (!$id) {
            JError::raiseWarning(500, JText::_('Xmap_Not_Sitemap_Selected'));
            return false;
        }

        $app->setUserState('com_osmap.edit.sitemap.id', $id);

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

        $id = JRequest::getInt('sitemap', 0);
        $link = urldecode(JRequest::getVar('link', ''));
        $name = JRequest::getCmd('e_name', '');
        if (!$id) {
            $id = $this->getDefaultSitemapId();
        }

        if (!$id) {
            JError::raiseWarning(500, JText::_('Xmap_Not_Sitemap_Selected'));
            return false;
        }

        $app->setUserState('com_osmap.edit.sitemap.id', $id);

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
        $query->from($db->quoteName('#__osmap_sitemap'));
        $query->where('is_default=1');
        $db->setQuery($query);
        return $db->loadResult();
    }

}
