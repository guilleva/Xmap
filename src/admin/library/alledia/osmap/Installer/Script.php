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
     */
    public function postFlight($type, $parent)
    {
        if (!parent::postFlight($type, $parent)) {
            return false;
        }

        // Load Alledia Framework
        require_once JPATH_ADMINISTRATOR . '/components/com_osmap/include.php';

        if ($type === 'update') {
            // Check if we still descriptions for each sitemap
            if (in_array('description', $this->getColumnsFromTable('#__osmap_sitemap'))) {
                Update::moveSitemapDescriptionToHtmlMenus();

                // Remove description and metadata from sitemap table
                $this->dropColumnsIfExists(
                    '#__osmap_sitemap',
                    array(
                        'description',
                        'metadesc',
                        'metakey'
                    )
                );
            }
        }
    }
}
