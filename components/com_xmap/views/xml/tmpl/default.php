<?php
/**
 * @version             $Id$
 * @copyright			Copyright (C) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
 */

// no direct access
defined('_JEXEC') or die;

// Create shortcut to parameters.
$params = $this->item->params;

$live_site = substr_replace(JURI::root(), "", -1, 1);

$this->isNews = JRequest::getInt('news',0);

header('Content-type: application/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>',"\n";
if (!$this->item->params->get('exclude_xsl') && !$this->displayer->isNews ) {
	$user = JFactory::getUser();
	if ( $this->displayer->_isAdmin) {
		echo '<?xml-stylesheet type="text/xsl" href="'. $live_site.'/index.php?option=com_xmap&amp;view=xml&amp;layout=adminxsl&amp;tmpl=component"?>'."\n";
	} else {
		echo '<?xml-stylesheet type="text/xsl" href="'. $live_site.'/index.php?option=com_xmap&amp;view=xml&amp;layout=xsl&amp;tmpl=component"?>'."\n";
	}
}
?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"<?php echo ($this->isNews? ' xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"':''); ?>>

<?php echo $this->loadTemplate('items'); ?>

</urlset>
