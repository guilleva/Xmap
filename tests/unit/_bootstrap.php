<?php
/**
 * @package   tests_osmap
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Codeception\Util\Autoload;
use AspectMock\Test;

/**
 * Loads the necessary support for testing OSMap
 */

define('OSMAP_TESTS_PATH', realpath(__DIR__ . '/../'));
define('OSMAP_UNIT_TESTS_PATH', __DIR__);
define('OSMAP_ROOT_PATH', realpath(OSMAP_UNIT_TESTS_PATH . '/../..'));
define('OSMAP_SRC_PATH', OSMAP_ROOT_PATH . '/src');
define('OSMAP_TEST', 1);

// Mock a minimal Joomla framework
define('_JEXEC', 1);
define('JPATH_BASE', realpath(__DIR__ . '/../_support/joomla'));
define('JPATH_PLATFORM', JPATH_BASE . '/libraries');
define('JPATH_SITE', JPATH_BASE);
define('JPATH_ADMINISTRATOR', JPATH_BASE . '/administrator');

// Copied from /includes/framework.php
@ini_set('magic_quotes_runtime', 0);
@ini_set('zend.ze1_compatibility_mode', '0');

error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', 1);

// Setup autoload libraries
require_once OSMAP_ROOT_PATH . '/vendor/autoload.php';

$kernel = \AspectMock\Kernel::getInstance();
$kernel->init([
    'debug'        => true,
    'includePaths' => [OSMAP_ROOT_PATH . '/admin/library/alledia/osmap']
]);

$kernel->loadFile(__DIR__ . '/autoloader.php');
$kernel->loadPhpFiles(OSMAP_TESTS_PATH . '/_support/mock');

AutoLoader::register('\Alledia\OSMap', OSMAP_SRC_PATH . '/admin/library/alledia/osmap');
