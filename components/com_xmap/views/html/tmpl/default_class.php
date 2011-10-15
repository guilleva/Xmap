<?php
/**
* @version             $Id$
* @copyright		Copyright (C) 2005 - 2009 Joomla! Vargas. All rights reserved.
* @license		GNU General Public License version 2 or later; see LICENSE.txt
* @author		Guillermo Vargas (guille@vargas.co.cr)
*/

// No direct access
defined('_JEXEC') or die;

require_once(JPATH_COMPONENT.DS.'displayer.php');


class XmapHtmlDisplayer extends XmapDisplayer {

    var $level = -1;
    var $_openList = '';
    var $_closeList = '';
    var $_closeItem = '';
    var $_childs;
    var $_width;
    var $live_site = 0;

    function __construct ($config, $sitemap) {
        $this->view = 'html';
        parent::__construct($config, $sitemap);
        $this->_parent_children=array();
        $this->_last_child=array();
        $this->live_site = substr_replace(JURI::root(), "", -1, 1);

        $user = JFactory::getUser();
    }

    /** 
    * Prints one node of the sitemap
    *
    *
    * @param object $node
    * @return boolean
    */
    function printNode( &$node )
    {

        $out = '';

        if ($this->isExcluded($node->id,$node->uid) && !$this->canEdit) {
            return FALSE;
        }

        // To avoid duplicate children in the same parent
        if ( !empty($this->_parent_children[$this->level][$node->uid]) ) {
            return FALSE;
        }

        //var_dump($this->_parent_children[$this->level]);
        $this->_parent_children[$this->level][$node->uid] = true;

        $out .= $this->_closeItem;
        $out .= $this->_openList;
        $this->_openList = "";

        $out .= '<li>';

        if( !isset($node->browserNav) )
            $node->browserNav = 0;

        if ($node->browserNav != 3) {
            $link = JRoute::_($node->link);
        }

        $node->name = htmlspecialchars($node->name);
        switch( $node->browserNav ) {
            case 1:		// open url in new window
                $ext_image = '';
                if ( $this->sitemap->params->get('exlinks') ) {
                    $ext_image = '&nbsp;<img src="'. $this->live_site .'/components/com_xmap/assets/images/'. $this->sitemap->params->get('exlinks') .'" alt="' . JText::_('COM_XMAP_SHOW_AS_EXTERN_ALT') . '" title="' . JText::_('COM_XMAP_SHOW_AS_EXTERN_ALT') . '" border="0" />';
                }
                $out .= '<a href="'. $link .'" title="'. htmlspecialchars($node->name) .'" target="_blank">'. $node->name . $ext_image .'</a>';
                break;

            case 2:		// open url in javascript popup window
                $ext_image = '';
                if( $this->sitemap->params->get('exlinks') ) {
                    $ext_image = '&nbsp;<img src="'. $this->live_site .'/components/com_xmap/assets/images/'. $this->sitemap->params->get('exlinks') .'" alt="' . JText::_('COM_XMAP_SHOW_AS_EXTERN_ALT') . '" title="' . JText::_('COM_XMAP_SHOW_AS_EXTERN_ALT') . '" border="0" />';
                }
                $out .= '<a href="'. $link .'" title="'. $node->name .'" target="_blank" '. "onClick=\"javascript: window.open('". $link ."', '', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550'); return false;\">". $node->name . $ext_image."</a>";
                break;

            case 3:		// no link
                $out .= '<span>'. $node->name .'</span>';
                break;

            default:	// open url in parent window
                $out .= '<a href="'. $link .'" title="'. $node->name .'">'. $node->name .'</a>';
                break;
        }

        $this->_closeItem = "</li>\n";
        $this->_childs[$this->level]++;
        echo $out;

        if ($this->canEdit) {
            if ( $this->isExcluded($node->id,$node->uid) ) {
                $img = '<img src="'.$this->live_site.'/components/com_xmap/assets/images/unpublished.png" alt="v" title="'.JText::_('JUNPUBLISHED').'">';
                $class= 'xmapexclon';
            } else {
                $img = '<img src="'.$this->live_site.'/components/com_xmap/assets/images/tick.png" alt="x" title="'.JText::_('JPUBLISHED').'" />';
                $class= 'xmapexcloff';
            }
            echo ' <a href= "#" class="xmapexcl '.$class.'" rel="{uid:\''.$node->uid.'\',itemid:'.$node->id.'}">'.$img.'</a>';
        }
        $this->count++;

        $this->_last_child[$this->level] = $node->uid;

        return TRUE;
    }

