<?php
/**
* @author Guillermo Vargas guille@vargas.co.cr
* @version $Id$
* @package xmap
* @license GNU/GPL
* @authorSite http://www.jooxmap.com
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

/** Adds support for SobiPro categories to Xmap */
class xmap_com_sobipro {

    static $sectionConfig = array();
    /*
    * This function is called before a menu item is printed. We use it to set the
    * proper uniqueid for the item and indicate whether the node is expandible or not
    */
    function prepareMenuItem($node, &$params) {
        $link_query = parse_url( $node->link );
        parse_str( html_entity_decode($link_query['query']), $link_vars);
        $sid = JArrayHelper::getValue($link_vars,'sid',0);

        $db = JFactory::getDbo();
        $db->setQuery('SELECT * FROM `#__sobipro_object` where id='.(int)$sid);
        $row = $db->loadObject();

        $node->uid = 'com_sobiproo'.$sid;
        if ( $row->oType == 'section' || $row->oType == 'category' ) {
            $node->expandible = true;
        } else {
            $node->expandible = false;
        }
    }

    /** Get the content tree for this kind of content */
    function getTree( $xmap, $parent, &$params ) {

        if ($xmap->isNews) // This component does not provide news content. don't waste time/resources
            return false;

        if (!self::loadSobi()){
            return;
        }

        $link_query = parse_url( $parent->link );
        parse_str( html_entity_decode($link_query['query']), $link_vars);
        $sid =JArrayHelper::getValue($link_vars,'sid',1);
        $task =JArrayHelper::getValue($link_vars,'task', null);

        if (in_array($task, array('search', 'entry.add'))) {
            return;
        }

        $db = JFactory::getDbo();
        $db->setQuery('SELECT * FROM `#__sobipro_object` where id='.(int)$sid);
        $object = $db->loadObject();

        if ($object->oType == 'entry') {
            return;
        } elseif ( $object->oType == 'category' ) {
            $sectionId = self::findCategorySection($object->parent);
        } else {
            $sectionId = $sid;
        }
        self::$sectionConfig = self::getSectionConfig($sectionId);


        $include_entries =JArrayHelper::getValue($params,'include_entries',1);
        $include_entries = ( $include_entries == 1
            || ( $include_entries == 2 && $xmap->view == 'xml')
            || ( $include_entries == 3 && $xmap->view == 'html')
            ||   $xmap->view == 'navigator');
        $params['include_entries'] = $include_entries;

        $priority =JArrayHelper::getValue($params,'cat_priority',$parent->priority);
        $changefreq =JArrayHelper::getValue($params,'cat_changefreq',$parent->changefreq);

        if ($priority  == '-1')
            $priority = $parent->priority;
        if ($changefreq  == '-1')
            $changefreq = $parent->changefreq;

        $params['cat_priority'] = $priority;
        $params['cat_changefreq'] = $changefreq;

        $priority =JArrayHelper::getValue($params,'entry_priority',$parent->priority);
        $changefreq =JArrayHelper::getValue($params,'entry_changefreq',$parent->changefreq);

        if ($priority  == '-1')
            $priority = $parent->priority;
        if ($changefreq  == '-1')
            $changefreq = $parent->changefreq;

        $params['entry_priority'] = $priority;
        $params['entry_changefreq'] = $changefreq;

        $date = JFactory::getDate();
        $params['now'] = $date->toMySql();

        if ( $include_entries ) {
            $ordering = JArrayHelper::getValue($params,'entries_order','b.position');
            $orderdir = JArrayHelper::getValue($params,'entries_orderdir','ASC');
            if ( !in_array($ordering,array('b.position','a.counter','b.validSince','a.updatedTime')) ){
                $ordering = 'b.position';
            }
            if ( !in_array($orderdir,array('ASC','DESC')) ){
                $orderdir = 'ASC';
            }
            $params['ordering'] = $ordering. ' '. $orderdir;

            $params['limit'] = '';
            $params['days'] = '';
            $limit = JArrayHelper::getValue($params,'max_entries','');
            if ( intval($limit) )
                $params['limit'] = ' LIMIT '.$limit;

            $days = JArrayHelper::getValue($params,'max_age','');
            if ( intval($days) )
                $params['days'] = ' AND a.publish_up >=\''.strftime("%Y-%m-%d %H:%M:%S",$xmap->now - ($days*86400)) ."' ";
        }

        xmap_com_sobipro::getCategoryTree($xmap, $parent, $sid, $params);
    }

