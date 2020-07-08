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
use Alledia\OSMap\Sitemap\Item;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Router\Route;
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
     * @var string
     */
    protected $language = null;

    /**
     * @var DateTime
     */
    protected $newsCutoff = null;

    /**
     * @var int
     */
    protected $newsLimit = 1000;

    /**
     * @var string
     */
    protected $pageHeading = null;

    /**
     * @param null $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $document = Factory::getDocument();
        if ($document->getType() != 'xml') {
            // There are ways to get here with a non-xml document, so we have to redirect
            $uri = Uri::getInstance();
            $uri->setVar('format', 'xml');

            Factory::getApplication()->redirect($uri);

            // Not strictly necessary, but makes the point :)
            return;
        }

        /** @var SiteApplication $app */
        $app = Factory::getApplication();

        $this->type    = General::getSitemapTypeFromInput();
        $this->sitemap = Factory::getSitemap($app->input->getInt('id'), $this->type);
        if (!$this->sitemap->isPublished) {
            throw new Exception(Text::_('COM_OSMAP_MSG_SITEMAP_IS_UNPUBLISHED'));
        }

        $this->params      = $app->getParams();
        $this->osmapParams = ComponentHelper::getParams('com_osmap');
        $this->language    = $document->getLanguage();
        $this->newsCutoff  = new DateTime('-' . $this->sitemap->newsDateLimit . ' days');

        if ($this->params->get('show_page_heading', 1)) {
            $this->pageHeading = $this->params->get('page_heading')
                ?: $this->params->get('page_title')
                    ?: $this->sitemap->name;
        }

        if ($this->params->get('debug', 0)) {
            $document->setMimeEncoding('text/plain');
        }

        parent::display($tpl);

        // @TODO: Does this really help at all?
        $this->sitemap->cleanup();
        $this->sitemap = null;
    }

    /**
     * @return string
     */
    protected function addStylesheet()
    {
        if ($this->params->get('add_styling', 1)) {
            $query = array(
                'option' => 'com_osmap',
                'view'   => 'xsl',
                'format' => 'xsl',
                'layout' => $this->type,
                'id'     => $this->sitemap->id
            );
            if ($this->params->get('show_page_heading', 1)) {
                $query['title'] = urlencode($this->pageHeading);
            }

            return sprintf(
                '<?xml-stylesheet type="text/xsl" href="%s"?>',
                Route::_('index.php?' . http_build_query($query))
            );
        }

        return '';
    }

    /**
     * @param Item $node
     *
     * @return DateTime
     * @throws Exception
     */
    protected function isNewsPublication(Item $node)
    {
        $publicationDate = (
            !empty($node->publishUp)
            && $node->publishUp != Factory::getDbo()->getNullDate()
            && $node->publishUp != -1
        ) ? $node->publishUp : null;

        if ($publicationDate) {
            $publicationDate = new DateTime($publicationDate);

            if ($this->newsCutoff <= $publicationDate) {
                $this->newsLimit--;
                if ($this->newsLimit >= 0) {
                    return $publicationDate;
                }
            }
        }

        return null;
    }
}
