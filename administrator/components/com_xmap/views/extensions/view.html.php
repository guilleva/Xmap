<?php
/**
 * @version             $Id$
 * @copyright			Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
 */
// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');
jimport('joomla.client.helper');

class XmapViewExtensions extends JView
{

    function display($tpl = null)
    {
        $ftp = JClientHelper::setCredentialsFromRequest('ftp');

        // Get data from the model
        $items = $this->get('Items');
        $state = $this->get('State');
        $total = $this->get('Total');
        $pagination = $this->get('Pagination');
        $lists = $this->get('Lists');

        $this->assignRef('items', $items);
        $this->assignRef('state', $state);

        $this->assignRef('ftp', $ftp);
        $this->assign($this->getModel());
        $this->assignRef('total', $total);
        $this->assignRef('lists', $lists);
        $this->assignRef('pagination', $pagination);

        JHTML::_('behavior.tooltip');

        $this->_setToolbar();
        parent::display($tpl);
    }

    /**
     * Setup the Toolbar
     *
     * @since    2.0
     */
    protected function _setToolbar()
    {
        JToolBarHelper::title(JText::_('XMAP_EXTENSION_MANAGER_TITLE'), 'generic.png');

        JToolBarHelper::divider();
        JToolBarHelper::editList('extension.edit', 'JTOOLBAR_EDIT');
        JToolBarHelper::custom('extensions.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
        JToolBarHelper::custom('extensions.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
        JToolBarHelper::divider();
        JToolBarHelper::custom('extensions.refresh', 'refresh', 'refresh', 'JTOOLBAR_REFRESH_CACHE', true);
        JToolBarHelper::divider();
        JToolBarHelper::deleteList('', 'extension.remove', 'JTOOLBAR_UNINSTALL');
        # JToolBarHelper::deleteList(JText::_('Are you sure you want to delete selected Items?'));
        JToolBarHelper::divider();
        JToolBarHelper::back('Back', 'index.php?option=com_xmap');
    }

}
