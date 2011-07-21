<?php
/**
 * @version		$Id$
 * @copyright           Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
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
		$this->state	  = $this->get('State');
		$this->items	  = $this->get('Items');
		$this->pagination     = $this->get('Pagination');

		$message     = $this->get('ExtensionsMessage');
		if ( $message ) {
		    JFactory::getApplication()->enqueueMessage($message);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

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
		$doc = JFactory::getDocument();

		$doc->addStyleDeclaration('.icon-48-sitemap {background-image: url(components/com_xmap/images/sitemap-icon.png);}');
		JToolBarHelper::title(JText::_('XMAP_SITEMAPS_TITLE'), 'sitemap.png');
		JToolBarHelper::custom('sitemaps.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_Publish', true);
		JToolBarHelper::custom('sitemaps.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
                JToolBarHelper::custom('sitemaps.setdefault', 'default.png', 'default_f2.png', 'XMAP_TOOLBAR_SET_DEFAULT', true);
		if ($state->get('filter.published') == -2) {
			JToolBarHelper::deleteList('', 'sitemaps.delete','JTOOLBAR_DELETE');
		}
		else {
			JToolBarHelper::trash('sitemaps.trash','JTOOLBAR_TRASH');
		}
		JToolBarHelper::divider();
		JToolBarHelper::custom('sitemap.edit', 'edit.png', 'edit_f2.png', 'JTOOLBAR_EDIT', true);
		JToolBarHelper::custom('sitemap.edit', 'new.png', 'new_f2.png', 'JTOOLBAR_New', false);
		//JToolBarHelper::divider();
		//JToolBarHelper::preferences('com_xmap');
		//JToolBarHelper::help('screen.xmap.sitemaps');
	}
}
