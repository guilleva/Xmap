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

$languages = $this->languages ?: array('');
foreach ($languages as $language) :
    $langCode = empty($language->sef) ? null : $language->sef;
    ?>
    <span class="osmap-link">
        <?php
        echo JHtml::_(
            'link',
            $this->getLink($this->item, 'xml', $langCode),
            JText::_('COM_OSMAP_XML_LINK'),
            sprintf('target="_blank" title="%s"', JText::_('COM_OSMAP_XML_LINK_TOOLTIP', true))
        );
        ?>
        <span class="icon-new-tab"></span>
    </span>

    <span class="osmap-link">
        <?php
        echo JHtml::_(
            'link',
            $this->getLink($this->item, 'html', $langCode),
            JText::_('COM_OSMAP_HTML_LINK'),
            sprintf('target="_blank" title="%s"', JText::_('COM_OSMAP_HTML_LINK_TOOLTIP', true))
        );
        ?>
        <span class="icon-new-tab"></span>
    </span>

    <span class="osmap-link">
        <?php
        echo JHtml::_(
            'link',
            $this->getLink($this->item, 'news', $langCode),
            JText::_('COM_OSMAP_NEWS_LINK'),
            sprintf('target="_blank" title="%s"', JText::_('COM_OSMAP_NEWS_LINK_TOOLTIP', true))
        );
        ?>
        <span class="icon-new-tab"></span>
    </span>

    <span class="osmap-link">
        <?php
        echo JHtml::_(
            'link',
            $this->getLink($this->item, 'images', $langCode),
            JText::_('COM_OSMAP_IMAGES_LINK'),
            sprintf('target="_blank" title="%s"', JText::_('COM_OSMAP_IMAGES_LINK_TOOLTIP', true))
        );
        ?>
        <span class="icon-new-tab"></span>
    </span>
    <br/>
    <?php
endforeach;
