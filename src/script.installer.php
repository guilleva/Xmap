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

// No direct access
defined('_JEXEC') or die('Restricted access');

$includePath = __DIR__ . '/admin/library/Installer/include.php';
if (! file_exists($includePath)) {
    $includePath = __DIR__ . '/library/Installer/include.php';
}

require_once $includePath;
require_once JPATH_ADMINISTRATOR . '/modules/mod_menu/helper.php';

use Alledia\Installer\AbstractScript;

/**
 * OSMap Installer Script
 *
 * @since  2.4.0
 */
class Com_OSMapInstallerScript extends AbstractScript
{
    protected $isXmapDataFound = false;

    /**
     * @param string                     $type
     * @param JInstallerAdapterComponent $parent
     *
     * @return void
     */
    public function postFlight($type, $parent)
    {
        $this->isXmapDataFound = $this->lookForXmapData();
        $this->createDefaultSitemap();

        parent::postFlight($type, $parent);
    }

    /**
     * Look for the Xmap data to suggest a data migration
     *
     * @return bool True if Xmap data was found
     */
    protected function lookForXmapData()
    {
        if ($this->tableExists('#__xmap_sitemap')) {
            $db = JFactory::getDbo();

            // Do we have any Xmap sitemap?
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from('#__xmap_sitemap');
            $db->setQuery($query);
            $total = (int) $db->loadResult();

            return $total > 0;
        }

        return false;
    }

    protected function createDefaultSitemap()
    {
        $db = JFactory::getDbo();

        // Check if we have any sitemap, otherwise lets create a default one
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__osmap_sitemap');
        $db->setQuery($query);
        $noSitemaps = ((int) $db->loadResult()) === 0;

        if ($noSitemaps) {
            // Get all menus
            $menus = ModMenuHelper::getMenus();

            if (!empty($menus)) {
                $selections = new stdClass;
                $i = 0;

                foreach ($menus as $menu) {
                    $selection = new stdClass;
                    $selection->priority   = 0.5;
                    $selection->changefreq = 'weekly';
                    $selection->ordering   = $i++;

                    $selections->{$menu->menutype} = $selection;
                }

                $attribs = new stdClass;
                $attribs->showintro             = "1";
                $attribs->show_menutitle        = "1";
                $attribs->classname             = "";
                $attribs->columns               = "";
                $attribs->exlinks               = "img_blue.gif";
                $attribs->compress_xml          = "1";
                $attribs->beautify_xml          = "1";
                $attribs->include_link          = "1";
                $attribs->news_publication_name = "";

                $config = JFactory::getConfig();

                $data = array(
                    'title'      => 'Sitemap',
                    'alias'      => 'sitemap',
                    'attribs'    => json_encode($attribs),
                    'selections' => json_encode($selections),
                    'is_default' => 1,
                    'state'      => 1,
                    'access'     => (int) $config->get('access', 1)
                );

                JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_osmap/tables');

                $table = JTable::getInstance('Sitemap', 'OSMapTable');
                $table->bind($data);
                $table->store();
            }
        }
    }
}
