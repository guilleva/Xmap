<?php

/**
 * @version             $Id$
 * @copyright			Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
 */
// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');
jimport('joomla.database.query');
jimport('joomla.installer.installer');
jimport('joomla.installer.helper');

require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'installer.php');

/**
 * Gateways Page Model
 *
 * @package		Xmap
 */
class XmapModelExtensions extends JModelList
{

    /**
     * Model context string.
     *
     * @var		string
     */
    public $_context = 'com_xmap.extensions';

    /**
     * Method to auto-populate the model state.
     *
     * @since	1.6
     */
    protected function _populateState()
    {
        $app = JFactory::getApplication();

        $search = $app->getUserStateFromRequest($this->_context . '.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $app->getUserStateFromRequest($this->_context . '.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        // List state information
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
        $this->setState('list.limit', $limit);

        $limitstart = $app->getUserStateFromRequest($this->_context . '.limitstart', 'limitstart', 0);
        $this->setState('list.limitstart', $limitstart);

        $orderCol = $app->getUserStateFromRequest($this->_context . '.ordercol', 'filter_order', 'a.name');
        $this->setState('list.ordering', $orderCol);

        $orderDirn = $app->getUserStateFromRequest($this->_context . '.orderdirn', 'filter_order_Dir', 'asc');
        $this->setState('list.direction', $orderDirn);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param	string		$id	A prefix for the store id.
     *
     * @return	string		A store id.
     */
    protected function _getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('list.start');
        $id .= ':' . $this->getState('list.limit');
        $id .= ':' . $this->getState('list.ordering');
        $id .= ':' . $this->getState('list.direction');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');

        return md5($id);
    }

    /**
     * Creates the query to get the items from the database
     *
     * @return	string
     */
    protected function getListQuery()
    {
        $db = JFactory::getDBO();
        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
                $this->getState(
                        'list.select',
                        'a.extension_id id,a.name,a.element,a.params,a.enabled,a.folder,a.manifest_cache')
        );
        $query->from('#__extensions AS a');
        $query->where('a.type = \'xmap_ext\'');

        // Filter by state.
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('a.enabled = ' . (int) $published);
        }

