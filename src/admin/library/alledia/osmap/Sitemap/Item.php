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

/**
 * Sitemap item
 */
class Item extends BaseItem
{
    /**
     * The constructor
     *
     * @param object  $item
     * @param Sitemap $sitemap
     * @param Object  $menu
     *
     * @return void
     */
    public function __construct($item, $sitemap, $menu)
    {
        parent::__construct($item, $sitemap, $menu);

        // Check if the link is an internal link
        $this->isInternal = $this->checkLinkIsInternal();

        $this->isMenuItem = (bool)$this->isMenuItem;

        $this->prepareParams();
        $this->setModificationDate();
        $this->setLink();
        $this->extractComponentFromLink();
        $this->setFullLink();

        // Sanitize internal links
        if ($this->isInternal) {
            $this->sanitizeFullLink();
        }

        // Make sure to have a hash of the full link
        $this->fullLinkHash = md5($this->fullLink);

        // Prepare the boolean attributes
        $this->published = (bool)$this->published;

        /*
         * Do not use a "prepare" method because we need to make sure it will
         * be calculated after the link is set.
         */
        $this->calculateUID();
    }

    /**
     * Prepares the param attribute to make sure it is always an instance of
     * \JRegistry.
     *
     * @return \JRegistry
     */
    protected function prepareParams()
    {
        if (is_string($this->params)) {
            $this->params = new \JRegistry($this->params);
        }
    }

    /**
     * Calculate a hash based on the link, to avoid duplicated links. It will
     * set the new UID to the item.
     *
     * @param bool   $force
     * @param string $prefix
     *
     * @return void
     */
    public function calculateUID($force = false, $prefix = '')
    {
        if (empty($this->uid) || $force) {
            $this->set('uid', $prefix . md5($this->fullLink));
        }
    }

    /**
     * Set the link in special cases, like alias, where the link doesn't have
     * the correct related menu id.
     *
     * @return void
     */
    protected function setLink()
    {
        // If is an alias, use the Itemid stored in the parameters to get the correct url
        if ($this->type === 'alias') {
            // Get the related menu item's link
            $db = OSMap\Factory::getDbo();

            $query = $db->getQuery(true)
                ->select('link')
                ->from('#__menu')
                ->where('id = ' . $db->quote($this->params->get('aliasoptions')));

            $this->link = $db->setQuery($query)->loadResult();
        }
    }

    /**
     * Sanitize the link removing double slashes and trailing slash
     *
     * @return void
     */
    protected function sanitizeFullLink()
    {
        $this->fullLink = OSMap\Router::sanitizeURL($this->fullLink);
    }

    /**
     * Converts the current link to a full URL, including the base URI.
     * If the item is the home menu, will return the base URI. If internal,
     * will return a routed full URL (SEF, if enabled). If it is an external
     * URL, won't change the link.
     *
     * @return void
     */
    protected function setFullLink()
    {
        if ((bool)$this->home) {
            // Correct the URL for the home page.
            $this->fullLink = OSMap\Router::getFrontendBase();

            // Check if multi-language is enabled to use the proper route
            if (\JLanguageMultilang::isEnabled()) {
                $lang  = OSMap\Factory::getLanguage();
                $tag   = $lang->getTag();
                $homes = \JLanguageMultilang::getSiteHomePages();

                if (isset($homes[$tag])) {
                    $home = $homes[$tag];
                } else {
                    $home = $homes['*'];
                }

                $this->fullLink .= OSMap\Router::routeURL('index.php?Itemid=' . $home->id);
            }

            // Removes the /administrator from the URI if in the administrator
            $this->fullLink = OSMap\Router::sanitizeURL(
                OSMap\Router::forceFrontendURL($this->fullLink)
            );

            return;
        }

        // Check if is not a url, and disable browser nav
        if ($this->type === 'separator' || $this->type === 'heading') {
            $this->browserNav = 3;
            // Always a unique UID, since this only appears in the HTML sitemap
            $this->set('uid', $this->type . '.' . md5($this->name . $this->id));

            return;
        }

        // If is an URL, but external, return the external URL. If internal,
        // follow with the routing
        if ($this->type === 'url') {
            // Check if it is a relative URI
            if (OSMap\Router::isRelativeUri($this->link)) {
                $this->fullLink = OSMap\Router::sanitizeURL(
                    OSMap\Router::convertRelativeUriToFullUri($this->link)
                );

                return;
            }

            $this->fullLink = $this->link;

            if (!$this->isInternal) {
                // External URLS have UID as a hash of its url
                $this->calculateUID(true, 'external.');

                return;
            }
        }

        if ($this->type === 'alias') {
            // Use the destination itemid, instead of the alias' item id.
            // This will make sure we have the correct routed url.
            $this->fullLink = 'index.php?Itemid=' . $this->params->get('aliasoptions');
        }

        // If is a menu item but not an alias, force to use the current menu's item id
        if ($this->isMenuItem && $this->type !== 'alias' && $this->type !== 'url') {
            $this->fullLink = 'index.php?Itemid=' . $this->id;
        }

        // If is not a menu item, use as base for the fullLink, the item link
        if (!$this->isMenuItem) {
            $this->fullLink = $this->link;
        }

        if ($this->isInternal) {
            // Route the full link
            $this->fullLink = OSMap\Router::routeURL($this->fullLink);

            // Make sure the link has the base uri
            $this->fullLink = OSMap\Router::forceFrontendURL($this->fullLink);
        }

        $this->fullLink = OSMap\Router::sanitizeURL($this->fullLink);
    }

    /**
     * Set the item adapter according to the type of content. The adapter can
     * extract custom params from the item like params and metadata.
     *
     * @return void
     */
    public function setAdapter()
    {
        // Check if there is class for the option
        $adapterClass = '\\Alledia\\OSMap\\Sitemap\\ItemAdapter\\' . $this->adapterName;
        if (class_exists($adapterClass)) {
            $this->adapter = new $adapterClass($this);
        } else {
            $this->adapter = new ItemAdapter\Generic($this);
        }
    }
}
