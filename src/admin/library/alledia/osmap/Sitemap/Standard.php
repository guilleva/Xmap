<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Sitemap;

use Alledia\OSMap;

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
     * @var \JRegistry
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
     * The constructor
     *
     * @param int $id
     *
     * @return void
     */
    public function __construct($id)
    {
        $row = OSMap\Factory::getTable('Sitemap');
        $row->load($id);

        if (empty($row)) {
            throw new \Exception(\JText::_('COM_OSMAP_SITEMAP_NOT_FOUND'));
        }

        $this->id          = $row->id;
        $this->name        = $row->name;
        $this->isDefault   = (bool)$row->is_default;
        $this->isPublished = $row->published == 1;
        $this->createdOn   = $row->created_on;
        $this->linksCount  = (int)$row->links_count;

        $this->params = new \JRegistry;
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
     *
     * @return void
     */
    public function traverse($callback, $triggerEvents = true)
    {
        if ($triggerEvents) {
            // Prepare the plugins
            \JPluginHelper::importPlugin('osmap');

            // Call the plugins, allowing to interact or override the collector
            $eventParams = array(
                &$this,
                &$callback
            );
            $results = \JEventDispatcher::getInstance()->trigger('osmapOnBeforeCollectItems', $eventParams);

            // A plugin asked to stop the traverse
            if (in_array(true, $results)) {
                return;
            }

            $results     = null;
            $eventParams = array();
        }

        // Fetch the sitemap items
        $count = $this->collector->fetch($callback);

        // Update the links count in the sitemap
        $this->updateLinksCount($count);

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
        $row = OSMap\Factory::getTable('Sitemap');
        $row->load($this->id);

        $data = array('links_count' => (int)$count);
        $row->save($data);
    }

    public function cleanup()
    {
        $this->collector = null;
        $this->params = null;
    }
}
