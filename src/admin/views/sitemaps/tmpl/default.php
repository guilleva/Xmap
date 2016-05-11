<?php
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

$baseUrl = JUri::root();
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
    <?php if (!empty($this->sidebar)) : ?>
        <div
            id="j-sidebar-container"
            class="span2">

            <?php echo $this->sidebar; ?>
        </div>

        <div
            id="j-main-container"
            class="span10">
    <?php else : ?>
        <div id="j-main-container">
    <?php endif;?>

            <div
                id="filter-bar"
                class="btn-toolbar">

                <div class="filter-search btn-group pull-left">
                    <input
                        type="text"
                        name="filter_search"
                        id="filter_search"
                        value="<?php echo $this->state->get('filter.search'); ?>"
                        size="60"
                        title="<?php echo JText::_('COM_OSMAP_FILTER_SEARCH_DESC'); ?>" />
                </div>

                <div class="btn-group pull-left hidden-phone">
                    <button
                        class="btn tip hasTooltip"
                        type="submit"
                        title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">

                        <i class="icon-search"></i>
                    </button>
                    <button
                        class="btn tip hasTooltip"
                        type="button"
                        onclick="document.id('filter_search').value='';this.form.submit();"
                        title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>">

                        <i class="icon-remove"></i>
                    </button>
                </div>
            </div>

            <table class="adminlist table table-striped">
                <thead>
                    <tr>
                        <th width="20">
                            <input
                                type="checkbox"
                                name="checkall-toggle"
                                value=""
                                title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
                                onclick="if (typeof Joomla != 'undefined'){Joomla.checkAll(this)} else {checkAll(this)}" />
                        </th>
                        <th class="title">
                            <?php echo JHtml::_(
                                'grid.sort',
                                'COM_OSMAP_HEADING_TITLE',
                                'a.title',
                                $this->state->get('list.direction'),
                                $this->state->get('list.ordering')
                            ); ?>
                        </th>
                        <th width="5%" class="center">
                            <?php echo JHtml::_(
                                'grid.sort',
                                'COM_OSMAP_HEADING_DEFAULT',
                                'a.is_default',
                                $this->state->get('list.direction'),
                                $this->state->get('list.ordering')
                            ); ?>
                        </th>
                        <th width="5%" class="center">
                            <?php echo JHtml::_(
                                'grid.sort',
                                'COM_OSMAP_HEADING_PUBLISHED',
                                'a.state',
                                $this->state->get('list.direction'),
                                $this->state->get('list.ordering')
                            ); ?>
                        </th>
                        <th width="200" class="center">
                            <?php echo JText::_('COM_OSMAP_HEADING_SITEMAP_LINKS'); ?>
                        </th>
                        <th width="8%" class="nowrap center">
                            <?php echo JText::_('COM_OSMAP_HEADING_NUM_LINKS'); ?>
                        </th>
                        <th width="1%" class="nowrap">
                            <?php echo JHtml::_(
                                'grid.sort',
                                'COM_OSMAP_HEADING_ID',
                                'a.id',
                                $this->state->get('list.direction'),
                                $this->state->get('list.ordering')
                            ); ?>
                        </th>
                    </tr>
                </thead>

                <tfoot>
                    <tr>
                        <td colspan="9">
                            <?php echo $this->pagination->getListFooter(); ?>
                        </td>
                    </tr>
                </tfoot>

                <tbody>
                <?php foreach ($this->items as $i => $item) : ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="center">
                            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td>
                            <a href="<?php echo JRoute::_('index.php?option=com_osmap&task=sitemap.edit&id='.$item->id);?>">
                                <?php echo $this->escape($item->title); ?>
                            </a>
                        </td>
                        <td class="center">
                            <?php if ($item->is_default == 1) : ?>
                                <span class="icon-featured"></span>
                            <?php endif; ?>
                        </td>
                        <td class="center">
                            <?php echo JHtml::_('jgrid.published', $item->state, $i, 'sitemaps.'); ?>
                        </td>
                        <td class="center">
                            <a
                                href="<?php echo $baseUrl. 'index.php?option=com_osmap&amp;view=xml&tmpl=component&id='.$item->id; ?>"
                                target="_blank"
                                title="<?php echo JText::_('COM_OSMAP_XML_LINK_TOOLTIP', true); ?>">

                                <?php echo JText::_('COM_OSMAP_XML_LINK'); ?>
                                <span class="icon-out-2"></span>
                            </a>
                            &nbsp;&nbsp;
                            <a
                                href="<?php echo $baseUrl. 'index.php?option=com_osmap&amp;view=html&id='.$item->id; ?>"
                                target="_blank"
                                title="<?php echo JText::_('COM_OSMAP_HTML_LINK_TOOLTIP', true); ?>">

                                <?php echo JText::_('COM_OSMAP_HTML_LINK'); ?>
                                <span class="icon-out-2"></span>
                            </a>
                            &nbsp;&nbsp;
                            <a
                                href="<?php echo $baseUrl. 'index.php?option=com_osmap&amp;view=xml&tmpl=component&images=1&id=' . $item->id; ?>"
                                target="_blank"
                                title="<?php echo JText::_('COM_OSMAP_IMAGES_LINK_TOOLTIP', true); ?>">

                                <?php echo JText::_('COM_OSMAP_IMAGES_LINK'); ?>
                                <span class="icon-out-2"></span>
                            </a>
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
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>
