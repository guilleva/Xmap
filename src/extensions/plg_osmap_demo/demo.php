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

class PlgOSMapDemo extends OSMap\Plugin\Base
{
    public function __construct(&$subject, $config = array())
    {
        require_once JPATH_ADMINISTRATOR . '/components/com_osmap/include.php';

        parent::__construct($subject, $config);
    }

    /**
     * Manipulate the submenus
     *
     * @param array $submenus An array of submenus to manipulate
     *
     * @return bool
     */
    public function onOSMapAddAdminSubmenu(&$submenus)
    {
        $submenus[] = array(
            'text' => 'COM_OSMAP_CUSTOM_SUBMENU',
            'link' => 'index.php&option=com_osmap',
            'view' => 'custom'
        );

        return true;
    }
}
