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

defined('_JEXEC') or die();

$languages = $this->languages ?: array('');

foreach ($languages as $language) :
    $linkQuery = array(
        'option' => 'com_osmap',
        'view'   => 'sitemapitems',
        'id'     => $this->item->id
    );

    if ($language) {
        $linkQuery['lang'] = $language->sef;

        $flag = JHtml::_(
            'image',
            'mod_languages/' . $language->image . '.gif',
            $language->title,
            null,
            true
        );
        $flag .= ' ' . $language->title;
    }


    echo JHtml::_(
        'link',
        JRoute::_('index.php?' . http_build_query($linkQuery)),
        '<span class="icon-edit"></span>' . ($language ? $flag : '')
    );
    ?>
    <br/>
<?php
endforeach;
