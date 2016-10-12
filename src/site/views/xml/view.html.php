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


/**
 * This class won't be removed to keep backward compatibility with older
 * versions and avoid breaking any submitted URL. The ideal is to use format
 * set to 'xml', to trigger a fix on OSEmbed to the bug where it was being
 * called and delaying the sitemap request.
 *
 * @deprecated Since v1.2.2
 */
class OSMapViewXml extends OSMap\View\Sitemap\Xml
{
}
