<?php

/**
 * @version		$Id$
 * @copyright   	Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @author		Guillermo Vargas (guille@vargas.co.cr)
 */
class com_xmapInstallerScript
{

    /**
     * Constructor function
     *
     * @param JInstallerComponent $adapter
     */
    function __construct($adapter)
    {
    }

    /**
     * Function called when the component is installed
     *
     * @param JInstallerComponent $adapter
     */
    function install($adapter)
    {
        echo '<h2>', JText::_("XMAP_INSTALLING_XMAP"), '</h2>';
        $this->_discoverAndInstallExtensions();
    }

    /**
     * Function called when the component is updated
     *
     * @param JInstallerComponent $adapter
     */
    function update($adapter)
    {
        echo '<h2>', JText::_("XMAP_UPGRADING_XMAP"), '</h2>';
        $this->_discoverAndInstallExtensions();
    }


    /**
     * Function called when the component is uninstalled
     *
     * @param JInstallerComponent $adapter
     */
    function uninstall($adapter)
    {

        echo '<h2>', JText::_("XMAP_UNISTALLING_XMAP_EXTENSIONS"), '</h2>';
        $db = JFactory::getDBO();

        $query = "SELECT extension_id from `#__extensions` where type='xmap_ext'";
        $db->setQuery($query);
        $extensions = $db->loadObjectList();
        $table = JTable::getInstance('Extension');
        foreach ($extensions as $extension) {
            $table->delete($extension->extension_id);
        }
    }

    /**
     * Search and auto install extensions for the component
     *
     */
    private function _discoverAndInstallExtensions()
    {
        $db = JFactory::getDBO();
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');
        // require_once JPATH_ADMINISTRATOR.DS.'components'.DS.'com_xmap'.DS.'adapters'.DS.'xmap_ext.php';
        require_once JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_xmap' . DS . 'helpers' . DS . 'installer.php';
        $installer = XmapInstaller::getInstance();
		$results = $installer->discover();
		$query = 'SELECT extension_id, element, folder FROM #__extensions where type=\'xmap_ext\'';
		$db->setQuery($query);
		$installedtmp = $db->loadObjectList('folder');
		$extensions = Array();

		foreach ($results as $e) {
			if (!isset($installedtmp[$e->folder])) {
				if ($e->store() && $installer->discover_install($e->extension_id)) {
					echo '<p />' . JText::sprintf('XMAP_INSTALLED_EXTENSION_X', $e->folder);

					// Auto-publish the extension if the component is installed
					$db->setQuery(
							"SELECT extension_id from `#__extensions` "
						   ."where `type`='component' and `name`='{$e->folder}'"
					);
					$id = $db->loadResult();
					// if the component is installed, then publish the extension
					if ($id) {
						$db->setQuery(
								"update `#__extensions` set `enabled`=1, `state`=1 "
							   ."WHERE `extension_id`='{$e->extension_id}'"
						);
						$db->query();
					}
				}
			}
		}
		return;

    }
}