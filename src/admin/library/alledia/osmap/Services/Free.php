<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Services;

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
