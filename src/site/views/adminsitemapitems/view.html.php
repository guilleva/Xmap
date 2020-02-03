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

use Alledia\OSMap;

class OSMapViewAdminSitemapItems extends JViewLegacy
{
    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $this->checkAccess();

        $container = OSMap\Factory::getContainer();

        try {
            $id = $container->input->getInt('id');

            $this->params = OSMap\Factory::getApplication()->getParams();

            // Load the sitemap instance
            $this->sitemap     = OSMap\Factory::getSitemap($id, 'standard');
            $this->osmapParams = JComponentHelper::getParams('com_osmap');
        } catch (Exception $e) {
            $this->message = $e->getMessage();
        }

        parent::display($tpl);
    }

    /**
     * This view should only be available from the backend
     *
     * @return void
     * @throws Exception
     */
    protected function checkAccess()
    {
        $server  = new JInput(array_change_key_case($_SERVER, CASE_LOWER));
        $referer = parse_url($server->getString('http_referer'));

        if (!empty($referer['query'])) {
            parse_str($referer['query'], $query);

            $option = empty($query['option']) ? null : $query['option'];
            $view   = empty($query['view']) ? null : $query['view'];

            if ($option == 'com_osmap' && $view == 'sitemapitems') {
                // Good enough
                return;
            }
        }

        throw new Exception(JText::_('JERROR_PAGE_NOT_FOUND'), 404);
    }
}
