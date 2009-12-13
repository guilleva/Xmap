<?php
/**
 * @version		$Id$
 * @copyright   Copyright (C) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @author		Guillermo Vargas (guille@vargas.co.cr)
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * @package	     Xmap
 * @subpackage  com_xmap
 * @since	       2.0
 */
class XmapViewSitemaps extends JView
{
	protected $state;
	protected $items;
	protected $pagination;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state	  = $this->get('State');
		$items	  = $this->get('Items');
		$pagination     = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',	       $state);
		$this->assignRef('items',	       $items);
		$this->assignRef('pagination',  $pagination);

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Display the toolbar
	 *
	 * @access      private
	 */
	protected function _setToolbar()
	{
		$state = $this->get('State');
		$doc =& JFactory::getDocument();

		$doc->addStyleDeclaration('.icon-48-sitemap {background-image: url(components/com_xmap/images/sitemap-icon.png);}');
		JToolBarHelper::title(JText::_('Xmap_sitemaps_Title'), 'sitemap.png');
		JToolBarHelper::custom('sitemaps.publish', 'publish.png', 'publish_f2.png', 'Publish', true);
		JToolBarHelper::custom('sitemaps.unpublish', 'unpublish.png', 'unpublish_f2.png', 'Unpublish', true);
                JToolBarHelper::custom('sitemaps.setdefault', 'default.png', 'default_f2.png', 'Xmap_Toolbar_Set_Default', true);
		if ($state->get('filter.published') == -2) {
			JToolBarHelper::deleteList('', 'sitemaps.delete');
		}
		else {
			JToolBarHelper::trash('sitemaps.trash');
		}
		JToolBarHelper::divider();
		JToolBarHelper::custom('sitemap.edit', 'edit.png', 'edit_f2.png', 'Edit', true);
		JToolBarHelper::custom('sitemap.edit', 'new.png', 'new_f2.png', 'New', false);
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_xmap');
		JToolBarHelper::help('screen.xmap.sitemaps');
	}
}
