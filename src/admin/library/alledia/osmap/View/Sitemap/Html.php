<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\View\Sitemap;

use Alledia\OSMap;

defined('_JEXEC') or die();


class Html extends OSMap\View\Base
{
    /**
     * @var \JRegistry
     */
    protected $params;

    /**
     * @var int
     */
    protected $lastMenuId = 0;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var int
     */
    protected $lastLevel = -1;

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * @var int
     */
    protected $showExternalLinks = 0;

    /**
     * @var int
     */
    protected $showMenuTitles = 1;

    /**
     * @var bool
     */
    protected $shouldCloseMenu = false;

    /**
     * The constructor
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->params            = OSMap\Factory::getApplication()->getParams();
        $this->debug             = (bool)$this->params->get('debug', 0);
        $this->osmapParams       = \JComponentHelper::getParams('com_osmap');
        $this->showExternalLinks = (int)$this->osmapParams->get('show_external_links', 0);
        $this->showMenuTitles    = (int)$this->params->get('show_menu_titles', 1);
    }

    /**
     * The display method
     *
     * @param string $tpl
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $container = OSMap\Factory::getContainer();

        try {
            $id = $container->input->getInt('id');

            $this->osmapParams = \JComponentHelper::getParams('com_osmap');

            // Load the sitemap instance
            $this->sitemap = OSMap\Factory::getSitemap($id, 'standard');

            // Check if the sitemap is published
            if (!$this->sitemap->isPublished) {
                throw new \Exception(\JText::_('COM_OSMAP_MSG_SITEMAP_IS_UNPUBLISHED'));
            }
        } catch (\Exception $e) {
            $this->message = $e->getMessage();
        }

        parent::display($tpl);
    }

    /**
     * Close levels
     *
     * @param int
     *
     * @return void
     */
    public function closeLevels($offset)
    {
        if ($offset > 0) {
            for ($i = 0; $i < $offset; $i++) {
                echo '</ul></li>';
            }
        }
    }

    /**
     * Open a menu list
     *
     * @param object $node
     * @param string $cssClass
     *
     * @return void
     */
    public function openMenu($node, $cssClass = '')
    {
        if ($this->showMenuTitles) {
            echo '<h2>' . $node->menuItemTitle;

            if ($this->debug) {
                echo '<div><span>' . \JText::_('COM_OSMAP_MENUTYPE') . ':</span>&nbsp;' . $node->menuItemId . ': ' . $node->menuItemType . '</div>';
            }

            echo '</h2>';
        }

        echo '<ul class="level_0 ' . $cssClass . '">';

        // It says we opened at least one menu, so we need to close it after traverse the sitemap
        $this->shouldCloseMenu = true;
    }

    /**
     * Close a menu
     *
     * @return void
     */
    public function closeMenu()
    {
        echo '</ul>';
    }

    /**
     * Open a new sub level
     *
     * @param object $node
     *
     * @return void
     */
    public function openSubLevel($node)
    {
        echo '<li><ul class="level_' . $node->level . '">';
    }

    /**
     * Print debug info for a note
     *
     * @param object $node
     * @param int    $count
     *
     * @return void
     */
    public function printDebugInfo($node, $count)
    {
        echo '<div class="osmap-debug-box">';
        echo '<div><span>#:</span>&nbsp;' . $count . '</div>';
        echo '<div><span>' . \JText::_('COM_OSMAP_UID') . ':</span>&nbsp;' . $node->uid . '</div>';
        echo '<div><span>' . \JText::_('COM_OSMAP_FULL_LINK') . ':</span>&nbsp;' . htmlspecialchars($node->fullLink) . '</div>';
        echo '<div><span>' . \JText::_('COM_OSMAP_RAW_LINK') . ':</span>&nbsp;' . htmlspecialchars($node->rawLink) . '</div>';
        echo '<div><span>' . \JText::_('COM_OSMAP_LINK') . ':</span>&nbsp;' . htmlspecialchars($node->link) . '</div>';
        echo '<div><span>' . \JText::_('COM_OSMAP_MODIFIED') . ':</span>&nbsp;' . htmlspecialchars($node->modified) . '</div>';
        echo '<div><span>' . \JText::_('COM_OSMAP_LEVEL') . ':</span>&nbsp;' . $node->level . '</div>';
        echo '<div><span>' . \JText::_('COM_OSMAP_DUPLICATE') . ':</span>&nbsp;' . \JText::_($node->duplicate ? 'JYES' : 'JNO') . '</div>';
        echo '<div><span>' . \JText::_('COM_OSMAP_VISIBLE_FOR_ROBOTS') . ':</span>&nbsp;' . \JText::_($node->visibleForRobots ? 'JYES' : 'JNO') . '</div>';
        echo '<div><span>' . \JText::_('COM_OSMAP_ADAPTER_CLASS') . ':</span>&nbsp;' . get_class($node->adapter) . '</div>';

        $adminNotes = $node->getAdminNotesString();
        if (!empty($adminNotes)) {
            echo '<div><span>' . \JText::_('COM_OSMAP_ADMIN_NOTES') . ':</span>&nbsp;' . nl2br($adminNotes) . '</div>';
        }
        echo '</div>';
    }

    /**
     * Print an item
     *
     * @param object $node
     * @param int    $count
     *
     * @return void
     */
    public function printItem($node, $count)
    {
        $liClass = $this->debug ? 'osmap-debug-item' : '';
        $liClass .= $count % 2 == 0 ? ' even' : '';

        echo "<li class=\"{$liClass}\">";

        // Some items are just separator, without a link. Do not print as link then
        if (trim($node->rawLink) === '') {
            $type = isset($node->type) ? $node->type : 'separator';
            echo '<span class="osmap-item-' . $type . '">';
            echo htmlspecialchars($node->name);
            echo '</span>';
        } else {
            echo '<a href="' . $node->rawLink . '" target="_self" class="osmap-link">';
            echo htmlspecialchars($node->name);
            echo '</a>';
        }

        // Debug box
        if ($this->debug) {
            $this->printDebugInfo($node, $count);
        }

        echo '</li>';
    }

    /**
     * The callback called to print each node. Returns true if it was
     * able to print. False, if not.
     *
     * @param object $node
     *
     * @return bool
     */
    public function printNodeCallback($node)
    {
        $ignoreDuplicatedUIDs = (int)$this->osmapParams->get('ignore_duplicated_uids', 1);

        $display = !$node->ignore
            && $node->published
            && (!$node->duplicate || ($node->duplicate && !$ignoreDuplicatedUIDs))
            && $node->visibleForHTML;

        // Check if is external URL and if should be ignored
        if ($display && !$node->isInternal) {
            $display = $this->showExternalLinks > 0;
        }

        if (!$node->hasCompatibleLanguage()) {
            $display = false;
        }

        if (!$display) {
            return false;
        }

        $this->count++;

        if ($this->lastMenuId !== $node->menuItemId) {
            // Make sure we need to close the last menu
            if ($this->lastMenuId > 0) {
                $this->closeLevels($this->lastLevel);
                $this->closeMenu();
            }

            $this->openMenu($node);
            $this->lastLevel = 0;
        }

        // Check if we have a different level to start or close tags
        if ($this->lastLevel !== $node->level) {
            if ($node->level > $this->lastLevel) {
                $this->openSubLevel($node);
            }

            if ($node->level < $this->lastLevel) {
                // Make sure we close the stack of prior levels
                $this->closeLevels($this->lastLevel - $node->level);
            }
        }

        $this->printItem($node, $this->count);

        $this->lastLevel  = $node->level;
        $this->lastMenuId = $node->menuItemId;

        return true;
    }
}
