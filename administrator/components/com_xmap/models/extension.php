<?php
/**
 * @version		$Id$
 * @copyright   	Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @author		Guillermo Vargas (guille@vargas.co.cr)
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.database.query');

/**
 * Sitemap Item Model for Xmap.
 *
 * @package	     Xmap
 * @subpackage  com_xmap
 * @since	       2.0
 */
class XmapModelExtension extends JModelForm
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	 protected $_context		= 'com_xmap.extension';
	 protected $_xml		= null;

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
	public function &getTable($type = 'Sitemap', $prefix = 'XmapTable', $config = array())
	{
		return JTable::getInstance('extension');
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return	void
	 */
	protected function _populateState()
	{
		$app	= &JFactory::getApplication('administrator');

		// Load the User state.
		if (!($pk = (int) $app->getUserState('com_xmap.edit.extension.id'))) {
			$pk = (int) JRequest::getInt('item_id');
		}
		$this->setState('extension.id',			$pk);

		// Load the parameters.
		$params	= &JComponentHelper::getParams('com_xmap');
		$this->setState('params', $params);
	}

	/**
	 * Method to get a menu item.
	 *
	 * @param	integer	The id of the menu item to get.
	 *
	 * @return	mixed	Menu item data object on success, false on failure.
	 */
	public function &getItem($itemId = null)
	{
		if (isset($this->_item)) {
			return $this->_item;
		}

		// Initialize variables.
		$itemId = (!empty($itemId)) ? $itemId : (int)$this->getState('extension.id');
		$false	= false;

		// Get a row instance.
		$table = &$this->getTable();

		// Attempt to load the row.
		$return = $table->load($itemId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->setError($table->getError());
			return $false;
		}

		$lang =& JFactory::getLanguage();
		// Core or 1.5
		$lang->load( 'xmapext_' . trim( $table->element ), JPATH_ADMINISTRATOR );
		$lang->load( 'xmapext_' . trim( $table->element ), JPATH_SITE ); // handle language files not in admin, mostly core
		$lang->load( 'xmapext_' . trim( $table->element ), JPATH_COMPONENT_ADMINISTRATOR . DS . 'extensions'. DS .$table->folder );


		$value = JArrayHelper::toObject($table->getProperties(1), 'JObject');

		$data = JApplicationHelper::parseXMLInstallFile(JPATH_COMPONENT_ADMINISTRATOR.DS.'extensions'.DS.$value->folder.DS.$value->element.'.xml');
		$value->description = $data['description'];
		$value->author = $data['author'];
		$value->creationdate = $data['creationdate'];
		$value->copyright = $data['copyright'];
		$value->authorEmail = $data['authorEmail'];
		$value->authorUrl = $data['authorUrl'];
		$value->version = $data['version'];


		$this->_item = $value;
		return $this->_item;
	}

	function &getParams()
	{
		// Get the state parameters
		$extension = &$this->getItem();
		$params = new JParameter($extension->params);

		if ($xml = &$this->_getXML())
		{
			if ($ps = & $xml->document->params) {
				foreach ($ps as $p)
				{
					$params->setXML($p);
				}
			}
		}
		return $params;
	}


	/**
	 * Method to get the row form.
	 *
	 * @return	mixed	JForm object on success, false on failure.
	 * @since	1.6
	 */
	public function getForm()
	{
		// Initialize variables.
		$app	= &JFactory::getApplication();

		// Get the form.
		$form = parent::getForm('extension', 'com_xmap.extension', array('array' => 'jform', 'event' => 'onPrepareForm'));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_xmap.edit.extension.data', array());

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
		// Initialise variables;
		$table		= &$this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int)$this->getState('extension.id');
		$isNew		= true;

		// Load the row if saving an existing item.
		if ($pk > 0) {
			$table->load($pk);
			$isNew = false;
		}

		// Bind the data.
		if (!$table->bind($data)) {
			$this->setError(JText::sprintf('JTable_Error_Bind_failed', $table->getError()));
			return false;
		}

		// Check the data.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		// Clean the cache.
		$cache = &JFactory::getCache('com_xmap');
		$cache->clean();

		$this->setState('extension.id', $table->extension_id);

		return true;
	}

	/**
	 * Method to delete rows.
	 *
	 * @param	array	An array of item ids.
	 *
	 * @return	boolean	Returns true on success, false on failure.
	 */
	public function delete($pks)
	{
		// Sanitize the ids.
		$pks = (array) $pks;
		JArrayHelper::toInteger($pks);

		// Get a row instance.
		$table = &$this->getTable();

		// Iterate the items to delete each one.
		foreach ($pks as $itemId)
		{
			if (!$table->delete($itemId))
			{
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to publish categories.
	 *
	 * @param	array	The ids of the items to publish.
	 * @param	int		The value of the published state
	 *
	 * @return	boolean	True on success.
	 */
	function publish($pks, $value = 1)
	{
		// Sanitize the ids.
		$pks = (array) $pks;
		JArrayHelper::toInteger($pks);

		// Get the current user object.
		$user = &JFactory::getUser();

		// Get a category row instance.
		$table = &$this->getTable();

		// Attempt to publish the items.
		if (!$table->publish($pks, $value, $user->get('id'))) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

	function &_getXML()
	{
		if (!$this->_xml)
		{
			$clientId       = $this->getState('clientId', 0);
			$path	   = ($clientId == 1) ? 'mod1_xml' : 'mod0_xml';
			$extension	 = &$this->getItem();

			$xmlpath = JPATH_COMPONENT.DS.'extensions'.DS.$extension->folder.DS. $extension->element.'.xml';

			if (file_exists($xmlpath))
			{
				$xml = &JFactory::getXMLParser('Simple');
				if ($xml->loadFile($xmlpath)) {
					$this->_xml = &$xml;
				}
			}
		}
		return $this->_xml;
	}
}
