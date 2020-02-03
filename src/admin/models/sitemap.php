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

use Alledia\OSMap;

defined('_JEXEC') or die();


class OSMapModelSitemap extends JModelAdmin
{
    public function getTable($type = 'Sitemap', $prefix = 'OSMapTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_osmap.sitemap', 'sitemap', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    protected function loadFormData()
    {
        $data = OSMap\Factory::getApplication()->getUserState('com_osmap.edit.sitemap.data', array());

        if (empty($data)) {
            $data = $this->getItem();

            // Load some defaults for new sitemap
            $id = $data->get('id');
            if (empty($id)) {
                $data->set('published', 1);
                $data->set('created', OSMap\Factory::getDate()->toSql());
            }

            // Load the menus
            if (!empty($id)) {
                $db    = OSMap\Factory::getDbo();
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__osmap_sitemap_menus')
                    ->where('sitemap_id = ' . $db->quote($id))
                    ->order('ordering');
                $menus = $db->setQuery($query)->loadObjectList();

                $data->menus = array();

                foreach ($menus as $menu) {
                    $data->menus[$menu->menutype_id] = array(
                        'priority'   => $menu->priority,
                        'changefreq' => $menu->changefreq
                    );
                }
            }
        }

        return $data;
    }

    /**
     * @param JTable $table
     *
     * @return void
     */
    protected function prepareTable($table)
    {
    }

    /**
     * Returns an array with a list of the selected menus
     *
     * @return array
     */
    public function getSelectedMenus($sitemapId)
    {
    }
}
