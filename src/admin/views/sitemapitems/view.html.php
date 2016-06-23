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


class OSMapViewSitemapItems extends OSMap\View\Admin\Base
{
    /**
     * @var Sitemap
     */
    protected $sitemap = null;

    /**
     * @var JForm
     */
    public $form = null;

    /**
     * @var array
     */
    protected $sitemapItems = array();

    public function display($tpl = null)
    {
        $this->id = OSMap\Factory::getApplication()->input->getInt('id', 0);

        $this->sitemap = null;

        if (!empty($this->id)) {
            $this->sitemap = OSMap\Factory::getSitemap($this->id, 'standard');
        }

        $this->setToolBar();

        $this->osmapParams = JComponentHelper::getParams('com_osmap');

        parent::display($tpl);
    }

    protected function setToolBar($addDivider = true)
    {
        $isNew = true;

        $this->setTitle('COM_OSMAP_PAGE_VIEW_SITEMAP_ITEMS');

        if (isset($this->sitemap)) {
            $isNew = ($this->sitemap->id == 0);
            OSMap\Factory::getApplication()->input->set('hidemainmenu', true);

            JToolBarHelper::apply('sitemapitems.apply');
            JToolBarHelper::save('sitemapitems.save');
        }

        $alt = $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE';
        JToolBarHelper::cancel('sitemapitems.cancel', $alt);

        parent::setToolBar($addDivider);
    }
}
