<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap;

use Alledia\Framework;

defined('_JEXEC') or die();


/**
 * OSMap Factory
 */
class Factory extends Framework\Factory
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

            // Load the Service class according to the current license
            $serviceClass = '\\Alledia\\OSMap\\Services\\' . ucfirst(OSMAP_LICENSE);

            $container->register(new $serviceClass);

            static::$container = $container;
        }

        return static::$container;
    }

    /**
     * Returns an instance of the Sitemap class according to the given id and
     * sitemap type.
     *
     * @param int    $id
     * @param string $type
     *
     * @return mixed
     */
    public static function getSitemap($id, $type = 'standard')
    {
        if ($type === 'standard') {
            return new Sitemap\Standard($id);
        }

        if ($type === 'images') {
            return new Sitemap\Images($id);
        }

        if ($type === 'news') {
            return new Sitemap\News($id);
        }

        return false;
    }

    /**
     * Returns an instance of a table. If no prefix is set, we use OSMap's table
     * prefix as default.
     *
     * @param string $tableName
     *
     * @return mixed
     */
    public static function getTable($tableName, $prefix = 'OSMapTable')
    {
        return \JTable::getInstance($tableName, $prefix);
    }
}
