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

use Alledia\Framework;
use Alledia\OSMap;

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
    define('OSMAP_ADMIN_PATH', JPATH_ADMINISTRATOR . '/components/com_osmap');
    define('OSMAP_SITE_PATH', JPATH_SITE . '/components/com_osmap');
    define('OSMAP_LIBRARY_PATH', OSMAP_ADMIN_PATH . '/library');

    // Define the constant for the license
    define(
        'OSMAP_LICENSE',
        file_exists(OSMAP_LIBRARY_PATH . '/alledia/osmap/Services/Pro.php') ? 'pro' : 'free'
    );

    // Setup autoload libraries
    Framework\AutoLoader::register('Alledia\OSMap', OSMAP_LIBRARY_PATH . '/alledia/osmap');
    Framework\AutoLoader::register('Pimple', OSMAP_LIBRARY_PATH . '/pimple/pimple');

    // Load OSMap Plugins
    JPluginHelper::importPlugin('osmap');

    // Load the language files
    OSMap\Helper\General::loadOptionLanguage('com_osmap', OSMAP_ADMIN_PATH, OSMAP_SITE_PATH);

    JTable::addIncludePath(OSMAP_ADMIN_PATH . '/tables');
    JForm::addFieldPath(OSMAP_ADMIN_PATH . '/fields');
    JForm::addFormPath(OSMAP_ADMIN_PATH . '/form');
    JHtml::addIncludePath(OSMAP_ADMIN_PATH . '/helpers/html');

    JLog::addLogger(
        array(
            // Sets file name
            'text_file' => 'com_osmap.errors.php'
        ),
        // Sets messages of all log levels to be sent to the file
        JLog::ALL,
        // The log category/categories which should be recorded in this file
        // In this case, it's just the one category from our extension, still
        // we need to put it inside an array
        array('com_osmap')
    );
}
