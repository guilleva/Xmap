<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Controller;

defined('_JEXEC') or die();

class Json extends \JControllerLegacy
{
    /**
     * Standard token checking for json controllers
     *
     * @return void
     * @throws Exception
     */
    protected function checkToken()
    {
        if (!\JSession::checkToken()) {
            throw new \Exception(\JText::_('JINVALID_TOKEN'), 403);
        }
    }
}
