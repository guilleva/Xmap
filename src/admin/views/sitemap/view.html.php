<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap;

defined('_JEXEC') or die();


class OSMapViewSitemap extends OSMap\View\Admin
{
    /**
     * @var JObject
     */
    protected $item = null;

    /**
     * @var JForm
     */
    public $form = null;

    public function display($tpl = null)
    {
        $app = OSMap\Factory::getApplication();

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        // Convert dates from UTC
        $offset = $app->getCfg('offset');
        if (intval($this->item->created_on)) {
            $this->item->created_on = JHtml::date($this->item->created_on, '%Y-%m-%d %H-%M-%S', $offset);
        }

        $this->setToolBar();

        // Get the sitemap items, if not a new sitemap
        $this->sitemapItems = array();
        if (!empty($this->item->id)) {
            $sitemap = OSMap\Factory::getSitemap($this->item->id, 'standard');
            $sitemap->traverse(array($this, 'appendSitemapItem'));
        }

        parent::display($tpl);
    }

    /**
     * This method is called while traversing the sitemap items tree, and is
     * used to append the found item to the sitemapItems attribute, which will
     * be used in the view.
     *
     * @param object $item
     *
     * @result void
     */
    public function appendSitemapItem($item)
    {
        $this->sitemapItems[] = $item;
    }

    protected function setToolBar($addDivider = true)
    {
        $isNew = ($this->item->id == 0);
        OSMap\Factory::getApplication()->input->set('hidemainmenu', false);

        $title = 'COM_OSMAP_PAGE_VIEW_SITEMAP_' . ($isNew ? 'ADD' : 'EDIT');
        $this->setTitle($title);

        JToolBarHelper::apply('sitemap.apply');
        JToolBarHelper::save('sitemap.save');
        JToolBarHelper::save2new('sitemap.save2new');

        if (!$isNew) {
            JToolBarHelper::save2copy('sitemap.save2copy');
        }

        $alt = $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE';
        JToolBarHelper::cancel('sitemap.cancel', $alt);

        parent::setToolBar($addDivider);
    }
}
