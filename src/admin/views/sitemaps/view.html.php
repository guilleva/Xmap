<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2019 Joomlashack.com. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap;
use Joomla\CMS\Application\CMSApplication;

defined('_JEXEC') or die();


class OSMapViewSitemaps extends OSMap\View\Admin\Base
{
    /**
     * @var object[]
     */
    protected $items = null;

    /**
     * @var \Joomla\Registry\Registry
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

        $this->displayAlerts();
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
        JToolBarHelper::custom(
            'sitemap.setAsDefault',
            'featured.png',
            'featured_f2.png',
            'COM_OSMAP_TOOLBAR_SET_DEFAULT',
            true
        );

        if ($this->state->get('filter.published') == -2) {
            JToolBarHelper::deleteList('', 'sitemaps.delete', 'JTOOLBAR_DELETE');
        } else {
            JToolBarHelper::trash('sitemaps.trash', 'JTOOLBAR_TRASH');
        }

        parent::setToolBar($addDivider);
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function displayAlerts()
    {
        $app = JFactory::getApplication();
        if ($app->input->getInt('disablecache')) {
            $db = JFactory::getDbo();
            $db->setQuery(
                $db->getQuery(true)
                    ->update('#__extensions')
                    ->set('enabled = 0')
                    ->where(
                        array(
                            'type = ' . $db->quote('plugin'),
                            'element = ' . $db->quote('cache'),
                            'folder = ' . $db->quote('osmap')
                        )
                    )
            )->execute();
            $app->enqueueMessage(JText::_('COM_OSMAP_WARNING_CONFIRM_DISABLE_CACHE'));

            $url = JUri::getInstance();
            $url->delVar('disablecache');
            $app->redirect($url);

        } elseif (JLanguageMultilang::isEnabled() && JPluginHelper::getPlugin('osmap', 'cache')) {
            $url = JUri::getInstance();
            $url->setVar('disablecache', 1);
            $app->enqueueMessage(JText::sprintf('COM_OSMAP_WARNING_MULITLANGUAGE_CACHE', $url), 'warning');
        }
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
