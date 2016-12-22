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
$getLink = function ($type, $lang = null) use ($router) {
    $linkId = in_array($type, array('news', 'images')) ? 'xml' : $type;
    if (!empty($this->item->menuIdList[$linkId])) {
        $query['Itemid'] = $this->item->menuIdList[$linkId];
    }

    if (empty($query['Itemid'])) {
        $query = array(
            'option' => 'com_osmap',
            'view'   => $linkId,
            'id'     => $this->item->id
        );
    }

    if ($type != $linkId) {
        $query[$type] = 1;
    }

    if ($lang) {
        $query['lang'] = $lang;
    }

    return $router->routeURL('index.php?' . http_build_query($query));
};

$languages = $this->languages ?: array('');
foreach ($languages as $language) :
    $langCode = empty($language->sef) ? null : $language->sef;
    ?>
    <span class="osmap-link">
        <a
            href="<?php echo $baseUrl . $getLink('xml', $langCode); ?>"
            target="_blank"
            title="<?php echo JText::_('COM_OSMAP_XML_LINK_TOOLTIP', true); ?>">
            <?php echo JText::_('COM_OSMAP_XML_LINK'); ?>
        </a>
        <span class="icon-new-tab"></span>
    </span>

    <span class="osmap-link">
        <a
            href="<?php echo $baseUrl . $getLink('html', $langCode); ?>"
            target="_blank"
            title="<?php echo JText::_('COM_OSMAP_HTML_LINK_TOOLTIP', true); ?>">
            <?php echo JText::_('COM_OSMAP_HTML_LINK'); ?>
         </a>
        <span class="icon-new-tab"></span>
    </span>

    <span class="osmap-link">
        <a
            href="<?php echo $baseUrl . $getLink('news', $langCode); ?>"
            target="_blank"
            title="<?php echo JText::_('COM_OSMAP_NEWS_LINK_TOOLTIP', true); ?>">
            <?php echo JText::_('COM_OSMAP_NEWS_LINK'); ?>
        </a>
        <span class="icon-new-tab"></span>
    </span>

    <span class="osmap-link">
        <a
            href="<?php echo $baseUrl . $getLink('images', $langCode); ?>"
            target="_blank"
            title="<?php echo JText::_('COM_OSMAP_IMAGES_LINK_TOOLTIP', true); ?>">
            <?php echo JText::_('COM_OSMAP_IMAGES_LINK'); ?>
        </a>
        <span class="icon-new-tab"></span>
    </span>
    <br/>
    <?php
endforeach;
