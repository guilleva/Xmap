<?php

/**
 * @version		$Id: install.xmap.php 39 2011-07-15 02:57:47Z guille $
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
    }

    /**
     * Function called when the component is updated
     *
     * @param JInstallerComponent $adapter
     */
    function update($adapter)
    {
        echo '<h2>', JText::_("XMAP_UPGRADING_XMAP"), '</h2>';
    }


    /**
     * Function called when the component is uninstalled
     *
     * @param JInstallerComponent $adapter
     */
    function uninstall($adapter)
    {

    }

}
