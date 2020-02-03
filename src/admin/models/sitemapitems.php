<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2020 Joomlashack.com. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap;

defined('_JEXEC') or die();


class OSMapModelSitemapItems extends JModelLegacy
{
    public function getTable($name = 'SitemapItems', $prefix = 'OSMapTable', $options = array())
    {
        return JTable::getInstance($name, $prefix, $options);
    }
}
