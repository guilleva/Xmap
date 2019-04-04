<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

$languages = $this->languages ?: array('');

foreach ($languages as $language) :
    $linkQuery = array(
        'option' => 'com_osmap',
        'view'   => 'sitemapitems',
        'id'     => $this->item->id
    );

    if ($language) {
        $linkQuery['lang'] = $language->sef;

        $flag = JHtml::_(
            'image',
            'mod_languages/' . $language->image . '.gif',
            $language->title,
            null,
            true
        );
        $flag .= ' ' . $language->title;
    }


    echo JHtml::_(
        'link',
        JRoute::_('index.php?' . http_build_query($linkQuery)),
        '<span class="icon-edit"></span>' . ($language ? $flag : '')
    );
    ?>
    <br/>
<?php
endforeach;
