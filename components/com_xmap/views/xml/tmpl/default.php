<?php
/**
 * @version             $Id$
 * @copyright			Copyright (C) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Create shortcut to parameters.
$params = $this->item->params;

$live_site = substr_replace(JURI::root(), "", -1, 1);

header('Content-type: text/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>',"\n";
if (($this->item->params->get('beautify_xml', 1) == 1) && !$this->displayer->isNews) {
    $params  = '&amp;filter_showtitle='.JRequest::getBool('filter_showtitle',0);
    $params .= '&amp;filter_showexcluded='.JRequest::getBool('filter_showexcluded',0);
    $params .= (JRequest::getCmd('lang')?'&amp;lang='.JRequest::getCmd('lang'):'');
    echo '<?xml-stylesheet type="text/xsl" href="'. $live_site.'/index.php?option=com_xmap&amp;view=xml&amp;layout=xsl&amp;tmpl=component&amp;id='.$this->item->id.($this->isImages?'&amp;images=1':'').$params.'"?>'."\n";
}
?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"<?php echo ($this->displayer->isImages? ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"':''); ?><?php echo ($this->displayer->isNews? ' xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"':''); ?>>

<?php echo $this->loadTemplate('items'); ?>

</urlset>