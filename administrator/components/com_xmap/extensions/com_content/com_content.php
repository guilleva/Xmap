<?php
/**
 * $Id$
 * $LastChangedDate: 2009-08-01 20:56:12 -0600 (Sat, 01 Aug 2009) $
 * $LastChangedBy: guilleva $
 * Xmap by Guillermo Vargas
 * a sitemap component for Joomla! CMS (http://www.joomla.org)
 * Author Website: http://joomla.vargas.co.cr
 * Project License: GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/


defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'router.php');

/** Handles standard Joomla Content */
class xmap_com_content {

	/*
	* This function is called before a menu item is printed. We use it to set the
	* proper uniqueid for the item
	*/
	function prepareMenuItem(&$node) {

                $link_query = parse_url( $node->link );
                parse_str( html_entity_decode($link_query['query']), $link_vars);
                $view = JArrayHelper::getValue($link_vars,'view','');
                $layout = JArrayHelper::getValue($link_vars,'layout','');
                $id = JArrayHelper::getValue($link_vars,'id',0);

		switch( $view ) {
			case 'category':
				if ( $id ) {
					$node->uid = 'com_contentc'.$id;
				} else {
					$node->uid = 'com_content'.$layout;
				}
				$node->expandible=true;
				break;
			case 'article':
				$node->uid = 'com_contenta'.$id;
				$node->expandible=false;
		}
	}

	/** return a node-tree */
	function &getTree(&$xmap, &$parent, &$params) {
		$db	=& JFactory::getDBO();
		$user	=& JFactory::getUser();
		$result = null;

                $link_query = parse_url( $parent->link );
                parse_str( html_entity_decode($link_query['query']), $link_vars);
		$view = JArrayHelper::getValue($link_vars,'view','');
		$id = intval(JArrayHelper::getValue($link_vars,'id',''));

		/***
		* Parameters Initialitation
		**/
		//----- Set expand_categories param
		$expand_categories = JArrayHelper::getValue($params,'expand_categories',1);
		$expand_categories = ( $expand_categories == 1
				  || ( $expand_categories == 2 && $xmap->view == 'xml')
				  || ( $expand_categories == 3 && $xmap->view == 'html')
				  ||   $xmap->view == 'navigator');
		$params['expand_categories'] = $expand_categories;

		//----- Set show_unauth param
		$show_unauth = JArrayHelper::getValue($params,'show_unauth',1);
		$show_unauth = ( $show_unauth == 1
				  || ( $show_unauth == 2 && $xmap->view == 'xml')
				  || ( $show_unauth == 3 && $xmap->view == 'html'));
		$params['show_unauth'] = $show_unauth;

		//----- Set cat_priority and cat_changefreq params
		$priority = JArrayHelper::getValue($params,'cat_priority',$parent->priority);
		$changefreq = JArrayHelper::getValue($params,'cat_changefreq',$parent->changefreq);
		if ($priority  == '-1')
			$priority = $parent->priority;
		if ($changefreq  == '-1')
			$changefreq = $parent->changefreq;

		$params['cat_priority'] = $priority;
		$params['cat_changefreq'] = $changefreq;

		//----- Set art_priority and art_changefreq params
		$priority = JArrayHelper::getValue($params,'art_priority',$parent->priority);
		$changefreq = JArrayHelper::getValue($params,'art_changefreq',$parent->changefreq);
		if ($priority  == '-1')
			$priority = $parent->priority;
		if ($changefreq  == '-1')
			$changefreq = $parent->changefreq;

		$params['art_priority'] = $priority;
		$params['art_changefreq'] = $changefreq;

		$params['max_art'] = intval(JArrayHelper::getValue($params,'max_art',0));
		$params['max_art_age'] = intval(JArrayHelper::getValue($params,'max_art_age',0));

		$params['nullDate']	= $db->Quote($db->getNullDate());

		$params['nowDate']	= $db->Quote(JFactory::getDate()->toMySQL());
		$params['groups']	= implode(',', $user->authorisedLevels());

		switch( $view ) {
			case 'category':
				if ( !$id ) {
					$id = intval(JArrayHelper::getValue($params,'id',0));
				}
				if( $params['expand_categories'] && $id ) {
					$result = xmap_com_content::expandCategory( $xmap, $parent, $id, $params, $menuparams );
				}
			break;
			case 'categories':
				if( $params['expand_categories'] ) {
					$result = xmap_com_content::expandCategory( $xmap, $parent, 1, $params, $menuparams );
				}
			break;
			case 'article':
				$db = & JFactory::getDBO();
				$db->setQuery("SELECT UNIX_TIMESTAMP(modified) modified, UNIX_TIMESTAMP(created) created FROM #__content WHERE id=". $id);
				$item = $db->loadObject();
				if ( $item->modified ) {
					$item->modified = $item->created;
				}
			break;
		}
		return $result;
	}


