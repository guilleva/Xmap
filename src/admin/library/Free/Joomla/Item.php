<?php
/**
 * @package   OSMap
 * @copyright 2015 Open Source Training, LLC. All rights reserved..
 * @author    Alledia <support@alledia.com>
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file is part of OSMap.
 *
 * OSMap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * OSMap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSMap. If not, see <http://www.gnu.org/licenses/>.
 */
namespace Alledia\OSMap\Free\Joomla;

// No direct access
defined('_JEXEC') or die('Restricted access');

use JFactory;
use JRegistry;

class Item
{
    protected $item = null;

    protected $params = null;

    public function __construct($item)
    {
        $this->item = $item;

        if (is_object($this->item->params)) {
            $this->params = &$this->item->params;
        } else {
            $this->params = new JRegistry($this->item->params);

            if (isset($this->item->metadata)) {
                $metadata     = new JRegistry($this->item->metadata);

                $this->params->merge($metadata);
            }
        }
    }

    public function getParams()
    {
        return $this->params;
    }

    public function isVisibleForRobots()
    {
        return true;
    }
}
