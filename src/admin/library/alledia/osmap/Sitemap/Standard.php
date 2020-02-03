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

namespace Alledia\OSMap\Sitemap;

use Alledia\OSMap;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

class Standard implements SitemapInterface
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var Registry
     */
    public $params;

    /**
     * @var bool
     */
    public $isDefault = false;

    /**
     * @var bool
     */
    public $isPublished = true;

    /**
     * @var string
     */
    public $createdOn;

    /**
     * @var int
     */
    public $linkCount = 0;

    /**
     * @var string
     */
    protected $type = 'standard';

    /**
     * @var Collector
     */
    protected $collector;

    /**
     * Limit in days for news sitemap items
     *
     * @var int
     */
    public $newsDateLimit = 2;

    /**
     * The constructor
     *
     * @param int $id
     *
     * @return void
     * @throws \Exception
     */
    public function __construct($id)
    {
        $row = OSMap\Factory::getTable('Sitemap');
        $row->load($id);

        if (empty($row) || !$row->id) {
            throw new \Exception(\JText::_('COM_OSMAP_SITEMAP_NOT_FOUND'), 404);
        }

        $this->id          = $row->id;
        $this->name        = $row->name;
        $this->isDefault   = (bool)$row->is_default;
        $this->isPublished = $row->published == 1;
        $this->createdOn   = $row->created_on;
        $this->linksCount  = (int)$row->links_count;

        $this->params = new Registry();
        $this->params->loadString($row->params);

        $row = null;

        // Initiate the collector
        $this->initCollector();
    }

    /**
     * Method to initialize the items collector
     *
     * @return void
     */
    protected function initCollector()
    {
        $this->collector = new Collector($this);
    }

    /**
     * Traverse the sitemap items recursively and call the given callback,
     * passing each node as parameter.
     *
     * @param callable $callback
     * @param bool     $triggerEvents
     * @param bool     $updateCount
     *
     * @return void
     * @throws \Exception
     */
    public function traverse($callback, $triggerEvents = true, $updateCount = false)
    {
        if ($triggerEvents) {
            // Call the plugins, allowing to interact or override the collector
            \JPluginHelper::importPlugin('osmap');

            $eventParams = array(&$this, &$callback);
            $results     = \JEventDispatcher::getInstance()->trigger('osmapOnBeforeCollectItems', $eventParams);

            // A plugin asked to stop the traverse
            if (in_array(true, $results)) {
                return;
            }

            $results = null;
        }

        // Fetch the sitemap items
        $count = $this->collector->fetch($callback);

        if ($updateCount) {
            // Update the links count in the sitemap
            $this->updateLinksCount($count);
        }

        // Cleanup
        $this->collector->cleanup();
        $this->collector = null;
    }

    /**
     * Updates the count of links in the database
     *
     * @param int $count
     *
     * @return void
     */
    protected function updateLinksCount($count)
    {
        $db = OSMap\Factory::getDbo();

        $updateObject = (object)array(
            'id'          => $this->id,
            'links_count' => (int)$count
        );

        $db->updateObject('#__osmap_sitemaps', $updateObject, array('id'));
    }

    public function cleanup()
    {
        $this->collector = null;
        $this->params    = null;
    }
}
