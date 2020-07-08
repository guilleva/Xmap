<?php
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

// No direct access
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die();

if ($this->isXmapDataFound) :
    $updateLink = Route::_('index.php?option=com_osmap&task=sitemaps.migrateXMapData&format=json');
    ?>
    <div class="alledia-xmap-import">
        <div id="alledia-installer-xmap-import-message">
            <div id="alledia-installer-xmap-import-wipe-warning" class="alert alert-warning">
                <h4 class="alert-heading"><?php echo Text::_('COM_OSMAP_INSTALLER_IMPORT_XMAP_TITLE'); ?></h4>
                <p><?php echo Text::_('COM_OSMAP_INSTALLER_IMPORT_XMAP_DESCRIPTION'); ?></p>
                <p>
                    <strong><?php echo Text::_('COM_OSMAP_INSTALLER_IMPORT_XMAP_WIPE_WARNING'); ?></strong>
                </p>

                <a href="javascript:void(0);" id="alledia-installer-xmap-import-button" class="alledia-button">
                    <?php echo Text::_('COM_OSMAP_INSTALLER_IMPORT_XMAP_BUTTON'); ?>
                </a>
            </div>
        </div>

        <div id="alledia-installer-xmap-import-success" class="alert alert-success" style="display: none">
            <p>
                <?php echo Text::_('COM_OSMAP_INSTALLER_IMPORT_XMAP_SUCCESS'); ?>
            </p>
        </div>

        <div id="alledia-installer-xmap-import-error" class="alert alert-error" style="display: none">
            <p>
                <?php echo Text::_('COM_OSMAP_INSTALLER_IMPORT_XMAP_ERROR'); ?>
            </p>
        </div>
    </div>

    <script>
        (function($) {
            $(function() {
                var button  = $('#alledia-installer-xmap-import-button'),
                    message = $('#alledia-installer-xmap-import-message'),
                    title   = $('.alledia-xmap-import > h4.warning');

                var showError = function() {
                    $('#alledia-installer-xmap-import-error').show();
                    title.hide();
                };

                var showSuccess = function() {
                    $('#alledia-installer-xmap-import-success').show();
                    title.hide();
                };

                button.on('click', function() {
                    var goAhead = confirm('<?php echo Text::_("COM_OSMAP_INSTALLER_WIPE_CONFIRMATION"); ?>');

                    if (goAhead) {
                        button.text('<?php echo Text::_("COM_OSMAP_INSTALLER_IMPORTING"); ?>')
                            .off('click', this)
                            .css('cursor', 'default');

                            {},
                        $.post('<?php echo $updateLink; ?>',
                            function(data) {
                                message.hide();

                                try {
                                    var result = JSON.parse(data);

                                    if (result.success) {
                                        showSuccess();
                                    } else {
                                        showError();
                                    }
                                } catch (e) {
                                    showError();
                                }
                            },
                            'text'
                        ).fail(function() {
                            message.hide();
                            showError();
                        });
                    }
                });
            });

        })(jQuery);
    </script>
    <?php
endif;
