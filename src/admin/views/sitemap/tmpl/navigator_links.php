<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2015 Open Source Training, LLC. All rights reserved..
 * @author    Guillermo Vargas <guille@vargas.co.cr>
 * @author    Alledia <support@alledia.com>
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file is part of OSMap.
 *
 * OSMap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * OSMap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSMap. If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die('Restricted access');

header('Content-type: text/xml');

$name = JRequest::getCmd('e_name');
?>
<?php echo '<?xml version="1.0" encoding="UTF-8" ?>'; ?>
<nodes>
<?php foreach ($this->list as $node) {
    $load = 'index.php?option=com_osmap&amp;task=navigator-links&amp;sitemap='.$this->item->id.'&amp;e_name='.$name.(isset($node->id)?'&amp;Itemid='.$node->id:'').(isset($node->link)?'&amp;link='.urlencode($node->link):'').'&amp;tmpl=component';
?>
    <node text="<?php echo htmlentities($node->name); ?>" <?php echo ($node->expandible?" openicon=\"_open\" icon=\"_closed\" load=\"$load\"":' icon="_doc"'); ?> uid="<?php $node->uid; ?>" link="<?php echo str_replace(array('&amp;','&'),array('&','&amp;'),$node->link); ?>" selectable="<?php echo ($node->selectable?'true':'false'); ?>" />
<?php } ?>
</nodes>
