<?php
/**
 * @package   OSMap
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2020 Joomlashack.com. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSMap.
 *
 * OSMap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSMap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSMap.  If not, see <http://www.gnu.org/licenses/>.
 */

use Alledia\OSMap;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();


class OSMapViewSitemaps extends OSMap\View\Admin\Base
{
    /**
     * @var object[]
     */
    protected $items = null;

    /**
     * @var Registry
     */
    protected $state = null;

    /**
     * @var JForm
     */
    public $filterForm = null;

    /**
     * @var array
     */
    public $activeFilters = null;

    /**
     * @var array
     */
    protected $languages = null;

    /**
     * @var object
     */
    protected $item = null;

    /**
     * @param null $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        /** @var OSMapModelSitemaps $model */
        $model = $this->getModel();

        $this->items         = $model->getItems();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        if ($errors = $model->getErrors()) {
            throw new Exception(implode("\n", $errors));
        }

        // We don't need toolbar or submenus in the modal window
        if ($this->getLayout() !== 'modal') {
            $this->setToolbar();
        }

        // Get the active languages for multi-language sites
        $this->languages = null;
        if (JLanguageMultilang::isEnabled()) {
            $this->languages = JLanguageHelper::getLanguages();
        }

        parent::display($tpl);
    }

    protected function setToolbar($addDivider = true)
    {
        $this->setTitle('COM_OSMAP_SUBMENU_SITEMAPS');

        OSMap\Helper\General::addSubmenu('sitemaps');

        ToolbarHelper::addNew('sitemap.add');
        ToolbarHelper::custom('sitemap.edit', 'edit.png', 'edit_f2.png', 'JTOOLBAR_EDIT', true);
        ToolbarHelper::custom('sitemaps.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_Publish', true);
        ToolbarHelper::custom('sitemaps.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::custom(
            'sitemap.setAsDefault',
            'featured.png',
            'featured_f2.png',
            'COM_OSMAP_TOOLBAR_SET_DEFAULT',
            true
        );

        if ($this->state->get('filter.published') == -2) {
            ToolbarHelper::deleteList('', 'sitemaps.delete', 'JTOOLBAR_DELETE');
        } else {
            ToolbarHelper::trash('sitemaps.trash', 'JTOOLBAR_TRASH');
        }

        parent::setToolBar($addDivider);
    }

    /**
     * @param string $type
     * @param string $lang
     *
     * @return string
     * @throws \Exception
     */
    protected function getLink($item, $type, $lang = null)
    {
        $view   = in_array($type, array('news', 'images')) ? 'xml' : $type;
        $menuId = empty($item->menuIdList[$view]) ? null : $item->menuIdList[$view];

        $query = array();

        if ($menuId) {
            $query['Itemid'] = $menuId;
        }

        if (empty($query['Itemid'])) {
            $query = array(
                'option' => 'com_osmap',
                'view'   => $view,
                'id'     => $item->id
            );
        }

        if ($type != $view) {
            $query[$type] = 1;
        }
        if ($view == 'xml') {
            $menu     = CMSApplication::getInstance('site')->getMenu()->getItem($menuId);
            $menuView = empty($menu->query['view']) ? null : $menu->query['view'];

            if ($view != $menuView) {
                $query['format'] = 'xml';
            }
        }

        if ($lang) {
            $query['lang'] = $lang;
        }

        $router = OSMap\Factory::getContainer()->router;

        return $router->routeURL('index.php?' . http_build_query($query));
    }
}
