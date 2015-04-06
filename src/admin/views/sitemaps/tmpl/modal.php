<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2015 Open Source Training, LLC. All rights reserved..
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

$function = JRequest::getVar('function', 'jSelectSitemap');
$n = count($this->items);
?>
<form action="<?php echo JRoute::_('index.php?option=com_osmap&view=sitemaps');?>" method="post" name="adminForm">
    <fieldset class="filter clearfix">
        <div class="left">
            <label for="search">
                <?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>
            </label>
            <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->state->get('filter.search'); ?>" size="60" title="<?php echo JText::_('OSMAP_FILTER_SEARCH_DESC'); ?>" />

            <button type="submit">
                <?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button type="button" onclick="$('filter_search').value='';this.form.submit();">
                <?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
        </div>

        <div class="right">
            <select name="filter_access" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
                <?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
            </select>

            <select name="filter_published" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
            </select>

        </div>
    </fieldset>

    <table class="adminlist">
        <thead>
            <tr>
                <th class="title">
                    <?php echo JHtml::_('grid.sort', 'OSMAP_HEADING_TITLE', 'a.title', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
                </th>
                <th width="5%">
                    <?php echo JHtml::_('grid.sort', 'OSMAP_HEADING_PUBLISHED', 'a.state', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
                </th>
                <th width="10%">
                    <?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ACCESS', 'access_level', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
                </th>
                <th width="1%" nowrap="nowrap">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="4">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
        <tbody>
        <?php
        foreach ($this->items as $i => $item) :

            ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td>
                    <a style="cursor: pointer;" onclick="if (window.parent) window.parent.<?php echo $function;?>('<?php echo $item->id; ?>', '<?php echo $this->escape($item->title); ?>');">
                        <?php echo $this->escape($item->title); ?></a>
                </td>
                <td align="center">
                    <?php echo JHtml::_('jgrid.published', $item->state, $i, 'sitemaps.'); ?>
                </td>
                <td>
                    <?php echo $this->escape($item->access_level); ?>
                </td>
                <td align="center">
                    <?php echo (int) $item->id; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <input type="hidden" name="tmpl" value="component" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>
