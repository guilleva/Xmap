<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap;

use Alledia\Framework;

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
     */
    public function routeURL($url)
    {
        $container = Factory::getContainer();

        if (!$this->joomlaRouter) {
            // Get the router.
            $app = \JApplicationSite::getInstance('site');
            $this->joomlaRouter = $app::getRouter('site');

            // Make sure that we have our router
            if (!$this->joomlaRouter) {
                return null;
            }
        }

        if (!is_array($url) && (strpos($url, '&') !== 0) && (strpos($url, 'index.php') !== 0)) {
            return $url;
        }

        // Build route
        $scheme = array('path', 'query', 'fragment');
        $uri = $this->joomlaRouter->build($url);
        $url = $uri->toString($scheme);

        // Replace spaces.
        $url = preg_replace('/\s/u', '%20', $url);

        // Remove subfolders to return a relative frontend link
        $url = preg_replace('#^' . $container->uri->base(true) . '#', '', $url);

        // Remove administrator folder
        $url = preg_replace('#^/administrator/#', '', $url);

        return $url;
    }

    /**
     * This method returns a full URL related to frontend router. Specially
     * needed if the router is called by the admin
     *
     * @param string $url
     *
     * @return string
     */
    public function forceFrontendURL($url)
    {
        if (!preg_match('#^[^:]+://#', $url)) {
            $baseUri = $this->getFrontendBase();

            if (!substr_count($url, $baseUri)) {
                $url = $baseUri . $url;
            }
        }

        return $url;
    }

    /**
     * Checks if the supplied URL is internal
     *
     * @param   string  $url  The URL to check.
     *
     * @return  boolean  True if Internal.
     *
     * @since   11.1
     */
    public function isInternalURL($url)
    {
        $container = Factory::getContainer();

        $uri      = $container->uri->getInstance($url);
        $base     = $uri->toString(array('scheme', 'host', 'port', 'path'));
        $host     = $uri->toString(array('scheme', 'host', 'port'));
        $path     = $uri->toString(array('path'));
        $baseHost = $container->uri->getInstance($this->getFrontendBase())->toString(array('host'));

        // Check if we have a relative path as url, considering it will always be internal
        if ($path === $url) {
            return true;
        }

        $jriBase  = $this->getFrontendBase();

        // @see JURITest
        if (empty($host) && strpos($path, 'index.php') === 0
            || !empty($host) && preg_match('#' . preg_quote($jriBase, '#') . '#', $base)
            || !empty($host) && $host === $baseHost && strpos($path, 'index.php') !== false
            || !empty($host) && $base === $host && preg_match('#' . preg_quote($base, '#') . '#', $this->getFrontendBase())) {

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
     */
    public function getFrontendBase()
    {
        $container = Factory::getContainer();

        return preg_replace('#/administrator[/]?$#', '/', $container->uri->base());
    }

    /**
     * Check if the given URL is a relative URI. Returns true, if afirmative.
     *
     * @param string
     *
     * @return bool
     */
    public function isRelativeUri($url)
    {
        $container = Factory::getContainer();

        $uri  = $container->uri->getInstance($url);

        return $uri->toString(array('path')) === $url;
    }

    /**
     * Converts an internal relative URI into a full link.
     *
     * @param string $url
     *
     * @return string
     */
    public function convertRelativeUriToFullUri($path)
    {
        $path = preg_replace('#^/#', '', $path);
        $base = preg_replace('#/$#', '', $this->getFrontendBase());

        return $base . '/' . $path;
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

        // Remove trailing slash
        $url = preg_replace('#/$#', '', $url);

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
}
