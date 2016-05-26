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

class Standard
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var \JRegistry
     */
    protected $params;

    /**
     * @var bool
     */
    protected $isDefault = false;

    /**
     * @var bool
     */
    protected $isPublished = true;

    /**
     * @var string
     */
    protected $createdOn;

    /**
     * @var int
     */
    protected $linkCount = 0;

    /**
     * @var string
     */
    protected $type = 'standard';

    /**
     * The constructor
     *
     * @param int $id
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
    }
}
