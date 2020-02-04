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

use Alledia\OSMap\Factory;
use Alledia\OSMap\Helper\General;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

class OSMapViewXml extends HtmlView
{
    /**
     * @var string
     */
    protected $type = null;

    /**
     * @var Registry
     */
    protected $params = null;

    /**
     * @var Registry
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
        $document = JFactory::getDocument();
        $document->setMimeEncoding('text/xml');

        $container = OSMap\Factory::getContainer();

        // Help to show a clean XML without other content
        $container->input->set('tmpl', 'component');

        $id = $container->input->getInt('id');

        $this->type        = General::getSitemapTypeFromInput();
        $this->params      = Factory::getApplication()->getParams();
        $this->osmapParams = ComponentHelper::getParams('com_osmap');

        $this->sitemap = Factory::getSitemap($id, $this->type);

        if (!$this->sitemap->isPublished) {
            throw new Exception(JText::_('COM_OSMAP_MSG_SITEMAP_IS_UNPUBLISHED'));
        }

        parent::display($tpl);
    }
}