        // Filter by search in transaction id or author name
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.extension_id = ' . (int) substr($search, 3));
            }
        }

        // Add the list ordering clause.
        $query->order($this->_db->getEscaped($this->getState('list.ordering', 'a.name')) . ' ' . $this->_db->getEscaped($this->getState('list.direction', 'ASC')));

        //echo nl2br(str_replace('#__','jos_',$query));
        return $query;
    }

    /**
     * Method to get a list of items.
     *
     * @return      mixed   An array of objects on success, false on failure.
     */
    public function getItems()
    {
        $rows = parent::getItems();

        // Get the plugin base path
        $baseDir = JPATH_COMPONENT_ADMINISTRATOR . DS . 'extensions';

        $numRows = count($rows);
        for ($i = 0; $i < $numRows; $i++) {
            $row = & $rows[$i];


            if (strlen($row->manifest_cache)) {
                $data = unserialize($row->manifest_cache);
                if ($data) {
                    foreach ($data as $key => $value) {
                        if ($key == 'type') {
                            // ignore the type field
                            continue;
                        }
                        $row->$key = $value;
                    }
                }
            } else {
                // Get the plugin xml file
                $xmlfile = $baseDir . DS . $row->folder . DS . preg_replace('/^xmap_/', '', $row->element) . ".xml";

                if (file_exists($xmlfile)) {
                    if ($data = JApplicationHelper::parseXMLInstallFile($xmlfile)) {
                        foreach ($data as $key => $value) {
                            $row->$key = $value;
                        }
                    }
                }
            }
        }
        return $rows;
    }

    function install()
    {
        $this->setState('action', 'install');

        switch (JRequest::getWord('installtype')) {
            case 'folder':
                $package = $this->_getPackageFromFolder();
                break;

            case 'upload':
                $package = $this->_getPackageFromUpload();
                break;

            case 'url':
                $package = $this->_getPackageFromUrl();
                break;

            default:
                $this->setState('message', 'No Install Type Found');
                return false;
                break;
        }

        // Was the package unpacked?
        if (!$package) {
            $this->setState('message', 'Unable to find install package');
            return false;
        }

        // Get a database connector
        //$db = & JFactory::getDbo();
        // Get an installer instance
        $installer = XmapInstaller::getInstance();

        // Install the package
        if (!$installer->install($package['dir'])) {
            // There was an error installing the package
            $msg = JText::sprintf('INSTALLEXT', JText::_($package['type']), JText::_('Error'));
            $result = false;
        } else {
            // Package installed sucessfully
            $msg = JText::sprintf('INSTALLEXT', JText::_($package['type']), JText::_('Success'));
            $result = true;
        }

        // Set some model state values
        $app = JFactory::getApplication();
        $app->enqueueMessage($msg);
        $this->setState('name', $installer->get('name'));
        $this->setState('result', $result);
        $this->setState('message', $installer->message);
        $this->setState('extension_message', $installer->get('extension_message'));

        // Cleanup the install files
        if (!is_file($package['packagefile'])) {
            $config = JFactory::getConfig();
            $package['packagefile'] = $config->getValue('config.tmp_path') . DS . $package['packagefile'];
        }

        JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

        return $result;
    }

    /**
     * Refreshes the cached manifest information for an extension
     * @param int extension identifier (key in #__extensions)
     * @return boolean result of refresh
     * @since 2.0
     */
    function refresh($eid)
    {
        if (!is_array($eid)) {
            $eid = array($eid => 0);
        }

        // Get a database connector
        $db = JFactory::getDBO();

        // Get an installer object for the extension type
        jimport('joomla.installer.installer');
        $installer = XmapInstaller::getInstance();
        $row = JTable::getInstance('extension');
        $result = 0;

        // Uninstall the chosen extensions
        foreach ($eid as $id) {
            $result|= $installer->refreshManifestCache($id);
        }
        return $result;
    }

    /**
     * @param string The class name for the installer
     */
    function _getPackageFromUpload()
    {
        // Get the uploaded file information
        $userfile = JRequest::getVar('install_package', null, 'files', 'array');

        // Make sure that file uploads are enabled in php
        if (!(bool) ini_get('file_uploads')) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('WARNINSTALLFILE'));
            return false;
        }

        // Make sure that zlib is loaded so that the package can be unpacked
        if (!extension_loaded('zlib')) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('WARNINSTALLZLIB'));
            return false;
        }

        // If there is no uploaded file, we have a problem...
        if (!is_array($userfile)) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('No file selected'));
            return false;
        }

        // Check if there was a problem uploading the file.
        if ($userfile['error'] || $userfile['size'] < 1) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('WARNINSTALLUPLOADERROR'));
            return false;
        }

        // Build the appropriate paths
        $config = JFactory::getConfig();
        $tmp_dest = $config->getValue('config.tmp_path') . DS . $userfile['name'];
        $tmp_src = $userfile['tmp_name'];

        // Move uploaded file
        jimport('joomla.filesystem.file');
        $uploaded = JFile::upload($tmp_src, $tmp_dest);

        // Unpack the downloaded package file
        $package = JInstallerHelper::unpack($tmp_dest);

        return $package;
    }

    /**
     * Install an extension from a directory
     *
     * @static
     * @return boolean True on success
     * @since 1.0
     */
    function _getPackageFromFolder()
    {
        // Get the path to the package to install
        $p_dir = JRequest::getString('install_directory');
        $p_dir = JPath::clean($p_dir);

        // Did you give us a valid directory?
        if (!is_dir($p_dir)) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('Please enter a package directory'));
            return false;
        }

        // Detect the package type
        $type = JInstallerHelper::detectType($p_dir);

        // Did you give us a valid package?
        if (!$type) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('Path does not have a valid package'));
            return false;
        }

        $package['packagefile'] = null;
        $package['extractdir'] = null;
        $package['dir'] = $p_dir;
        $package['type'] = $type;

        return $package;
    }

    /**
     * Install an extension from a URL
     *
     * @static
     * @return boolean True on success
     * @since 1.5
     */
    function _getPackageFromUrl()
    {
        // Get a database connector
        $db = JFactory::getDbo();

        // Get the URL of the package to install
        $url = JRequest::getString('install_url');

        // Did you give us a URL?
        if (!$url) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('Please enter a URL'));
            return false;
        }

        // Download the package at the URL given
        $p_file = JInstallerHelper::downloadPackage($url);

        // Was the package downloaded?
        if (!$p_file) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('Invalid URL'));
            return false;
        }

        $config = JFactory::getConfig();
        $tmp_dest = $config->getValue('config.tmp_path');

        // Unpack the downloaded package file
        $package = JInstallerHelper::unpack($tmp_dest . DS . $p_file);

        return $package;
    }

    /**
     * Enable/Disable an extension
     *
     * @static
     * @return boolean True on success
     * @since 2.0
     */
    function publish($eid = array(), $value = 1)
    {

        // Initialise variables.
        $user = JFactory::getUser();
        if ($user->authorise('core.edit.state', 'com_installer')) {
            $result = true;

            /*
             * Ensure eid is an array of extension ids
             * TODO: If it isn't an array do we want to set an error and fail?
             */
            if (!is_array($eid)) {
                $eid = array($eid);
            }

            // Get a database connector
            $db = JFactory::getDBO();

            // Get a table object for the extension type
            $table = JTable::getInstance('Extension');

            // Enable the extension in the table and store it in the database
            foreach ($eid as $id) {
                $table->load($id);
                $table->enabled = $value || $table->protected;
                if (!$table->store()) {
                    $this->setError($table->getError());
                    $result = false;
                }
            }
        } else {
            $result = false;
            JError::raiseWarning(403, JText::_('JERROR_CORE_EDIT_STATE_NOT_PERMITTED'));
        }
        return $result;
    }

}
