<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap;


abstract class Helper
{
    /**
     * Build the submenu in admin if needed. Triggers the
     * onAdminSubmenu event for component addons to attach
     * their own admin screens.
     *
     * The expected response must be an array
     * [
     *    "text" => Static language string,
     *    "link" => Link to the screen
     *    "view" => unique view name
     * ]
     *
     * @param $viewName
     *
     * @return void
     */
    public static function addSubmenu($viewName)
    {
        $submenus = array(
            array(
                'text' => 'COM_OSMAP_SUBMENU_SITEMAPS',
                'link' => 'index.php?option=com_osmap&view=sitemaps',
                'view' => 'sitemaps'
            ),
            array(
                'text' => 'COM_OSMAP_SUBMENU_EXTENSIONS',
                'link' => 'index.php?option=com_plugins&view=plugins&filter_folder=osmap',
                'view' => 'extensions'
            )
        );

        $events = Factory::getContainer()->getEvents();
        $events->trigger('onOSMapAddAdminSubmenu', array(&$submenus));

        if (!empty($submenus)) {
            foreach ($submenus as $submenu) {
                if (is_array($submenu)) {
                    \JHtmlSidebar::addEntry(
                        \JText::_($submenu['text']),
                        $submenu['link'],
                        $viewName == $submenu['view']
                    );
                }
            }
        }
    }

    /**
     * Returns the sitemap type checking the input.
     * The expected types:
     *   - standard
     *   - images
     *   - news
     *
     * @return string
     */
    public static function getSitemapTypeFromInput()
    {
        $container = Factory::getContainer();

        if ((bool)$container->input->getStr('images', 0)) {
            return 'images';
        }

        if ((bool)$container->input->getStr('news', 0)) {
            return 'news';
        }

        return 'standard';
    }
}
