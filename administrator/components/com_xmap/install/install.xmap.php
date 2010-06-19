<?php
    
    
// Install any extension in the extensions folder
class com_xmapInstallerScript {
    private $_installer = null;

    function __construct($installer) {
        $this->_installer = $intaller;
        
        // Load Xmap's language file
        $lang = JFactory::getLanguage();
        $lang->load('com_xmap');
    }
    
    
    function install()
    {
        echo '<h2>',JText::_("XMAP_INSTALLING_XMAP"),'</h2>';
        $this->_discoverAndInstallExtensions();
    }
    
    function update()
    {
        echo '<h2>',JText::_("XMAP_UPGRADING_XMAP"),'</h2>';
        $this->_discoverAndInstallExtensions();
    }
    

    private function _discoverAndInstallExtensions()
    {
        $db = JFactory::getDBO();
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');
        // require_once JPATH_ADMINISTRATOR.DS.'components'.DS.'com_xmap'.DS.'adapters'.DS.'xmap_ext.php';
        require_once JPATH_ADMINISTRATOR.DS.'components'.DS.'com_xmap'.DS.'helpers'.DS.'installer.php';
        $installer =& XmapInstaller::getInstance();

        $path = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_xmap'.DS.'extensions';
        if ($folders = JFolder::folders($path)){
            foreach ($folders as $folder) {
                if (JFile::exists($path.DS.$folder.DS.$folder.'.xml')) {
                    $folder = $db->getEscaped($folder);
                    $query = "SELECT extension_id from `#__extensions` where type='xmap_ext' and folder='$folder'";
                    $db->setQuery($query);
                    $id = $db->loadResult();
                    if (!$id) {
                       if ($installer->install($path.DS.$folder)) {
                           echo '<p />'.JText::sprintf('XMAP_INSTALLED_EXTENSION_X',$folder);
                           // Auto-publish the extension if the component is installed
                           $query = "update `#__extensions` set state=1 WHERE type='xmap_ext' and folder='$folder' and folder in (select name from `#__extensions` where name='$folder')";
                           $db->setQuery($query);
                           $db->query();
                       } else {
                           echo '<p />'.JText::sprintf('XMAP_NOT_INSTALLED_EXTENSION_X',$folder);
                       }
                    }
                }
            }
        }
    }
}