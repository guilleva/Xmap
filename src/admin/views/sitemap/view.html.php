<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap;

defined('_JEXEC') or die();


class OSMapViewSitemap extends OSMap\View\Admin\Base
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

        $this->setToolBar();

        parent::display($tpl);
    }

    protected function setToolBar($addDivider = true)
    {
        $isNew = ($this->item->id == 0);
        OSMap\Factory::getApplication()->input->set('hidemainmenu', true);

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
