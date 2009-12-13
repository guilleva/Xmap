<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
require_once JPATH_LIBRARIES.DS.'joomla'.DS.'form'.DS.'fields'.DS.'list.php';

/**
 * Menus Form Field class for the Xmap Component
 *
 * @package		Xmap
 * @subpackage		com_xmap
 * @since		2.0
 */
class JFormFieldMenus extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Menus';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		$db		= &JFactory::getDbo();
		$query	= new JQuery;

		$query->select('menutype As value, title As text');
		$query->from('#__menu_types AS a');
		$query->order('a.title');

		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}

		$options = array_merge(
			parent::_getOptions(),
			$options
		);
		return $options;
	}

	/**
	 * Method to get the field input.
	 *
	 * @return      string	  The field input.
	 */
	protected function _getInput()
	{
		$disabled       = $this->_element->attributes('disabled') == 'true' ? true : false;
		$readonly       = $this->_element->attributes('readonly') == 'true' ? true : false;
		$attributes     = ' ';

		$type = 'radio';
		if ($v = $this->_element->attributes('size')) {
			$attributes     .= 'size="'.$v.'" ';
		}
		if ($v = $this->_element->attributes('class')) {
			$attributes     .= 'class="'.$v.'" ';
		}
		else {
			$attributes     .= 'class="inputbox" ';
		}
		if ($m = $this->_element->attributes('multiple'))
		{
			$type = 'checkbox';
		}

		if ($disabled || $readonly) {
			$attributes .= 'disabled="disabled"';
		}
		$options	= (array)$this->_getOptions();
		$return	 = '';



		// Create a regular list.
		$i=0;
		foreach ($options as $option) {
			$prioritiesName = preg_replace('/(jform\[[^\]]+)(\].*)/','$1_priority$2',$this->inputName);
			$changefreqName = preg_replace('/(jform\[[^\]]+)(\].*)/','$1_changefreq$2',$this->inputName);
			$selected = (isset($this->value->{$option->value})?' checked="checked"' : '');
			$i++;
			$return .= '<div id="menu_'.$i.'">';
			$return .= '  <input type="'.$type.'" id="'.$this->inputId.'_'.$i.'" name="'.$this->inputName.'" value="'.$option->value.'"'.$attributes.$selected.' /><label for="'.$this->inputId.'_'.$i.'">'.$option->text.'</label> <a href="#" onclick="showMenuOptions('.$i.');">Options</a>';
			$return .= '  <div class="xmap-menu-options" id="menu_options_'.$i.'">'.
				      JText::_('Xmap_Priority').': '.JHTML::_('xmap.priorities', $prioritiesName,($selected?$this->value->{$option->value}->priority:''),$i). '<br />' .
				      JText::_('Xmap_Change_Frequency').': '.JHTML::_('xmap.changefrequency', $changefreqName,($selected?$this->value->{$option->value}->changefreq:''),$i).
                                     '</div>';
			$return .= '</div>';
		}

		return $return;
	}
/*
	public function render(&$xml, $value, $formName, $groupName)
	{
		if (is_object($value)) {
			$value = array_keys(get_object_vars($value));
		}
		return parent::render($xml, $value, $formName, $groupName);
	}
*/
}
