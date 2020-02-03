/**
 * @package   OSMap
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2020 Joomlashack.com. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSMap.
 *
 * OSMap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSMap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSMap.  If not, see <http://www.gnu.org/licenses/>.
 */

;(function($) {
    var configureForm = function(lang) {
        var $frequencyField = $('<select>'),
            $priorityField = $('<select>');

        $.each($.osmap.fields.frequencies, function(value, text) {
            $('<option>').attr('value', value).text(text).appendTo($frequencyField)
        });

        $.each($.osmap.fields.priorities, function(index, value) {
            $('<option>').attr('value', value).text(value).appendTo($priorityField);
        });

        /**
         * Add field to select priority of an item.
         */
        function createPriorityField($tr) {
            $div = $tr.find('.sitemapitem-priority');

            $input = $priorityField.clone();
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
        }

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

            $input = $frequencyField.clone();

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
        }

        function removeChangeFreqField($tr) {
            $div = $tr.find('.sitemapitem-changefreq');

            $div.text($div.find('option:selected').text());
        }

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
})(jQuery);
