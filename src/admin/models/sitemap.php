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


class OSMapModelSitemap extends JModelAdmin
{
    public function getTable($type = 'Sitemap', $prefix = 'OSMapTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_osmap.sitemap', 'sitemap', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    protected function loadFormData()
    {
        $data = OSMap\Factory::getApplication()->getUserState('com_osmap.edit.sitemap.data', array());

        if (empty($data)) {
            $data = $this->getItem();

            // Load some defaults for new sitemap
            $id = $data->get('id');
            if (empty($id)) {
                $data->set('published', 1);
                $data->set('created', OSMap\Factory::getDate()->toSql());
            }

            // Load the menus
            if (!empty($id)) {
                $db    = OSMap\Factory::getDbo();
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__osmap_sitemap_menus')
                    ->where('sitemap_id = ' . $db->quote($id))
                    ->order('ordering');
                $menus = $db->setQuery($query)->loadObjectList();

                $data->menus = array();

                foreach ($menus as $menu) {
                    $data->menus[$menu->menutype_id] = array(
                        'priority'   => $menu->priority,
                        'changefreq' => $menu->changefreq
                    );
                }
            }
        }

        return $data;
    }

    /**
     * @param JTable $table
     *
     * @return void
     */
    protected function prepareTable($table)
    {
    }

    /**
     * Returns an array with a list of the selected menus
     *
     * @return array
     */
    public function getSelectedMenus($sitemapId)
    {
    }
}
