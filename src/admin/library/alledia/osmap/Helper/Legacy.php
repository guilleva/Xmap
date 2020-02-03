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
