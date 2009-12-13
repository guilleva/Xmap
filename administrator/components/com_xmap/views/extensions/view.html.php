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
		JToolBarHelper::title( JText::_('Extension Manager'), 'generic.png' );


		$ftp = &JClientHelper::setCredentialsFromRequest('ftp');

		$bar =& JToolBar::getInstance();

		JToolBarHelper::spacer();
		JToolBarHelper::editList();
		JToolBarHelper::trash('trash');
		# JToolBarHelper::deleteList(JText::_('Are you sure you want to delete selected Items?'));
		JToolBarHelper::spacer();
		JToolBarHelper::back('Back', 'index.php?option=com_xmap');

		// Get data from the model
		$this->assignRef('items', $this->get('Items'));
		$this->assignRef('state', $this->get('state'));

        $this->assignRef('ftp', $ftp);
		$this->assign($this->getModel());
		$this->assignRef('total', $this->get('Total'));
		$this->assignRef('lists', $this->get('Lists'));
		$this->assignRef('pagination', $this->get('Pagination'));

		JHTML::_('behavior.tooltip');

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
	
	function loadItem($index=0)
	{
			$item = &$this->items[$index];
			$item->index    = $index;
			$item->img		= $item->enabled ? 'tick.png' : 'publish_x.png';
			$item->task 	= $item->enabled ? 'disable' : 'enable';
			$item->alt 		= $item->enabled ? JText::_('Enabled') : JText::_('Disabled');
			$item->action	= $item->enabled ? JText::_('disable') : JText::_('enable');
			$item->author_info = @$item->authorEmail .'<br />'. @$item->authorUrl;
			$item->link = JRoute::_( 'index.php?option=com_xmap&view=extension&task=extension.edit&cid[]='. $item->id );
	
			$this->assignRef('item', $item);
	}
}