    /** SobiPro support */
    function getCategoryTree( $xmap, $parent, $sid, &$params ) {
        $database =& JFactory::getDBO();

        $query  =
             "SELECT a.id,a.nid, a.name, b.pid as pid "
            ."\n FROM #__sobipro_object AS a, #__sobipro_relations AS b "
            ."\n WHERE a.parent=$sid"
            ."   AND a.oType='category'"
            ."   AND b.oType=a.oType"
            ."   AND a.state=1 "
            ."   AND a.approved=1 "
            ."\n AND a.id=b.id "
            ."\n ORDER BY b.position ASC";

        $database->setQuery( $query );
        $rows = $database->loadObjectList();

        $modified = time();
        $xmap->changeLevel(1);
        foreach($rows as $row) {
            $node = new stdclass;
            $node->id = $parent->id;
            $node->uid = 'com_sobiproc'.$row->id; // Unique ID
            $node->browserNav = $parent->browserNav;
            $node->name = html_entity_decode($row->name);
            $node->modified = $modified;
            #$node->link = 'index.php?option=com_sobipro&sid='.$row->id.':'.trim( SPLang::urlSafe( $row->name ) ).'&Itemid='.$parent->id;
            $node->link = SPJoomlaMainFrame::url( array('sid' => $row->id, 'title' => $row->name), false, false );
            $node->priority = $params['cat_priority'];
            $node->changefreq = $params['cat_changefreq'];
            $node->expandible = true;
            $node->secure = $parent->secure;
            if ( $xmap->printNode($node) !== FALSE ) {
                xmap_com_sobipro::getCategoryTree($xmap, $parent, $row->id, $params);
            }
        }

        if ( $params['include_entries'] ) {
            $query  =
                 "SELECT a.id, c.baseData as name,a.updatedTime as modified,b.validSince as publish_up, b.pid as catid  "
                ."\n FROM #__sobipro_object AS a, #__sobipro_relations AS b, #__sobipro_field_data c"
                ."\n WHERE a.state=1 "
                ."\n AND a.id=b.id "
                ."\n AND b.oType = 'entry'"
                ."\n AND b.pid = $sid"
                ."\n AND a.approved=1 "
                ."\n AND (a.validUntil>='{$params['now']}' or a.validUntil='0000-00-00 00:00:00' ) "
                ."\n AND (a.validSince<='{$params['now']}' or a.validSince='0000-00-00 00:00:00' ) "
                ."\n AND a.id=c.sid AND c.fid=".self::$sectionConfig['name_field']->sValue
                ."\n AND c.section=".self::$sectionConfig['name_field']->section
                . $params['days']
                ."\n ORDER BY " . $params['ordering']
                . $params['limit'];

            $database->setQuery( $query );
            $rows = $database->loadObjectList();
            foreach($rows as $row) {
                $node = new stdclass;
                $node->id = $parent->id;
                $node->uid = 'com_sobiproe'.$row->id; // Unique ID
                $node->browserNav = $parent->browserNav;
                $node->name = html_entity_decode($row->name);
                $node->modified = $row->modified? $row->modified : $row->publish_up;
                $node->priority = $params['entry_priority'];
                $node->changefreq = $params['entry_changefreq'];
                $node->expandible = false;
                $node->secure = $parent->secure;
                # $node->link = 'index.php?option=com_sobipro&pid='.$row->catid . '&sid=' . $row->id.':'.trim( SPLang::urlSafe( $row->name )).'&Itemid='.$parent->id;
                $node->link = SPJoomlaMainFrame::url( array('sid' => $row->id, 'pid' => $row->catid, 'title' => $row->name), false, false );
                $xmap->printNode($node);
            }

        }
        $xmap->changeLevel(-1);
    }

    static protected function getSectionConfig($sectionId)
    {
        $db = JFactory::getDbo();
        $db->setQuery('SELECT * FROM `#__sobipro_config` where section='.(int)$sectionId);
        return $db->loadObjectList('sKey');
    }

    static protected function loadSobi()
    {
        if (defined('SOBI_TESTS')) {
            return true;
        }
        define( 'SOBI_TESTS', false );
        $ver = new JVersion();
        $ver = str_replace( '.', null, $ver->RELEASE );
        // added by Pierre Burri-Wittke globeall.de
        if ($ver > '15') { $ver = '16'; }
        define( 'SOBI_CMS', 'joomla'. $ver );
        define( 'SOBIPRO', true );
        define( 'SOBI_TASK', 'task' );
        define( 'SOBI_DEFLANG', JFactory::getLanguage()->getDefault() );
        define( 'SOBI_ACL', 'front' );
        define( 'SOBI_ROOT', JPATH_ROOT );
        define( 'SOBI_MEDIA', implode( '/', array( JPATH_ROOT, 'media', 'sobipro' ) ) );
        define( 'SOBI_MEDIA_LIVE', JURI::root().'/media/sobipro' );
        define( 'SOBI_PATH', SOBI_ROOT.'/components/com_sobipro' );
        if (!file_exists(SOBI_PATH.'/lib/base/fs/loader.php')) {
           return false;
        }
        require_once SOBI_PATH.'/lib/base/fs/loader.php';
        SPLoader::loadClass( 'sobi' );
        SPLoader::loadClass( 'base.request' );
        SPLoader::loadClass( 'base.object' );
        SPLoader::loadClass( 'base.factory' );
        SPLoader::loadClass( 'base.mainframe' );
        // added by Pierre Burri-Wittke globeall.de
        SPLoader::loadClass( 'base.const' );
        SPLoader::loadClass( 'cms.base.mainframe' );
        SPLoader::loadClass( 'cms.base.lang' );
        return true;
    }

    static protected function findCategorySection($sid)
    {
        $db = JFactory::getDbo();
        $db->setQuery('SELECT id,parent,oType FROM `#__sobipro_object` where id='.(int)$sid);
        $row = $db->loadObject();
        if ($row->oType == 'section') {
            return $row->id;
        } else {
            return self::findCategorySection($row->parent);
        }
    }
}
