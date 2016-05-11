<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap;

defined('_JEXEC') or die();


/**
 * OSMap Factory
 */
class Factory extends \JFactory
{
    /**
     * @var Service
     */
    protected static $container;

    /**
     * Get a OSMap container class
     *
     * @return Container
     * @throws Exception
     */
    public static function getContainer()
    {
        if (empty(static::$container)) {
            // $params = Component\Helper::getParams();

            $config = array(

            );

            $container = new Container(
                array(
                    'configuration' => new Configuration($config)
                )
            );
            $container->register(new Services);

            static::$container = $container;
        }

        return static::$container;
    }
}
