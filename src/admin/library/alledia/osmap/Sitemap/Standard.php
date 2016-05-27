<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
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
        $db = OSMap\Factory::getContainer()->db;

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__osmap_sitemaps')
            ->where('id = ' . $db->quote($id));
        $row = $db->setQuery($query)->loadObject();

        if (empty($row)) {
            throw new \Exception(\JText::_('COM_OSMAP_SITEMAP_NOT_FOUND'));
        }

        $this->id          = $row->id;
        $this->name        = $row->name;
        $this->isDefault   = (bool)$row->is_default;
        $this->isPublished = (bool)$row->published;
        $this->createdOn   = $row->created_on;
        $this->linksCount  = (int)$row->links_count;

        $this->params = new \JRegistry;
        $this->params->loadString($row->params);

        // Initiate the collector
        $this->collector = new Collector($this);
    }

    /**
     * Traverse the sitemap items recursively and call the given callback,
     * passing each node as parameter.
     *
     * @param callable $callback
     *
     * @return void
     */
    public function traverse($callback)
    {
        $this->collector->fetch($callback);
    }
}
