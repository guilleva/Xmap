<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap;

defined('_JEXEC') or die();

$router  = OSMap\Factory::getContainer()->router;
$baseUrl = $router->sanitizeURL(JUri::root());

/**
 * @param string $type
 * @param string $lang
 *
 * @return string
 */
$getLink = function ($type, $lang = null) use ($router, $baseUrl) {
    switch ($type) {
        case 'xml':
        case 'html':
            if (!empty($this->item->menuIdList[$type])) {
                $query['Itemid'] = $this->item->menuIdList[$type];
            }
            break;

        case 'news':
        case 'images':
            if (!empty($this->item->menuIdList['xml'])) {
                $query['Itemid'] = $this->item->menuIdList['xml'];
            }
            $query[$type] = 1;
            break;

    }

    if (empty($query['Itemid'])) {
        $query = array(
            'option' => 'com_osmap',
            'view'   => in_array($type, array('html', 'xml')) ? $type : 'xml',
            'id'     => $this->item->id
        );
    }
    if ($lang) {
        $query['lang'] = $lang;
    }

    return $router->routeURL('index.php?' . http_build_query($query));
};

?>
<span class="osmap-link">
    <a
        href="<?php echo $baseUrl . $getLink('xml'); ?>"
        target="_blank"
        title="<?php echo JText::_('COM_OSMAP_XML_LINK_TOOLTIP', true); ?>">
        <?php echo JText::_('COM_OSMAP_XML_LINK'); ?>
    </a>
    <span class="icon-new-tab"></span>
</span>

<span class="osmap-link">
    <a
        href="<?php echo $baseUrl . $getLink('html'); ?>"
        target="_blank"
        title="<?php echo JText::_('COM_OSMAP_HTML_LINK_TOOLTIP', true); ?>">
        <?php echo JText::_('COM_OSMAP_HTML_LINK'); ?>
     </a>
    <span class="icon-new-tab"></span>
</span>

<span class="osmap-link">
    <a
        href="<?php echo $baseUrl . $getLink('news'); ?>"
        target="_blank"
        title="<?php echo JText::_('COM_OSMAP_NEWS_LINK_TOOLTIP', true); ?>">
        <?php echo JText::_('COM_OSMAP_NEWS_LINK'); ?>
    </a>
    <span class="icon-new-tab"></span>
</span>

<span class="osmap-link">
    <a
        href="<?php echo $baseUrl . $getLink('images'); ?>"
        target="_blank"
        title="<?php echo JText::_('COM_OSMAP_IMAGES_LINK_TOOLTIP', true); ?>">
        <?php echo JText::_('COM_OSMAP_IMAGES_LINK'); ?>
    </a>
    <span class="icon-new-tab"></span>
</span>
