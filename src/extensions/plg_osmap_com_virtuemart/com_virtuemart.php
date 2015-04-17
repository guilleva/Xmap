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

defined('_JEXEC') or die('Restricted access');

/** Adds support for Virtuemart categories to OSMap */
class osmap_com_virtuemart
{
    protected static $categoryModel;
    protected static $productModel;
    protected static $initialized = false;

    public static $urlBase;

    /*
     * This function is called before a menu item is printed. We use it to set the
     * proper uniqueid for the item and indicate whether the node is expandible or not
     */
    public static function prepareMenuItem($node, &$params)
    {
        $app = JFactory::getApplication();

        $link_query = parse_url($node->link);

        parse_str(html_entity_decode($link_query['query']), $link_vars);

        $catid  = JArrayHelper::getValue($link_vars, 'virtuemart_category_id', 0);
        $prodid = JArrayHelper::getValue($link_vars, 'virtuemart_product_id', 0);

        if (!$catid) {
            $menu       = $app->getMenu();
            $menuParams = $menu->getParams($node->id);
            $catid      = $menuParams->get('virtuemart_category_id', 0);
        }

        if (!$prodid) {
            $menu       = $app->getMenu();
            $menuParams = $menu->getParams($node->id);
            $prodid     = $menuParams->get('virtuemart_product_id', 0);
        }

        if ($prodid && $catid) {
            $node->uid        = 'com_virtuemartc' . $catid . 'p' . $prodid;
            $node->expandible = false;
        } elseif ($catid) {
            $node->uid        = 'com_virtuemartc' . $catid;
            $node->expandible = true;
        }
    }

    /** Get the content tree for this kind of content */
    public static function getTree($osmap, $parent, &$params)
    {
        self::initialize();

        $app  = JFactory::getApplication();
        $menu = $app->getMenu();

        $link_query = parse_url($parent->link);

        parse_str(html_entity_decode($link_query['query']), $link_vars);

        $catid            = intval(JArrayHelper::getValue($link_vars, 'virtuemart_category_id', 0));
        $params['Itemid'] = intval(JArrayHelper::getValue($link_vars, 'Itemid', $parent->id));

        $view = JArrayHelper::getValue($link_vars, 'view', '');

        // we currently support only categories
        if (!in_array($view, array('categories', 'category'))) {
            return true;
        }

        $include_products = JArrayHelper::getValue($params, 'include_products', 1);
        $include_products = (
            $include_products == 1
            || ($include_products == 2 && $osmap->view == 'xml')
            || ($include_products == 3 && $osmap->view == 'html')
        );

        $params['include_products']          = $include_products;
        $params['include_product_images']    = (JArrayHelper::getValue($params, 'include_product_images', 1)
            && $osmap->view == 'xml');
        $params['product_image_license_url'] = trim(JArrayHelper::getValue($params, 'product_image_license_url', ''));

        $priority   = JArrayHelper::getValue($params, 'cat_priority', $parent->priority);
        $changefreq = JArrayHelper::getValue($params, 'cat_changefreq', $parent->changefreq);

        if ($priority == '-1') {
            $priority = $parent->priority;
        }

        if ($changefreq == '-1') {
            $changefreq = $parent->changefreq;
        }

        $params['cat_priority']   = $priority;
        $params['cat_changefreq'] = $changefreq;

        $priority   = JArrayHelper::getValue($params, 'prod_priority', $parent->priority);
        $changefreq = JArrayHelper::getValue($params, 'prod_changefreq', $parent->changefreq);

        if ($priority == '-1') {
            $priority = $parent->priority;
        }

        if ($changefreq == '-1') {
            $changefreq = $parent->changefreq;
        }

        $params['prod_priority']   = $priority;
        $params['prod_changefreq'] = $changefreq;

        self::getCategoryTree($osmap, $parent, $params, $catid);

        return true;
    }

