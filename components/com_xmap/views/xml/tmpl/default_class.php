<?php
/**
 * @version		 $Id$
 * @copyright		Copyright (C) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @author		Guillermo Vargas (guille@vargas.co.cr)
 */
	
// No direct access
defined('_JEXEC') or die;
		
require_once(JPATH_COMPONENT.DS.'displayer.php');


class XmapXmlDisplayer extends XmapDisplayer {
	var $_links;
	var $view = 'xml';
	var $doCompression=1;
	var $isNews=0;

	function XmapXML (&$config, &$sitemap) {
		parent::__construct($config, $sitemap);
		$this->uids = array();
	}

	/** 
	 * Prints an XML node for the sitemap
	 *
	 *
	 */
	function printNode( &$node ) {
		global $Itemid;

		if ($this->isNews && (!isset($node->newsItem) || !$node->newsItem) ) {
			return true;
		}

		static $live_site,$len_live_site;
		if ( !isset($live_site) ) {
			$live_site = substr_replace(JURI::root(), "", -1, 1);
			$len_live_site = strlen( $live_site );
		}

		$out = '';

		$link = JRoute::_($node->link,true,-1);

		$is_extern = ( 0 != strcasecmp( substr($link, 0, $len_live_site), $live_site ) );

		if ( !isset($node->browserNav) )
			$node->browserNav = 0;

		if ( $node->browserNav != 3			// ignore "no link"
		     && !$is_extern					// ignore external links
		     && empty($this->_links[$link]) ) {	// ignore links that have been added already

			$this->_count++;
		 	$this->_links[$link] = 1;


			if( !isset($node->priority) )
				$node->priority = "0.5";

			if( !isset($node->changefreq) )
				$node->changefreq = 'daily';

			$changefreq = $this->getProperty('changefreq',$node->changefreq,$node->id,'xml',$node->uid);
			$priority   = $this->getProperty('priority',$node->priority,$node->id,'xml',$node->uid);

			echo '<url>'."\n";
			echo '<loc>', $link ,'</loc>'."\n";
			if ($this->_isAdmin) {
				echo '<uid>', $node->uid ,'</uid>'."\n";
				echo '<itemid>', $node->id ,'</itemid>'."\n";
			}
			$timestamp = (isset($node->modified) && $node->modified != FALSE && $node->modified != -1) ? $node->modified : time();
			$modified = gmdate('Y-m-d\TH:i:s\Z', $timestamp);
			if ( !$this->isNews ) {
				echo '<lastmod>',$modified,'</lastmod>'."\n";
   				echo '<changefreq>',$changefreq,'</changefreq>'."\n";
				echo '<priority>',$priority,'</priority>'."\n";
			} else {
				if ( isset($node->keywords) ) {
					# $keywords = str_replace(array('&amp;','&'),array('&','&amp;'),$node->keywords);
					# $keywords = str_replace('&','&amp;',$node->keywords);
					$keywords =  htmlspecialchars($node->keywords);
				} else {
					$keywords = '';
				}

				echo "<news:news>\n";
   				echo '<news:publication_date>',$modified,'</news:publication_date>'."\n";
				if ( $keywords ) {
					echo '<news:keywords>',$keywords,'</news:keywords>'."\n";
				}
				echo "</news:news>\n";
			}
 			echo '</url>',"\n";
		}else{
			return empty($this->_links[$link]);
		}
		return true;
	}

	function escapeURL($str) {
		static $xTrans;
		if (!isset($xTrans)) {
		$xTrans = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);
		foreach ($xTrans as $key => $value)
			$xTrans[$key] = '&#'.ord($key).';';
		// dont translate the '&' in case it is part of &xxx;
		$xTrans[chr(38)] = '&';
		}   
		return preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,4};)/","&amp;" , strtr($str, $xTrans));
	}

	function getProperty($property, $value, $Itemid, $view, $uid)
	{
		return $value;
	}

	function changeLevel($level) {
		return true;
	}

	function startMenu(&$menu) {
		return true;
	}

	function endMenu(&$menu) {
		return true;
	}

}
