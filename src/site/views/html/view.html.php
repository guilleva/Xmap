<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2015 Open Source Training, LLC. All rights reserved..
 * @author    Guillermo Vargas <guille@vargas.co.cr>
 * @author    Alledia <support@alledia.com>
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file is part of OSMap.
 *
 * OSMap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
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
// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

# For compatibility with older versions of Joola 2.5
if (!class_exists('JViewLegacy')){
    class JViewLegacy extends JView {

    }
}

/**
 * HTML Site map View class for the OSMap component
 *
 * @package         OSMap
 * @subpackage      com_osmap
 * @since           2.0
 */
class OSMapViewHtml extends JViewLegacy
{

    protected $state;
    protected $print;

    function display($tpl = null)
    {
        // Initialise variables.
        $this->app = JFactory::getApplication();
        $this->user = JFactory::getUser();
        $doc = JFactory::getDocument();

        // Get view related request variables.
        $this->print = JRequest::getBool('print');

        // Get model data.
        $this->state = $this->get('State');
        $this->item = $this->get('Item');
        $this->items = $this->get('Items');

        $this->canEdit = JFactory::getUser()->authorise('core.admin', 'com_osmap');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseWarning(500, implode("\n", $errors));
            return false;
        }

        $this->extensions = $this->get('Extensions');

        // Add router helpers.
        $this->item->slug = $this->item->alias ? ($this->item->id . ':' . $this->item->alias) : $this->item->id;

        $this->item->rlink = JRoute::_('index.php?option=com_osmap&view=html&id=' . $this->item->slug);

        // Create a shortcut to the paramemters.
        $params = &$this->state->params;
        $offset = $this->state->get('page.offset');
        if ($params->get('include_css', 0)){
            $doc->addStyleSheet(JURI::root().'components/com_osmap/assets/css/osmap.css');
        }

        // If a guest user, they may be able to log in to view the full article
        // TODO: Does this satisfy the show not auth setting?
        if (!$this->item->params->get('access-view')) {
            if ($user->get('guest')) {
                // Redirect to login
                $uri = JFactory::getURI();
                $base = '64';
                $function = "base${base}_encode";
                $app->redirect(
                    'index.php?option=com_users&view=login&return=' . call_user_func($function, $uri),
                    JText::_('OSMAP_ERROR_LOGIN_TO_VIEW_SITEMAP')
                );
                return;
            } else {
                JError::raiseWarning(403, JText::_('OSMAP_ERROR_NOT_AUTH'));
                return;
            }
        }

        // Override the layout.
        if ($layout = $params->get('layout')) {
            $this->setLayout($layout);
        }

        // Load the class used to display the sitemap
        $this->loadTemplate('class');
        $this->displayer = new OSMapHtmlDisplayer($params, $this->item);

        $this->displayer->setJView($this);
        $this->displayer->canEdit = $this->canEdit;

        $this->_prepareDocument();
        parent::display($tpl);

        $model = $this->getModel();
        $model->hit($this->displayer->getCount());
    }

    /**
     * Prepares the document
     */
    protected function _prepareDocument()
    {
        $app = JFactory::getApplication();
        $pathway = $app->getPathway();
        $menus = $app->getMenu();

        $title = $this->item->title;

        // Because the application sets a default page title, we need to get it from the menu item itself
        if ($menu = $menus->getActive()) {
            if (isset($menu->query['view']) && isset($menu->query['id'])) {

                if ($menu->query['view'] == 'html' && $menu->query['id'] == $this->item->id) {
                    $title = $menu->params->get('page_title', '');

                    if (empty($title)) {
                        $title = $menu->title;
                    }

                    if (empty($title)) {
                        $title = $app->getCfg('sitename');
                    } else if ($app->getCfg('sitename_pagetitles', 0) == 1) {
                        $title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
                    } else if ($app->getCfg('sitename_pagetitles', 0) == 2) {
                        $title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
                    }
                    // set meta description and keywords from menu item's params
                    $params = new JRegistry();
                    $params->loadString($menu->params);
                    $this->document->setDescription($params->get('menu-meta_description'));
                    $this->document->setMetadata('keywords', $params->get('menu-meta_keywords'));
                }
            }
        }

        $this->document->setTitle($title);

        if ($app->getCfg('MetaTitle') == '1') {
            $this->document->setMetaData('title', $title);
        }

        if ($this->print) {
            $this->document->setMetaData('robots', 'noindex, nofollow');
        }
    }

}
