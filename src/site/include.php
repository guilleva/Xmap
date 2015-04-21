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
// no direct access
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

$osmap = new Licensed('OSMap', 'component');

if (!defined('OSMAP_LICENSE')) {
    $license = $osmap->isPro() ? 'pro' : 'free';

    define('OSMAP_LICENSE', $license);
}

$osmap->loadLibrary();

require_once JPATH_ADMINISTRATOR . '/components/com_osmap/include.php';
require_once JPATH_COMPONENT . '/displayer.php';
