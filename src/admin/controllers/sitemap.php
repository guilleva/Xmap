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


class OSMapControllerSitemap extends OSMap\Controller\Form
{
    /**
     * Method override to check if the user can edit an existing record.
     *
     * @param    array    An array of input data.
     * @param    string   The name of the key for the primary key.
     *
     * @return   boolean
     */
    protected function _allowEdit($data = array(), $key = 'id')
    {
        // Initialise variables.
        $recordId = (int) isset($data[$key]) ? $data[$key] : 0;

        // Assets are being tracked, so no need to look into the category.
        return \JFactory::getUser()->authorise('core.edit', 'com_osmap.sitemap.' . $recordId);
    }

    /**
     * Mark the sitemap as default
     */
    public function setAsDefault()
    {
        $cid = OSMap\Factory::getApplication()->input->get('cid', array(), 'array');

        if (isset($cid[0])) {
            // Cleanup the is_default field
            $db = OSMap\Factory::getDbo();

            $query = $db->getQuery(true)
                ->set('is_default = 0')
                ->update('#__osmap_sitemaps');
            $db->setQuery($query)->execute();

            // Set the sitemap as default
            $model = $this->getModel();
            $row   = $model->getTable();

            $row->load($cid[0]);
            $row->save(
                array(
                    'is_default' => true
                )
            );
        }

        $this->setRedirect('index.php?option=com_osmap&view=sitemaps');
    }
}
