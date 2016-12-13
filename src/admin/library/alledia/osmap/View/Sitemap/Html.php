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
use Joomla\Registry\Registry;

defined('_JEXEC') or die();


class Html extends OSMap\View\Base
{
    /**
     * @var Registry
     */
    protected $params;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var int
     */
    protected $showExternalLinks = 0;

    /**
     * @var int
     */
    protected $showMenuTitles = 1;

    /**
     * @var int
     */
    public $generalCounter = 0;

    /**
     * List of found items to render the sitemap
     *
     * @var array
     */
    protected $menus = array();

    /**
     * A list of last items per level. Used to identify the parent items
     *
     * @var array
     */
    protected $lastItemsPerLevel = array();

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
     * The callback called to print each node. Returns true if it was
     * able to print. False, if not.
     *
     * @param object $node
     *
     * @return bool
     */
    public function registerNodeIntoList($node)
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

        // Check if the menu was already registered and register if needed
        if ($node->level === 0 && !isset($this->menus[$node->menuItemType])) {
            $queueItem = (object)array(
                'menuItemId'    => $node->menuItemId,
                'menuItemTitle' => $node->menuItemTitle,
                'menuItemType'  => $node->menuItemType,
                'level'         => -1,
                'children'      => array()
            );

            // Add the menu to the main list of items
            $this->menus[$node->menuItemType] = $queueItem;

            // Add this menu as the last one on the list of menus
            $this->lastItemsPerLevel[-1] = $queueItem;
        }

        // Instantiate the current item
        $queueItem           = new \stdClass;
        $queueItem->rawLink  = $node->rawLink;
        $queueItem->type     = $node->type;
        $queueItem->level    = $node->level;
        $queueItem->name     = $node->name;
        $queueItem->uid      = $node->uid;
        $queueItem->children = array();

        // Add debug information, if debug is enabled
        if ($this->debug) {
            $queueItem->fullLink         = $node->fullLink;
            $queueItem->link             = $node->link;
            $queueItem->modified         = $node->modified;
            $queueItem->duplicate        = $node->duplicate;
            $queueItem->visibleForRobots = $node->visibleForRobots;
            $queueItem->adapter          = get_class($node->adapter);
            $queueItem->menuItemType     = $node->menuItemType;
        }

        // Add this item to its parent children list
        $this->lastItemsPerLevel[$queueItem->level - 1]->children[] = $queueItem;

        // Add this item as the last one on the its level
        $this->lastItemsPerLevel[$queueItem->level] = $queueItem;

        unset($node);

