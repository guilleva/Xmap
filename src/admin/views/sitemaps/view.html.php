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


class OSMapViewSitemaps extends OSMap\View\Admin
{
    /**
     * @var array
     */
    protected $items = array();

    public function display($tpl = null)
    {
        $this->state         = $this->get('State');
        $this->items         = $this->get('Items');

        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        $this->setToolbar();

        parent::display($tpl);
    }

    protected function setToolbar($addDivider = true)
    {
        OSMap\Helper::addSubmenu('plans');

        $this->setTitle('COM_OSMAP_SUBMENU_SITEMAPS');

        // SimplerenewToolbarHelper::addNew('plan.add');
        // SimplerenewToolbarHelper::editList('plan.edit');
        // SimplerenewToolbarHelper::publish('plans.publish', 'JTOOLBAR_PUBLISH', true);
        // SimplerenewToolbarHelper::unpublish('plans.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        // SimplerenewToolbarHelper::deleteList('COM_SIMPLERENEW_PLAN_DELETE_CONFIRM', 'plans.delete');

        parent::setToolBar($addDivider);
    }
}
