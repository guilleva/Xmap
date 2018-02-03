<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Alledia\OSMap;

jimport('joomla.application.component.view');


class OSMapViewXml extends JViewLegacy
{
    /**
     * @var string
     */
    protected $type = null;

    /**
     * @var \Joomla\Registry\Registry
     */
    protected $params = null;

    /**
     * @var \Joomla\Registry\Registry
     */
    protected $osmapParams = null;

    /**
     * @var Alledia\OSMap\Sitemap\Standard
     */
    protected $sitemap = null;

    /**
     * @param null $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $container = OSMap\Factory::getContainer();

        // Help to show a clean XML without other content
        $container->input->set('tmpl', 'component');

        $id = $container->input->getInt('id');

        $this->type        = OSMap\Helper\General::getSitemapTypeFromInput();
        $this->params      = JFactory::getApplication()->getParams();
        $this->osmapParams = JComponentHelper::getParams('com_osmap');

        $this->sitemap = OSMap\Factory::getSitemap($id, $this->type);

        if (!$this->sitemap->isPublished) {
            throw new Exception(JText::_('COM_OSMAP_MSG_SITEMAP_IS_UNPUBLISHED'));
        }

        parent::display($tpl);

        // Force to show a clean XML without other content or execution plugins
        jexit();
    }
}
