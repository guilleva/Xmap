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


class OSMapControllerSitemaps extends OSMap\Controller\Admin
{
    protected $text_prefix = 'COM_OSMAP_SITEMAP';

    public function editItems()
    {
        $id = OSMap\Factory::getApplication()->input->getInt('id');

        $this->redirect('index.php?option=com_osmap&view=sitemapitems&id=' . $id);
    }

    public function getModel($name = 'Sitemaps', $prefix = 'OSMapModel', $config = array())
    {
        return parent::getModel($name, $prefix, $config);
    }
}
