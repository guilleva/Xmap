<?php
/**
 * @author Guillermo Vargas, http://joomla.vargas.co.cr
 * @email guille@vargas.co.cr
 * @version $Id: com_virtuemart.php 154 2011-04-09 23:44:12Z guilleva $
 * @package Xmap
 * @license GNU/GPL
 * @description Xmap plugin for Virtuemart component
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/** Adds support for Virtuemart categories to Xmap */
class xmap_com_virtuemart
{
    protected static $categoryModel;
    protected static $productModel;
    protected static $initialized = false;

    static $urlBase;
    /*
     * This function is called before a menu item is printed. We use it to set the
     * proper uniqueid for the item and indicate whether the node is expandible or not
     */

    static function prepareMenuItem($node, &$params)
    {
        $link_query = parse_url($node->link);
        parse_str(html_entity_decode($link_query['query']), $link_vars);
        $catid = JArrayHelper::getValue($link_vars, 'virtuemart_category_id', 0);
        $prodid = JArrayHelper::getValue($link_vars, 'virtuemart_product_id', 0);
        if (!$catid) {
            $menu = & JSite::getMenu();
            $menuParams = $menu->getParams($node->id);
            $catid = $menuParams->get('virtuemart_category_id', 0);
        }
        if (!$prodid) {
            $menu = & JSite::getMenu();
            $menuParams = $menu->getParams($node->id);
            $prodid = $menuParams->get('virtuemart_product_id', 0);
        }
        if ($prodid && $catid) {
            $node->uid = 'com_virtuemartc' . $catid . 'p' . $prodid;
            $node->expandible = false;
        } elseif ($catid) {
            $node->uid = 'com_virtuemartc' . $catid;
            $node->expandible = true;
        }
    }

    /** Get the content tree for this kind of content */
    static function getTree($xmap, $parent, &$params)
    {
        self::initialize();

        $menu = & JSite::getMenu();

        $link_query = parse_url($parent->link);
        parse_str(html_entity_decode($link_query['query']), $link_vars);
        $catid = intval(JArrayHelper::getValue($link_vars, 'virtuemart_category_id', 0));
        $params['Itemid'] = intval(JArrayHelper::getValue($link_vars, 'Itemid', $parent->id));

        $view = JArrayHelper::getValue($link_vars, 'view', '');

        // we currently support only categories
        if (!in_array($view, array('categories','category'))) {
            return true;
        }

        $include_products = JArrayHelper::getValue($params, 'include_products', 1);
        $include_products = ( $include_products == 1
                || ( $include_products == 2 && $xmap->view == 'xml')
                || ( $include_products == 3 && $xmap->view == 'html'));
        $params['include_products'] = $include_products;

        $params['include_product_images'] = (JArrayHelper::getValue($params, 'include_product_images', 1) && $xmap->view == 'xml');
        $params['product_image_license_url'] = trim(JArrayHelper::getValue($params, 'product_image_license_url', ''));

        $priority = JArrayHelper::getValue($params, 'cat_priority', $parent->priority);
        $changefreq = JArrayHelper::getValue($params, 'cat_changefreq', $parent->changefreq);
        if ($priority == '-1')
            $priority = $parent->priority;
        if ($changefreq == '-1')
            $changefreq = $parent->changefreq;

        $params['cat_priority'] = $priority;
        $params['cat_changefreq'] = $changefreq;

        $priority = JArrayHelper::getValue($params, 'prod_priority', $parent->priority);
        $changefreq = JArrayHelper::getValue($params, 'prod_changefreq', $parent->changefreq);
        if ($priority == '-1')
            $priority = $parent->priority;
        if ($changefreq == '-1')
            $changefreq = $parent->changefreq;

        $params['prod_priority'] = $priority;
        $params['prod_changefreq'] = $changefreq;


        xmap_com_virtuemart::getCategoryTree($xmap, $parent, $params, $catid);
        return true;
    }

    /** Virtuemart support */
    static function getCategoryTree($xmap, $parent, &$params, $catid=0)
    {
        $database = &JFactory::getDBO();

        if (!isset($urlBase)) {
            $urlBase = JURI::base();
        }

        $vendorId = 1;
        $cache = & JFactory::getCache('com_virtuemart','callback');
        $children = $cache->call( array( 'VirtueMartModelCategory', 'getChildCategoryList' ),$vendorId, $catid );

        $xmap->changeLevel(1);
        foreach ($children as $row) {
            $node = new stdclass;

            $node->id = $parent->id;
            $node->uid = $parent->uid . 'c' . $row->virtuemart_category_id;
            $node->browserNav = $parent->browserNav;
            $node->name = stripslashes($row->category_name);
            $node->priority = $params['cat_priority'];
            $node->changefreq = $params['cat_changefreq'];
            $node->expandible = true;
            $node->link = 'index.php?option=com_virtuemart&amp;view=category&amp;virtuemart_category_id=' . $row->virtuemart_category_id . '&amp;Itemid='.$parent->id;
            if ($xmap->printNode($node) !== FALSE) {
                xmap_com_virtuemart::getCategoryTree($xmap, $parent, $params, $row->virtuemart_category_id);
            }
        }
        $xmap->changeLevel(-1);

        if ($params['include_products']) {
            $products = self::$productModel->getProductsInCategory($catid);

            if ($params['include_product_images']) {
                self::$categoryModel->addImages($products,1);
            }

            $xmap->changeLevel(1);
            foreach ($products as $row) {
                $node = new stdclass;
                $node->id = $parent->id;
                $node->uid = $parent->uid . 'c' . $row->virtuemart_category_id . 'p' . $row->virtuemart_product_id;
                $node->browserNav = $parent->browserNav;
                $node->priority = $params['prod_priority'];
                $node->changefreq = $params['prod_changefreq'];
                $node->name = $row->product_name;
                $node->modified = strtotime($row->modified_on);
                $node->expandible = false;
                $node->link = 'index.php?option=com_virtuemart&amp;view=productdetails&amp;virtuemart_product_id=' . $row->virtuemart_product_id.'&amp;virtuemart_category_id=' . $row->virtuemart_category_id . '&amp;Itemid='.$parent->id;
                if ($params['include_product_images']) {
                    foreach ($row->images as $image) {
                        if (isset($image->file_url)) {
                            $imagenode = new stdClass;
                            $imagenode->src = $urlBase . $image->file_url_thumb;
                            $imagenode->title = $row->product_name;
                            $imagenode->license = $params['product_image_license_url'];
                            $node->images[] = $imagenode;
                        }
                    }
                }
                $xmap->printNode($node);
            }
            $xmap->changeLevel(-1);
        }
    }

    static protected function initialize()
    {
        if (self::$initialized) return;

        $app = JFactory::getApplication ();

        if (!class_exists( 'VmConfig' )) {
            require(JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php');
            VmConfig::loadConfig();
        }
        JTable::addIncludePath(JPATH_VM_ADMINISTRATOR . '/tables');

        $app->setUserState('com_virtuemart.htmlc0.limit',9000);
        $app->setUserState('com_virtuemart.xmlc0.limit',9000);

        if (!class_exists('VirtueMartModelCategory')) require(JPATH_VM_ADMINISTRATOR . '/models/category.php');
        self::$categoryModel = new VirtueMartModelCategory();

        if (!class_exists('VirtueMartModelProduct')) require(JPATH_VM_ADMINISTRATOR  . '/models/product.php');
        self::$productModel = new VirtueMartModelProduct();
    }

}
