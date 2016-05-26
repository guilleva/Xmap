<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Sitemap;

use Alledia\Framework;

defined('_JEXEC') or die();

/**
 * Sitemap items collector
 */
class Collector
{
    /**
     * Collect sitemap items based on menus.
     *
     * @param int      $sitemapId
     * @param callable $callbackForItems
     *
     * @return array
     */
    public function fetch($sitemapId, $callbackForItems)
    {
        // Get the selected menus
        $db = Framework\Factory::getDbo();

        $callbackForItems(1);
        $callbackForItems(2);
        $callbackForItems(4);
        $callbackForItems(6);
        $callbackForItems(3);
    }
}
