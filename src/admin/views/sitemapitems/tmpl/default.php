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
                                <tr class="sitemapitem row<?php echo $i; ?>">
                                    <td class="center">
                                        <div class="sitemapitem-published"
                                            data-uid="<?php echo $item->uid; ?>"
                                            data-original="<?php echo $item->published ? '1' : '0'; ?>"
                                            data-value="<?php echo $item->published ? '1' : '0'; ?>">

                                            <span class="icon-<?php echo $item->published ? 'publish' : 'unpublish'; ?>"></span>
                                        </div>
                                    </td>
                                    <td class="sitemapitem-link">
                                        <a
                                            href="<?php echo $item->fullLink; ?>"
                                            target="_blank"
                                            title="<?php echo $item->link; ?>">

                                            <span class="icon-new-tab"></span>
                                            <?php echo $item->fullLink; ?>
                                        </a>
                                    </td>
                                    <td class="sitemapitem-name">
                                        <?php echo isset($item->name) ? $item->name : ''; ?>
                                    </td>
                                    <td class="center">
                                        <div class="sitemapitem-priority"
                                            data-uid="<?php echo $item->uid; ?>"
                                            data-original="<?php echo $item->priority; ?>"
                                            data-value="<?php echo $item->priority; ?>">

                                            <?php echo $item->priority; ?>
                                        </div>
                                    </td>
                                    <td class="center">
                                        <div class="sitemapitem-changefreq"
                                            data-uid="<?php echo $item->uid; ?>"
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
    <input type="hidden" name="update-data" value=""/>
    <?php echo JHtml::_('form.token'); ?>
</form>

<script>
;(function($) {
    $(function() {
        $('#itemList .sitemapitem').hover(
            function(event) {
                $this = $(this);

                // Remove the selected class from the last item
                $('#itemList .selected').removeClass('selected');

                // Add the selected class to highlight the row and fields
                $this.addClass('selected');
            }
        );

        // Add the event for the publish status elements
        $('#itemList .sitemapitem-published').click(
            function(event) {
                var $this = $(this),
                    newValue  = $this.data('value') == 1 ? 0 : 1,
                    spanClass = newValue == 1 ? 'publish' : 'unpublish',
                    span = $this.find('span');

                $this.data('value', newValue);

                $(this).parents('.sitemapitem').addClass('updated');

                span.attr('class', '');
                span.addClass('icon-' + spanClass);
            }
        );

        // Add the event for the priority elements
        $('#itemList .sitemapitem-priority').click(
            function(event) {
                $this = $(this);

                if (event.toElement.tagName != 'SELECT') {
                    $input = $('<select>');
                    $opt01 = $('<option>').attr('value', '0.1').text('0.1').appendTo($input);
                    $opt02 = $('<option>').attr('value', '0.2').text('0.2').appendTo($input);
                    $opt03 = $('<option>').attr('value', '0.3').text('0.3').appendTo($input);
                    $opt04 = $('<option>').attr('value', '0.4').text('0.4').appendTo($input);
                    $opt05 = $('<option>').attr('value', '0.5').text('0.5').appendTo($input);
                    $opt06 = $('<option>').attr('value', '0.6').text('0.6').appendTo($input);
                    $opt07 = $('<option>').attr('value', '0.7').text('0.7').appendTo($input);
                    $opt08 = $('<option>').attr('value', '0.8').text('0.8').appendTo($input);
                    $opt09 = $('<option>').attr('value', '0.9').text('0.9').appendTo($input);
                    $opt10 = $('<option>').attr('value', '1.0').text('1.0').appendTo($input);

                    $input.val($this.data('value'));

                    $this.html('');
                    $this.append($input);

                    $input.change(
                        function(event) {
                            $(this).parent().data('value', $(this).val());
                            $(this).parents('.sitemapitem').addClass('updated');

                            $(this).parent().html($(this).parent().data('value'));
                        }
                    );
                }
            }
        );

        // Add the event for the changefreq elements
        $('#itemList .sitemapitem-changefreq').click(
            function(event) {
                $this = $(this);

                if (event.toElement.tagName != 'SELECT') {
                    $input = $('<select>');
                    $opt01 = $('<option>').attr('value', 'hourly').text('<?php echo JText::_('COM_OSMAP_HOURLY'); ?>').appendTo($input);
                    $opt02 = $('<option>').attr('value', 'daily').text('<?php echo JText::_('COM_OSMAP_DAILY'); ?>').appendTo($input);
                    $opt03 = $('<option>').attr('value', 'weekly').text('<?php echo JText::_('COM_OSMAP_WEEKLY'); ?>').appendTo($input);
                    $opt04 = $('<option>').attr('value', 'monthly').text('<?php echo JText::_('COM_OSMAP_MONTHLY'); ?>').appendTo($input);
                    $opt05 = $('<option>').attr('value', 'yearly').text('<?php echo JText::_('COM_OSMAP_YEARLY'); ?>').appendTo($input);
                    $opt06 = $('<option>').attr('value', 'never').text('<?php echo JText::_('COM_OSMAP_NEVER'); ?>').appendTo($input);

                    $input.val($this.data('value'));

                    $this.html('');
                    $this.append($input);

                    $input.change(
                        function(event) {
                            console.log(event);
                            $(this).parent().data('value', $(this).val());
                            $(this).parents('.sitemapitem').addClass('updated');
                            $(this).parent().html($(this).find('option:selected').text());
                        }
                    );
                }
            }
        );
    });
})(jQuery);
</script>
