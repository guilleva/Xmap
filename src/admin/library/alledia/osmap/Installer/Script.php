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

namespace Alledia\OSMap\Installer;

use Alledia\Installer\AbstractScript;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

defined('_JEXEC') or die();

if (!defined('OSMAP_TEST')) {
    if (file_exists(__DIR__ . '/../../../Installer/include.php')) {
        $basePath = __DIR__ . '/../../..';
    } else {
        $basePath = __DIR__ . '/../..';
    }

    require_once $basePath . '/Installer/include.php';
    require_once __DIR__ . '/XmapConverter.php';

    require_once JPATH_ADMINISTRATOR . '/modules/mod_menu/helper.php';
}


/**
 * OSMap Installer Script
 */
class Script extends AbstractScript
{
    /**
     * @var bool
     */
    protected $isXmapDataFound = false;

    /**
     * Post installation actions
     *
     * @return void
     * @throws \Exception
     */
    public function postFlight($type, $parent)
    {
        // Check if XMap is installed, to start a migration
        $xmapConverter = new XmapConverter;

        // This attribute will be used by the cusotm template to display the option to migrate legacy sitemaps
        $this->isXmapDataFound = $this->tableExists('#__xmap_sitemap') ?
            $xmapConverter->checkXmapDataExists() : false;

        // If Xmap plugins are still available and we don't have the OSMap plugins yet,
        // save Xmap plugins params to re-apply after install OSMap plugins
        $xmapConverter->saveXmapPluginParamsIfExists();

        // Runs the post install/update method
        parent::postFlight($type, $parent);

        // Load Alledia Framework
        require_once JPATH_ADMINISTRATOR . '/components/com_osmap/include.php';

        switch ($type) {
            case 'install':
            case 'discover_install':
                // New installation [discover_install|install]
                $this->createDefaultSitemap();

                $app = Factory::getApplication();

                $link = HTMLHelper::_(
                    'link',
                    'index.php?option=com_plugins&view=plugins&filter.search=OSMap',
                    Text::_('COM_OSMAP_INSTALLER_PLUGINS_PAGE')
                );
                $app->enqueueMessage(Text::sprintf('COM_OSMAP_INSTALLER_GOTOPLUGINS', $link), 'warning');
                break;

            case 'update':
                $this->migrateLegacySitemaps();
                $this->fixXMLMenus();
                break;


        }

        $xmapConverter->moveXmapPluginsParamsToOSMapPlugins();
        $this->checkDbScheme();

        $this->showMessages();
    }

    /**
     * Creates a default sitemap if no one is found.
     *
     * @return void
     */
    protected function createDefaultSitemap()
    {
        $db = Factory::getDbo();

        // Check if we have any sitemap, otherwise lets create a default one
        $query      = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__osmap_sitemaps');
        $noSitemaps = ((int)$db->setQuery($query)->loadResult()) === 0;

        if ($noSitemaps) {
            // Get all menus
            $menus = \ModMenuHelper::getMenus();
            if (!empty($menus)) {
                $data = array(
                    'name'       => 'Default Sitemap',
                    'is_default' => 1,
                    'published'  => 1
                );

                // Create the sitemap
                Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_osmap/tables');
                $row = Table::getInstance('Sitemap', 'OSMapTable');
                $row->save($data);

                $i = 0;
                foreach ($menus as $menu) {
                    $menuTypeId = $this->getMenuTypeId($menu->menutype);

                    $query = $db->getQuery(true)
                        ->set('sitemap_id = ' . $db->quote($row->id))
                        ->set('menutype_id = ' . $db->quote($menuTypeId))
                        ->set('priority = ' . $db->quote('0.5'))
                        ->set('changefreq = ' . $db->quote('weekly'))
                        ->set('ordering = ' . $db->quote($i++))
                        ->insert('#__osmap_sitemap_menus');
                    $db->setQuery($query)->execute();
                }
            }
        }
    }

