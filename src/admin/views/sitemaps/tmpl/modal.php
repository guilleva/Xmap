<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.tooltip');
JHtml::_('formbehavior.chosen', 'select');

JHtml::stylesheet('media/com_osmap/css/admin.min.css');

$function  = JRequest::getString('function', 'jSelectSitemap');
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
                        <div class="btn-group osmap-modal-status">
                            <?php if ($item->published) : ?>
                                <i class="icon-save"></i>
                            <?php else : ?>
                                <i class="icon-remove"></i>
                            <?php endif; ?>
                            &nbsp;

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
    <input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>
