<?php
/**
 * @version             $Id$
 * @copyright			Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
*/

// no direct access
defined( '_JEXEC' ) or die;

jimport( 'joomla.application.component.view');
jimport('joomla.client.helper');

class XmapViewExtensions extends JView
{
	function display($tpl = null)
	{
		$ftp = &JClientHelper::setCredentialsFromRequest('ftp');

		// Get data from the model
		$this->assignRef('items', $this->get('Items'));
		$this->assignRef('state', $this->get('state'));

        $this->assignRef('ftp', $ftp);
		$this->assign($this->getModel());
		$this->assignRef('total', $this->get('Total'));
		$this->assignRef('lists', $this->get('Lists'));
		$this->assignRef('pagination', $this->get('Pagination'));

		JHTML::_('behavior.tooltip');

        $this->_setToolbar();
		parent::display($tpl);
	}

	function form( $tpl = null )
	{
		$edit=JRequest::getVar( 'edit', true );
		$cid = JRequest::getVar( 'cid', array(0), '', 'array' );

		JArrayHelper::toInteger($cid, array(0));
		$text = ( $edit ? JText::_( 'Edit' ) : JText::_( 'New' ) );



		$item =& $this->get('Item');

		JToolBarHelper::title(  JText::_( 'Gateways' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::save();
		JToolBarHelper::apply();
		if ($edit) {
				// for existing items the button is renamed `close`
				JToolBarHelper::cancel( 'cancel', 'Close' );
		} else {
				JToolBarHelper::cancel();
		}
		JToolBarHelper::help( 'screen.items.edit' );


		$this->assignRef('cid',         $cid);
		$this->assignRef('item',        $item);
		$this->assignRef('lists',       $this->get('ListsEdit'));
		parent::display($tpl);
	}
	
    /**
     * Setup the Toolbar
     *
     * @since    2.0
     */
    protected function _setToolbar()
    {
        JToolBarHelper::title( JText::_('Extension Manager'), 'generic.png' );

        JToolBarHelper::divider();
        JToolBarHelper::editList();
        JToolBarHelper::custom('extensions.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
        JToolBarHelper::custom('extensions.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
        JToolBarHelper::divider();
        JToolBarHelper::custom('extensions.refresh', 'refresh', 'refresh','JTOOLBAR_REFRESH_CACHE',true);
        JToolBarHelper::divider();
        JToolBarHelper::deleteList('', 'extension.remove','JTOOLBAR_UNINSTALL');
        # JToolBarHelper::deleteList(JText::_('Are you sure you want to delete selected Items?'));
        JToolBarHelper::divider();
        JToolBarHelper::back('Back', 'index.php?option=com_xmap');

    }
}