    /**
     * In case we are updating from a legacy version, make sure to cleanup
     * the new tables to get a clean start for the data migration
     *
     * @return void
     */
    protected function cleanupDatabase()
    {
        $db = Factory::getDbo();

        $db->setQuery('DELETE FROM ' . $db->quoteName('#__osmap_items_settings'))->execute();
        $db->setQuery('DELETE FROM ' . $db->quoteName('#__osmap_sitemap_menus'))->execute();
        $db->setQuery('DELETE FROM ' . $db->quoteName('#__osmap_sitemaps'))->execute();
    }

    /**
     * Check if there are sitemaps in the old table. After migrate, remove
     * the table.
     *
     * @return void
     * @throws \Exception
     */
    protected function migrateLegacySitemaps()
    {
        $db = Factory::getDbo();

        if ($this->tableExists('#__osmap_sitemap')) {
            try {
                $db->transactionStart();

                // For the migration, as we only have new tables, make sure to have a clean start
                $this->cleanupDatabase();

                // Get legacy sitemaps
                $query    = $db->getQuery(true)
                    ->select(
                        array(
                            'id',
                            'title',
                            'is_default',
                            'state',
                            'created',
                            'selections',
                            'excluded_items'
                        )
                    )
                    ->from('#__osmap_sitemap');
                $sitemaps = $db->setQuery($query)->loadObjectList();

                // Move the legacy sitemaps to the new table
                if (!empty($sitemaps)) {
                    foreach ($sitemaps as $sitemap) {
                        // Make sure we have a creation date
                        if ($sitemap->created === $db->getNullDate()) {
                            $sitemap->created = Factory::getDate()->toSql();
                        }

                        $query = $db->getQuery(true)
                            ->insert('#__osmap_sitemaps')
                            ->set(
                                array(
                                    'id = ' . $db->quote($sitemap->id),
                                    'name = ' . $db->quote($sitemap->title),
                                    'is_default = ' . $db->quote($sitemap->is_default),
                                    'published = ' . $db->quote($sitemap->state),
                                    'created_on = ' . $db->quote($sitemap->created)
                                )
                            );
                        $db->setQuery($query)->execute();

                        // Add the selected menus to the correct table
                        $menus = json_decode($sitemap->selections, true);

                        if (!empty($menus)) {
                            foreach ($menus as $menuType => $menu) {
                                $menuTypeId = $this->getMenuTypeId($menuType);

                                // Check if the menutype still exists
                                if (!empty($menuTypeId)) {
                                    // Convert the selection of menus into a row
                                    $query = $db->getQuery(true)
                                        ->insert('#__osmap_sitemap_menus')
                                        ->columns(
                                            array(
                                                'sitemap_id',
                                                'menutype_id',
                                                'priority',
                                                'changefreq',
                                                'ordering'
                                            )
                                        )
                                        ->values(
                                            implode(
                                                ',',
                                                array(
                                                    $db->quote($sitemap->id),
                                                    $db->quote($menuTypeId),
                                                    $db->quote($menu['priority']),
                                                    $db->quote($menu['changefreq']),
                                                    $db->quote($menu['ordering'])
                                                )
                                            )
                                        );
                                    $db->setQuery($query)->execute();
                                }
                            }
                        }

                        // Convert settings about excluded items
                        $excludedItems = array();
                        if (!empty($sitemap->excluded_items)) {
                            $excludedItems = json_decode($sitemap->excluded_items, true);

                            if (!empty($excludedItems)) {
                                foreach ($excludedItems as $item) {
                                    $uid = $this->convertItemUID($item[0]);

                                    // Check if the item was already registered
                                    $query = $db->getQuery(true)
                                        ->select('COUNT(*)')
                                        ->from('#__osmap_items_settings')
                                        ->where(
                                            array(
                                                'sitemap_id = ' . $db->quote($sitemap->id),
                                                'uid = ' . $db->quote($uid)
                                            )
                                        );
                                    $count = $db->setQuery($query)->loadResult();

                                    if ($count == 0) {
                                        // Insert the settings
                                        $query = $db->getQuery(true)
                                            ->insert('#__osmap_items_settings')
                                            ->columns(
                                                array(
                                                    'sitemap_id',
                                                    'uid',
                                                    'published',
                                                    'changefreq',
                                                    'priority'
                                                )
                                            )
                                            ->values(
                                                implode(
                                                    ',',
                                                    array(
                                                        $sitemap->id,
                                                        $db->quote($uid),
                                                        0,
                                                        $db->quote('weekly'),
                                                        $db->quote('0.5')
                                                    )
                                                )
                                            );
                                        $db->setQuery($query)->execute();
                                    } else {
                                        // Update the setting
                                        $query = $db->getQuery(true)
                                            ->update('#__osmap_items_settings')
                                            ->set('published = 0')
                                            ->where(
                                                array(
                                                    'sitemap_id = ' . $db->quote($sitemap->id),
                                                    'uid = ' . $db->quote($uid)
                                                )
                                            );
                                        $db->setQuery($query)->execute();
                                    }
                                }
                            }
                        }

                        // Convert custom settings for items
                        if ($this->tableExists('#__osmap_items')) {
                            $query         = $db->getQuery(true)
                                ->select(
                                    array(
                                        'uid',
                                        'properties'
                                    )
                                )
                                ->from('#__osmap_items')
                                ->where('sitemap_id = ' . $db->quote($sitemap->id))
                                ->where('view = ' . $db->quote('xml'));
                            $modifiedItems = $db->setQuery($query)->loadObjectList();

                            if (!empty($modifiedItems)) {
                                foreach ($modifiedItems as $item) {
                                    $item->properties = str_replace(';', '&', $item->properties);
                                    parse_str($item->properties, $properties);

                                    $item->uid = $this->convertItemUID($item->uid);

                                    // Check if the item already exists to update, or insert
                                    $query  = $db->getQuery(true)
                                        ->select('COUNT(*)')
                                        ->from('#__osmap_items_settings')
                                        ->where(
                                            array(
                                                'sitemap_id = ' . $db->quote($sitemap->id),
                                                'uid = ' . $db->quote($item->uid)
                                            )
                                        );
                                    $exists = (bool)$db->setQuery($query)->loadResult();


                                    if ($exists) {
                                        $fields = array();

                                        // Check if the changefreq is set and set to update
                                        if (isset($properties['changefreq'])) {
                                            $fields = 'changefreq = ' . $db->quote($properties['changefreq']);
                                        }

                                        // Check if the priority is set and set to update
                                        if (isset($properties['priority'])) {
                                            $fields = 'priority = ' . $db->quote($properties['priority']);
                                        }

                                        // Update the item
                                        $query = $db->getQuery(true)
                                            ->update('#__osmap_items_settings')
                                            ->set($fields)
                                            ->where(
                                                array(
                                                    'sitemap_id = ' . $db->quote($sitemap->id),
                                                    'uid = ' . $db->quote($item->uid)
                                                )
                                            );
                                        $db->setQuery($query)->execute();
                                    }

                                    if (!$exists) {
                                        $columns = array(
                                            'sitemap_id',
                                            'uid',
                                            'published'
                                        );

                                        $values = array(
                                            $db->quote($sitemap->id),
                                            $db->quote($item->uid),
                                            1
                                        );

                                        // Check if the changefreq is set and set to update
                                        if (isset($properties['changefreq'])) {
                                            $columns[] = 'changefreq';
                                            $values[]  = 'changefreq = ' . $db->quote($properties['changefreq']);
                                        }

                                        // Check if the priority is set and set to update
                                        if (isset($properties['priority'])) {
                                            $columns[] = 'priority';
                                            $values[]  = 'priority = ' . $db->quote($properties['priority']);
                                        }

                                        // Insert a new item
                                        $query = $db->getQuery(true)
                                            ->insert('#__osmap_items_settings')
                                            ->columns($columns)
                                            ->values(implode(',', $values));
                                        $db->setQuery($query)->execute();
                                    }
                                }
                            }
                        }
                    }
                }

                // Remove the old table
                $query = 'DROP TABLE IF EXISTS ' . $db->quoteName('#__osmap_items');
                $db->setQuery($query)->execute();

                // Remove the old table
                $query = 'DROP TABLE IF EXISTS ' . $db->quoteName('#__osmap_sitemap');
                $db->setQuery($query)->execute();

                $db->transactionCommit();
            } catch (\Exception $e) {
                Factory::getApplication()->enqueueMessage(
                    Text::sprintf('COM_OSMAP_INSTALLER_ERROR_MIGRATING_DATA', $e->getMessage()),
                    'error'
                );
                $db->transactionRollback();
            }
        }
    }

