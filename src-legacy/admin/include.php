<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved..
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

use Alledia\Framework\Joomla\Extension\Helper as ExtensionHelper;
use Alledia\Framework\Joomla\Extension\Licensed as Licensed;

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

    // OSMap library
    $osmap = new Licensed('OSMap', 'component');

    if (!defined('OSMAP_LICENSE')) {
        $license = $osmap->isPro() ? 'pro' : 'free';

        define('OSMAP_LICENSE', $license);
    }

    $osmap->loadLibrary();

    // Register helper class
    JLoader::register('OSMapHelper', dirname(__FILE__) . '/helpers/osmap.php');

    // Joomla dependencies
    jimport('joomla.application.component.controller');
    jimport('joomla.form.form');

    JTable::addIncludePath(JPATH_COMPONENT . '/tables');
    JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
}
