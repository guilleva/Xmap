<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JHtml::_('behavior.core');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

JHtml::stylesheet('media/com_osmap/css/admin.css');
JHtml::stylesheet('media/jui/css/icomoon.css');
?>
<form
    action="<?php echo JRoute::_('index.php?option=com_osmap&view=sitemapitems&id=' . (int)$this->sitemap->id); ?>"
    method="post"
    name="adminForm"
    id="adminForm"
    class="form-validate">

    <div class="row-fluid">
        <div class="span12">
            <div id="osmap-items-container">
                <?php if (empty($this->sitemapItems)) : ?>
                    <div class="alert alert-warning">
                        <?php echo JText::_('COM_OSMAP_NO_ITEMS'); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($this->sitemapItems)) : ?>
                    <table class="adminlist table table-striped" id="itemList">
                        <thead>
                            <tr>
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
                            <?php $i = 0; ?>
                            <?php foreach ($this->sitemapItems as $item) : ?>
                                <tr class="row<?php echo $i; ?>">
                                    <td class="center">
                                        <div class="btn-group">
                                            <?php if ($item->published) : ?>
                                                <span class="icon-publish"></span>
                                            <?php else : ?>
                                                <span class="icon-unpublish"></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <a
                                            href="<?php echo $item->fullLink; ?>"
                                            target="_blank"
                                            title="<?php echo $item->link; ?>">

                                            <span class="icon-new-tab"></span>
                                            <?php echo $item->fullLink; ?>
                                        </a>
                                        <div class="small silver">
                                            UID: <?php echo $item->uid; ?>
                                        </div>
                                    </td>
                                    <td><?php echo isset($item->name) ? $item->name : ''; ?></td>
                                    <td class="center"><?php echo $item->priority; ?></td>
                                    <td class="center"><?php echo $item->changefreq; ?></td>
                                </tr>
                                <?php $i++; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <input type="hidden" id="menus_ordering" name="jform[menus_ordering]" value=""/>
    <input type="hidden" name="task" value=""/>
    <?php echo JHtml::_('form.token'); ?>
</form>

