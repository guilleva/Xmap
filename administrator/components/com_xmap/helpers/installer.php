<?php
/**
 * @version             $Id$
 * @copyright			Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
 */
// no direct access
defined('_JEXEC') or die;
jimport('joomla.installer.installer');
jimport('joomla.installer.helper');

$lang = JFactory::getLanguage();
$lang->load('com_installer', JPATH_ADMINISTRATOR);

class XmapInstaller extends JInstaller
{

    public function __construct($basepath, $classprefix=null, $adapterfolder=null)
    {
        JAdapter::__construct(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_xmap', 'JInstaller');
    }

    /**
     * Returns a reference to the Xmap Installer object, only creating it
     * if it doesn't already exist.
     *
     * @static
     * @return      object  An installer object
     */
    public static function getInstance()
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new XmapInstaller('');
        }
        return $instance;
    }

}