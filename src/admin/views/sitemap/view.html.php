<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2020 Joomlashack.com. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap\Factory;
use Alledia\OSMap\View\Admin\Base;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Object\CMSObject;

defined('_JEXEC') or die();


class OSMapViewSitemap extends Base
{
    /**
     * @var CMSObject
     */
    protected $item = null;

    /**
     * @var Form
     */
    public $form = null;

    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        $this->setToolBar();

        parent::display($tpl);
    }

    /**
     * @param bool $addDivider
     *
     * @return void
     * @throws Exception
     */
    protected function setToolBar($addDivider = true)
    {
        $isNew = ($this->item->id == 0);
        Factory::getApplication()->input->set('hidemainmenu', true);

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
