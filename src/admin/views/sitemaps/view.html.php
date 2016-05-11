<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap;
use Alledia\Framework\Joomla\Extension;

defined('_JEXEC') or die();


class OSMapViewSitemaps extends OSMap\View\Admin
{
    /**
     * @var array
     */
    protected $items = array();

    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        $this->state = $this->get('State');

        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        // We don't need toolbar or submenus in the modal window
        if ($this->getLayout() !== 'modal') {
            OSMap\Helper::addSubmenu('sitemaps');
            $this->setToolbar();
        }

        parent::display($tpl);
    }

    protected function setToolbar($addDivider = true)
    {
        $this->setTitle('COM_OSMAP_SUBMENU_SITEMAPS');

        JToolBarHelper::addNew('sitemap.add');
        JToolBarHelper::custom('sitemap.edit', 'edit.png', 'edit_f2.png', 'JTOOLBAR_EDIT', true);
        JToolBarHelper::custom('sitemaps.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_Publish', true);
        JToolBarHelper::custom('sitemaps.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
        JToolBarHelper::custom('sitemaps.setdefault', 'featured.png', 'featured_f2.png', 'COM_OSMAP_TOOLBAR_SET_DEFAULT', true);

        if ($this->state->get('filter.published') == -2) {
            JToolBarHelper::deleteList('', 'sitemaps.delete', 'JTOOLBAR_DELETE');
        } else {
            JToolBarHelper::trash('sitemaps.trash', 'JTOOLBAR_TRASH');
        }

        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_PUBLISHED'),
            'filter_published',
            JHtml::_(
                'select.options',
                JHtml::_('jgrid.publishedOptions'),
                'value',
                'text',
                $this->state->get('filter.published'),
                true
            )
        );

        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_ACCESS'),
            'filter_access',
            JHtml::_(
                'select.options',
                JHtml::_('access.assetgroups'),
                'value',
                'text',
                $this->state->get('filter.access')
            )
        );

        $this->sidebar = JHtmlSidebar::render();

        parent::setToolBar($addDivider);
    }

    /**
     * Display a standard footer on all admin pages
     *
     * @return void
     */
    protected function displayFooter()
    {
        parent::displayFooter();

        $extension = new Extension\Licensed('OSMap', 'component');
        echo $extension->getFooterMarkup();
    }
}
