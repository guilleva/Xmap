<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Controller;

use Alledia\OSMap;

defined('_JEXEC') or die();

class Base extends \JControllerLegacy
{
    /**
     * Standard form token check and redirect
     *
     * @return void
     */
    protected function checkToken()
    {
        if (!\JSession::checkToken()) {
            $home = OSMap\Factory::getApplication()->getMenu()->getDefault();

            OSMap\Factory::getApplication()->redirect(
                OSMap\Router::routeURL('index.php?Itemid=' . $home->id),
                JText::_('JINVALID_TOKEN'),
                'error'
            );
        }
    }
}
