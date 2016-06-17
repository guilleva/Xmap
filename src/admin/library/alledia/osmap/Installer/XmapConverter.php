<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Installer;

use Alledia\OSMap;

defined('_JEXEC') or die();


/**
 * Class with methods to migrate a Xmap installation to OSMap
 */
class XmapConverter
{
    /**
     * @var array
     */
    protected $xmapPluginsParams = array();

    /**
     * List of refactored Xmap plugins to migrate the settings
     *
     * @var array
     */
    protected $refactoredXmapPlugins = array('com_content');

    /**
     * Look for the Xmap data to suggest a data migration
     *
     * @return bool True if Xmap data was found
     */
    public function checkXmapDataExists()
    {
        $db = OSMap\Factory::getDbo();

        // Do we have any Xmap sitemap?
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__xmap_sitemap');

        $total = (int) $db->setQuery($query)->loadResult();

        return $total > 0;
    }

    /**
     * Save the Xmap plugins params into the new plugins. Receives a list of
     * plugin names to look for params.
     *
     * @return void
     */
    public function saveXmapPluginParamsIfExists()
    {
        $db = \JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select(
                array(
                    'element',
                    'params'
                )
            )
            ->from('#__extensions')
            ->where(
                array(
                    'type = "plugin"',
                    'folder = "xmap"',
                    'element IN ("' . implode('","', $this->refactoredXmapPlugins) . '")'
                )
            );
        $legacyPlugins = $db->setQuery($query)->loadObjectList();

        // Check if the respective OSMap plugin is already installed. If so, do not save its params to not override.
        if (!empty($legacyPlugins)) {
            foreach ($legacyPlugins as $plugin) {
                $query = $db->getQuery(true)
                    ->select('extension_id')
                    ->from('#__extensions')
                    ->where(
                        array(
                            'type = "plugin"',
                            'folder = "osmap"',
                            'element = "' . $plugin->element . '"'
                        )
                    );
                $osmapPluginID = $db->setQuery($query)->loadResult();

                if (empty($osmapPluginID)) {
                    $this->xmapPluginsParams[] = $plugin;
                }
            }
        }
    }

    /**
     * This method move the Xmap plugins' params to the OSMap plugins.
     *
     * @return void
     */
    public function moveXmapPluginsParamsToOSMapPlugins()
    {
        $db = \JFactory::getDbo();

        if (!empty($this->xmapPluginsParams)) {
            foreach ($this->xmapPluginsParams as $plugin) {
                // Look for the OSMap plugin
                $query = $db->getQuery(true)
                    ->select('extension_id')
                    ->from('#__extensions')
                    ->where(
                        array(
                            'type = "plugin"',
                            'folder = "osmap"',
                            'element = "' . $plugin->element . '"'
                        )
                    );
                $osmapPluginID = $db->setQuery($query)->loadResult();

                if (!empty($osmapPluginID)) {
                    $query = $db->getQuery(true)
                        ->update('#__extensions')
                        ->set('params = "' . addslashes($plugin->params) . '"')
                        ->where('extension_id = ' . $osmapPluginID);
                    $db->setQuery($query)->execute();
                }
            }
        }
    }
}
