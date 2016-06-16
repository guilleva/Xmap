<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JHtml::_('behavior.core');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

JHtml::stylesheet('media/com_osmap/css/admin.css');
JHtml::stylesheet('media/jui/css/icomoon.css');

JHtml::script('com_osmap/sitemapitems.js', false, true);

$showItemUID = $this->osmapParams->get('show_item_uid', 0);
?>

<form
    action="<?php echo JRoute::_('index.php?option=com_osmap&view=sitemapitems&id=' . (int)$this->sitemap->id); ?>"
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

                <?php if (empty($this->sitemapItems)) : ?>
                    <div class="alert alert-warning">
                        <?php echo JText::_('COM_OSMAP_NO_ITEMS'); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($this->sitemapItems)) : ?>
                    <table class="adminlist table table-striped" id="itemList">
                        <thead>
                            <tr>
                                <th width="1%" style="min-width:55px" class="nowrap center">
                                    <?php echo JText::_('COM_OSMAP_HEADING_STATUS'); ?>
                                </th>

                                <th class="title">
                                    <?php echo JText::_('COM_OSMAP_HEADING_URL'); ?>
                                </th>

                                <th class="title">
                                    <?php echo JText::_('COM_OSMAP_HEADING_TITLE'); ?>
                                </th>

                                <th class="center">
                                    <?php echo JText::_('COM_OSMAP_HEADING_PRIORITY'); ?>
                                </th>

                                <th class="nowrap center">
                                    <?php echo JText::_('COM_OSMAP_HEADING_CHANGE_FREQ'); ?>
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $i = 0; ?>
                            <?php foreach ($this->sitemapItems as $item) : ?>
                                <tr
                                    class="sitemapitem row<?php echo $i; ?> <?php echo ($showItemUID) ? 'with-uid' : ''; ?>"
                                    data-uid="<?php echo $item->uid; ?>"
                                    data-url-hash="<?php echo $item->fullLinkHash; ?>">

                                    <td class="center">
                                        <div class="sitemapitem-published"
                                            data-original="<?php echo $item->published ? '1' : '0'; ?>"
                                            data-value="<?php echo $item->published ? '1' : '0'; ?>">

                                                <?php if ($item->ignore) : ?>
                                                    <?php $title = $item->isInternal ? 'COM_OSMAP_IGNORED_TOOLTIP' : 'COM_OSMAP_IGNORED_EXTERNAL_TOOLTIP'; ?>
                                                    <span class="icon-warning" title="<?php echo JText::_($title); ?>"></span>
                                                <?php endif; ?>

                                                <span class="icon-<?php echo $item->published ? 'publish' : 'unpublish'; ?>"></span>

                                        </div>
                                    </td>
                                    <td class="sitemapitem-link">
                                        <?php if ($item->level > 0) : ?>
                                            <span class="level-mark">
                                                <?php echo str_repeat('—', $item->level); ?>
                                            </span>
                                        <?php endif; ?>

                                        <a
                                            href="<?php echo $item->fullLink; ?>"
                                            target="_blank"
                                            title="<?php echo $item->link; ?>">
                                            <?php echo $item->fullLink; ?>
                                        </a>
                                        <span class="icon-new-tab"></span>

                                        <?php if ($showItemUID) : ?>
                                            <br>
                                            <div class="small">
                                                <?php echo JText::_('COM_OSMAP_UID'); ?>: <?php echo $item->uid; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="sitemapitem-name">
                                        <?php echo isset($item->name) ? $item->name : ''; ?>
                                    </td>
                                    <td class="center">
                                        <div class="sitemapitem-priority"
                                            data-original="<?php echo $item->priority; ?>"
                                            data-value="<?php echo $item->priority; ?>">

                                            <?php echo $item->priority; ?>
                                        </div>
                                    </td>
                                    <td class="center">
                                        <div class="sitemapitem-changefreq"
                                            data-original="<?php echo $item->changefreq; ?>"
                                            data-value="<?php echo $item->changefreq; ?>">

                                            <?php echo JText::_('COM_OSMAP_' . strtoupper($item->changefreq)); ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <input type="hidden" id="menus_ordering" name="jform[menus_ordering]" value=""/>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="id" value="<?php echo $this->id; ?>"/>
    <input type="hidden" name="update-data" id="update-data" value=""/>
    <?php echo JHtml::_('form.token'); ?>
</form>


<script>
;(function($) {
    $(function() {
        var JText = {
            'COM_OSMAP_HOURLY': '<?php echo JText::_('COM_OSMAP_HOURLY'); ?>',
            'COM_OSMAP_DAILY': '<?php echo JText::_('COM_OSMAP_DAILY'); ?>',
            'COM_OSMAP_WEEKLY': '<?php echo JText::_('COM_OSMAP_WEEKLY'); ?>',
            'COM_OSMAP_MONTHLY': '<?php echo JText::_('COM_OSMAP_MONTHLY'); ?>',
            'COM_OSMAP_YEARLY': '<?php echo JText::_('COM_OSMAP_YEARLY'); ?>',
            'COM_OSMAP_NEVER': '<?php echo JText::_('COM_OSMAP_NEVER'); ?>'
        };

        $.fn.osmapSitemapItems(JText);
    });
})(jQuery);
</script>
