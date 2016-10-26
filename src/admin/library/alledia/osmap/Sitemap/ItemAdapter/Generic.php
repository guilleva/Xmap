<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Sitemap\ItemAdapter;

use Alledia\OSMap;

defined('_JEXEC') or die();

class Generic implements AdapterInterface
{
    /**
     * @var OSMap\Sitemap\Item
     */
    protected $item;

    /**
     * The constructor
     *
     * @param OSMap\Sitemap\Item $item
     *
     * @return void
     */
    public function __construct(OSMap\Sitemap\Item $item)
    {
        $this->item = $item;
    }

    /**
     * Gets the visible state for robots. Each adapter will check specific params. Returns
     * true if the item is visible.
     *
     * @return void
     */
    public function checkVisibilityForRobots()
    {
        $this->item->visibleForRobots = true;
    }

    /**
     * Sets the images attribute to the item. This method should be overriden
     * by child adapters to extract images from the specific content's text or
     * params.
     *
     * @return void
     */
    public function extractImages()
    {
        // Should be overriden
    }
}
