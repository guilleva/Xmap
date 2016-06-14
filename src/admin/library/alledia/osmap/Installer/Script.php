<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Installer;

use Alledia\Installer\AbstractScript;
use Alledia\OSMap;

defined('_JEXEC') or die();

if (file_exists(__DIR__ . '/../../../Installer/include.php')) {
    $basePath = __DIR__ . '/../../..';
} else {
    $basePath = __DIR__ . '/../..';
}
require_once $basePath . '/Installer/include.php';
require_once $basePath . '/alledia/osmap/Installer/Update.php';

require_once JPATH_ADMINISTRATOR . '/modules/mod_menu/helper.php';


/**
 * OSMap Installer Script
 */
class Script extends AbstractScript
{
    /**
     * Post installation actions
     *
     * @return bool
     */
    public function postFlight($type, $parent)
    {
        if (!parent::postFlight($type, $parent)) {
            return false;
        }

        // Load Alledia Framework
        require_once JPATH_ADMINISTRATOR . '/components/com_osmap/include.php';

        if ($type === 'update') {
            $this->checkDeprecatedSitemapColumns();
        }

        $this->createDefaultSitemap();

        return true;
    }

    /**
     * Check if we still have old columns in the sitemap table
     *
     @return void
     */
    protected function checkDeprecatedSitemapColumns()
    {
        $deprecatedColumns = array(
            'description',
            'metadesc',
            'metakey'
        );

        $foundColumns = array_intersect($deprecatedColumns, $this->getColumnsFromTable('#__osmap_sitemaps'));
        if (!empty($foundColumns)) {
            Update::moveSitemapDescriptionToHtmlMenus();

            // Remove description and metadata from sitemap table
            $this->dropColumnsIfExists('#__osmap_sitemaps', $foundColumns);
        }
    }

    /**
     * Creates a default sitemap if no one is found.
     *
     @return void
     */
    protected function createDefaultSitemap()
    {
        $db = OSMap\Factory::getDbo();
        // Check if we have any sitemap, otherwise lets create a default one
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__osmap_sitemaps');
        $noSitemaps = ((int) $db->setQuery($query)->loadResult()) === 0;

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
                \JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_osmap/tables');
                $row = \JTable::getInstance('Sitemap', 'OSMapTable');
                $row->save($data);

                $i = 0;
                foreach ($menus as $menu) {
                    $query = $db->getQuery(true)
                        ->select('id')
                        ->from('#__menu_types')
                        ->where('menutype = ' . $db->quote($menu->menutype));
                    $menuTypeId = (int)$db->setQuery($query)->loadResult();

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
}
