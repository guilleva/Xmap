<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2015 Open Source Training, LLC. All rights reserved..
 * @author    Guillermo Vargas <guille@vargas.co.cr>
 * @author    Alledia <support@alledia.com>
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file is part of OSMap.
 *
 * OSMap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * OSMap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSMap. If not, see <http://www.gnu.org/licenses/>.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Alledia\Framework\Factory;

jimport('joomla.application.component.view');

# For compatibility with older versions of Joola 2.5
if (!class_exists('JViewLegacy')){
    class JViewLegacy extends JView {

    }
}

/**
 * @package    OSMap
 * @subpackage com_osmap
 */
class OSMapViewSitemap extends JViewLegacy
{
    protected $item;

    protected $list;

    protected $form;

    protected $state;


    /**
     * Display the view
     *
     * @access    public
     */
    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $this->state = $this->get('State');
        $this->item  = $this->get('Item');
        $this->form  = $this->get('Form');

        $version = new JVersion;

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));

            return false;
        }

        JHTML::stylesheet('administrator/components/com_osmap/css/osmap.css');

        // Convert dates from UTC
        $offset = $app->getCfg('offset');
        if (intval($this->item->created)) {
            $this->item->created = JHtml::date($this->item->created, '%Y-%m-%d %H-%M-%S', $offset);
        }

        $this->_setToolbar();

        if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
            $tpl = 'legacy';
        }

        // Load the extension
        $extension = Factory::getExtension('OSMap', 'component');
        $extension->loadLibrary();

        $this->assignRef("extension", $extension);

        parent::display($tpl);

        JRequest::setVar('hidemainmenu', true);
    }

    /**
     * Display the view
     *
     * @access    public
     */
    public function navigator($tpl = null)
    {
        require_once(JPATH_COMPONENT_SITE . '/helpers/osmap.php');

        $app         = JFactory::getApplication();
        $this->state = $this->get('State');
        $this->item  = $this->get('Item');

        # $menuItems = OSMapHelper::getMenuItems($item->selections);
        # $extensions = OSMapHelper::getExtensions();
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));

            return false;
        }

        JHTML::script('mootree.js', 'media/system/js/');
        JHTML::stylesheet('mootree.css', 'media/system/css/');

        $this->loadTemplate('class');
        $displayer = new OSMapNavigatorDisplayer($state->params, $this->item);

        parent::display($tpl);
    }

    public function navigatorLinks($tpl = null)
    {

        require_once(JPATH_COMPONENT_SITE . '/helpers/osmap.php');

        $link   = urldecode(JRequest::getVar('link', ''));
        $name   = JRequest::getCmd('e_name', '');
        $Itemid = JRequest::getInt('Itemid');

        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        $menuItems  = OSMapHelper::getMenuItems($item->selections);
        $extensions = OSMapHelper::getExtensions();

        $this->loadTemplate('class');
        $nav = new OSMapNavigatorDisplayer($state->params, $item);
        $nav->setExtensions($extensions);

        $this->list = array();

        // Show the menu list
        if (!$link && !$Itemid) {
            foreach ($menuItems as $menutype => &$menu) {
                $menu = new stdclass();
                #$menu->id = 0;
                #$menu->menutype = $menutype;

                $node = new stdClass;
                $node->uid = "menu-" . $menutype;
                $node->menutype = $menutype;
                $node->ordering = $item->selections->$menutype->ordering;
                $node->priority = $item->selections->$menutype->priority;
                $node->changefreq = $item->selections->$menutype->changefreq;
                $node->browserNav = 3;
                $node->type = 'separator';
                if (!$node->name = $nav->getMenuTitle($menutype, @$menu->module)) {
                    $node->name = $menutype;
                }
                $node->link = '-menu-' . $menutype;
                $node->expandible = true;
                $node->selectable = false;
                //$node->name = $this->getMenuTitle($menutype,@$menu->module);    // get the mod_mainmenu title from modules table

                $this->list[] = $node;
            }
        } else {
            $parent = new stdClass;

            if ($Itemid) {
                // Expand a menu Item
                $items = &JSite::getMenu();
                $node = & $items->getItem($Itemid);
                if (isset($menuItems[$node->menutype])) {
                    $parent->name = $node->title;
                    $parent->id = $node->id;
                    $parent->uid = 'itemid' . $node->id;
                    $parent->link = $link;
                    $parent->type = $node->type;
                    $parent->browserNav = $node->browserNav;
                    $parent->priority = $item->selections->{$node->menutype}->priority;
                    $parent->changefreq = $item->selections->{$node->menutype}->changefreq;
                    $parent->menutype = $node->menutype;
                    $parent->selectable = false;
                    $parent->expandible = true;
                }
            } else {
                $parent->id = 1;
                $parent->link = $link;
            }

            $this->list = $nav->expandLink($parent);
        }

        parent::display('links');

        exit;
    }

    /**
     * Display the toolbar
     *
     * @access    private
     */
    private function _setToolbar()
    {
        $user = JFactory::getUser();

        $isNew = ($this->item->id == 0);

        if (version_compare(JVERSION, '3.0', '<')) {
            JToolBarHelper::title(JText::_('OSMAP_PAGE_' . ($isNew ? 'ADD_SITEMAP' : 'EDIT_SITEMAP')), 'article-add.png');
        } else {
            JToolBarHelper::title(JText::_('OSMAP_PAGE_' . ($isNew ? 'ADD_SITEMAP' : 'EDIT_SITEMAP')), 'tree-2');
        }

        JToolBarHelper::apply('sitemap.apply', 'JTOOLBAR_APPLY');
        JToolBarHelper::save('sitemap.save', 'JTOOLBAR_SAVE');
        JToolBarHelper::save2new('sitemap.save2new');
        if (!$isNew) {
            JToolBarHelper::save2copy('sitemap.save2copy');
        }
        JToolBarHelper::cancel('sitemap.cancel', 'JTOOLBAR_CLOSE');
    }

}
