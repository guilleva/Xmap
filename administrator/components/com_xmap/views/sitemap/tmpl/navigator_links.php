<?php
/**
 * @version             $Id$
 * @copyright		Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
 */

defined('_JEXEC') or die;

header('Content-type: text/xml');

$name = JRequest::getCmd('e_name');
?>
<?xml version="1.0" encoding="UTF-8" ?>
<nodes>
<?php foreach ($this->list as $node) {
         $load = 'index.php?option=com_xmap&amp;task=navigator-links&amp;sitemap='.$this->item->id.'&amp;e_name='.$name.(isset($node->id)?'&amp;Itemid='.$node->id:'').(isset($node->link)?'&amp;link='.urlencode($node->link):'').'&amp;tmpl=component';
?>
 	<node text="<?php echo htmlentities($node->name); ?>" <?php echo ($node->expandible?" openicon=\"_open\" icon=\"_closed\" load=\"$load\"":' icon="_doc"'); ?> uid="<?php $node->uid; ?>" link="<?php echo str_replace(array('&amp;','&'),array('&','&amp;'),$node->link); ?>" selectable="<?php echo ($node->selectable?'true':'false'); ?>" />
<?php 
                }
?>
</nodes>
