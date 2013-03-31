<?php

/**
 * @version          $Id$
 * @copyright        Copyright (C) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @license          GNU General Public License version 2 or later; see LICENSE.txt
 * @author           Guillermo Vargas (guille@vargas.co.cr)
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');

# For compatibility with older versions of Joola 2.5
if (!class_exists('JViewLegacy')){
    class JViewLegacy extends JView {

    }
}

/**
 * HTML Site map View class for the Xmap component
 *
 * @package         Xmap
 * @subpackage      com_xmap
 * @since           2.0
 */
class XmapViewHtml extends JViewLegacy
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

        $this->canEdit = JFactory::getUser()->authorise('core.admin', 'com_xmap');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseWarning(500, implode("\n", $errors));
            return false;
        }

        $this->extensions = $this->get('Extensions');
        // Add router helpers.
        $this->item->slug = $this->item->alias ? ($this->item->id . ':' . $this->item->alias) : $this->item->id;

        $this->item->rlink = JRoute::_('index.php?option=com_xmap&view=html&id=' . $this->item->slug);

        // Create a shortcut to the paramemters.
        $params = &$this->state->params;
        $offset = $this->state->get('page.offset');
        if ($params->get('include_css', 0)){
            $doc->addStyleSheet(JURI::root().'components/com_xmap/assets/css/xmap.css');
        }

        // If a guest user, they may be able to log in to view the full article
        // TODO: Does this satisfy the show not auth setting?
        if (!$this->item->params->get('access-view')) {
            if ($user->get('guest')) {
                // Redirect to login
                $uri = JFactory::getURI();
                $app->redirect(
                    'index.php?option=com_users&view=login&return=' . base64_encode($uri),
                    JText::_('Xmap_Error_Login_to_view_sitemap')
                );
                return;
            } else {
                JError::raiseWarning(403, JText::_('Xmap_Error_Not_auth'));
                return;
            }
        }

        // Override the layout.
        if ($layout = $params->get('layout')) {
            $this->setLayout($layout);
        }

        // Load the class used to display the sitemap
        $this->loadTemplate('class');
        $this->displayer = new XmapHtmlDisplayer($params, $this->item);

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
        $title = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        if ($menu = $menus->getActive()) {
            if (isset($menu->query['view']) && isset($menu->query['id'])) {
                if ($menu->query['view'] == 'html' && $menu->query['id'] == $this->item->id) {
                    $menuParams = new JRegistry($menu->params);
                    $title = $menuParams->get('page_title');

                    $this->document->setDescription($menuParams->get('menu-meta_description'));
                    $this->document->setMetadata('keywords', $menuParams->get('menu-meta_keywords'));
                }
            }
        }
        if (empty($title)) {
            $title = $this->item->title;
        }
        $this->document->setTitle($title);

        if ($app->getCfg('MetaTitle') == '1') {
            $this->document->setMetaData('title', $this->item->title);
        }

        if ($this->print) {
            $this->document->setMetaData('robots', 'noindex, nofollow');
        }
    }

}
