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
class Item extends \JObject
{
    /**
     * @var int;
     */
    public $id;

    /**
     * @var string;
     */
    public $uid;

    /**
     * @var string
     */
    public $link;

    /**
     * @var string
     */
    public $fullLink;

    /**
     * @var \JRegistry
     */
    public $params;

    /**
     * @var string
     */
    public $priority;

    /**
     * @var string
     */
    public $changefreq;

    /**
     * @var string
     */
    public $modified;

    /**
     * The component associated to the option URL param
     *
     * @var string
     */
    public $option;

    /**
     * @var Sitemap
     */
    public $sitemap;

    /**
     * @var bool
     */
    public $ignore = false;

    /**
     * @var int
     */
    public $browserNav = null;

    /**
     * @var bool
     */
    public $isInternal = true;

    /**
     * @var bool
     */
    public $home = false;

    /**
     * @var string
     */
    public $type;

    /**
     * @var bool
     */
    public $expandible = false;

    /**
     * @var bool
     */
    public $secure = false;

    /**
     * @var int
     */
    public $isMenuItem = 0;

    /**
     * The constructor
     *
     * @param object  $item
     * @param Sitemap $sitemap
     *
     * @return void
     */
    public function __construct($item, $sitemap)
    {
        $this->setProperties($item);

        $this->set('sitemap', $sitemap);

        // Check if the link is an internal link
        $this->isInternal = \JUri::isInternal($this->link);

        $this->extractOptionFromLink();
        $this->prepareParams();
        $this->setModificationDate();
        $this->setFullLink();

        if ($this->isInternal) {
            $this->sanitizeLink();
        }

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
     * @return void
     */
    public function calculateUID()
    {
        $this->set('uid', md5($this->sitemap->id . ':' . $this->fullLink));
    }

    /**
     * Extract the option from the link, to identify the component called by
     * the link.
     *
     * @return void
     */
    protected function extractOptionFromLink()
    {
        $this->option = null;

        if (preg_match('#^/?index.php.*option=(com_[^&]+)#', $this->link, $matches)) {
            $this->option = $matches[1];
        }
    }

    /**
     * Set the correct modification date.
     *
     * @return void
     */
    protected function setModificationDate()
    {
        if (OSMap\Helper::isEmptyDate($this->modified)) {
            $this->modified = time();
        }

        if (!OSMap\Helper::isEmptyDate($this->modified)) {
            if (!is_numeric($this->modified)) {
                $date =  new \JDate($this->modified);
                $this->modified = $date->toUnix();
            }

            $this->modified = gmdate('Y-m-d\TH:i:s\Z', $this->modified);
        }
    }

    /**
     * Sanitize the link removing double slashes and trailing slash
     *
     * @return void
     */
    protected function sanitizeLink()
    {
        // Remove double slashes
        $this->fullLink = preg_replace('#([^:])(/{2,})#', '$1/', $this->fullLink);

        // Remove trailing slash
        $this->fullLink = preg_replace('#/$#', '', $this->fullLink);
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
            $this->fullLink = \JUri::base();

            // Removes the /administrator from the URI if in the administrator
            if (OSMap\Factory::getApplication()->isAdmin()) {
                $this->fullLink = preg_replace('#/administrator/$#', '', $this->fullLink);
            }

            return;
        }

        // Check if is not a url, and disable browser nav
        if ($this->type === 'separator' || $this->type === 'heading') {
            $this->browserNav = 3;

            return;
        }

        // If is an alias, use the Itemid stored in the parameters to get the correct url
        if ($this->type === 'alias') {
            $this->fullLink = 'index.php?Itemid=' . $this->params->get('aliasoptions');
        }

        // If is a menu item but not an alias, force to use the current menu's item id
        if ((bool)$this->isMenuItem && $this->type !== 'alias') {
            $this->fullLink = 'index.php?Itemid=' . $this->id;
        }

        // If is not a menu item, use as base for the fullLink, the item link
        if (!(bool)$this->isMenuItem) {
            $this->fullLink = $this->link;
        }

        if ($this->isInternal) {
            // If this is an internal Joomla link, ensure the Itemid is set

            // Route the full link
            $this->fullLink = OSMap\Router::routeURL($this->fullLink);

            // Make sure the link has the base uri
            if (!preg_match('#^[^:]+://#', $this->fullLink)) {
                $baseUri = \JUri::base();

                // If in admin, removes the /administrator from the URI and fullLink
                if (OSMap\Factory::getApplication()->isAdmin()) {
                    $baseUri = preg_replace('#/administrator/$#', '/', $baseUri);
                    $this->fullLink = preg_replace('#^/administrator/#', '/', $this->fullLink);
                }

                if (!substr_count($this->fullLink, $baseUri)) {
                    $this->fullLink = $baseUri . $this->fullLink;
                }
            }
        }
    }
}
