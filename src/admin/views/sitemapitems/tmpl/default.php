<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap;

defined('_JEXEC') or die();

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.core');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

JHtml::_('stylesheet', 'com_osmap/admin.min.css', array('relative' => true));
JHtml::_('stylesheet', 'jui/icomoon.css', array('relative' => true));

JHtml::script('com_osmap/sitemapitems.min.js', false, true);

$container = OSMap\Factory::getContainer();
?>

<form
    action="<?php echo JRoute::_('index.php?option=com_osmap&view=sitemapitems&id=' . (int)$this->sitemapId); ?>"
    method="post"
    name="adminForm"
    id="adminForm"
    class="form-validate">

    <div class="row-fluid">
        <div class="span12">
            <div id="osmap-items-container">
                <div class="osmap-loading">
                    <span class="icon-loop spin"></span>
                    &nbsp;
                    <?php echo JText::_('COM_OSMAP_LOADING'); ?>
                </div>

                <div id="osmap-items-list"></div>
            </div>
        </div>
    </div>

    <input type="hidden" id="menus_ordering" name="jform[menus_ordering]" value=""/>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="id" value="<?php echo $this->sitemapId; ?>"/>
    <input type="hidden" name="update-data" id="update-data" value=""/>
    <input type="hidden" name="language" value="<?php echo $this->language; ?>"/>
    <?php echo JHtml::_('form.token'); ?>
</form>


<script>
;(function($) {
    $(function() {
        $.fn.osmap.loadSitemapItems({
            baseUri: '<?php echo $container->uri->root(); ?>',
            sitemapId: '<?php echo $this->sitemapId; ?>',
            container: '#osmap-items-list',
            language: '<?php echo $this->language; ?>',
            lang: {
                'COM_OSMAP_HOURLY': '<?php echo JText::_('COM_OSMAP_HOURLY'); ?>',
                'COM_OSMAP_DAILY': '<?php echo JText::_('COM_OSMAP_DAILY'); ?>',
                'COM_OSMAP_WEEKLY': '<?php echo JText::_('COM_OSMAP_WEEKLY'); ?>',
                'COM_OSMAP_MONTHLY': '<?php echo JText::_('COM_OSMAP_MONTHLY'); ?>',
                'COM_OSMAP_YEARLY': '<?php echo JText::_('COM_OSMAP_YEARLY'); ?>',
                'COM_OSMAP_NEVER': '<?php echo JText::_('COM_OSMAP_NEVER'); ?>',
                'COM_OSMAP_TOOLTIP_CLICK_TO_UNPUBLISH': '<?php echo JText::_('COM_OSMAP_TOOLTIP_CLICK_TO_UNPUBLISH'); ?>',
                'COM_OSMAP_TOOLTIP_CLICK_TO_PUBLISH': '<?php echo JText::_('COM_OSMAP_TOOLTIP_CLICK_TO_PUBLISH'); ?>'
            }
        });
    });
})(jQuery);
</script>
