<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved..
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


/**
 * @package    OSMap
 * @subpackage com_osmap
 */
class OSMapViewSitemap extends JViewLegacy
{
    /**
     * @var stdClass
     */
    protected $item;

    /**
     * @var array
     */
    protected $list;

    /**
     * @var object
     */
    protected $form;

    /**
     * @var JRegistry
     */
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

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));

            return false;
        }

        JHTML::stylesheet('media/com_osmap/css/admin.css');

        // Convert dates from UTC
        $offset = $app->getCfg('offset');
        if (intval($this->item->created)) {
            $this->item->created = JHtml::date($this->item->created, '%Y-%m-%d %H-%M-%S', $offset);
        }

        $this->addToolbar();

        // Load the extension
        $this->extension = Factory::getExtension('OSMap', 'component');
        $this->extension->loadLibrary();

        parent::display($tpl);

        JRequest::setVar('hidemainmenu', true);
    }

    /**
     * Display the toolbar
     *
     * @access    private
     */
    private function addToolbar()
    {
        $isNew = $this->item->id == 0;

        JToolBarHelper::title(JText::_('COM_OSMAP_PAGE_' . ($isNew ? 'ADD_SITEMAP' : 'EDIT_SITEMAP')), 'tree-2');

        JToolBarHelper::apply('sitemap.apply', 'JTOOLBAR_APPLY');
        JToolBarHelper::save('sitemap.save', 'JTOOLBAR_SAVE');
        JToolBarHelper::save2new('sitemap.save2new');

        if (!$isNew) {
            JToolBarHelper::save2copy('sitemap.save2copy');
        }

        JToolBarHelper::cancel('sitemap.cancel', 'JTOOLBAR_CLOSE');
    }
}