	/**
	 * Get all content items within a content category.
	 * Returns an array of all contained content items.
	 *
	 *
	 */
	function expandCategory(&$xmap, &$parent, $catid, &$params, &$menuparams) {
		$db = & JFactory::getDBO();

		$orderby = 'a.lft';
		$query = 'SELECT a.id, a.title, a.alias, a.access, a.path AS route, UNIX_TIMESTAMP(a.created_time) created, UNIX_TIMESTAMP(a.modified_time) modified '.
		         'FROM #__categories AS a '.
		         'WHERE a.parent_id = '.$catid.' AND a.published = 1 AND a.extension=\'com_content\' ' .
			 (!$params['show_unauth']? ' AND a.access IN ('.$params['groups'].') ' : '').
			 ( $xmap->view != 'xml'?"\n ORDER BY ". $orderby ."": '' );


		$db->setQuery( $query );
		// echo $db->getQuery();
		$db->getQuery(  );
		$items = $db->loadObjectList();

		if ( count($items) > 0 ) {
			$xmap->changeLevel(1);
			foreach($items as $item) {
				$node = new stdclass();
				$node->id = $parent->id;
				$node->uid = $parent->uid.'c'.$item->id;
				$node->browserNav = $parent->browserNav;
				$node->priority = $params['cat_priority'];
				$node->changefreq = $params['cat_changefreq'];
				$node->name = $item->title;
				$node->expandible = true;
				// TODO: Should we include category name or metakey here?
				// $node->keywords = $item->metakey;
				$node->newsItem = 1;

				// For the google news we should use te publication date instead
				// the last modification date. See
				if ( $xmap->isNews || !$item->modified )
					$item->modified = $item->created;

				$node->slug	= $item->route ? ($item->id.':'.$item->route) : $item->id;
				$node->link	= ContentRoute::category($node->slug);
				if ( $xmap->printNode($node) ) {
					xmap_com_content::expandCategory($xmap, $parent, $item->id,$params, $menuparams);
				}
	    		}
			$xmap->changeLevel(-1);
	    	}

		// Include Category's content
		xmap_com_content::includeCategoryContent($xmap, $parent, $catid,$params, $menuparams);
	    	return true;
	}

	/**
	 * Get all content items within a content category.
	 * Returns an array of all contained content items.
	 *
	 *
	 */
	function includeCategoryContent(&$xmap, &$parent, $catid, &$params, &$menuparams) {
		$db = & JFactory::getDBO();
		$orderby = !empty($menuparams['orderby']) ?  $menuparams['orderby'] : (!empty($menuparams['orderby_sec'])? $menuparams['orderby_sec'] : 'rdate' );
		$orderby = xmap_com_content::orderby_sec( $orderby );

		$query = 'SELECT a.id, a.title, a.alias, a.title_alias, UNIX_TIMESTAMP(a.created) created, UNIX_TIMESTAMP(a.modified) modified, c.path AS category_route '.
		         'FROM #__content AS a '.
		         'LEFT JOIN #__categories AS c ON c.id = a.catid '.
		         'WHERE a.catid = '.$catid.' AND a.state = 1 AND ' .
		         '      (a.publish_up = '.$params['nullDate'].' OR a.publish_up <= '.$params['nowDate'].') AND '.
		         '      (a.publish_down = '.$params['nullDate'].' OR a.publish_down >= '.$params['nowDate'].') '.
			 ( ($params['max_art_age'] || $xmap->isNews) ? "\n AND ( a.created >= '".date('Y-m-d H:i:s',time() - (($xmap->isNews && ($params['max_art_age'] > 3 || !$params['max_art_age']))? 3 : $params['max_art_age']) *86400)."' ) " : '').
			 (!$params['show_unauth']? ' AND a.access IN ('.$params['groups'].') ' : '').
			 ( $xmap->view != 'xml'?"\n ORDER BY $orderby  ": '' ).
			 ( $params['max_art'] ? "\n LIMIT {$params['max_art']}" : '');


		$db->setQuery( $query );
		// echo $db->getQuery();
		$items = $db->loadObjectList();

		if ( count($items) > 0 ) {
			$xmap->changeLevel(1);
			foreach($items as $item) {
				$node = new stdclass();
				$node->id = $parent->id;
				$node->uid = $parent->uid.'a'.$item->id;
				$node->browserNav = $parent->browserNav;
				$node->priority = $params['art_priority'];
				$node->changefreq = $params['art_changefreq'];
				$node->name = $item->title;
				$node->expandible = false;
				// TODO: Should we include category name or metakey here?
				// $node->keywords = $item->metakey;
				$node->newsItem = 1;

				// For the google news we should use te publication date instead
				// the last modification date. See
				if ( $xmap->isNews || !$item->modified )
					$item->modified = $item->created;

				$node->slug		= $item->alias ? ($item->id.':'.$item->alias) : $item->id;
				$node->catslug		= $item->category_route ? ($catid.':'.$item->category_route) : $catid;
				$node->link = ContentRoute::article($node->slug, $node->catslug);
				$xmap->printNode($node);
	    		}
			$xmap->changeLevel(-1);
	    	}
	    	return true;
	}

