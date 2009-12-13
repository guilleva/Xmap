<?php
/**
 * @version             $Id$
 * @copyright			Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
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
class JFormFieldXmapmenus extends JFormFieldList
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

		$currentMenus = array_keys(get_object_vars($this->value));

		$query->select('menutype As value, title As text');
		$query->from('#__menu_types AS a');
		$query->order('a.title');

		// Get the options.
		$db->setQuery($query);
		$menus = $db->loadObjectList('value');
		$options = array();

		// Add the current sitemap menus in the defined order to the list
		foreach ($currentMenus as $menutype){
			if (!empty($menus[$menutype])){

				$options[] = $menus[$menutype];
			}
		}

		// Add the rest of the menus to the list (if any)
		foreach ($menus as $menutype => $menu){
			if (!in_array($menutype,$currentMenus)){
				$options[] = $menu;
			}
		}

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

		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration("
		window.addEvent('domready',function(){
			new Sortables(\$('ul_".$this->inputId."'),{
				clone:true,
				revert: true,
				onStart: function(el) {
					el.setStyle('background','#bbb');
				},
				onComplete: function(el) {
					el.setStyle('background','#eee');
				}
			});
		});");

		if ($disabled || $readonly) {
			$attributes .= 'disabled="disabled"';
		}
		$options	= (array)$this->_getOptions();
		$return	 = '<ul id="ul_'.$this->inputId.'" class="ul_sortable">';

		// Create a regular list.
		$i=0;
		foreach ($options as $option) {
			$prioritiesName = preg_replace('/(jform\[[^\]]+)(\].*)/','$1_priority$2',$this->inputName);
			$changefreqName = preg_replace('/(jform\[[^\]]+)(\].*)/','$1_changefreq$2',$this->inputName);
			$selected = (isset($this->value->{$option->value})?' checked="checked"' : '');
			$i++;
			$return .= '<li id="menu_'.$i.'">';
			$return .= '  <input type="'.$type.'" id="'.$this->inputId.'_'.$i.'" name="'.$this->inputName.'" value="'.$option->value.'"'.$attributes.$selected.' /><label for="'.$this->inputId.'_'.$i.'" class="menu_label">'.$option->text.'</label>';
			$return .= '  <div class="xmap-menu-options" id="menu_options_'.$i.'">'.
				     '<label>'. JText::_('Xmap_Priority').'</label> '.JHTML::_('xmap.priorities', $prioritiesName,($selected?$this->value->{$option->value}->priority:''),$i). '<br />' .
				     '<label>'. JText::_('Xmap_Change_Frequency').'</label> '.JHTML::_('xmap.changefrequency', $changefreqName,($selected?$this->value->{$option->value}->changefreq:''),$i).
                                     '</div>';
			$return .= '</li>';
		}
		$return .= "</ul>";
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