    /** Virtuemart support */
    public static function getCategoryTree($osmap, $parent, &$params, $catid = 0)
    {
        if (!isset($urlBase)) {
            $urlBase = JURI::base();
        }

        $vendorId = 1;

        $m = VirtueMartModelCategory::getInstance('Category', 'VirtueMartModel');

        $cache = JFactory::getCache('com_virtuemart', 'callback');
        $cache->setCaching(true);
        $children = $cache->call(array($m, 'getChildCategoryList'), $vendorId, $catid);

        if (!empty($children)) {
            $osmap->changeLevel(1);

            foreach ($children as $row) {
                $node = new stdclass;

                $node->id         = $parent->id;
                $node->uid        = $parent->uid . 'c' . $row->virtuemart_category_id;
                $node->browserNav = $parent->browserNav;
                $node->name       = stripslashes($row->category_name);
                $node->priority   = $params['cat_priority'];
                $node->changefreq = $params['cat_changefreq'];
                $node->expandible = true;
                $node->link       = 'index.php?option=com_virtuemart&amp;view=category&amp;virtuemart_category_id='
                    . $row->virtuemart_category_id . '&amp;Itemid='.$parent->id;

                if ($osmap->printNode($node) !== FALSE) {
                    self::getCategoryTree($osmap, $parent, $params, $row->virtuemart_category_id);
                }
            }
        }

        $osmap->changeLevel(-1);

        if ($params['include_products'] && $catid != 0) {
            $products = self::$productModel->getProductsInCategory($catid);

            if ($params['include_product_images']) {
                self::$categoryModel->addImages($products, 1);
            }

            $osmap->changeLevel(1);

            foreach ($products as $row) {
                $node = new stdclass;

                $node->id         = $parent->id;
                $node->uid        = $parent->uid . 'c' . $row->virtuemart_category_id . 'p' . $row->virtuemart_product_id;
                $node->browserNav = $parent->browserNav;
                $node->priority   = $params['prod_priority'];
                $node->changefreq = $params['prod_changefreq'];
                $node->name       = $row->product_name;
                $node->modified   = strtotime($row->modified_on);
                $node->expandible = false;
                $node->link       = 'index.php?option=com_virtuemart&amp;view=productdetails&amp;virtuemart_product_id='
                    . $row->virtuemart_product_id . '&amp;virtuemart_category_id='
                    . $row->virtuemart_category_id . '&amp;Itemid=' . $parent->id;

                if ($params['include_product_images']) {
                    foreach ($row->images as $image) {
                        if (isset($image->file_url)) {
                            $imagenode = new stdClass;

                            $imagenode->src     = $urlBase . $image->file_url_thumb;
                            $imagenode->title   = $row->product_name;
                            $imagenode->license = $params['product_image_license_url'];

                            $node->images[] = $imagenode;
                        }
                    }
                }

                $osmap->printNode($node);
            }

            $osmap->changeLevel(-1);
        }
    }

    protected static function initialize()
    {
        if (self::$initialized) {
            return;
        }

        $app = JFactory::getApplication();

        if (!class_exists('VmConfig')) {
            require JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php';

            VmConfig::loadConfig();
        }

        JTable::addIncludePath(JPATH_VM_ADMINISTRATOR . '/tables');

        VmConfig::set('llimit_init_FE', 9000);

        $app->setUserState('com_virtuemart.htmlc-1.limit', 9000);
        $app->setUserState('com_virtuemart.htmlc0.limit', 9000);
        $app->setUserState('com_virtuemart.xmlc0.limit' , 9000);

        if (!class_exists('VirtueMartModelCategory')) {
            require JPATH_VM_ADMINISTRATOR . '/models/category.php';
        }

        self::$categoryModel = new VirtueMartModelCategory();

        if (!class_exists('VirtueMartModelProduct')) {
            require JPATH_VM_ADMINISTRATOR . '/models/product.php';
        }

        self::$productModel = new VirtueMartModelProduct();
    }
}