	/***************************************************/
	/* copied from /components/com_content/content.php */
	/***************************************************/

	/** translate primary order parameter to sort field */
	function orderby_pri( $orderby ) {
		switch ( $orderby ) {
			case 'alpha':
				$orderby = 'cc.title, ';
				break;

			case 'ralpha':
				$orderby = 'cc.title DESC, ';
				break;

			case 'order':
				$orderby = 'cc.ordering, ';
				break;

			default:
				$orderby = '';
				break;
		}

		return $orderby;
	}

	/** translate secondary order parameter to sort field */
	function orderby_sec( $orderby ) {
		switch ( $orderby ) {
			case 'date':
				$orderby = 'a.created';
				break;

			case 'rdate':
				$orderby = 'a.created DESC';
				break;

			case 'alpha':
				$orderby = 'a.title';
				break;

			case 'ralpha':
				$orderby = 'a.title DESC';
				break;

			case 'hits':
				$orderby = 'a.hits';
				break;

			case 'rhits':
				$orderby = 'a.hits DESC';
				break;

			case 'order':
				$orderby = 'a.ordering';
				break;

			case 'author':
				$orderby = 'a.created_by_alias, u.name';
				break;

			case 'rauthor':
				$orderby = 'a.created_by_alias DESC, u.name DESC';
				break;

			case 'front':
				$orderby = 'f.ordering';
				break;

			default:
				$orderby = 'a.ordering';
				break;
		}

		return $orderby;
	}
	/** @param int 0 = Archives, 1 = Section, 2 = Category */
	function where( $type=1, &$access, &$noauth, $gid, $id, $now=NULL, $year=NULL, $month=NULL ) {
		$db = & JFactory::getDBO();

		$nullDate = $db->getNullDate();
		$where = array();

		// normal
		if ( $type > 0) {
			$where[] = "a.state = '1'";
			if ( !$access->canEdit ) {
				$where[] = "( a.publish_up = '$nullDate' OR a.publish_up <= '$now' )";
				$where[] = "( a.publish_down = '$nullDate' OR a.publish_down >= '$now' )";
			}
			if ( $noauth ) {
				$where[] = "a.access <= $gid";
			}
			if ( $id > 0 ) {
				if ( $type == 1 ) {
					$where[] = "a.sectionid IN ( $id ) ";
				} else if ( $type == 2 ) {
					$where[] = "a.catid IN ( $id ) ";
				}
			}
		}

		// archive
		if ( $type < 0 ) {
			$where[] = "a.state='-1'";
			if ( $year ) {
				$where[] = "YEAR( a.created ) = '$year'";
			}
			if ( $month ) {
				$where[] = "MONTH( a.created ) = '$month'";
			}
			if ( $noauth ) {
				$where[] = "a.access <= $gid";
			}
			if ( $id > 0 ) {
				if ( $type == -1 ) {
					$where[] = "a.sectionid = $id";
				} else if ( $type == -2) {
					$where[] = "a.catid = $id";
				}
			}
		}

		return $where;
	}
}
