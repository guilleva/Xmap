<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap;

defined('_JEXEC') or die();


class OSMapViewSitemaps extends OSMap\View\Admin\Base
{
    /**
     * @var array
     */
    protected $items = array();

    public function display($tpl = null)
    {
        $this->items         = $this->get('Items');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        // We don't need toolbar or submenus in the modal window
        if ($this->getLayout() !== 'modal') {
            $this->setToolbar();
        }

        // Get the active languages for multi-language sites
        $this->languages = null;
        if (\JLanguageMultilang::isEnabled()) {
            $this->languages = \JLanguageHelper::getLanguages();
        }

        parent::display($tpl);
    }

    protected function setToolbar($addDivider = true)
    {
        $this->setTitle('COM_OSMAP_SUBMENU_SITEMAPS');

        OSMap\Helper\General::addSubmenu('sitemaps');

        JToolBarHelper::addNew('sitemap.add');
        JToolBarHelper::custom('sitemap.edit', 'edit.png', 'edit_f2.png', 'JTOOLBAR_EDIT', true);
        JToolBarHelper::custom('sitemaps.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_Publish', true);
        JToolBarHelper::custom('sitemaps.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
        JToolBarHelper::custom('sitemap.setAsDefault', 'featured.png', 'featured_f2.png', 'COM_OSMAP_TOOLBAR_SET_DEFAULT', true);

        if ($this->state->get('filter.published') == -2) {
            JToolBarHelper::deleteList('', 'sitemaps.delete', 'JTOOLBAR_DELETE');
        } else {
            JToolBarHelper::trash('sitemaps.trash', 'JTOOLBAR_TRASH');
        }

        parent::setToolBar($addDivider);
    }
}
