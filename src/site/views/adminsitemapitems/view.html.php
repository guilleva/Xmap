<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Alledia\OSMap;

jimport('joomla.application.component.view');


class OSMapViewAdminSitemapItems extends JViewLegacy
{
    public function display($tpl = null)
    {
        $container = OSMap\Factory::getContainer();

        try {
            $id = $container->input->getInt('id');

            $this->params = JFactory::getApplication()->getParams();

            // Load the sitemap instance
            $this->sitemap     = OSMap\Factory::getSitemap($id, 'standard');
            $this->osmapParams = JComponentHelper::getParams('com_osmap');
        } catch (Exception $e) {
            $this->message = $e->getMessage();
        }

        parent::display($tpl);
    }
}
