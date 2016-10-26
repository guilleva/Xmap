<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Helper;

use Alledia\Framework;
use Alledia\OSMap;

defined('_JEXEC') or die();


abstract class Legacy
{
    /**
     * Method to return a list of language home page menu items.
     * Added here to keep compatibility with Joomla 3.4.x.
     *
     * @return  array of menu objects.
     */
    public static function getSiteHomePages()
    {
        // To avoid doing duplicate database queries.
        static $multilangSiteHomePages = null;

        if (!isset($multilangSiteHomePages)) {
            // Check for Home pages languages.
            $db = \JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('language')
                ->select('id')
                ->from($db->quoteName('#__menu'))
                ->where('home = 1')
                ->where('published = 1')
                ->where('client_id = 0');
            $db->setQuery($query);

            $multilangSiteHomePages = $db->loadObjectList('language');
        }

        return $multilangSiteHomePages;
    }
}
