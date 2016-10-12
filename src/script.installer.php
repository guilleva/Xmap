<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap;

defined('_JEXEC') or die();

// Adapt for install and uninstall environments
if (file_exists(__DIR__ . '/admin/library/alledia/osmap/Installer/Script.php')) {
    require_once __DIR__ . '/admin/library/alledia/osmap/Installer/Script.php';
} else {
    require_once __DIR__ . '/library/alledia/osmap/Installer/Script.php';
}

class com_osmapInstallerScript extends OSMap\Installer\Script
{
}
