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

namespace Alledia\OSMap\Joomla;

use \JUri;

defined('_JEXEC') or die();

/**
 * Class used to adapt JUri to a non-static context. Created to make tests viable
 * allowing to mock the JUri class and methods for better isolated tests on classes
 * which depends of JUri.
 */
class Uri
{
    /**
     * Returns the base URI for the request.
     * This will include any subfolders created via SEF
     *
     * @param   boolean  $pathonly  If false, prepend the scheme, host and port information. Default is false.
     *
     * @return  string  The base URI string
     */
    public function base($pathonly = false)
    {
        return JUri::base($pathonly);
    }

    /**
     * Returns the root URI for the site.
     * This will never include any subfolders (including e.g. /administrator)
     *
     * @param   boolean  $pathonly  If false, prepend the scheme, host and port information. Default is false.
     * @param   string   $path      The path
     *
     * @return  string  The root URI string.
     */
    public static function root($pathonly = false, $path = null)
    {
        return JUri::root($pathonly, $path);
    }

    /**
     * Returns the global JUri object, only creating it if it doesn't already exist.
     *
     * @param   string  $uri  The URI to parse.  [optional: if null uses script URI]
     *
     * @return  JUri  The URI object.
     */
    public function getInstance($uri = 'SERVER')
    {
        return JUri::getInstance($uri);
    }
}
