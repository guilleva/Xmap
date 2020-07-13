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

namespace Alledia\OSMap\Services;

use Joomla\CMS\Uri\Uri;
use Pimple\Container as Pimple;
use Pimple\ServiceProviderInterface;
use Alledia\OSMap;
use Alledia\Framework;

defined('_JEXEC') or die();

/**
 * Class Services
 *
 * Pimple services for OSMap. The container must be instantiated with
 * at least the following values:
 *
 * new \OSMap\Container(
 *    array(
 *       'configuration' => new Configuration($config)
 *    )
 * )
 *
 * @package OSMap
 */
class Free implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Pimple $pimple An Container instance
     */
    public function register(Pimple $pimple)
    {
        // Events class
        $pimple['events'] = function (OSMap\Container $c) {
            return \JEventDispatcher::getInstance();
        };

        $pimple['app'] = function (OSMap\Container $c) {
            return OSMap\Factory::getApplication();
        };

        $pimple['db'] = function (OSMap\Container $c) {
            return OSMap\Factory::getDbo();
        };

        $pimple['input'] = function (OSMap\Container $c) {
            return OSMap\Factory::getApplication()->input;
        };

        $pimple['user'] = function (OSMap\Container $c) {
            return OSMap\Factory::getUser();
        };

        $pimple['language'] = function (OSMap\Container $c) {
            return OSMap\Factory::getLanguage();
        };

        $pimple['profiler'] = function (OSMap\Container $c) {
            return new Framework\Profiler;
        };

        $pimple['router'] = function (OSMap\Container $c) {
            return new OSMap\Router;
        };

        $pimple['uri'] = function (OSMap\Container $c) {
            return new Uri();
        };


        $this->registerHelper($pimple);
    }

    /**
     * Registers the image helper
     */
    protected function registerHelper(Pimple $pimple)
    {
        $pimple['imagesHelper'] = function (OSMap\Container $c) {
            return new OSMap\Helper\Images;
        };
    }
}
