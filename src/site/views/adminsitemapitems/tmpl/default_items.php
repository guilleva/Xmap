<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

// Declares global variables to be used into the callback
global $count, $showItemUID, $showExternalLinks;

$count             = 0;
$showItemUID       = $this->osmapParams->get('show_item_uid', 0);
$showExternalLinks = (int)$this->osmapParams->get('show_external_links', 0);

/**
 * This method is called while traversing the sitemap items tree, and is
 * used to append the found item to the sitemapItems attribute, which will
 * be used in the view. Will not add ignored items. Duplicate items will
 * be included.
 *
 * @param object $item
 *
 * @result void
 */
$printNodeCallback = function ($item) {
    global $count, $showItemUID, $showExternalLinks;

    // Check if is external URL and if should be ignored
    if (!$item->isInternal) {
        if ($showExternalLinks === 0) {
            // No external links
            $item->set('ignore', true);
            $item->addAdminNote('COM_OSMAP_ADMIN_NOTE_IGNORED_EXTERNAL');
        } elseif ($showExternalLinks === 2) {
            // Display only in the HTML sitemap
            $item->addAdminNote('COM_OSMAP_ADMIN_NOTE_IGNORED_EXTERNAL_HTML');
        }
    }

    // Add notes about sitemap visibility
    if (!$item->visibleForXML) {
        $item->addAdminNote('COM_OSMAP_ADMIN_NOTE_VISIBLE_HTML_ONLY');
    }

    if (!$item->visibleForHTML) {
        $item->addAdminNote('COM_OSMAP_ADMIN_NOTE_VISIBLE_XML_ONLY');
    }

    if (!$item->hasCompatibleLanguage()) {
        return false;
    }

    if ($item->ignore) {
        return false;
    }

    ?>
    <tr
        class="sitemapitem row<?php echo $count; ?> <?php echo ($showItemUID) ? 'with-uid' : ''; ?>"
        data-uid="<?php echo $item->uid; ?>"
        data-settings-hash="<?php echo $item->settingsHash; ?>">

        <td class="center">
            <?php if (!$item->ignore) : ?>
                <div class="sitemapitem-published"
                    data-original="<?php echo $item->published ? '1' : '0'; ?>"
                    data-value="<?php echo $item->published ? '1' : '0'; ?>">

                    <?php
                    $class = $item->published ? 'publish' : 'unpublish';
                    $title = $item->published ? 'COM_OSMAP_TOOLTIP_CLICK_TO_UNPUBLISH' : 'COM_OSMAP_TOOLTIP_CLICK_TO_PUBLISH';
                    ?>

                    <span
                        title="<?php echo JText::_($title); ?>"
                        class="hasTooltip icon-<?php echo $class; ?>">
                    </span>
                </div>
            <?php endif; ?>
            <?php $notes = $item->getAdminNotesString(); ?>
            <?php if (!empty($notes)) : ?>
                <span class="icon-warning hasTooltip osmap-info" title="<?php echo $notes; ?>"></span>
            <?php endif; ?>
        </td>

        <td class="sitemapitem-link">
            <?php if ($item->level > 0) : ?>
                <span class="level-mark">
                    <?php echo str_repeat('—', $item->level); ?>
                </span>
            <?php endif; ?>

            <?php if (!empty($item->rawLink) && $item->rawLink !== '#' && $item->link !== '#') : ?>
                <a
                    href="<?php echo $item->rawLink; ?>"
                    target="_blank"
                    class="hasTooltip"
                    title="<?php echo $item->link; ?>">
                    <?php echo $item->rawLink; ?>
                </a>
                <span class="icon-new-tab"></span>
            <?php else : ?>
                <span>
                    <?php echo isset($item->name) ? $item->name : ''; ?>
                </span>
            <?php endif; ?>

            <?php if ($showItemUID) : ?>
                <br>
                <div class="small osmap-item-uid">
                    <?php echo JText::_('COM_OSMAP_UID'); ?>: <?php echo $item->uid; ?>
                </div>
            <?php endif; ?>
        </td>

        <td class="sitemapitem-name">
            <?php echo isset($item->name) ? $item->name : ''; ?>
        </td>

        <td class="center">
            <div class="sitemapitem-priority"
                data-original="<?php echo $item->priority; ?>"
                data-value="<?php echo $item->priority; ?>">

                <?php echo $item->priority; ?>
            </div>
        </td>

        <td class="center">
            <div class="sitemapitem-changefreq"
                data-original="<?php echo $item->changefreq; ?>"
                data-value="<?php echo $item->changefreq; ?>">

                <?php echo JText::_('COM_OSMAP_' . strtoupper($item->changefreq)); ?>
            </div>
        </td>
    </tr>
    <?php

    $count++;

    return true;
};
?>

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
        <?php if (is_object($this->sitemap)) : ?>
            <?php $this->sitemap->traverse($printNodeCallback, false); ?>
        <?php endif; ?>
    </tbody>
</table>
<div><?php echo JText::sprintf('COM_OSMAP_NUMBER_OF_ITEMS_FOUND', $count); ?></div>

<?php if (empty($count)) : ?>
    <div class="alert alert-warning">
        <?php echo JText::_('COM_OSMAP_NO_ITEMS'); ?>
    </div>
<?php endif;
