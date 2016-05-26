<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap;
use Alledia\Framework;

defined('_JEXEC') or die();


class OSMapControllerSitemap extends OSMap\Controller\Json
{
    public function getItems()
    {
        try {
            $sitemapId = OSMap\Factory::getApplication()->input->getInt('sitemap_id');

            $cache = OSMap\Factory::getContainer()->sitemapCache;
            $cache->setSitemapId($sitemapId);

            // Test only
            $cache->updateItems();

            $result = $cache->getItems();

            echo new JResponseJson($result);
        } catch (Exception $e) {
            echo new JResponseJson($e);
        }
    }
}