    /**
    * Moves sitemap level up or down
    */
    function changeLevel( $level ) {
        if ( $level > 0 ) {
            # We do not print start ul here to avoid empty list, it's printed at the first child
            $this->level += $level;
            $this->_childs[$this->level]=0;
            $this->_openList = "\n<ul class=\"level_".$this->level."\">\n";
            $this->_closeItem = '';

            // If we are moving up, then lets clean the children of this level
            // because for sure this is a new set of links
            if ( empty ($this->_last_child[$this->level-1]) || empty ($this->_parent_children[$this->level]['parent']) || $this->_parent_children[$this->level]['parent'] != $this->_last_child[$this->level-1] ) {
                $this->_parent_children[$this->level]=array();
                $this->_parent_children[$this->level]['parent'] = @$this->_last_child[$this->level-1];
            }
        } else {
            if ($this->_childs[$this->level]){
                echo $this->_closeItem."</ul>\n";
            }
            $this->_closeItem ='</li>';
            $this->_openList = '';
            $this->level += $level;
        }
    }

    /** Print component heading, etc. Then call getHtmlList() to print list */
    function startOutput(&$menus,&$config) {
        $sitemap = &$this->sitemap;

        $menu = &JTable::getInstance('Menu');
        $menu->load( $Itemid );			// Load params for the Xmap menu-item
        $params = new JParameter($menu->params);
        $title = $params->get('page_title',$menu->name);

        $exlink[0] = $sitemap->exlinks;		// image to mark popup links
        $exlink[1] = $sitemap->ext_image;

        if( $sitemap->columns > 1 ) {		// calculate column widths
            $total = count($menus);
            $columns = $total < $sitemap->columns ? $total : $sitemap->columns;
            $this->_width	= (100 / $columns) - 1;
        }
        echo '<div class="'. $sitemap->classname .'">';

        if ( $params->get( 'show_page_title' ) ) {
            echo '<div class="componentheading">'.$title.'</div>';
        }
        echo '<div class="contentpaneopen"'. ($sitemap->columns > 1 ? ' style="float:left;width:100%;"' : '') .'>';


    }

    /** Print component heading, etc. Then call getHtmlList() to print list */
    function endOutput(&$menus) {
        $sitemap = &$this->sitemap;

        echo '<div style="clear:left"></div>';
        //BEGIN: Advertisement
        if( $sitemap->includelink ) {
            echo "<div style=\"text-align:center;\"><a href=\"http://joomla.vargas.co.cr\" style=\"font-size:10px;\">Powered by Xmap!</a></div>";
        }
        //END: Advertisement

        echo "</div>";
        echo "</div>\n";
    }

    function startMenu(&$menu) {
        if( $this->sitemap->params->get('columns') > 1 )			// use columns
            echo '<div style="float:left;width:'.$this->_width.'%;">';
        if( $this->sitemap->params->get('show_menutitle') )			// show menu titles
            echo '<h2 class="menutitle">'.$menu->name.'</h2>';
    }

    function endMenu(&$menu) {
        $sitemap=&$this->sitemap;
        $this->_closeItem='';
        if( $sitemap->params->get('columns')> 1 ) {
            echo "</div>\n";
        }
    }
}
