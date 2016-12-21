<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

if (empty($this->languages)) :
    ?>
    <a href="<?php echo JRoute::_('index.php?option=com_osmap&view=sitemapitems&id=' . $this->item->id); ?>">
        <span class="icon-edit"></span>
    </a>
    <?php
else :
    foreach ($this->languages as $language) :
        ?>
        <a href="<?php echo JRoute::_('index.php?option=com_osmap&view=sitemapitems&id=' . $this->item->id . '&lang=' . $language->sef); ?>">
            <span class="icon-edit"></span>
            <img src="/media/mod_languages/images/<?php echo $language->image; ?>.gif"/>
            <?php echo $language->title; ?>
        </a>
        <br/>
        <?php
    endforeach;
endif;
