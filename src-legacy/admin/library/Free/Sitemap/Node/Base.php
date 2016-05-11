<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved..
 * @author    Guillermo Vargas <guille@vargas.co.cr>
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

namespace Alledia\OSMap\Free\Sitemap\Node;

class Base
{
    /**
     * Unique ID to avoid duplicated items
     *
     * @var string
     */
    public $uid;

    /**
     * The priority of this URL relative to other URLs on your site.
     * Valid values will range from 0.0 to 1.0. Used by search engines.
     * Default value is 0.5.
     *
     * @var string
     */
    public $priority = '0.5';

    /**
     * How frequently the page is likely to change. This value provides general
     * information for search engines.
     * Excepted values: always, hourly, daily, weekly, monthly, yearly, never.
     *
     * @var string
     */
    public $changeFreq = 'weekly';

    /**
     * Node's name.
     *
     * @var string
     */
    public $name;

    /**
     * Node's url
     *
     * @var string
     */
    public $url;

    /**
     * Node's content modified date
     *
     * @var string
     */
    public $modified;
}
