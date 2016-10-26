<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved..
 * @author    Guillermo Vargas <guille@vargas.co.cr>
 * @author    Joomlashack <help@joomlashack.com>
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file is part of OSMap.
 *
 * OSMap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * OSMap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSMap. If not, see <http://www.gnu.org/licenses/>.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$app = JFactory::getApplication();
$link = '<a href="index.php?option=com_plugins&view=plugins&filter.search=OSMap">' . JText::_('COM_OSMAP_INSTALLER_PLUGINS_PAGE') . '</a>';
$app->enqueueMessage(JText::sprintf('COM_OSMAP_INSTALLER_GOTOPLUGINS', $link), 'warning');
?>

<?php if ($this->isXmapDataFound) : ?>

    <div class="alledia-xmap-import">
        <div id="alledia-installer-xmap-import-message">
            <div id="alledia-installer-xmap-import-wipe-warning" class="alert alert-warning">
                <h4 class="alert-heading"><?php echo JText::_('COM_OSMAP_INSTALLER_IMPORT_XMAP_TITLE'); ?></h4>
                <p><?php echo JText::_('COM_OSMAP_INSTALLER_IMPORT_XMAP_DESCRIPTION'); ?></p>
                <p>
                    <strong><?php echo JText::_('COM_OSMAP_INSTALLER_IMPORT_XMAP_WIPE_WARNING'); ?></strong>
                </p>

                <a href="javascript:void(0);" id="alledia-installer-xmap-import-button" class="alledia-button">
                    <?php echo JText::_('COM_OSMAP_INSTALLER_IMPORT_XMAP_BUTTON'); ?>
                </a>
            </div>
        </div>

        <div id="alledia-installer-xmap-import-success" class="alert alert-success" style="display: none">
            <p>
                <?php echo JText::_('COM_OSMAP_INSTALLER_IMPORT_XMAP_SUCCESS'); ?>
            </p>
        </div>

        <div id="alledia-installer-xmap-import-error" class="alert alert-error" style="display: none">
            <p>
                <?php echo JText::_('COM_OSMAP_INSTALLER_IMPORT_XMAP_ERROR'); ?>
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
            }

            var showSuccess = function() {
                $('#alledia-installer-xmap-import-success').show();
                title.hide();
            }

            button.on('click', function() {
                var goAhead = confirm('<?php echo JText::_("COM_OSMAP_INSTALLER_WIPE_CONFIRMATION"); ?>');

                if (goAhead) {
                    button.text('<?php echo JText::_("COM_OSMAP_INSTALLER_IMPORTING"); ?>').off('click', this).css('cursor', 'default');

                    $.post('<?php echo JURI::root(); ?>/administrator/index.php?option=com_osmap&task=sitemaps.migrateXMapData&format=json',
                        {},
                        function(data) {
                            message.hide();

                            try
                            {
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

    })(jQueryAlledia);
    </script>

<?php endif; ?>
