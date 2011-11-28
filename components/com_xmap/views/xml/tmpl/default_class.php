<?php
/**
 * @version         $Id$
 * @copyright        Copyright (C) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @author        Guillermo Vargas (guille@vargas.co.cr)
 */
// No direct access
defined('_JEXEC') or die;

require_once(JPATH_COMPONENT . DS . 'displayer.php');

class XmapXmlDisplayer extends XmapDisplayer
{

    /**
     *
     * @var array  Stores the list of links that have been already included in
     *             the sitemap to avoid duplicated items
     */
    var $_links;

    /**
     *
     * @var string
     */
    var $view = 'xml';

    /**
     *
     * @var int Indicates if this is a google news sitemap or not
     */
    var $isNews = 0;

    function __construct($config, $sitemap)
    {
        parent::__construct($config, $sitemap);
        $this->uids = array();
        
        $this->defaultLanguage = strtolower(JFactory::getLanguage()->getTag());
        if (preg_match('/^([a-z]+)-.*/',$this->defaultLanguage,$matches) && !in_array($this->defaultLanguage, array(' zh-cn',' zh-tw')) ) {
            $this->defaultLanguage = $matches[1];
        }
        
        $this->showTitle = JRequest::getBool('filter_showtitle', 0);
    }

    /**
     * Prints an XML node for the sitemap
     *
     * @param stdclass $node
     */
    function printNode($node)
    {
        if ($this->isExcluded($node->id,$node->uid)) {
            return FALSE;
        }

        if ($this->isNews && (!isset($node->newsItem) || !$node->newsItem)) {
            return true;
        }

        // Get the item's URL
        $link = JRoute::_($node->link, true, (@$node->secure? 1: -1));

        if (!isset($node->browserNav))
            $node->browserNav = 0;

        if ($node->browserNav != 3   // ignore "no link"
                && empty($this->_links[$link])) { // ignore links that have been added already
            $this->count++;
            $this->_links[$link] = 1;

            if (!isset($node->priority))
                $node->priority = "0.5";

            if (!isset($node->changefreq))
                $node->changefreq = 'daily';

            // Get the chancefrequency and priority for this item
            $changefreq = $this->getProperty('changefreq', $node->changefreq, $node->id, 'xml', $node->uid);
            $priority = $this->getProperty('priority', $node->priority, $node->id, 'xml', $node->uid);

            echo '<url>' . "\n";
            echo '<loc>', $link, '</loc>' . "\n";
            if ($this->canEdit) {
                if ($this->showTitle) {
                    echo '<title><![CDATA['.$node->name.']]></title>' . "\n";
                }
                echo '<uid>', $node->uid, '</uid>' . "\n";
                echo '<itemid>', $node->id, '</itemid>' . "\n";
            }
            $modified = (isset($node->modified) && $node->modified != FALSE && $node->modified != -1) ? $node->modified : NULL;
            if (!$modified && $this->isNews) {
                $modified = time();
            }
            if ($modified) {
                $modified = gmdate('Y-m-d\TH:i:s\Z', $modified);
            }

            // If this is not a news sitemap
            if (!$this->isNews) {
                if ($modified){
                    echo '<lastmod>', $modified, '</lastmod>' . "\n";
                }
                echo '<changefreq>', $changefreq, '</changefreq>' . "\n";
                echo '<priority>', $priority, '</priority>' . "\n";
            } else {
                if (isset($node->keywords)) {
                    $keywords = htmlspecialchars($node->keywords);
                } else {
                    $keywords = '';
                }
                
                if (!isset($node->language) || $node->language == '*') {
                    $node->language = $this->defaultLanguage;
                }

                echo "<news:news>\n";
                echo '<news:publication>'."\n";
                echo '  <news:name>'.(htmlspecialchars($this->sitemap->params->get('news_publication_name'))).'</news:name>'."\n";
                echo '  <news:language>'.$node->language.'</news:language>'."\n";
                echo '</news:publication>'."\n";
                echo '<news:publication_date>', $modified, '</news:publication_date>' . "\n";
                echo '<news:title><![CDATA['.$node->name.']]></news:title>' . "\n";
                if ($keywords) {
                    echo '<news:keywords>', $keywords, '</news:keywords>' . "\n";
                }
                echo "</news:news>\n";
            }
            echo '</url>', "\n";
        } else {
            return empty($this->_links[$link]);
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

    /**
     * Called on every level change
     *
     * @param int $level
     * @return boolean
     */
    function changeLevel($level)
    {
        return true;
    }

    /**
     * Function called before displaying the menu
     *
     * @param stdclass $menu The menu node item
     * @return boolean
     */
    function startMenu($menu)
    {
        return true;
    }

    /**
     * Function called after displaying the menu
     *
     * @param stdclass $menu The menu node item
     * @return boolean
     */
    function endMenu($menu)
    {
        return true;
    }
}
