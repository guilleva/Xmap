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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die();

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('formbehavior.chosen', 'select');

HTMLHelper::_('stylesheet', 'com_osmap/admin.min.css', array('relative' => true));

$function  = Factory::getApplication()->input->getString('function', 'jSelectSitemap');
$baseUrl   = Uri::root();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDir   = $this->escape($this->state->get('list.direction'));
?>
<form
    action="<?php echo Route::_('index.php?option=com_osmap&view=sitemaps');?>"
    method="post"
    name="adminForm"
    id="adminForm">

<?php if (empty($this->items)) : ?>
    <div class="alert alert-no-items">
        <?php echo Text::_('COM_OSMAP_NO_MATCHING_RESULTS'); ?>
    </div>
<?php else : ?>
    <div id="j-main-container">
        <table class="adminlist table table-striped" id="sitemapList">
            <thead>
                <tr>
                    <th class="title">
                        <?php
                        echo HTMLHelper::_(
                            'grid.sort',
                            'COM_OSMAP_HEADING_NAME',
                            'sitemap.name',
                            $listDir,
                            $listOrder
                        ); ?>
                    </th>

                    <th width="1%" style="min-width:55px" class="nowrap center">
                        <?php
                        echo HTMLHelper::_(
                            'grid.sort',
                            'COM_OSMAP_HEADING_STATUS',
                            'sitemap.published',
                            $listDir,
                            $listOrder
                        );
                        ?>
                    </th>

                    <th width="8%" class="nowrap center">
                        <?php echo Text::_('COM_OSMAP_HEADING_NUM_LINKS'); ?>
                    </th>

                    <th width="1%" class="nowrap">
                        <?php
                        echo HTMLHelper::_(
                            'grid.sort',
                            'COM_OSMAP_HEADING_ID',
                            'sitemap.id',
                            $listDir,
                            $listOrder
                        ); ?>
                    </th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($this->items as $i => $item) :
                $onClick = sprintf(
                    "if (window.parent) window.parent.%s('%s', '%s');",
                    $function,
                    $item->id,
                    $this->escape($item->name)
                );
                ?>
                <tr class="<?php echo 'row' . ($i % 2); ?>">
                    <td>
                         <a
                            style="cursor: pointer;"
                            onclick="<?php echo $onClick; ?>">

                            <?php echo $this->escape($item->name); ?>
                        </a>
                    </td>

                    <td class="center">
                        <div class="btn-group osmap-modal-status">
                            <?php if ($item->published) : ?>
                                <i class="icon-save"></i>
                            <?php else : ?>
                                <i class="icon-remove"></i>
                            <?php endif; ?>

                            <?php if ($item->is_default) : ?>
                                <i class="icon-star"></i>
                            <?php endif; ?>
                        </div>
                    </td>

                    <td class="center">
                        <?php echo (int) $item->links_count; ?>
                    </td>

                    <td class="center">
                        <?php echo (int) $item->id; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

    <input type="hidden" name="tmpl" value="component" />
    <input type="hidden" name="layout" value="modal" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
