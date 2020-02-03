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


class OSMapControllerSitemapItems extends OSMap\Controller\Form
{
    /**
     * Method override to check if the user can edit an existing record.
     *
     * @param array    An array of input data.
     * @param string   The name of the key for the primary key.
     *
     * @return boolean
     */
    protected function _allowEdit($data = array(), $key = 'id')
    {
        // Initialise variables.
        $recordId = (int)isset($data[$key]) ? $data[$key] : 0;

        // Assets are being tracked, so no need to look into the category.
        return JFactory::getUser()->authorise('core.edit', 'com_osmap.sitemap.' . $recordId);
    }

    public function cancel($key = null)
    {
        $this->setRedirect('index.php?option=com_osmap&view=sitemaps');
    }

    /**
     * @param string $key
     * @param null   $urlVar
     *
     * @return bool
     * @throws Exception
     */
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = OSMap\Factory::getApplication();

        $sitemapId  = $app->input->getInt('id');
        $updateData = $app->input->getRaw('update-data');
        $language   = $app->input->getString('language');

        $model = $this->getModel();

        if (!empty($updateData)) {
            $updateData = json_decode($updateData, true);

            if (!empty($updateData) && is_array($updateData)) {
                foreach ($updateData as $data) {
                    $row = $model->getTable();
                    $row->load(
                        array(
                            'sitemap_id'    => $sitemapId,
                            'uid'           => $data['uid'],
                            'settings_hash' => $data['settings_hash']
                        )
                    );

                    $data['sitemap_id'] = $sitemapId;
                    $data['format']     = '2';

                    $row->save($data);
                }
            }
        }

        if ($this->getTask() === 'apply') {
            $url = 'index.php?option=com_osmap&view=sitemapitems&id=' . $sitemapId;

            if (!empty($language)) {
                $url .= '&lang=' . $language;
            }

            $this->setRedirect($url);
        } else {
            $this->setRedirect('index.php?option=com_osmap&view=sitemaps');
        }

        return true;
    }
}