        return true;
    }

    /**
     * Print debug info for a note
     *
     * @param object $node
     *
     * @return void
     */
    public function printDebugInfo($node)
    {
        echo '<div class="osmap-debug-box">';
        echo '<div><span>#:</span>&nbsp;' . $this->generalCounter . '</div>';
        echo '<div><span>' . \JText::_('COM_OSMAP_UID') . ':</span>&nbsp;' . $node->uid . '</div>';
        echo '<div><span>' . \JText::_('COM_OSMAP_FULL_LINK') . ':</span>&nbsp;' . htmlspecialchars($node->fullLink) . '</div>';
        echo '<div><span>' . \JText::_('COM_OSMAP_RAW_LINK') . ':</span>&nbsp;' . htmlspecialchars($node->rawLink) . '</div>';
        echo '<div><span>' . \JText::_('COM_OSMAP_LINK') . ':</span>&nbsp;' . htmlspecialchars($node->link) . '</div>';
        echo '<div><span>' . \JText::_('COM_OSMAP_MODIFIED') . ':</span>&nbsp;' . htmlspecialchars($node->modified) . '</div>';
        echo '<div><span>' . \JText::_('COM_OSMAP_LEVEL') . ':</span>&nbsp;' . $node->level . '</div>';
        echo '<div><span>' . \JText::_('COM_OSMAP_DUPLICATE') . ':</span>&nbsp;' . \JText::_($node->duplicate ? 'JYES' : 'JNO') . '</div>';
        echo '<div><span>' . \JText::_('COM_OSMAP_VISIBLE_FOR_ROBOTS') . ':</span>&nbsp;' . \JText::_($node->visibleForRobots ? 'JYES' : 'JNO') . '</div>';
        echo '<div><span>' . \JText::_('COM_OSMAP_ADAPTER_CLASS') . ':</span>&nbsp;' . $node->adapter . '</div>';

        if (method_exists($node, 'getAdminNotesString')) {
            $adminNotes = $node->getAdminNotesString();
            if (!empty($adminNotes)) {
                echo '<div><span>' . \JText::_('COM_OSMAP_ADMIN_NOTES') . ':</span>&nbsp;' . nl2br($adminNotes) . '</div>';
            }
        }
        echo '</div>';
    }

    /**
     * Print an item
     *
     * @param object $item
     *
     * @return void
     */
    public function printItem($item)
    {
        $this->generalCounter++;

        $liClass = $this->debug ? 'osmap-debug-item' : '';
        $liClass .= $this->generalCounter % 2 == 0 ? ' even' : '';

        if (!empty($item->children)) {
            $liClass .= ' osmap-has-children';
        }

        $sanitizedUID = \JApplicationHelper::stringURLSafe($item->uid);

        echo "<li class=\"{$liClass}\" id=\"osmap-li-uid-{$sanitizedUID}\">";

        // Some items are just separator, without a link. Do not print as link then
        if (trim($item->rawLink) === '') {
            $type = isset($item->type) ? $item->type : 'separator';
            echo '<span class="osmap-item-' . $type . '">';
            echo htmlspecialchars($item->name);
            echo '</span>';
        } else {
            echo '<a href="' . $item->rawLink . '" target="_self" class="osmap-link">';
            echo htmlspecialchars($item->name);
            echo '</a>';
        }

        // Debug box
        if ($this->debug) {
            $this->printDebugInfo($item);
        }

        // Check if we have children items to print
        if (!empty($item->children)) {
            $this->printMenu($item);
        }

        echo "</li>";
    }

    /**
     * Renders the list of items as a html sitemap
     */
    public function renderSitemap()
    {
        if (!empty($this->menus)) {
            $columns = max((int)$this->params->get('columns', 1), 1);

            foreach ($this->menus as $menuType => $menu) {
                if (isset($menu->menuItemTitle)
                    && $this->showMenuTitles
                    && !empty($menu->children)
                ) {
                    if ($this->debug) {
                        $debug = sprintf(
                            '<div><span>%s:</span>&nbsp;%s: %s</div>',
                            \JText::_('COM_OSMAP_MENUTYPE'),
                            $menu->menuItemId,
                            $menu->menuItemType
                        );
                    }

                    echo sprintf(
                        '<h2 id="osmap-menu-uid-%s">%s%s</h2>',
                        \JApplicationHelper::stringURLSafe($menu->menuItemType),
                        $menu->menuItemTitle,
                        empty($debug) ? '' : $debug
                    );
                }

                $this->printMenu($menu, $columns);
            }
        }
    }

    /**
     * Render the menu item and its children items
     *
     * @param object $menu
     * @param int    $columns
     *
     * @return void
     */
    protected function printMenu($menu, $columns = null)
    {
        if (isset($menu->menuItemType)) {
            $sanitizedUID = \JApplicationHelper::stringURLSafe($menu->menuItemType);
        } else {
            $sanitizedUID = \JApplicationHelper::stringURLSafe($menu->uid);
        }

        $class = array('level_' . ($menu->level + 1));
        if ($columns && $columns > 1) {
            $class[] = 'columns_' . $columns;
        }

        echo sprintf(
            '<ul class="%s" id="osmap-ul-uid-%s">',
            join(' ', $class),
            $sanitizedUID
        );

        foreach ($menu->children as $item) {
            $this->printItem($item);
        }

        echo '</ul>';
    }
}
