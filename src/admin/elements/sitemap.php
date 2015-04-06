<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2015 Open Source Training, LLC. All rights reserved..
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

// no direct access
defined('_JEXEC') or die('Restricted access');


class JElementSitemap extends JElement
{
    /**
     * Element name
     *
     * @var    string
     */
    var $_name = 'Sitemap';

    public function fetchElement($name, $value, &$node, $control_name)
    {
        $db        = JFactory::getDBO();
        $fieldName = $control_name . '[' . $name . ']';

        $query = $db->getQuery(true)
            ->select('id')
            ->select('name')
            ->from('#__osmap_sitemap')
            ->order('name');
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $html = JHTML::_('select.genericlist', $rows, $fieldName, '', 'id', 'name', $value);

        return $html;
    }

}
