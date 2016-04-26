<?php
/**
 * @version         $Id$
 * @copyright        Copyright (C) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @author        Guillermo Vargas (guille@vargas.co.cr)
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_COMPONENT . '/displayer.php');

class OSMapXmlDisplayer extends OSMapDisplayer
{

    /**
     *
     * @var array  Stores the list of links that have been already included in
     *             the sitemap to avoid duplicated items
     */
    protected $_links;

    /**
     *
     * @var string
     */
    public $view = 'xml';

    public $showTitle = false;

    public $showExcluded = false;

    public $isImages = 0;

    public function __construct($config, $sitemap)
    {
        parent::__construct($config, $sitemap);

        $this->uids = array();

        $this->defaultLanguage = strtolower(JFactory::getLanguage()->getTag());
        if (preg_match('/^([a-z]+)-.*/', $this->defaultLanguage, $matches) && !in_array($this->defaultLanguage, array(' zh-cn',' zh-tw'))) {
            $this->defaultLanguage = $matches[1];
        }

        $this->showTitle    = JRequest::getBool('filter_showtitle', 0);
        $this->showExcluded = JRequest::getBool('filter_showexcluded', 0);

        $db = JFactory::getDbo();
        $this->nullDate = $db->getNullDate();
    }

    /**
     * Prints an XML node for the sitemap
     *
     * @param stdclass $node
     */
    public function printNode($node)
    {
        $node->isExcluded = false;

        if ($this->isExcluded($node->id, $node->uid)) {
            if (!$this->showExcluded || !$this->canEdit) {
                return false;
            }

            $node->isExcluded = true;
        }

        // For images sitemaps only display pages with images
        if ($this->isImages && (!isset($node->images) || !count($node->images))) {
            return true;
        }

        // Get the item's URL
        $link = JRoute::_($node->link, true, @$node->secure == 0 ? (JFactory::getURI()->isSSL() ? 1 : -1) : $node->secure);

        if (!isset($node->browserNav)) {
            $node->browserNav = 0;
        }

        if ($node->browserNav != 3   // ignore "no link"
                && empty($this->_links[$link])) { // ignore links that have been added already

            $this->count++;
            $this->_links[$link] = 1;

            if (!isset($node->priority)) {
                $node->priority = "0.5";
            }

            if (!isset($node->changefreq)) {
                $node->changefreq = 'daily';
            }

            // Get the changefrequency and priority for this item
            $changefreq = $this->getProperty('changefreq', $node->changefreq, $node->id, 'xml', $node->uid);
            $priority   = $this->getProperty('priority', $node->priority, $node->id, 'xml', $node->uid);

            echo '<url>' . "\n";
            echo "<loc><![CDATA[" . trim($link) . "]]></loc>\n";
            if ($this->canEdit) {
                if ($this->showTitle) {
                    echo '<title><![CDATA['.$node->name.']]></title>' . "\n";
                }

                if ($this->showExcluded) {
                    echo '<rowclass>',($node->isExcluded? 'excluded':''),'</rowclass>';
                }

                echo '<uid>', $node->uid, '</uid>' . "\n";
                echo '<itemid>', $node->id, '</itemid>' . "\n";
            }

            $modified = (isset($node->modified) && $node->modified != false && $node->modified != $this->nullDate && $node->modified != -1) ? $node->modified : null;
            if ($modified && !is_numeric($modified)) {
                $date =  new JDate($modified);
                $modified = $date->toUnix();
            }

            if ($modified) {
                $modified = gmdate('Y-m-d\TH:i:s\Z', $modified);
            }

            if ($this->isImages) {
                foreach ($node->images as $image) {
                    echo '<image:image>', "\n";
                    echo '<image:loc>', $image->src, '</image:loc>', "\n";

                    if ($image->title) {
                        $image->title = htmlentities($image->title, ENT_NOQUOTES, 'UTF-8');
                        echo '<image:title><![CDATA[', $image->title, ']]></image:title>', "\n";
                    } else {
                        echo '<image:title />';
                    }

                    if (isset($image->license) && $image->license) {
                        echo '<image:license><![CDATA[', htmlentities($image->license, ENT_NOQUOTES, 'UTF-8'), ']]></image:license>', "\n";
                    }

                    echo '</image:image>', "\n";
                }
            } else {
                if ($modified) {
                    echo '<lastmod>', $modified, '</lastmod>' . "\n";
                }

                echo '<changefreq>', $changefreq, '</changefreq>' . "\n";
                echo '<priority>', $priority, '</priority>' . "\n";
            }

            echo '</url>', "\n";
        }

        return true;
    }

    /**
     *
     * @param string $property The property that is needed
     * @param string $value The default value if the property is not found
     * @param int $Itemid   The menu item id
     * @param string $view  (xml / html)
     * @param int $uid      Unique id of the element on the sitemap
     *                      (the id asigned by the extension)
     * @return string
     */
    function getProperty($property, $value, $Itemid, $view, $uid)
    {
        if (isset($this->jview->sitemapItems[$view][$Itemid][$uid][$property])) {
            return $this->jview->sitemapItems[$view][$Itemid][$uid][$property];
        }

        return $value;
    }
}
