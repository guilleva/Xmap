/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

;(function($, Joomla, document, JSON) {
    var configureForm = function(lang) {
        /**
         * Add field to select priority of an item.
         */
        function createPriorityField($tr) {
            $div = $tr.find('.sitemapitem-priority');

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

            $input.val($div.data('value'));

            $div.html('');
            $div.append($input);

            $input.on('change',
                function() {
                    $this = $(this);

                    $this.parent().data('value', $this.val());
                    $this.parents('tr').addClass('updated');
                }
            );
        };

        /**
         * Remove the field for priority and add it's value as text of the
         * parent element
         */
        function removePriorityField($tr) {
            $div = $tr.find('.sitemapitem-priority');

            $div.text($div.data('value'));
        }

        // Add the event for the changefreq elements
        function createChangeFreqField($tr) {
            $div = $tr.find('.sitemapitem-changefreq');

            $input = $('<select>');
            $opt01 = $('<option>').attr('value', 'hourly').text(lang.COM_OSMAP_HOURLY).appendTo($input);
            $opt02 = $('<option>').attr('value', 'daily').text(lang.COM_OSMAP_DAILY).appendTo($input);
            $opt03 = $('<option>').attr('value', 'weekly').text(lang.COM_OSMAP_WEEKLY).appendTo($input);
            $opt04 = $('<option>').attr('value', 'monthly').text(lang.COM_OSMAP_MONTHLY).appendTo($input);
            $opt05 = $('<option>').attr('value', 'yearly').text(lang.COM_OSMAP_YEARLY).appendTo($input);
            $opt06 = $('<option>').attr('value', 'never').text(lang.COM_OSMAP_NEVER).appendTo($input);

            $input.val($div.data('value'));

            $div.html('');
            $div.append($input);

            $input.change(
                function(event) {
                    $this = $(this);

                    $this.parent().data('value', $this.val());
                    $this.parents('tr').addClass('updated');
                }
            );
        };

        function removeChangeFreqField($tr) {
            $div = $tr.find('.sitemapitem-changefreq');

            $div.text($div.find('option:selected').text());
        };

        // Adds the event for a hovered line
        $('#itemList .sitemapitem').hover(
            function(event) {
                if (event.target.tagName === 'TD') {
                    $tr = $(event.currentTarget);
                    $currentSelection = $('#itemList .selected');

                    if ($tr != $currentSelection) {
                        // Remove the selected class from the last item
                        $currentSelection.removeClass('selected');
                        removePriorityField($currentSelection);
                        removeChangeFreqField($currentSelection);

                        // Add the selected class to highlight the row and fields
                        $tr.addClass('selected');

                        createPriorityField($tr);
                        createChangeFreqField($tr);
                    }
                }
            }
        );

        // Add the event for the publish status elements
        $('#itemList .sitemapitem-published').click(
            function(event) {
                var $this = $(this),
                    newValue  = $this.data('value') == 1 ? 0 : 1,
                    spanClass = newValue == 1 ? 'publish' : 'unpublish',
                    $span = $this.find('span');

                $this.data('value', newValue);

                $this.parents('.sitemapitem').addClass('updated');

                $span.attr('class', '');
                $span.addClass('icon-' + spanClass);

                // Tooltip
                $span.attr(
                    'title',
                    newValue == 1 ? lang.COM_OSMAP_TOOLTIP_CLICK_TO_UNPUBLISH : lang.COM_OSMAP_TOOLTIP_CLICK_TO_PUBLISH
                );

                $span.tooltip('destroy');
                $span.tooltip();
            }
        );

        Joomla.submitbutton = function (task) {
            if (task === 'sitemapitems.save' || task === 'sitemapitems.apply') {
                var $updateDataField = $('#update-data'),
                    $updatedLines = $('.sitemapitem.updated'),
                    data = [];

                $updateDataField.val('');

                // Grab updated values and build the post data
                $updatedLines.each(function() {
                    $tr = $(this);

                    data.push({
                        'uid': $tr.data('uid'),
                        'settings_hash': $tr.data('settings-hash'),
                        'published': $tr.find('.sitemapitem-published').data('value'),
                        'priority': $tr.find('.sitemapitem-priority').data('value'),
                        'changefreq': $tr.find('.sitemapitem-changefreq').data('value')
                    });
                });

                $updateDataField.val(JSON.stringify(data));
            }

            Joomla.submitform(task, document.getElementById('adminForm'));
        };

        // Removes the loading element
        setTimeout(function() {
            $('.osmap-loading').remove();
        }, 1000);
    };

    $.fn.osmap = {
        loadSitemapItems: function(params) {
            var url = params.baseUri.replace(/\/$/, '');

            url += '/index.php?option=com_osmap&view=adminsitemapitems&tmpl=component&id=' + params.sitemapId;

            if (params.language !== '') {
                url += '&lang=' + params.language;
            }

            $.ajax({
                url: url,
                async: true,
                success: function(data) {
                    $(params.container).html(data);

                    configureForm(params.lang);

                    $('.hasTooltip').tooltip();
                }
            });
        }
    };
})(jQuery, Joomla, document, JSON);
