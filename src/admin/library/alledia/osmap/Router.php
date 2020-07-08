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

namespace Alledia\OSMap;

use Alledia\Framework;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die();


class Router
{
    /**
     * @var object
     */
    protected $joomlaRouter;

    /**
     * Route the given URL using the site application. If in admin, the result
     * needs to be the same as the frontend. Replicates partially the native
     * JRoute::_ method, but forcing to use the frontend routes. Required to
     * allow see correct routed URLs in the admin while editing a sitemap.
     *
     * @param string $url
     *
     * @return string
     * @throws \Exception
     */
    public function routeURL($url)
    {
        if (!$this->joomlaRouter) {
            // Get the router.
            $app = CMSApplication::getInstance('site');

            $this->joomlaRouter = $app::getRouter('site');

            // Make sure that we have our router
            if (!$this->joomlaRouter) {
                return null;
            }
        }

        if (is_string($url) && (strpos($url, '&') !== 0) && (strpos($url, 'index.php') !== 0)) {
            return $url;
        }

        // Build route
        $scheme = array('path', 'query', 'fragment');
        $uri    = $this->joomlaRouter->build($url);
        $url    = $uri->toString($scheme);

        // Replace spaces.
        $url = preg_replace('/\s/u', '%20', $url);

        // Remove application subpaths (typically /administrator)
        $adminPath = str_replace(Uri::root(), '', Uri::base());
        $url       = str_replace($adminPath, '', $url);

        return $url;
    }

    /**
     * Checks if the supplied URL is internal
     *
     * @param string $url
     *
     * @return boolean
     *
     * @return bool
     * @throws \Exception
     */
    public function isInternalURL($url)
    {
        $uri      = Uri::getInstance($url);
        $base     = $uri->toString(array('scheme', 'host', 'port', 'path'));
        $host     = $uri->toString(array('scheme', 'host', 'port'));
        $path     = $uri->toString(array('path'));
        $baseHost = Uri::getInstance($uri->root())->toString(array('host'));

        if ($path === $url) {
            return true;

        } elseif (empty($host) && strpos($path, 'index.php') === 0
            || !empty($host) && preg_match('#' . preg_quote($uri->root(), '#') . '#', $base)
            || !empty($host) && $host === $baseHost && strpos($path, 'index.php') !== false
            || !empty($host) && $base === $host
            && preg_match('#' . preg_quote($base, '#') . '#', $uri->root())
        ) {
            return true;
        }

        return false;
    }

    /**
     * Returns the result of JUri::base() from the site used in the sitemap.
     * This is better than the JUri::base() because when we are editing a
     * sitemap in the admin that method returns the /administrator and mess
     * all the urls, which should point to the frontend only.
     *
     * @return string
     * @throws \Exception
     */
    public function getFrontendBase()
    {
        return Factory::getContainer()->uri->root();
    }

    /**
     * Check if the given URL is a relative URI. Returns true, if afirmative.
     *
     * @param string
     *
     * @return bool
     * @throws \Exception
     */
    public function isRelativeUri($url)
    {
        $container = Factory::getContainer();

        $uri = $container->uri->getInstance($url);

        return $uri->toString(array('path')) === $url;
    }

    /**
     * Converts an internal relative URI into a full link.
     *
     * @param string $path
     *
     * @return string
     * @throws \Exception
     */
    public function convertRelativeUriToFullUri($path)
    {
        if ($path[0] == '/') {
            $scheme = array('scheme', 'user', 'pass', 'host', 'port');
            $path   = Factory::getContainer()->uri->getInstance()->toString($scheme) . $path;

        } elseif ($this->isRelativeUri($path)) {
            $path = $this->getFrontendBase() . $path;
        }

        return $path;
    }

    /**
     * Returns a sanitized URL, removing double slashes and trailing slash.
     *
     * @return string
     */
    public function sanitizeURL($url)
    {
        // Remove double slashes
        $url = preg_replace('#([^:])(/{2,})#', '$1/', $url);

        return $url;
    }

    /**
     * Returns an URL without the hash
     *
     * @return string
     */
    public function removeHashFromURL($url)
    {
        // Check if the URL has a hash to remove it (XML sitemap shouldn't have hash on the URL)
        $hashPos = strpos($url, '#');

        if ($hashPos !== false) {
            // Remove the hash
            $url = substr($url, 0, $hashPos);
        }

        $url = trim($url);

        return $url;
    }

    /**
     * Create a consistent url hash regardless of scheme or site root.
     *
     * @param string $url
     *
     * @return string
     * @throws \Exception
     */
    public function createUrlHash($url)
    {
        return md5(str_replace(Factory::getContainer()->uri->root(), '', $url));
    }
}
