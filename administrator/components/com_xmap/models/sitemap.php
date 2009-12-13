<?php
/**
 * @version		$Id$
 * @copyright   Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
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
class XmapModelSitemap extends JModelForm
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	 protected $_context		= 'com_xmap.sitemap';

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
		return JTable::getInstance($type, $prefix, $config);
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
		if (!($pk = (int) $app->getUserState('com_xmap.edit.sitemap.id'))) {
			$pk = (int) JRequest::getInt('item_id');
		}
		$this->setState('sitemap.id',			$pk);

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
		// Initialize variables.
		$itemId = (!empty($itemId)) ? $itemId : (int)$this->getState('sitemap.id');
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

		// Prime required properties.
		if (empty($table->id))
		{
			//$table->parent_id	= $this->getState('item.parent_id');
			//$table->menutype	= $this->getState('item.menutype');
			//$table->type		= $this->getState('item.type');
		}

		// Convert the attribs field to an array.
		$registry = new JRegistry;
		$registry->loadJSON($table->attribs);
		$table->attribs = $registry->toArray();

		// Convert the selections field to an array.
		$registry = new JRegistry;
		$registry->loadJSON($table->selections);
		$table->selections = $registry->toArray();

		$value = JArrayHelper::toObject($table->getProperties(1), 'JObject');


		return $value;
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
		$form = parent::getForm('sitemap', 'com_xmap.sitemap', array('array' => 'jform', 'event' => 'onPrepareForm'));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_xmap.edit.sitemap.data', array());

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
		$pk			= (!empty($data['id'])) ? $data['id'] : (int)$this->getState('sitemap.id');
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

		// Check if there is no default sitemap. Then, set it as default if not
		$query = 'SELECT COUNT(id) FROM `#__xmap_sitemap` where is_default=1';
		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();
		if (!$result) {
			$table->is_default=1;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		if ($table->is_default) {
			$query = 'UPDATE `#__xmap_sitemap` set is_default=0 where id <> '.$table->id;
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError($table->_db->getErrorMsg());
				return false;
			}
		}

		// Clean the cache.
		$cache = &JFactory::getCache('com_xmap');
		$cache->clean();

		$this->setState('sitemap.id', $table->id);

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

	/**
	 * Method to set a sitemap as default
	 *
	 * @param	int		The primary key ID for the style.
	 *
	 * @return	boolean	True if successful.
	 * @throws	Exception
	 */
	public function setDefault($id = 0)
	{
		// Initialise variables.
		$user	= JFactory::getUser();
		$db		= $this->getDbo();


		// Reset the home fields for the client_id.
		$db->setQuery(
			'UPDATE #__xmap_sitemap' .
			' SET is_default = 0'
		);
		if (!$db->query())
		{
			throw new Exception($db->getErrorMsg());
		}

		// Set the new home style.
		$db->setQuery(
			'UPDATE #__xmap_sitemap' .
			' SET is_default = 1' .
			' WHERE id = '.(int) $id
		);
		if (!$db->query())
		{
			throw new Exception($db->getErrorMsg());
		}

		return true;
	}
}
