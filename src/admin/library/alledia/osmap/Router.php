<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap;

use Alledia\Framework;

defined('_JEXEC') or die();


abstract class Router
{
    /**
     * @var object
     */
    protected static $router;

    /**
     * Route the given URL using the site application. If in admin, the result
     * needs to be the same as the frontend. Replicates partially the native
     * JRoute::_ method, but forcing to use the frontend routes. Required to
     * allow see correct routed URLs in the admin while editing a sitemap.
     */
    public static function routeURL($url)
    {
        if (!static::$router) {
            // Get the router.
            $app = \JApplicationSite::getInstance('site');
            static::$router = $app::getRouter('site');

            // Make sure that we have our router
            if (!static::$router) {
                return null;
            }
        }

        if (!is_array($url) && (strpos($url, '&') !== 0) && (strpos($url, 'index.php') !== 0)) {
            return $url;
        }

        // Build route.
        $uri = static::$router->build($url);

        $scheme = array('path', 'query', 'fragment');

        $url = $uri->toString($scheme);

        // Replace spaces.
        $url = preg_replace('/\s/u', '%20', $url);

        // Extract the subfolders to replace and return a relative frontend link, without any subfolder
        preg_match('#(.*)?index\.php#i', $uri->toString(array('path')), $matches);
        if (isset($matches[1])) {
            $url = preg_replace('#^' . $matches[1] . '#', '', $url);
        } else {
            $url = preg_replace('#^/administrator/#', '', $url);
        }

        return $url;
    }
}
