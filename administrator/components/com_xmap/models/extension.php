<?php
/**
 * @version		$Id$
 * @copyright   	Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @author		Guillermo Vargas (guille@vargas.co.cr)
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');
jimport('joomla.database.query');

/**
 * Extension Item Model for Xmap.
 *
 * @package         Xmap
 * @subpackage      com_xmap
 * @since           2.0
 */
class XmapModelExtension extends JModelAdmin
{

    /**
     * @var     string    The prefix to use with controller messages.
     * @since   2.0
     */
    protected $text_prefix = 'XMAP';
    protected $_xml = null;
    /**
     * extension data
     *
     * @var array
     */
    var $_item = null;

    /**
     * Returns a reference to the a Table object, always creating it
     *
     * @param	type 	$type 	 The table type to instantiate
     * @param	string 	$prefix	 A prefix for the table class name. Optional.
     * @param	array	$options Configuration array for model. Optional.
     * @return	JTable	A database object
     */
    public function getTable($type = 'Extension', $prefix = 'JTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * @return	void
     */
    protected function populateState()
    {
        parent::populateState();

        $app = JFactory::getApplication('administrator');

        // Load the User state.
        if (!($pk = (int) $app->getUserState('com_xmap.edit.extension.id'))) {
            $pk = (int) JRequest::getInt('item_id');
        }

//        $this->setState('extension.id', $pk);

        // Load the parameters.
        $params = JComponentHelper::getParams('com_xmap');
        $this->setState('params', $params);
    }

    /**
     * Method to get a menu item.
     *
     * @param	integer	The id of the menu item to get.
     *
     * @return	mixed	Menu item data object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        if (isset($this->_item)) {
            return $this->_item;
        }

        // Initialize variables.
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('extension.id');
        $false = false;

        // Get a row instance.
        $table = $this->getTable();

        // Attempt to load the row.
        $return = $table->load($pk);

        // Check for a table object error.
        if ($return === false && $table->getError()) {
            $this->setError($table->getError());
            return $false;
        }

        $lang = JFactory::getLanguage();
        $lang->load('xmapext_' . trim($table->element), JPATH_ADMINISTRATOR);
        $lang->load('xmapext_' . trim($table->element), JPATH_SITE); // handle language files not in admin, mostly core
        $lang->load('xmapext_' . trim($table->element), JPATH_COMPONENT_ADMINISTRATOR . DS . 'extensions' . DS . $table->folder);


        $value = $table->getProperties(1);
        $value = JArrayHelper::toObject($value, 'JObject');

        $data = JApplicationHelper::parseXMLInstallFile(JPATH_COMPONENT_ADMINISTRATOR . DS . 'extensions' . DS . $value->folder . DS . preg_replace('/^xmap_/', '', $value->element) . '.xml');
        $value->description = $data['description'];
        $value->author = $data['author'];
        $value->creationdate = $data['creationDate'];
        $value->copyright = $data['copyright'];
        $value->authorEmail = $data['authorEmail'];
        $value->authorUrl = $data['authorUrl'];
        $value->version = $data['version'];

        // Convert the params field to an array.
        $registry = new JRegistry;
        $registry->loadJSON($value->params);
        $value->params = $registry->toArray();

        $this->_item = $value;
        return $this->_item;
    }

    /**
     * Method to get the record form.
     *
     * @param    array    $data        Data for the form.
     * @param    boolean    $loadData    True if the form is to load its own data (default case), false if not.
     * @return   mixed    A JForm object on success, false on failure
     * @since    2.0
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_xmap.extension', 'extension', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }


        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     * @since    2.0
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_xmap.edit.extension.data', array());
        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    protected function preprocessForm(JForm $form, $data, $group = 'content')
    {

        // $this->populateState();

        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');
        $this->populateState();

        // Initialise variables.
        $item = $this->getItem();
        $folder = $item->folder;
        $element = $item->element;
        $element = preg_replace('/^xmap_/', '', $element);
/*
        // These variables are used to add data from the plugin XML files.
        $this->setState('item.folder', $folder);
        $this->setState('item.element', $element);

        $folder = $this->getState('item.folder');
        $element = $this->getState('item.element');*/
        $lang = JFactory::getLanguage();

        // Try format: /components/com_xmap/extensions/folder/element.xml
        $formFile = JPath::clean(JPATH_COMPONENT_ADMINISTRATOR . '/extensions/' . $folder . '/' . $element . '.xml');
        //echo $formFile;exit;
        if (!file_exists($formFile)) {
            throw new Exception(JText::sprintf('JError_File_not_found', $element . '.xml'));
            return false;
        }

        // Load the extension's language file(s).
        $lang->load('xmapext_' . $element, JPATH_ADMINISTRATOR, null, false, false)
                || $lang->load('xmapext_' . $element, JPATH_COMPONENT_ADMINISTRATOR . '/extensions/' . $folder, null, false, false)
                || $lang->load('xmapext_' . $element, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
                || $lang->load('xmapext_' . $element, JPATH_COMPONENT_ADMINISTRATOR . '/extensions/' . $folder . '/', $lang->getDefault(), false, false);

        // Get the plugin form.
        if (!$form->loadFile($formFile, false, '//config')) {
            throw new Exception(JText::_('JModelForm_Error_loadFile_failed'));
        }

        // Trigger the default form events.
        parent::preprocessForm($form, $data, $group);
    }

    public function save($data)
    {
        // Load the extension plugin group.
        JPluginHelper::importPlugin('extension');

        return parent::save($data);
    }

    /**
     * Method to publish categories.
     *
     * @param	array	The ids of the items to publish.
     * @param	int		The value of the published state
     *
     * @return	boolean	True on success.
     */
    function publish(&$pks, $value = 1)
    {
        // Sanitize the ids.
        $pks = (array) $pks;
        JArrayHelper::toInteger($pks);

        // Get the current user object.
        $user = JFactory::getUser();

        // Get a category row instance.
        $table = $this->getTable();

        // Attempt to publish the items.
        if (!$table->publish($pks, $value, $user->get('id'))) {
            $this->setError($table->getError());
            return false;
        }

        return true;
    }

    function &_getXML()
    {
        if (!$this->_xml) {
            $clientId = $this->getState('clientId', 0);
            $path = ($clientId == 1) ? 'mod1_xml' : 'mod0_xml';
            $extension = $this->getItem();

            $xmlpath = JPATH_COMPONENT . DS . 'extensions' . DS . $extension->folder . DS . $extension->element . '.xml';

            if (file_exists($xmlpath)) {
                $xml = JFactory::getXMLParser('Simple');
                if ($xml->loadFile($xmlpath)) {
                    $this->_xml = &$xml;
                }
            }
        }
        return $this->_xml;
    }

    function _orderConditions($table = null)
    {
        $condition = array();
        return $condition;
    }

}