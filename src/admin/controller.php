<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2015 Open Source Training, LLC. All rights reserved..
 * @author    Guillermo Vargas <guille@vargas.co.cr>
 * @author    Alledia <support@alledia.com>
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file is part of OSMap.
 *
 * OSMap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * OSMap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSMap. If not, see <http://www.gnu.org/licenses/>.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Alledia\Framework\Joomla\Extension\Generic as GenericExtension;

jimport('joomla.application.component.controller');

/**
 * Component Controller
 *
 * @package     OSMap
 * @subpackage  com_osmap
 */
class OSMapController extends JControllerLegacy
{

    public function __construct()
    {
        parent::__construct();

        $this->registerTask('navigator-links', 'navigatorLinks');
        $this->registerTask('migrate-xmap', 'migrateXmapData');
    }

    /**
     * Display the view
     */
    public function display($cachable = false, $urlparams = false)
    {
        require_once JPATH_COMPONENT . '/helpers/osmap.php';

        // Get the document object.
        $document = JFactory::getDocument();

        // Set the default view name and format from the Request.
        $vName   = JRequest::getWord('view', 'sitemaps');
        $vFormat = $document->getType();
        $lName   = JRequest::getWord('layout', 'default');

        // Get and render the view.
        if ($view = $this->getView($vName, $vFormat)) {
            // Get the model for the view.
            $model = $this->getModel($vName);

            // Push the model into the view (as default).
            $view->setModel($model, true);
            $view->setLayout($lName);

            // Push document object into the view.
            $view->assignRef('document', $document);

            $view->display();
        }
    }

    public function navigator()
    {
        $db       = JFactory::getDBO();
        $document = JFactory::getDocument();
        $app      = JFactory::getApplication('administrator');

        $id   = JRequest::getInt('sitemap', 0);
        $link = urldecode(JRequest::getVar('link', ''));
        $name = JRequest::getCmd('e_name', '');

        if (!$id) {
            $id = $this->getDefaultSitemapId();
        }

        if (!$id) {
            JError::raiseWarning(500, JText::_('OSMAP_NOT_SITEMAP_SELECTED'));

            return false;
        }

        $app->setUserState('com_osmap.edit.sitemap.id', $id);

        $view  = $this->getView('sitemap', $document->getType());
        $model = $this->getModel('Sitemap');

        $view->setLayout('navigator');
        $view->setModel($model, true);

        // Push document object into the view.
        $view->assignRef('document', $document);

        $view->navigator();
    }

    public function navigatorLinks()
    {
        $db       = JFactory::getDBO();
        $document = JFactory::getDocument();
        $app      = JFactory::getApplication('administrator');

        $id   = JRequest::getInt('sitemap', 0);
        $link = urldecode(JRequest::getVar('link', ''));
        $name = JRequest::getCmd('e_name', '');

        if (!$id) {
            $id = $this->getDefaultSitemapId();
        }

        if (!$id) {
            JError::raiseWarning(500, JText::_('OSMAP_NOT_SITEMAP_SELECTED'));

            return false;
        }

        $app->setUserState('com_osmap.edit.sitemap.id', $id);

        $view  = $this->getView('sitemap', $document->getType());
        $model = $this->getModel('Sitemap');

        $view->setLayout('navigator');
        $view->setModel($model, true);

        // Push document object into the view.
        $view->assignRef('document', $document);

        $view->navigatorLinks();
    }

    private function getDefaultSitemapId()
    {
        $db = JFactory::getDBO();
        $query  = $db->getQuery(true);
        $query->select('id');
        $query->from($db->quoteName('#__osmap_sitemap'));
        $query->where('is_default=1');
        $db->setQuery($query);

        return $db->loadResult();
    }

