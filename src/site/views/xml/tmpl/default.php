<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

// If debug is enabled, use text content type
if (isset($this->params) && $this->params->get('debug', 0)) {
    header('Content-type: text/plain; charset=utf-8');
} else {
    header('Content-type: text/xml; charset=utf-8');
}

// Check if we have parameters from a menu, acknowledging we have a menu
if (!is_null($this->params->get('menu_text'))) {
    // We have a menu, so let's use its params to display the heading
    $this->pageHeading = $this->params->get('page_heading', $this->params->get('page_title'));
} else {
    // We don't have a menu, so lets use the sitemap name
    $this->pageHeading = $this->sitemap->name;
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

// Only display the message in the XML
if (!empty($this->message)) {
    echo $this->loadTemplate('message');
}

// Load the template of sitemap according to the requested type
if (empty($this->message)) {
    echo $this->loadTemplate($this->type);
}
