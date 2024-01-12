<?php
/**
 * @version     $Id$
 * @copyright   Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Guillermo Vargas (guille@vargas.co.cr)
 */


// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Version as JVersion;
use Joomla\CMS\HTML\Helpers\Sidebar as JHtmlSidebar;
use Joomla\CMS\Language\Text as JText; 	 

/**
 * Xmap component helper.
 *
 * @package     Xmap
 * @subpackage  com_xmap
 * @since       2.0
 */
class XmapHelper
{
    /**
     * Configure the Linkbar.
     *
     * @param    string  The name of the active view.
     */
    public static function addSubmenu($vName)
    {
        $version = new JVersion;

        if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
            JSubMenuHelper::addEntry(
                JText::_('Xmap_Submenu_Sitemaps'),
                'index.php?option=com_xmap',
                $vName == 'sitemaps'
            );
            JSubMenuHelper::addEntry(
                JText::_('Xmap_Submenu_Extensions'),
                'index.php?option=com_plugins&view=plugins&filter_folder=xmap',
                $vName == 'extensions');
        } else {
            JHtmlSidebar::addEntry(
                JText::_('Xmap_Submenu_Sitemaps'),
                'index.php?option=com_xmap',
                $vName == 'sitemaps'
            );
            JHtmlSidebar::addEntry(
                JText::_('Xmap_Submenu_Extensions'),
                'index.php?option=com_plugins&view=plugins&filter_folder=xmap',
                $vName == 'extensions');
        }
    }
	
	public static function getpost() {
		if (version_compare(JVERSION, '4.0', 'ge')){
			return JFactory::getApplication()->input->getArray(array());
		}
		else {
			return call_user_func_array('XmapHelper::get', ['post']);
		}
	}
	
	public static function get(...$params) {
		if (version_compare(JVERSION, '4.0', 'ge')){
			if ($params[0] == 'post '){
				return JFactory::getApplication()->input->getInputForRequestMethod('POST');
			} else {
				return call_user_func_array(array(JFactory::getApplication()->input, 'get'), $params);
			}
		}
		else {
			return call_user_func_array('XmapHelper::get', $params);
		}
	}
	
	public static function getVar(...$params) {
		if (version_compare(JVERSION, '4.0', 'ge')){
			return call_user_func_array(array(JFactory::getApplication()->input, 'getVar'), $params);
		}
		else {
			return call_user_func_array('XmapHelper::getVar', $params);
		}
	}
	

	public static function setVar(...$params) {
		if (version_compare(JVERSION, '4.0', 'ge')){
			call_user_func_array(array(JFactory::getApplication()->input, 'setVar'), $params);
		}
		else {
			call_user_func_array('XmapHelper::setVar', $params);
		}
	}

	public static function getCmd(...$params) {
		if (version_compare(JVERSION, '4.0', 'ge')){
			return call_user_func_array(array(JFactory::getApplication()->input, 'getCmd'), $params);
		}
		else {
			return call_user_func_array('XmapHelper::getCmd', $params);
		}
	}

	public static function getInt(...$params) {
		if (version_compare(JVERSION, '4.0', 'ge')){
			$recordId = call_user_func_array(array(JFactory::getApplication()->input, 'getInt'), $params);
		}
		else {
			$recordId	= (int)call_user_func_array('XmapHelper::getInt', $params);
		}
	}
	
	
	public static function getBool(...$params) {
		if (version_compare(JVERSION, '4.0', 'ge')){
			return call_user_func_array(array(JFactory::getApplication()->input, 'getBool'), $params);
		}
		else {
			return (int)call_user_func_array('XmapHelper::getBool', $params);
		}
	}
	public static function getWord(...$params) {
		if (version_compare(JVERSION, '4.0', 'ge')){
			return call_user_func_array(array(JFactory::getApplication()->input, 'getWord'), $params);
		}
		else {
			return (int)call_user_func_array('XmapHelper::getWord', $params);
		}
	}
	
	public static function getURI() {
		if (version_compare(JVERSION, '4.0', 'ge')){
			return JUri::getInstance();
		}
		else {
			return JFactory::getURI();
		}
	}
	
	public static function getShortVersion() {
		return implode(".", array_slice(explode(".", JVERSION), 0,3));
	}
}
