<?php

/**
 * @version             $Id$
 * @copyright			Copyright (C) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * XML Sitemap View class for the Xmap component
 *
 * @package		Xmap
 * @subpackage	com_xmap
 * @since		2.0
 */
class XmapViewXml extends JView
{

    protected $state;
    protected $print;

    function display($tpl = null)
    {
        // Initialise variables.
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        // $dispatcher	= &JDispatcher::getInstance();


        $layout = $this->getLayout();

        if ($layout == 'xsl' || $layout == 'adminxsl') {
            return $this->displayXSL($layout);
        }

        // Get model data.
        $state = $this->get('State');
        $item = $this->get('Item');
        $items = $this->get('Items');
        $sitemapItems = $this->get('SitemapItems');
        $extensions = $this->get('Extensions');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseWarning(500, implode("\n", $errors));
            return false;
        }

        // Add router helpers.
        $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;

        $item->rlink = JRoute::_('index.php?option=com_xmap&view=xml&id=' . $item->slug);

        // Create a shortcut to the paramemters.
        $params = &$state->params;
        $offset = $state->get('page.offset');

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
        $displayer = new XmapXmlDisplayer($params, $item);

        $displayer->setJView($this);

        $this->assignRef('state', $state);
        $this->assignRef('item', $item);
        $this->assignRef('items', $items);
        $this->assignRef('sitemapItems', $sitemapItems);
        $this->assignRef('extensions', $extensions);
        $this->assignRef('user', $user);
        $this->assignRef('displayer', $displayer);

        $doCompression = ($this->item->params->get('compress_xml') && !ini_get('zlib.output_compression') && ini_get('output_handler') != 'ob_gzhandler');
        @ob_end_clean();
        if ($doCompression) {
            ob_start();
        }

        parent::display($tpl);

        $model = $this->getModel();
        $model->hit($displayer->getCount());

        if ($doCompression) {
            $data = ob_get_contents();
            @ob_end_clean();
            JResponse::setBody($data);
            echo JResponse::toString(true);
        }
        exit;
    }

    function displayXSL()
    {
        $this->setLayout('default');

        @ob_end_clean();
        parent::display('xsl');
        exit;
    }

}
