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

// Check if we need to inject the CSS
if ($this->params->get('use_css', 1)) {
    JHtml::stylesheet('media/com_osmap/css/sitemap_html.min.css');
}

// If debug is enabled, use text content type
if ($this->debug) {
    OSMap\Factory::getApplication()->input->set('tmpl', 'component');
    JHtml::stylesheet('media/com_osmap/css/sitemap_html_debug.min.css');
}

// Check if we have parameters from a menu, acknowledging we have a menu
if (!is_null($this->params->get('menu_text'))) {
    // We have a menu, so let's use its params to display the heading
    $pageHeading = $this->params->get('page_heading', $this->params->get('page_title'));
} else {
    // We don't have a menu, so lets use the sitemap name
    $pageHeading = $this->sitemap->name;
}
?>

<div id="osmap" class="osmap-sitemap <?php echo $this->debug ? 'osmap-debug' : ''; ?> <?php echo $this->params->get('pageclass_sfx', ''); ?>">
    <!-- Heading -->

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <div class="page-header">
            <h1><?php echo $this->escape($pageHeading); ?></h1>
        </div>
    <?php endif; ?>

    <!-- Description -->
    <?php if ($this->params->get('show_sitemap_description', 1)) :   ?>
        <div class="osmap-sitemap-description">
            <?php echo $this->params->get('sitemap_description', ''); ?>
        </div>
    <?php endif; ?>

    <!-- Error message, if exists -->
    <?php if (!empty($this->message)) : ?>
        <div class="alert alert-warning">
            <?php echo $this->message; ?>
        </div>
    <?php endif; ?>

    <!-- Items -->
    <?php if (empty($this->message)) : ?>
        <?php echo $this->loadTemplate('items'); ?>
    <?php endif; ?>
</div>
