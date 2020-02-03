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

namespace Alledia\OSMap;

use Alledia\Framework;
use Alledia\OSMap\Sitemap\SitemapInterface;

defined('_JEXEC') or die();


/**
 * OSMap Factory
 */
class Factory extends Framework\Factory
{
    /**
     * @var Container
     */
    protected static $container;

    /**
     * Get a OSMap container class
     *
     * @return Container
     * @throws \Exception
     */
    public static function getContainer()
    {
        if (empty(static::$container)) {
            $config = array();

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
     * @return SitemapInterface
     * @throws \Exception
     */
    public static function getSitemap($id, $type = 'standard')
    {
        switch ($type) {
            case 'standard':
                return new Sitemap\Standard($id);

            case 'images':
                return new Sitemap\Images($id);

            case 'news':
                return new Sitemap\News($id);

            default:
                return null;
        }
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
