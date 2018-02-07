<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap;

defined('_JEXEC') or die();

JHtml::addIncludePath(OSMAP_ADMIN_PATH . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');

JHtml::_('stylesheet', 'com_osmap/admin.min.css', array('relative' => true));

$container = OSMap\Factory::getContainer();

$baseUrl   = $container->router->sanitizeURL(JUri::root());
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDir   = $this->escape($this->state->get('list.direction'));
?>
<form
    action="<?php echo JRoute::_('index.php?option=com_osmap&view=sitemaps'); ?>"
    method="post"
    name="adminForm"
    id="adminForm">

    <?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

    <?php if (empty($this->items)) : ?>
        <div class="alert alert-no-items">
            <?php echo JText::_('COM_OSMAP_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php else : ?>
        <div id="j-main-container">
            <table class="adminlist table table-striped" id="sitemapList">
                <thead>
                <tr>
                    <th width="1%">
                        <?php echo JHtml::_('grid.checkall'); ?>
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

                    <?php
                    $editLinksWidth = empty($this->languages) ? '63' : '130';
                    $editLinksClass = empty($this->languages) ? 'center' : '';
                    ?>
                    <th width="8%"
                        style="min-width: <?php echo $editLinksWidth . 'px'; ?>"
                        class="<?php echo $editLinksClass; ?>">
                        <?php echo JText::_('COM_OSMAP_HEADING_SITEMAP_EDIT_LINKS'); ?>
                    </th>

                    <th width="260" class="center">
                        <?php echo JText::_('COM_OSMAP_HEADING_SITEMAP_LINKS'); ?>
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
                <?php
                foreach ($this->items as $i => $this->item) :
                    $editLink = JRoute::_('index.php?option=com_osmap&view=sitemap&layout=edit&id=' . $this->item->id);
                    ?>
                    <tr class="<?php echo 'row' . ($i % 2); ?>">
                        <td class="center">
                            <?php echo JHtml::_('grid.id', $i, $this->item->id); ?>
                        </td>

                        <td class="center">
                            <div class="btn-group">
                                <?php
                                echo JHtml::_(
                                    'jgrid.published',
                                    $this->item->published,
                                    $i,
                                    'sitemaps.'
                                );
                                ?>
                                <a
                                    href="#"
                                    onclick="return listItemTask('cb<?php echo $i; ?>','sitemap.setAsDefault')"
                                    class="btn btn-micro hasTooltip"
                                    title=""
                                    data-original-title="Toggle default status.">
                                    <?php
                                    echo sprintf(
                                        '<span class="icon-%s"></span>',
                                        $this->item->is_default ? 'featured' : 'unfeatured'
                                    );
                                    ?>
                                </a>
                            </div>
                        </td>

                        <td class="nowrap">
                            <?php echo JHtml::_('link', $editLink, $this->escape($this->item->name)); ?>
                        </td>

                        <td class="nowrap <?php echo $editLinksClass; ?>">
                            <?php echo $this->loadTemplate('editlinks'); ?>
                        </td>

                        <td class="nowrap center osmap-links">
                            <?php echo $this->loadTemplate('previews'); ?>
                        </td>

                        <td class="center">
                            <?php echo (int)$this->item->links_count; ?>
                        </td>

                        <td class="center">
                            <?php echo (int)$this->item->id; ?>
                        </td>
                    </tr>
                    <?php
                endforeach;
                ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>"/>
    <?php echo JHtml::_('form.token'); ?>
</form>
