<?php
/**
 * @version     $Id$
 * @copyright   Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Guillermo Vargas (guille@vargas.co.cr)
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

# For compatibility with older versions of Joola 2.5
if (!class_exists('JViewLegacy')){
    class JViewLegacy extends JView {

    }
}

/**
 * @package     Xmap
 * @subpackage  com_xmap
 * @since       2.0
 */
class XmapViewSitemaps extends JViewLegacy
{
    protected $state;
    protected $items;
    protected $pagination;

    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        if ($this->getLayout() !== 'modal') {
            XmapHelper::addSubmenu('sitemaps');
        }

        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        $version = new JVersion;

        $message = $this->get('ExtensionsMessage');
        if ( $message ) {
            JFactory::getApplication()->enqueueMessage($message);
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
                $tpl = 'legacy';
            }
            $this->addToolbar();
        }

        parent::display($tpl);
    }

    /**
     * Display the toolbar
     *
     * @access      private
     */
    protected function addToolbar()
    {
        $state = $this->get('State');
        $doc = JFactory::getDocument();
        $version = new JVersion;

        JToolBarHelper::addNew('sitemap.add');
        JToolBarHelper::custom('sitemap.edit', 'edit.png', 'edit_f2.png', 'JTOOLBAR_EDIT', true);

        $doc->addStyleDeclaration('.icon-48-sitemap {background-image: url(components/com_xmap/images/sitemap-icon.png);}');
        JToolBarHelper::title(JText::_('XMAP_SITEMAPS_TITLE'), 'sitemap.png');
        JToolBarHelper::custom('sitemaps.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_Publish', true);
        JToolBarHelper::custom('sitemaps.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);

        if (version_compare($version->getShortVersion(), '3.0.0', '>=')) {
            JToolBarHelper::custom('sitemaps.setdefault', 'featured.png', 'featured_f2.png', 'XMAP_TOOLBAR_SET_DEFAULT', true);
        } else {
            JToolBarHelper::custom('sitemaps.setdefault', 'default.png', 'default_f2.png', 'XMAP_TOOLBAR_SET_DEFAULT', true);
        }
        if ($state->get('filter.published') == -2) {
            JToolBarHelper::deleteList('', 'sitemaps.delete','JTOOLBAR_DELETE');
        }
        else {
            JToolBarHelper::trash('sitemaps.trash','JTOOLBAR_TRASH');
        }
        JToolBarHelper::divider();


        if (class_exists('JHtmlSidebar')){
            JHtmlSidebar::addFilter(
                JText::_('JOPTION_SELECT_PUBLISHED'),
                'filter_published',
                JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
            );

            JHtmlSidebar::addFilter(
                JText::_('JOPTION_SELECT_ACCESS'),
                'filter_access',
                JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
            );

            $this->sidebar = JHtmlSidebar::render();
        }
    }
}
