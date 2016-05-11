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

namespace Alledia\OSMap\Free\Sitemap\Displayer;

use JModelAdmin;
use JRegistry;

defined('_JEXEC') or die();

jimport('joomla.application.component.modeladmin');

/**
 * Base class for sitemap displayers
 */
abstract class Base implements Displayable
{
    /**
     * The sitemap model.
     *
     * @var OSMapModel
     */
    protected $sitemapModel;

    /**
     * The sitemap instance
     *
     * @var JObject
     */
    protected $sitemap;

    /**
     * The constructor
     *
     * @param  int  $sitemapId
     *
     * @return void
     */
    public function __construct($sitemapId)
    {
        if ($sitemapId > 0) {
            $this->sitemapModel = JModelAdmin::getInstance('Sitemap', 'OSMapModel');
            $this->sitemap      = $this->sitemapModel->getItem($sitemapId);

            // Load the sitemap menu selections
            $selections = new JRegistry();
            $this->sitemap->selections = $selections->loadString($this->sitemap->selections);
        }
    }

    /**
     * Echo the sitemap content
     *
     * @return void
     */
    public function display()
    {
        // To be extended
    }
}
