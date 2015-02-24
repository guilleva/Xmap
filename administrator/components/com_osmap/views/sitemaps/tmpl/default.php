<?php
/**
 * @version     $Id$
 * @copyright   Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Guillermo Vargas (guille@vargas.co.cr)
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('bootstrap.tooltip');
if(version_compare(JVERSION,'3.0.0','ge')) {
    JHtml::_('formbehavior.chosen', 'select');
}

$n = count($this->items);

$baseUrl = JUri::root();

$version = new JVersion;

?>
<form action="<?php echo JRoute::_('index.php?option=com_xmap&view=sitemaps');?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)): ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
<?php else : ?>
    <div id="j-main-container">
<?php endif;?>
        <div id="filter-bar" class="btn-toolbar">
            <div class="filter-search btn-group pull-left">
                <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->state->get('filter.search'); ?>" size="60" title="<?php echo JText::_('Xmap_Filter_Search_Desc'); ?>" />
            </div>

            <div class="btn-group pull-left hidden-phone">
                <button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                <button class="btn tip hasTooltip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
            </div>
        </div>

        <table class="adminlist table table-striped">
            <thead>
                <tr>
                    <th width="20">
                        <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="if (typeof Joomla != 'undefined'){Joomla.checkAll(this)} else {checkAll(this)}" />
                    </th>
                    <th class="title">
                        <?php echo JHtml::_('grid.sort', 'Xmap_Heading_Sitemap', 'a.title', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
                    </th>
                    <th width="5%">
                        <?php echo JHtml::_('grid.sort', 'Xmap_Heading_Published', 'a.state', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
                    </th>
                    <th width="10%">
                        <?php echo JHtml::_('grid.sort',  'Xmap_Heading_Access', 'access_level', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
                    </th>
                    <th width="10%" class="nowrap">
                        <?php echo JText::_('Xmap_Heading_Html_Stats'); ?><br />
                        (<?php echo JText::_('Xmap_Heading_Num_Links') . ' / '. JText::_('Xmap_Heading_Num_Hits') . ' / ' . JText::_('Xmap_Heading_Last_Visit'); ?>)
                    </th>
                    <th width="10%" class="nowrap">
                        <?php echo JText::_('Xmap_Heading_Xml_Stats'); ?><br />
                        <?php echo JText::_('Xmap_Heading_Num_Links') . '/'. JText::_('Xmap_Heading_Num_Hits') . '/' . JText::_('Xmap_Heading_Last_Visit'); ?>
                    </th>
                    <th width="1%" class="nowrap">
                        <?php echo JHtml::_('grid.sort', 'Xmap_Heading_ID', 'a.id', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="15">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
            <?php foreach ($this->items as $i => $item) :

                $now = JFactory::getDate()->toUnix();
                if ( !$item->lastvisit_html ) {
                    $htmlDate = JText::_('Date_Never');
                }elseif ( $item->lastvisit_html > ($now-3600)) { // Less than one hour
                    $htmlDate = JText::sprintf('Date_Minutes_Ago',intval(($now-$item->lastvisit_html)/60));
                } elseif ( $item->lastvisit_html > ($now-86400)) { // Less than one day
                    $hours = intval (($now-$item->lastvisit_html)/3600 );
                    $htmlDate = JText::sprintf('Date_Hours_Minutes_Ago',$hours,($now-($hours*3600)-$item->lastvisit_html)/60);
                } elseif ( $item->lastvisit_html > ($now-259200)) { // Less than three days
                    $days = intval(($now-$item->lastvisit_html)/86400);
                    $htmlDate = JText::sprintf('Date_Days_Hours_Ago',$days,intval(($now-($days*86400)-$item->lastvisit_html)/3600));
                } else {
                    $date = new JDate($item->lastvisit_html);
                    $htmlDate = $date->format('Y-m-d H:i');
                }

                if ( !$item->lastvisit_xml ) {
                    $xmlDate = JText::_('Date_Never');
                } elseif ( $item->lastvisit_xml > ($now-3600)) { // Less than one hour
                    $xmlDate = JText::sprintf('Date_Minutes_Ago',intval(($now-$item->lastvisit_xml)/60));
                } elseif ( $item->lastvisit_xml > ($now-86400)) { // Less than one day
                    $hours = intval (($now-$item->lastvisit_xml)/3600 );
                    $xmlDate = JText::sprintf('Date_Hours_Minutes_Ago',$hours,($now-($hours*3600)-$item->lastvisit_xml)/60);
                } elseif ( $item->lastvisit_xml > ($now-259200)) { // Less than three days
                    $days = intval(($now-$item->lastvisit_xml)/86400);
                    $xmlDate = JText::sprintf('Date_Days_Hours_Ago',$days,intval(($now-($days*86400)-$item->lastvisit_xml)/3600));
                } else {
                    $date = new JDate($item->lastvisit_xml);
                    $xmlDate = $date->format('Y-m-d H:i');
                }

            ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td class="center">
                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                    </td>
                    <td>
                        <a href="<?php echo JRoute::_('index.php?option=com_xmap&task=sitemap.edit&id='.$item->id);?>">
                            <?php echo $this->escape($item->title); ?></a>
                            <?php if ($item->is_default == 1) : ?>
                                <?php if (version_compare($version->getShortVersion(), '3.0.0', '>=')): ?>
                                    <span class="icon-featured"></span>
                                <?php else: ?>
                                    <img src="templates/bluestork/images/menu/icon-16-default.png" alt="<?php echo JText::_('Default'); ?>" />
                                <?php endif; ?>
                            <?php endif; ?>
                                <?php if ($item->state): ?>
                                    <small>[<a href="<?php echo $baseUrl. 'index.php?option=com_xmap&amp;view=xml&tmpl=component&id='.$item->id; ?>" target="_blank" title="<?php echo JText::_('XMAP_XML_LINK_TOOLTIP',true); ?>"><?php echo JText::_('XMAP_XML_LINK'); ?></a>]</small>
                                    <small>[<a href="<?php echo $baseUrl. 'index.php?option=com_xmap&amp;view=xml&tmpl=component&news=1&id='.$item->id; ?>" target="_blank" title="<?php echo JText::_('XMAP_NEWS_LINK_TOOLTIP',true); ?>"><?php echo JText::_('XMAP_NEWS_LINK'); ?></a>]</small>
                                    <small>[<a href="<?php echo $baseUrl. 'index.php?option=com_xmap&amp;view=xml&tmpl=component&images=1&id='.$item->id; ?>" target="_blank" title="<?php echo JText::_('XMAP_IMAGES_LINK_TOOLTIP',true); ?>"><?php echo JText::_('XMAP_IMAGES_LINK'); ?></a>]</small>
                                <?php endif; ?>
                                     <br />
									 <small>(<?php echo $this->escape($item->alias); ?>)</small>
                    </td>
                    <td class="center">
                        <?php echo JHtml::_('jgrid.published', $item->state, $i, 'sitemaps.'); ?>
                    </td>
                    <td class="center">
                        <?php echo $this->escape($item->access_level); ?>
                    </td>
                    <td class="center">
                        <?php echo $item->count_html .' / '.$item->views_html. ' / ' . $htmlDate; ?>
                    </td>
                    <td class="center">
                        <?php echo $item->count_xml .' / '.$item->views_xml. ' / ' . $xmlDate; ?>
                    </td>
                    <td class="center">
                        <?php echo (int) $item->id; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
