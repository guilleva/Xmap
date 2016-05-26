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

class Cache
{
    /**
     * The sitemap id
     */
    protected $sitemapId;

    /**
     * Set the sitemap ID
     *
     * @param int $sitemapId
     */
    public function setSitemapId($sitemapId)
    {
        $this->sitemapId = (int)$sitemapId;
    }

    /**
     * Get the sitemap items cached in the database. If limit is null, all items
     * will be returned.
     *
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getItems($limit = null, $offset = null)
    {
        $db = OSMap\Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__osmap_sitemap_items_cache')
            ->where('sitemap_id = ' . $db->quote((int)$this->sitemapId))
            ->order(
                array(
                    'parent_id',
                    'level'
                )
            );

        if ($limit !== null) {
            $query->setLimit($limit, $offset);
        }

        $db->setQuery($query);

        return $db->loadAssocList();
    }

    /**
     * Updates the whole cache for the current sitemap.
     *
     * @return void
     */
    public function updateItems()
    {
        $collector = OSMap\Factory::getContainer()->sitemapCollector;

        $this->cleanupUpdateTable();

        $collector->fetch($this->sitemapId, array($this, 'registerItem'));
    }

    /**
     * Clean up the db table to receive the new items.
     *
     * @return void
     */
    protected function cleanupUpdateTable()
    {
        $db = OSMap\Factory::getDbo();

        $query = $db->getQuery(true)
            ->delete('#__osmap_sitemap_items_for_update')
            ->where('sitemap_id = ' . $db->quote($this->sitemapId));
        $db->setQuery($query);
        $db->execute();
    }

    public function registerItem($item)
    {
        var_dump($item);
    }
}
