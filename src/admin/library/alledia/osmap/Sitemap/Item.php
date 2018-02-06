<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Sitemap;

use Alledia\OSMap;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

/**
 * Sitemap item
 */
class Item extends BaseItem
{
    /**
     * The constructor
     *
     * @param array $itemData
     * @param int   $currentMenuItemId
     *
     * @return void
     * @throws \Exception
     */
    public function __construct($itemData, $currentMenuItemId)
    {
        parent::__construct($itemData);

        $this->published  = (bool)$this->published;
        $this->isMenuItem = (bool)$this->isMenuItem;
        $this->params     = new Registry($this->params);

        $itemData = null;

        // Check if the link is an internal link
        $this->isInternal = $this->checkLinkIsInternal();

        $this->prepareDate('created');
        $this->prepareDate('modified');

        $defaultDate = is_null($this->created) ? $this->modified : $this->created;
        $this->prepareDate('publishUp', $defaultDate);

        $this->setLink();
        $this->extractComponentFromLink();
        $this->setFullLink();

        // Sanitize internal links
        if ($this->isInternal) {
            $this->sanitizeFullLink();
        }

        $this->rawLink = $this->fullLink;

        // Removes the hash segment from the Full link, if exists
        $container      = OSMap\Factory::getContainer();
        $this->fullLink = $container->router->removeHashFromURL($this->fullLink);

        // Make sure to have a unique hash for the settings
        $this->settingsHash = $container->router->createUrlHash($this->fullLink . $currentMenuItemId);

        /*
         * Do not use a "prepare" method because we need to make sure it will
         * be calculated after the link is set.
         */
        $this->calculateUID();
    }

    /**
     * Extract the option from the link, to identify the component called by
     * the link.
     *
     * @return void
     */
    protected function extractComponentFromLink()
    {
        $this->component = null;

        if (preg_match('#^/?index.php.*option=(com_[^&]+)#', $this->link, $matches)) {
            $this->component = $matches[1];
        }
    }

    /**
     * Adds the note to the admin note attribute and initialize the variable
     * if needed
     *
     * @param string $note
     *
     * @return void
     */
    public function addAdminNote($note)
    {
        if (!is_array($this->adminNotes)) {
            $this->adminNotes = array();
        }

        $this->adminNotes[] = \JText::_($note);
    }

    /**
     * Returns the admin notes as a string.
     *
     * @return string
     */
    public function getAdminNotesString()
    {
        if (!empty($this->adminNotes)) {
            return implode("\n", $this->adminNotes);
        }

        return '';
    }

    /**
     * Check if the current link is an internal link.
     *
     * @return bool
     * @throws \Exception
     */
    protected function checkLinkIsInternal()
    {
        $container = OSMap\Factory::getContainer();

        return $container->router->isInternalURL($this->link)
            || in_array(
                $this->type,
                array(
                    'separator',
                    'heading'
                )
            );
    }

    /**
     * Set the correct date for the attribute
     *
     * @param string $attributeName
     * @param string $default
     *
     * @return void
     */
    public function prepareDate($attributeName, $default = null)
    {
        if (!is_null($default)) {
            if (empty($this->$attributeName)) {
                $this->$attributeName = $default;
            }
        }

        if (OSMap\Helper\General::isEmptyDate($this->$attributeName)) {
            $this->$attributeName = null;
        }

        if (!OSMap\Helper\General::isEmptyDate($this->$attributeName)) {
            if (!is_numeric($this->$attributeName)) {
                $date                 = new \JDate($this->$attributeName);
                $this->$attributeName = $date->toUnix();
            }

            // Convert dates from UTC
            if (intval($this->$attributeName)) {
                if ($this->$attributeName < 0) {
                    $this->$attributeName = null;
                } else {
                    $date                 = new \JDate($this->$attributeName);
                    $this->$attributeName = $date->toISO8601();
                }
            }
        }
    }

    /**
     * Check if the item's language has compatible language with
     * the current language.
     *
     * @return bool
     */
    public function hasCompatibleLanguage()
    {
        // Check the language
        if (\JLanguageMultilang::isEnabled() && isset($this->language)) {
            if ($this->language === '*' || $this->language === \JFactory::getLanguage()->getTag()) {
                return true;
            }

            return false;
        }

        return true;
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
            $this->uid = $prefix . md5($this->fullLink);
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
     * @throws \Exception
     */
    protected function sanitizeFullLink()
    {
        $container = OSMap\Factory::getContainer();

        $this->fullLink = $container->router->sanitizeURL($this->fullLink);
    }

    /**
     * Converts the current link to a full URL, including the base URI.
     * If the item is the home menu, will return the base URI. If internal,
     * will return a routed full URL (SEF, if enabled). If it is an external
     * URL, won't change the link.
     *
     * @return void
     * @throws \Exception
     */
    protected function setFullLink()
    {
        $container = OSMap\Factory::getContainer();

        if ((bool)$this->home) {
            // Correct the URL for the home page.
            // Check if multi-language is enabled to use the proper route
            if (\JLanguageMultilang::isEnabled()) {
                $lang = OSMap\Factory::getLanguage();
                $tag  = $lang->getTag();
                $lang = null;

                $homes = \JLanguageMultilang::getSiteHomePages();

                $home = isset($homes[$tag]) ? $homes[$tag] : $home = $homes['*'];

                // Joomla bug? When in subfolder, multilingual home page doubles up the base path
                $uri            = $container->uri->getInstance();
                $this->fullLink = $uri->toString(array('scheme', 'host', 'port'))
                    . $container->router->routeURL('index.php?Itemid=' . $home->id);

            } else {
                $this->fullLink = $container->uri->root();
            }

            $this->fullLink = $container->router->sanitizeURL($this->fullLink);

            return;
        }

        // Check if is not a url, and disable browser nav
        if ($this->type === 'separator' || $this->type === 'heading') {
            $this->browserNav = 3;
            // Always a unique UID, since this only appears in the HTML sitemap
            $this->uid = $this->type . '.' . md5($this->name . $this->id);

            return;
        }

        // If is an URL, but external, return the external URL. If internal,
        // follow with the routing
        if ($this->type === 'url') {
            $this->link = trim($this->link);
            // Check if it is a single Hash char, the user doesn't want to point to any URL
            if ($this->link === '#' || empty($this->link)) {
                $this->fullLink      = '';
                $this->visibleForXML = false;

                return;
            }

            // Check if it is a relative URI
            if ($container->router->isRelativeUri($this->link)) {
                $this->fullLink = $container->router->sanitizeURL(
                    $container->router->convertRelativeUriToFullUri($this->link)
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
            $this->fullLink = $container->router->routeURL($this->fullLink);

            // Make sure the link has the base uri
            $this->fullLink = $container->router->convertRelativeUriToFullUri($this->fullLink);
        }

        $this->fullLink = $container->router->sanitizeURL($this->fullLink);
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

    public function cleanup()
    {
        $this->adapter->cleanup();

        $this->menu    = null;
        $this->adapter = null;
        $this->params  = null;
    }
}
