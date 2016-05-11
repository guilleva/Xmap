<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Alledia\Framework;

// Alledia Framework
if (!defined('ALLEDIA_FRAMEWORK_LOADED')) {
    $allediaFrameworkPath = JPATH_SITE . '/libraries/allediaframework/include.php';

    if (file_exists($allediaFrameworkPath)) {
        require_once $allediaFrameworkPath;
    } else {
        JFactory::getApplication()
            ->enqueueMessage('[OSMap] Alledia framework not found', 'error');
    }
}

if (!defined('OSMAP_LOADED')) {
    define('OSMAP_LOADED', 1);
    define('OSMAP_ADMIN', JPATH_ADMINISTRATOR . '/components/com_osmap');
    define('OSMAP_SITE', JPATH_SITE . '/components/com_osmap');
    define('OSMAP_LIBRARY', OSMAP_ADMIN . '/library');

    // Setup autoload libraries
    Framework\AutoLoader::register('Alledia\OSMap', OSMAP_LIBRARY . '/alledia/osmap');
    Framework\AutoLoader::register('Pimple', OSMAP_LIBRARY . '/pimple/pimple');

    JTable::addIncludePath(OSMAP_SITE . '/tables');
    JForm::addFieldPath(OSMAP_SITE . '/models/fields');
}
