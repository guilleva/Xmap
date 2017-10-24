<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Plugin;

use Alledia\OSMap\Sitemap\Collector;
use Alledia\OSMap\Sitemap\Item;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();


interface ContentInterface
{
    /**
     * Returns the unique instance of the plugin
     *
     * @return object
     */
    public static function getInstance();

    /**
     * Returns the element of the component which this plugin supports.
     *
     * @return string
     */
    public function getComponentElement();

    /**
     * This function is called before a menu item is used. We use it to set the
     * proper uniqueid for the item
     *
     * @param Item     $node   Menu item to be "prepared"
     * @param Registry $params The extension params
     *
     * @return void
     * @since  1.2
     */
    public static function prepareMenuItem($node, $params);

    /**
     * Expands a com_content menu item
     *
     * @param Collector $collector
     * @param Item      $parent
     * @param Registry  $params
     *
     * @return void
     * @since  1.0
     */
    public static function getTree($collector, $parent, $params);
}
