<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Alledia\OSMap;

jimport('joomla.application.component.view');


class OSMapViewXml extends JViewLegacy
{
    public function display($tpl = null)
    {
        $container = OSMap\Factory::getContainer();

        $container->input->set('tmpl', 'component');

        try {
            $id   = $container->input->getInt('id');
            $type = OSMap\Helper::getSitemapTypeFromInput();

            $this->sitemap = OSMap\Factory::getSitemap($id, $type);
        } catch (Exception $e) {
            $this->message = $e->getMessage();
        }

        parent::display($tpl);

        jexit();
    }
}
