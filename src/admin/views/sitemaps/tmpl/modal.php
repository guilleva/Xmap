<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved..
 * @author    Guillermo Vargas <guille@vargas.co.cr>
 * @author    Alledia <support@alledia.com>
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

// no direct access
defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('behavior.tooltip');

$function = JRequest::getString('function', 'jSelectSitemap');
?>
<form
    action="<?php echo JRoute::_('index.php?option=com_osmap&view=sitemaps');?>"
    method="post"
    name="adminForm"><?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JHtml::addIncludePath(OSMAP_ADMIN . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');

$baseUrl   = JUri::root();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDir   = $this->escape($this->state->get('list.direction'));
?>
<form
    action="<?php echo JRoute::_('index.php?option=com_osmap&view=sitemaps');?>"
    method="post"
    name="adminForm"
    id="adminForm">

<?php if (empty($this->items)) : ?>
    <div class="alert alert-no-items">
        <?php echo JText::_('COM_OSMAP_NO_MATCHING_RESULTS'); ?>
    </div>
<?php else : ?>
    <div id="j-main-container">
        <table class="adminlist table table-striped" id="sitemapList">
            <thead>
                <tr>
                    <th class="title">
                        <?php
                        echo JHtml::_(
                            'grid.sort',
                            'COM_OSMAP_HEADING_NAME',
                            'sitemap.name',
                            $listDir,
                            $listOrder
                        ); ?>
                    </th>

                    <th width="1%" style="min-width:55px" class="nowrap center">
                        <?php
                        echo JHtml::_(
                            'grid.sort',
                            'COM_OSMAP_HEADING_STATUS',
                            'sitemap.published',
                            $listDir,
                            $listOrder
                        );
                        ?>
                    </th>

                    <th width="8%" class="nowrap center">
                        <?php echo JText::_('COM_OSMAP_HEADING_NUM_LINKS'); ?>
                    </th>

                    <th width="1%" class="nowrap">
                        <?php
                        echo JHtml::_(
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
            <?php foreach ($this->items as $i => $item) : ?>
                <tr class="<?php echo 'row' . ($i % 2); ?>">
                    <td>
                         <a
                            style="cursor: pointer;"
                            onclick="if (window.parent) window.parent.<?php echo $function;?>('<?php echo $item->id; ?>', '<?php echo $this->escape($item->name); ?>');">

                            <?php echo $this->escape($item->name); ?>
                        </a>
                    </td>

                    <td class="center">
                        <div class="btn-group" style="font-size: 13px;">
                            <?php if ($item->published) : ?>
                                <i class="icon-save"></i>
                            <?php else : ?>
                                <i class="icon-remove"></i>
                            <?php endif; ?>
                            &nbsp;

                            <?php if ($item->is_default) : ?>
                                <i class="icon-star" style="color: #feb123;"></i>
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
    <input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>
</form>
