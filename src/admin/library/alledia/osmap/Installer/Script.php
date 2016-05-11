<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Installer;

defined('_JEXEC') or die();

use Alledia\Installer\AbstractScript;

if (file_exists(__DIR__ . '/../../../Installer/include.php')) {
    require_once __DIR__ . '/../../../Installer/include.php';
} else {
    require_once __DIR__ . '/../../Installer/include.php';
}


require_once JPATH_ADMINISTRATOR . '/modules/mod_menu/helper.php';


/**
 * OSMap Installer Script
 */
class Script extends AbstractScript
{

}
