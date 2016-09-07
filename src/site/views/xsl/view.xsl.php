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


class OSMapViewXsl extends JViewLegacy
{
    public function display($tpl = null)
    {
        $container = OSMap\Factory::getContainer();

        // Help to show a clean XSL without other content
        $container->input->set('tmpl', 'component');

        // Get the title
        $this->pageHeading = htmlspecialchars(urldecode(JFactory::getApplication()->input->getRaw('title')));

        parent::display($tpl);

        // Force to show a clean XSL without other content
        jexit();
    }
}
