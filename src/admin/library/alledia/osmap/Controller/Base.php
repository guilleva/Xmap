<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Controller;

use Alledia\OSMap;

defined('_JEXEC') or die();

class Base extends \JControllerLegacy
{
    public function checkToken($method = 'post', $redirect = true)
    {
        $valid = \JSession::checkToken();
        if (!$valid && $redirect) {
            if ($redirect) {
                $home      = OSMap\Factory::getApplication()->getMenu()->getDefault();
                $container = OSMap\Factory::getContainer();

                OSMap\Factory::getApplication()->redirect(
                    $container->router->routeURL('index.php?Itemid=' . $home->id),
                    \JText::_('JINVALID_TOKEN'),
                    'error'
                );
            }
        }

        return $valid;
    }
}
