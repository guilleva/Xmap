<?php
/**
 * @version             $Id$
 * @copyright           Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
 */
// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * @package	Xmap
 * @subpackage	com_xmap
 */
class XmapViewSitemap extends JView
{

    /**
     * Display the view
     *
     * @access	public
     */
    function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $state = $this->get('State');
        $item = $this->get('Item');
        $form = $this->get('Form');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        JHTML::stylesheet('administrator/components/com_xmap/css/xmap.css');
        // Convert dates from UTC
        $offset = $app->getCfg('offset');
        if (intval($item->created)) {
            $item->created = JHtml::date($item->created, '%Y-%m-%d %H-%M-%S', $offset);
        }


        $form->bind($item);

        $this->assignRef('state', $state);
        $this->assignRef('item', $item);
        $this->assignRef('form', $form);

        $this->_setToolbar();
        parent::display($tpl);
        JRequest::setVar('hidemainmenu', true);
    }

    /**
     * Display the view
     *
     * @access	public
     */
    function navigator($tpl = null)
    {
        require_once(JPATH_COMPONENT_SITE . DS . 'helpers' . DS . 'xmap.php');
        $app = JFactory::getApplication();
        $state = $this->get('State');
        $item = $this->get('Item');

        # $menuItems = XmapHelper::getMenuItems($item->selections);
        # $extensions = XmapHelper::getExtensions();
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        JHTML::script('mootree.js', 'media/system/js/');
        JHTML::stylesheet('mootree.css', 'media/system/css/');

        $this->loadTemplate('class');
        $displayer = new XmapNavigatorDisplayer($state->params, $item);

        # $displayer->setMenuItems($menuItems);
        # $displayer->setExtensions($extensions);

        $this->assignRef('state', $state);
        $this->assignRef('item', $item);

        parent::display($tpl);
    }

    function navigatorLinks($tpl = null)
    {

        require_once(JPATH_COMPONENT_SITE . DS . 'helpers' . DS . 'xmap.php');
        $link = urldecode(JRequest::getVar('link', ''));
        $name = JRequest::getCmd('e_name', '');
        $Itemid = JRequest::getInt('Itemid');

        $item = $this->get('Item');
        $state = $this->get('State');
        $menuItems = XmapHelper::getMenuItems($item->selections);
        $extensions = XmapHelper::getExtensions();

        $this->loadTemplate('class');
        $nav = new XmapNavigatorDisplayer($state->params, $item);
        $nav->setExtensions($extensions);

        $list = array();
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
                //$node->name = $this->getMenuTitle($menutype,@$menu->module);	// get the mod_mainmenu title from modules table

                $list[] = $node;
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
            $list = $nav->expandLink($parent);
        }

        $this->assignRef('item', $item);
        $this->assignRef('list', $list);
        parent::display('links');
        exit;
    }

    /**
     * Display the toolbar
     *
     * @access	private
     */
    function _setToolbar()
    {
        $user = JFactory::getUser();
        $isNew = ($this->item->id == 0);

        JToolBarHelper::title(JText::_('XMAP_PAGE_' . ($isNew ? 'ADD_SITEMAP' : 'EDIT_SITEMAP')), 'article-add.png');

        // If an existing item, can save to a copy.
        if (!$isNew) {
            JToolBarHelper::custom('sitemap.save2copy', 'copy.png', 'copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
        }

        JToolBarHelper::custom('sitemap.save2new', 'new.png', 'new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
        JToolBarHelper::save('sitemap.save', 'JTOOLBAR_SAVE');
        JToolBarHelper::apply('sitemap.apply', 'JTOOLBAR_APPLY');
        JToolBarHelper::cancel('sitemap.cancel', 'JTOOLBAR_CANCEL');
        JToolBarHelper::divider();
        JToolBarHelper::help('screen.xmap.sitemap');
    }

}
