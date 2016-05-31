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
    public $changeFreq;

    /**
     * @var string
     */
    public $modifiedOn;

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
        $this->getLink();

        if ($this->isInternal) {
            $this->routeLink();
            $this->convertToFullLink();
            $this->sanitizeLink();
        }

        /*
         * Do not use a "prepare" method because we need to make sure it will
         * be calculated after the link is set.
         */
        $this->calculateUID();
    }

    /**
     * Prepares the link, browserNav and isInternal attributes.
     *
     * @return void
     */
    protected function getLink()
    {
        if ((bool)$this->home) {
            // Correct the URL for the home page.
            $this->link = \JUri::base();
            return;
        }

        // Check if is not a url, and disable browser nav
        if ($this->type === 'separator' || $this->type === 'heading') {
            $this->browserNav = 3;
            return;
        }

        // If is an alias, use the Itemid stored in the parameters to get the correct url
        if ($this->type === 'alias') {
            $this->link = 'index.php?Itemid=' . $this->params->get('aliasoptions');
            return;
        }


        // If this is an internal Joomla link, ensure the Itemid is set
        if ($this->isInternal) {
            $this->link = 'index.php?Itemid=' . $this->id;
        }
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
        $this->set('uid', md5($this->sitemap->id . ':' . $this->link));
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
        $db = OSMap\Factory::getContainer()->db;

        // @todo: extract from the specific item - use the plugin?
        $this->modifiedOn = null;

        $invalidDates = array(
            false,
            -1,
            '-1',
            $db->getNullDate()
        );

        if (isset($this->modified) && !in_array($this->modified, $invalidDates)) {
            $this->modifiedOn = $this->modified;
            unset($this->modified);
        }

        // @todo: not for news?
        if (empty($this->modifiedOn)) {
            $this->modifiedOn = time();
        }

        if (!empty($this->modifiedOn) && !is_numeric($this->modifiedOn)) {
            $date =  new \JDate($this->modifiedOn);
            $this->modifiedOn = $date->toUnix();
        }

        if ($this->modifiedOn) {
            $this->modifiedOn = gmdate('Y-m-d\TH:i:s\Z', $this->modifiedOn);
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
        $this->link = preg_replace('#([^:])(/{2,})#', '$1/', $this->link);

        // Remove trailing slash
        $this->link = preg_replace('#/$#', '', $this->link);
    }

    /**
     * Make sure the link is routed
     *
     * @return void
     */
    protected function routeLink()
    {
        $this->link = \JRoute::_($this->link);
    }

    /**
     * Converts the current link to a full url, including the base URI
     *
     * @return void
     */
    protected function convertToFullLink()
    {
        if (!preg_match('#^[^:]+://#', $this->link)) {
            // Make sure the link has the base uri
            $baseUri = \JUri::base();
            if (!substr_count($this->link, $baseUri)) {
                $this->link = $baseUri . $this->link;
            }
        }

    }
}
