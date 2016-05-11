<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap;

use Pimple\Container as Pimple;
use Pimple\ServiceProviderInterface;

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
class Services implements ServiceProviderInterface
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
        $pimple['license'] = function (Container $c) {
            return file_exists(OSMAP_LIBRARY . '/alledia/osmap/Pro') ? 'pro' : 'free';
        };

        // Events class
        $pimple['events'] = function (Container $c) {
            return \JEventDispatcher::getInstance();
        };
    }
}
