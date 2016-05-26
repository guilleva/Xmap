<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JHtml::script(Juri::root() . 'media/com_osmap/js/ractive.min.js');
?>
<div class="row-fluid">
    <div class="span12">
        <div id="osmap-items-container"></div>
    </div>
</div>

<script id="items-template" type="text/ractive">
    {{^items}}
        <div class="alert alert-warning">
            <?php echo JText::_('COM_OSMAP_NO_ITEMS'); ?>
        </div>
    {{/items}}

    {{#if items}}
        <table class="adminlist table table-striped" id="itemList">
            <thead>
                <tr>
                    <th width="1%">
                        <?php echo JHtml::_('grid.checkall'); ?>
                    </th>

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
                {{#items}}
                    <tr class="row{{index}}">
                        <td class="center">
                        <!-- Checkbox -->
                        </td>

                        <td class="center">
                            <div class="btn-group">
                            <!-- Status -->
                            </div>
                        </td>

                        <td>
                            {{this.url}}
                        </td>

                        <td>
                           {{this.title}}
                        </td>

                        <td class="center">
                            {{this.priority}}
                        </td>

                        <td class="center">
                            {{this.changefreq}}
                        </td>
                    </tr>
                {{/items}}
            </tbody>
        </table>
    {{/if}}
</script>

<script>
(function(Ractive, $) {
    Ractive.DEBUG = false;

    var ractive = new Ractive({
        el: '#osmap-items-container',
        template: '#items-template',
        data: {
            'items'     : [],
            'isLoading' : false,
            'foundError': false,
            'index'     : 0
        },
        load: function() {
            self = this;
        }
    });

    ractive.on('load', function(e) {
        this.load();
    });

    window.ractive = ractive;
})(Ractive, jQuery);
</script>
