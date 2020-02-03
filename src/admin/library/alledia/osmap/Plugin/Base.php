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

namespace Alledia\OSMap\Plugin;

use Alledia\Framework\Exception;

defined('_JEXEC') or die();


/**
 * Base plugin
 */
abstract class Base extends \JPlugin
{
    /**
     * @var int
     */
    protected static $memoryLimit = null;

    /**
     * Minimum memory in MB required to continue on sites with limited memory
     *
     * @var int
     */
    protected static $memoryMinimum = 4;

    public function __construct(&$subject, $config = array())
    {
        require_once JPATH_ADMINISTRATOR . '/components/com_osmap/include.php';

        parent::__construct($subject, $config);
    }

    /**
     * Set memory limit to unlimited. If unable to do so,
     * we'll want to check that we have enough memory left to continue
     * so we can fail gracefully
     *
     * @return void
     */
    protected static function fixMemoryLimit()
    {
        if (static::$memoryLimit === null) {
            $limit = @ini_set('memory_limit', -1);

            if (empty($limit) || $limit === false) {
                $mags  = array(
                    'K' => 1024,
                    'M' => 1024 * 1024,
                    'G' => 1024 * 1024 *1024
                );
                $limit = ini_get('memory_limit');
                $regex = sprintf('/(\d*)([%s])/', join(array_keys($mags)));
                if (preg_match($regex, $limit, $match)) {
                    $limit = $match[1] * $mags[$match[2]];
                }

                static::$memoryLimit   = $limit;
                static::$memoryMinimum *= $mags['M'];
            }
        }
    }

    /**
     * Check to see if we're about to run out of memory. If things get too tight
     * all we can do is throw an informative message or redirect somewhere else
     * that isn't an OSMap page
     *
     * @TODO: Decide whether to implement the redirect option
     *
     * @return void
     * @throws Exception
     */
    protected static function checkMemory()
    {
        if (static::$memoryLimit === null) {
            static::fixMemoryLimit();
        }

        if (static::$memoryLimit && (static::$memoryLimit - memory_get_usage(true) < static::$memoryMinimum)) {
            $message = \JText::sprintf('COM_OSMAP_WARNING_OOM', get_called_class());
            throw new Exception($message, 500);
        }
    }
}
