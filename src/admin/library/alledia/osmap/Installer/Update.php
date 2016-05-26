<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Installer;

use Alledia\Framework;

defined('_JEXEC') or die();

/**
 * OSMap Updater
 */
class Update
{
    /**
     * Get a list of sitemaps or false, if an error was found.
     *
     * @return mixed
     */
    protected static function getSitemapsWithDescription()
    {
        $db = Framework\Factory::getDbo();

        $query = $db->getQuery(true)
            ->select(
                array(
                    'id',
                    'description'
                )
            )
            ->from('#__osmap_sitemaps');

        // The description column can not exist anymore, so let's ignore errors
        $sitemaps = @$db->setQuery($query)->loadObjectList();

        if ($db->getErrorNum()) {
            return false;
        }

        return $sitemaps;
    }

    /**
     * Returns the sitemap id extracted from a menu link. Basically extracts
     * the query param ID. Returns false if no id was found.
     *
     * @param string $link
     *
     * @return mixed.
     */
    protected static function getSitemapIdFromLink($link)
    {
        $query = parse_url($link, PHP_URL_QUERY);
        parse_str($query, $params);

        return isset($params['id']) ? $params['id'] : false;
    }

    /**
     * Get a list of sitemap menu items which are related to the
     * given sitemap id.
     *
     * @param int $sitemapId
     *
     * @return array
     */
    protected static function getHTMLSitemapMenuItems($sitemapId)
    {
        $db    = Framework\Factory::getDbo();
        $osmap = Framework\Factory::getExtension('OSMap', 'component');

        $query = $db->getQuery(true)
            ->select(
                array(
                    'id',
                    'params',
                    'link'
                )
            )
            ->from('#__menu')
            ->where(
                array(
                    'type = ' . $db->quote('component'),
                    'component_id = ' . $osmap->getId(),
                    'link LIKE ' . $db->quote('%view=html%')
                )
            );

        $items = $db->setQuery($query)->loadObjectList();

        // Test the sitemap id into the menu objects
        $matchedItems = array();
        if (!empty($items)) {
            foreach ($items as &$menuItem) {
                if ($sitemapId == static::getSitemapIdFromLink($menuItem->link)) {
                    $paramsStr = $menuItem->params;

                    // Convert the params string to JRegistry object
                    $menuItem->params = new \JRegistry();
                    $menuItem->params->loadString($paramsStr);

                    $paramsStr = null;

                    $matchedItems[] = $menuItem;
                }
            }
        }

        return $matchedItems;
    }

    /**
     * Update the params for the given menu item.
     *
     * @param object $item  The given menu item
     *
     * @return void
     */
    protected static function updateMenuItemParams($item)
    {
        $db = Framework\Factory::getDbo();

        $params = $item->params;
        if (!is_string($params) && is_object($params)) {
            $params = $params->toString();
        }

        $query = $db->getQuery(true)
            ->update('#__menu')
            ->set('params = ' . $db->quote($params));
        $db->setQuery($query)->execute();
    }

    /**
     * Check all sitemaps and look for menus to move the description field.
     *
     * @return void
     */
    public static function moveSitemapDescriptionToHtmlMenus()
    {
        $sitemaps = static::getSitemapsWithDescription();

        if (!empty($sitemaps)) {
            foreach ($sitemaps as $sitemap) {
                if (isset($sitemap->description)) {
                    $menuItems = static::getHTMLSitemapMenuItems($sitemap->id);

                    if (!empty($menuItems)) {
                        foreach ($menuItems as $item) {
                            // Check if the menu didn't received any description
                            if (empty($item->params->get('sitemap_description'))) {
                                $item->params->set('sitemap_description', $sitemap->description);

                                static::updateMenuItemParams($item);
                            }
                        }
                    }
                }
            }
        }
    }
}
