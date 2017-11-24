<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Joomla;

use \JUri;

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
