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

defined('_JEXEC') or die();

/**
 * A simple class for handling layered variables
 *
 * Class AbstractConfiguration
 *
 * @package OSMap
 */
class Configuration
{
    /**
     * @var array
     */
    protected $settings = null;

    public function __construct(array $settings = array())
    {
        $this->settings = $settings;
    }

    /**
     * Confirm that the current configuration is valid
     *
     * @return bool
     */
    public function isValid()
    {
        return true;
    }

    /**
     * Translate dot notation into array keys
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (strpos($name, '.') === false) {
            return isset($this->settings[$name]) ? $this->settings[$name] : $default;
        }
        $levels = explode('.', $name);

        $value = &$this->settings;
        for ($i = 0; $i < count($levels) - 1; $i++) {
            $key = $levels[$i];
            if (is_array($value) && isset($value[$key])) {
                $value = &$value[$key];
            } elseif (is_object($value) && isset($value->$key)) {
                $value = $value->$key;
            } else {
                return $default;
            }
        }

        $key = $levels[$i];
        if (isset($value[$key])) {
            return $value[$key];
        }
        return $default;
    }

    /**
     * Save a dot notation key to the setting array
     *
     * @param string $name
     * @param mixed  $newValue
     *
     * @return mixed
     * @throws Exception
     */
    public function set($name, $newValue)
    {
        $oldValue = $this->get($name);

        if (strpos($name, '.') === false) {
            $this->settings[$name] = $newValue;
        } else {
            $keys = explode('.', $name);
            $tree = &$this->settings;
            for ($i = 0; $i < count($keys) - 1; $i++) {
                $key = $keys[$i];
                if (empty($tree[$key]) || !is_array($tree[$key])) {
                    $tree[$key] = array();
                }
                $tree = &$tree[$key];
            }

            $final = array_pop($keys);
            if ($newValue === null) {
                unset($tree[$final]);
            } else {
                $tree[$final] = $newValue;
            }
        }
        return $oldValue;
    }

    /**
     * Return as Configuration class
     *
     * @param string $key
     *
     * @return Configuration
     */
    public function toConfig($key = null)
    {
        if ($key) {
            return new static($this->get($key, array()));
        }

        return clone $this;
    }

    /**
     * Return as JSON string
     *
     * @param string $key
     *
     * @return string
     */
    public function toString($key = null)
    {
        $value = $key ? $this->get($key) : $this->settings;
        return json_encode($value);
    }

    /**
     * Return as stdClass
     *
     * @param string $key
     *
     * @return object
     */
    public function toObject($key = null)
    {
        $value = $key ? $this->get($key) : $this->settings;
        return json_decode(json_encode($value));
    }

    /**
     * Return as array
     *
     * @param string $key
     *
     * @return array
     */
    public function toArray($key = null)
    {
        $value = $key ? $this->get($key) : $this->settings;
        return json_decode(json_encode($value), true);
    }

    /*
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
