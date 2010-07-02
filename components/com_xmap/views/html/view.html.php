<?php

/**
 * @version             $Id$
 * @copyright		Copyright (C) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML Site map View class for the Xmap component
 *
 * @package         Xmap
 * @subpackage      com_xmap
 * @since           2.0
 */
class XmapViewHtml extends JView
{

    protected $state;
    protected $item;
    protected $print;

    function display($tpl = null)
    {
        // Initialise variables.
        $app = JFactory::getApplication();
        $user = JFactory::getUser();

        // Get view related request variables.
        $print = JRequest::getBool('print');

        // Get model data.
        $state = $this->get('State');
        $item = $this->get('Item');
        $items = $this->get('Items');
        $extensions = $this->get('Extensions');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseWarning(500, implode("\n", $errors));
            return false;
        }

        // Add router helpers.
        $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;

        $item->rlink = JRoute::_('index.php?option=com_xmap&view=html&id=' . $item->slug);

        // Create a shortcut to the paramemters.
        $params = &$state->params;
        $offset = $state->get('page.offset');

        // If a guest user, they may be able to log in to view the full article
        // TODO: Does this satisfy the show not auth setting?
        if (!$item->params->get('access-view')) {
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
        $displayer = new XmapHtmlDisplayer($params, $item);

        $displayer->setJView($this);

        $this->assignRef('state', $state);
        $this->assignRef('item', $item);
        $this->assignRef('items', $items);
        $this->assignRef('extensions', $extensions);
        $this->assignRef('user', $user);
        $this->assign('print', $print);
        $this->assignRef('displayer', $displayer);

        $this->_prepareDocument();
        parent::display($tpl);

        $model = $this->getModel();
        $model->hit($displayer->getCount());
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
                    $menuParams = new JParameter($menu->params);
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
