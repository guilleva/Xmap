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
        $this->user = JFactory::getUser();
        $isNewsSitemap = JRequest::getInt('news',0);
        // $dispatcher	= &JDispatcher::getInstance();


        $layout = $this->getLayout();

        $this->item = $this->get('Item');
        $this->state = $this->get('State');
	    $this->canEdit = JFactory::getUser()->authorise('core.admin', 'com_xmap');
	    
	    // For now, news sitemaps are not editable
	    $this->canEdit = $this->canEdit && !$isNewsSitemap;

        if ($layout == 'xsl') {
            return $this->displayXSL($layout);
        }

        // Get model data.
        $this->items = $this->get('Items');
        $this->sitemapItems = $this->get('SitemapItems');
        $this->extensions = $this->get('Extensions');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseWarning(500, implode("\n", $errors));
            return false;
        }

        // Add router helpers.
        $this->item->slug = $this->item->alias ? ($this->item->id . ':' . $this->item->alias) : $this->item->id;

        $this->item->rlink = JRoute::_('index.php?option=com_xmap&view=xml&id=' . $this->item->slug);

        // Create a shortcut to the paramemters.
        $params = &$this->state->params;
        $offset = $this->state->get('page.offset');

        if (!$this->item->params->get('access-view')) {
            if ($this->user->get('guest')) {
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
        $this->displayer = new XmapXmlDisplayer($params, $this->item);

        $this->displayer->setJView($this);
        
        $this->displayer->isNews = $isNewsSitemap;
        $this->displayer->canEdit = $this->canEdit;


        $doCompression = ($this->item->params->get('compress_xml') && !ini_get('zlib.output_compression') && ini_get('output_handler') != 'ob_gzhandler');
        @ob_end_clean();
        if ($doCompression) {
            ob_start();
        }

        parent::display($tpl);

        $model = $this->getModel();
        $model->hit($this->displayer->getCount());

        if ($doCompression) {
            $data = ob_get_contents();
            JResponse::setBody($data);
            @ob_end_clean();
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
