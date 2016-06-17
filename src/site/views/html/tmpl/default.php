<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap;

defined('_JEXEC') or die();

// If debug is enabled, use text content type
if ($this->debug) {
    OSMap\Factory::getApplication()->input->set('tmpl', 'component');
}

JHtml::stylesheet('media/com_osmap/css/sitemap-html.css');
?>

<div id="osmap-sitemap" class="<?php echo $this->debug ? 'osmap-debug' : ''; ?>">
    <?php if ($this->params->get('show_page_heading', 1) && $this->params->get('page_heading') != '') : ?>
        <!-- Heading -->
        <h2><?php echo $this->escape($this->params->get('page_heading')); ?></h2>
    <?php endif; ?>

    <?php if ($this->params->get('show_sitemap_description', 1)) :   ?>
        <!-- Description -->
        <p><?php echo $this->params->get('sitemap_description', ''); ?></p>
    <?php endif; ?>

    <!-- Error message, if exists -->
    <?php if (!empty($this->message)) : ?>
        <div class="alert alert-warning">
            <?php echo $this->message; ?>
        </div>
    <?php endif; ?>

    <!-- Items -->
    <?php if (empty($this->message)) : ?>
        <div class="osmap-items"><?php echo $this->loadTemplate('items'); ?></div>
    <?php endif; ?>
</div>
