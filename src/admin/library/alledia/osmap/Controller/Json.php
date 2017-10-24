<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Controller;

defined('_JEXEC') or die();

class Json extends \JControllerLegacy
{
    /**
     * @param string $method
     * @param bool   $redirect
     *
     * @return bool
     * @throws \Exception
     */
    public function checkToken($method = 'post', $redirect = true)
    {
        if (!\JSession::checkToken()) {
            throw new \Exception(\JText::_('JINVALID_TOKEN'), 403);
        }

        return true;
    }
}