    public function migrateXmapData()
    {
        $result = new stdClass;
        $result->success = false;

        $db = JFactory::getDbo();
        $db->startTransaction();

        try {
            // Do we have any Xmap sitemap?
            $sitemapIds       = array();
            $itemIds          = array();
            $sitemapFailedIds = array();
            $itemFailedIds = array();
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__xmap_sitemap');
            $db->setQuery($query);
            $sitemaps = $db->loadObjectList();

            if (!empty($sitemaps)) {
                // Import the sitemaps
                foreach ($sitemaps as $sitemap) {
                    $query = $db->getQuery(true)
                        ->set(
                            array(
                                $db->quoteName('title') . '=' . $db->quote($sitemap->title),
                                $db->quoteName('alias') . '=' . $db->quote($sitemap->alias),
                                $db->quoteName('introtext') . '=' . $db->quote($sitemap->introtext),
                                $db->quoteName('metadesc') . '=' . $db->quote($sitemap->metadesc),
                                $db->quoteName('metakey') . '=' . $db->quote($sitemap->metakey),
                                $db->quoteName('attribs') . '=' . $db->quote($sitemap->attribs),
                                $db->quoteName('selections') . '=' . $db->quote($sitemap->selections),
                                $db->quoteName('excluded_items') . '=' . $db->quote($sitemap->excluded_items),
                                $db->quoteName('is_default') . '=' . $db->quote($sitemap->is_default),
                                $db->quoteName('state') . '=' . $db->quote($sitemap->state),
                                $db->quoteName('access') . '=' . $db->quote($sitemap->access),
                                $db->quoteName('created') . '=' . $db->quote($sitemap->created),
                                $db->quoteName('count_xml') . '=' . $db->quote($sitemap->count_xml),
                                $db->quoteName('count_html') . '=' . $db->quote($sitemap->count_html),
                                $db->quoteName('views_xml') . '=' . $db->quote($sitemap->views_xml),
                                $db->quoteName('views_html') . '=' . $db->quote($sitemap->views_html),
                                $db->quoteName('lastvisit_xml') . '=' . $db->quote($sitemap->lastvisit_xml),
                                $db->quoteName('lastvisit_html') . '=' . $db->quote($sitemap->lastvisit_html)
                            )
                        )
                        ->insert('#__osmap_sitemap');
                    $db->setQuery($query);

                    if ($db->execute()) {
                        $sitemapIds[$sitemap->id] = $db->insertId();
                    } else {
                        $sitemapFailedIds = $sitemap->id;
                    }
                }

                // Import the Items
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__xmap_items');
                $db->setQuery($query);
                $items = $db->loadObjectList();

                if (!empty($items)) {
                    foreach ($items as $item) {
                        $query = $db->getQuery(true)
                            ->set(
                                array(
                                    $db->quoteName('uid') . '=' . $db->quote($item->uid),
                                    $db->quoteName('itemid') . '=' . $db->quote($item->itemid),
                                    $db->quoteName('view') . '=' . $db->quote($item->view),
                                    $db->quoteName('sitemap_id') . '=' . $db->quote($sitemapIds[$item->sitemap_id]),
                                    $db->quoteName('properties') . '=' . $db->quote($item->properties)
                                )
                            )
                            ->insert('#__osmap_items');
                        $db->setQuery($query);

                        if ($db->execute()) {
                            $itemIds[$item->itemid] = $db->insertId();
                        } else {
                            $itemFailedIds = $item->itemid;
                        }
                    }
                }
            }

            if (!empty($sitemapFailedIds) || !empty($itemFailedIds)) {
                throw new Exception("Failed the sitemap or item migration");
            }

            /*
             * Menu Migration
             */
            $xmap  = new GenericExtension('Xmap', 'component');
            $osmap = new GenericExtension('OSMap', 'component');

            // Remove OSMap menus
            $query = $db->getQuery(true)
                ->delete('#__menu')
                ->where('type = ' . $db->quote('component'))
                ->where('component_id = ' . $db->quote($osmap->getId()));
            $db->setQuery($query);
            $db->execute();

            // Get the Xmap menus
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__menu')
                ->where('type = ' . $db->quote('component'))
                ->where('component_id = ' . $db->quote($xmap->getId()));
            $db->setQuery($query);
            $xmapMenus = $db->loadObjectList();

            if (!empty($xmapMenus)) {
                // Convert each menu to OSMap
                foreach ($xmapMenus as $menu) {
                    $query = $db->getQuery(true)
                        ->set('title = ' . $db->quote($this->replaceXmapByOSMap($menu->title)))
                        ->set('alias = ' . $db->quote($this->replaceXmapByOSMap($menu->alias)))
                        ->set('path = ' . $db->quote($this->replaceXmapByOSMap($menu->path)))
                        ->set('link = ' . $db->quote($this->replaceXmapByOSMap($menu->link)))
                        ->set('img = ' . $db->quote($this->replaceXmapByOSMap($menu->img)))
                        ->set('component_id = ' . $db->quote($osmap->getId()))
                        ->update('#__menu')
                        ->where('id = ' . $db->quote($menu->id));
                    $db->setQuery($query);
                    $db->execute();
                }
            }

            // Disable Xmap
            $query = $db->getQuery(true)
                ->set('enabled = 0')
                ->update('#__extensions')
                ->where('extension_id = ' . $db->quote($xmap->getId()));
            $db->setQuery($query);
            $db->execute();

            // Clean up Xmap db tables
            $db->setQuery('DELETE FROM ' . $db->quoteName('#__xmap_sitemap'));
            $db->execute();

            $db->setQuery('DELETE FROM ' . $db->quoteName('#__xmap_items'));
            $db->execute();

            $db->commitTransaction();

            $result->success = true;
        } catch(Exception $e) {
            $db->rollbackTransaction();
        }

        echo json_encode($result);
    }

    private function replaceXmapByOSMap($str)
    {
        $str = str_replace('XMAP', 'OSMAP', $str);
        $str = str_replace('XMap', 'OSMap', $str);
        $str = str_replace('xMap', 'OSMap', $str);
        $str = str_replace('xmap', 'osmap', $str);

        return $str;
    }
}