    /**
     * Returns the id of the menutype.
     *
     * @param string $menuType
     *
     * @return int
     */
    protected function getMenuTypeId($menuType)
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__menu_types')
            ->where('menutype = ' . $db->quote($menuType));
        return (int)$db->setQuery($query)->loadResult();
    }

    /**
     * Converts a legacy UID to the new pattern. Instead of "com_contenta25",
     * "joomla.article.25". Returns the new UID
     *
     * @param string $uid
     *
     * @return string
     */
    protected function convertItemUID($uid)
    {
        // Joomla articles in categories
        if (preg_match('#com_contentc[0-9]+a([0-9]+)#', $uid, $matches)) {
            return 'joomla.article.' . $matches[1];
        }

        // Joomla categories
        if (preg_match('#com_contentc([0-9]+)#', $uid, $matches)) {
            return 'joomla.category.' . $matches[1];
        }

        // Joomla articles
        if (preg_match('#com_contenta([0-9]+)#', $uid, $matches)) {
            return 'joomla.article.' . $matches[1];
        }

        // Joomla featured
        if (preg_match('#com_contentfeatureda([0-9]+)#', $uid, $matches)) {
            return 'joomla.featured.' . $matches[1];
        }

        // Menu items
        if (preg_match('#itemid([0-9]*)#', $uid, $matches)) {
            return 'menuitem.' . $matches[1];
        }

        return $uid;
    }

    /**
     * Check the database scheme
     */
    protected function checkDbScheme()
    {
        // Table: #__osmap_items_settings
        $existentColumns = $this->getColumnsFromTable('#__osmap_items_settings');

        $db = Factory::getDbo();

        // URH Hash
        if (in_array('url_hash', $existentColumns)) {
            $db->setQuery('ALTER TABLE `#__osmap_items_settings`
                CHANGE `url_hash` `settings_hash` CHAR(32)
                CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL DEFAULT ""');
            $db->execute();
        }

        // Format
        if (!in_array('format', $existentColumns)) {
            $db->setQuery('ALTER TABLE `#__osmap_items_settings`
                ADD `format` TINYINT(1) UNSIGNED DEFAULT NULL
                COMMENT \'Format of the setting: 1) Legacy Mode - UID Only; 2) Based on menu ID and UID\'');
            $db->execute();
        }
    }

    /**
     * Adds new format=xml to existing xml menus
     * @since v4.2.25
     */
    protected function fixXMLMenus()
    {
        $db      = Factory::getDbo();
        $siteApp = SiteApplication::getInstance('site');

        $query = $db->getQuery(true)
            ->select('id, link')
            ->from('#__menu')
            ->where(
                array(
                    'client_id = ' . $siteApp->getClientId(),
                    sprintf('link LIKE %s', $db->quote('%com_osmap%')),
                    sprintf('link LIKE %s', $db->quote('%view=xml%')),
                    sprintf('link NOT LIKE %s', $db->quote('%format=xml%'))
                )
            );

        $menus = $db->setQuery($query)->loadObjectList();
        foreach ($menus as $menu) {
            $menu->link .= '&format=xml';
            $db->updateObject('#__menu', $menu, array('id'));
        }
    }
}
