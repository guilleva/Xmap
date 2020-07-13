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

use Alledia\Framework\Profiler;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die();

/**
 * Class Container
 *
 * @package OSMap
 *
 * @property \JEventDispatcher $events
 * @property \JApplicationWeb  $app
 * @property \JDatabaseDriver  $db
 * @property \JInput           $input
 * @property \JUser            $user
 * @property \JLanguage        $language
 * @property Profiler          $profiler
 * @property Router            $router
 * @property Uri               $uri
 *
 * @method \JEventDispatcher getEvents()
 * @method \JApplicationWeb  getApp()
 * @method \JDatabaseDriver  getDb()
 * @method \JInput           getInput()
 * @method \JUser            getUser()
 * @method \JLanguage        getLanguage()
 * @method Profiler          getProfiler()
 * @method Router            getRouter()
 * @method Uri               getUri()
 *
 */
class Container extends \Pimple\Container
{
    public function __get($name)
    {
        if (isset($this[$name])) {
            return $this[$name];
        }

        return null;
    }

    public function __call($name, $args)
    {
        if (strpos($name, 'get') === 0 && !$args) {
            $key = strtolower(substr($name, 3));
            if (isset($this[$key])) {
                return $this[$key];
            }
        }
        return null;
    }

    /**
     * Get instance of a class using parameter autodetect
     *
     * @param $className
     *
     * @return object
     */
    public function getInstance($className)
    {
        $class = new \ReflectionClass($className);
        if ($instance = $this->getServiceEntry($class)) {
            return $instance;
        }

        $dependencies = array();
        if (!is_null($class->getConstructor())) {
            $params = $class->getConstructor()->getParameters();
            foreach ($params as $param) {
                $dependentClass = $param->getClass();
                if ($dependentClass) {
                    $dependentClassName  = $dependentClass->name;
                    $dependentReflection = new \ReflectionClass($dependentClassName);
                    if ($dependentReflection->isInstantiable()) {
                        //use recursion to get dependencies
                        $dependencies[] = $this->getInstance($dependentClassName);
                    } elseif ($dependentReflection->isInterface()) {
                        // Interfaces need to be pre-registered in the container
                        if ($concrete = $this->getServiceEntry($dependentReflection, true)) {
                            $dependencies[] = $concrete;
                        }
                    }
                }
            }
        }

        $instance = $class->newInstanceArgs($dependencies);
        return $instance;
    }

    /**
     * Find a service in the container based on class name
     * Classes can be registered either through their short name
     * or full class name. Short name take precedence.
     *
     * @param \ReflectionClass $class
     * @param bool             $require
     *
     * @return object|null
     * @throws \Exception
     */
    protected function getServiceEntry(\ReflectionClass $class, $require = false)
    {
        $key = strtolower($class->getShortName());
        if (isset($this[$key])) {
            return $this[$key];
        }

        $name = $class->getName();
        if (isset($this[$name])) {
            return $this[$name];
        }

        if ($require) {
            throw new \Exception($class->getName() . ' -  is not registered in the container');
        }

        return null;
    }
}
