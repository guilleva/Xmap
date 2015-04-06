<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2015 Open Source Training, LLC. All rights reserved..
 * @author    Guillermo Vargas <guille@vargas.co.cr>
 * @author    Alledia <support@alledia.com>
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file is part of OSMap.
 *
 * OSMap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * OSMap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSMap. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.html');

require_once JPATH_LIBRARIES . '/joomla/form/fields/list.php';

/**
 * Menus Form Field class for the OSMap Component
 *
 * @package      OSMap
 * @subpackage   com_osmap
 * @since        2.0
 */
class JFormFieldOSMapMenus extends JFormFieldList
{

    /**
     * The field type.
     *
     * @var      string
     */
    public $type = 'OSMapMenus';

    /**
     * Method to get a list of options for a list input.
     *
     * @return   array        An array of JHtml options.
     */
    protected function _getOptions()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        //$currentMenus = array_keys(get_object_vars($this->value));
        $currentMenus = array();

        $query->select('menutype As value, title As text');
        $query->from('#__menu_types AS a');
        $query->order('a.title');

        // Get the options.
        $db->setQuery($query);
        // echo $db->getQuery();
        $menus = $db->loadObjectList('value');
        $options = array();

        // Add the current sitemap menus in the defined order to the list
        foreach ($currentMenus as $menutype) {
            if (!empty($menus[$menutype])) {
                $options[] = $menus[$menutype];
            }
        }

        // Add the rest of the menus to the list (if any)
        foreach ($menus as $menutype => $menu) {
            if (!in_array($menutype, $currentMenus)) {
                $options[] = $menu;
            }
        }

        // Check for a database error.
        if ($db->getErrorNum()) {
            JError::raiseWarning(500, $db->getErrorMsg());
        }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }

    /**
     * Method to get the field input.
     *
     * @return      string      The field input.
     */
    protected function getInput()
    {
        $disabled = $this->element['disabled'] == 'true' ? true : false;
        $readonly = $this->element['readonly'] == 'true' ? true : false;
        $attributes = ' ';

        $type = 'radio';

        if ($v = $this->element['size']) {
            $attributes .= 'size="' . $v . '" ';
        }

        if ($v = $this->element['class']) {
            $attributes .= 'class="' . $v . '" ';
        } else {
            $attributes .= 'class="inputbox" ';
        }

        if ($m = $this->element['multiple']) {
            $type = 'checkbox';
        }

        $value = $this->value;
        if (!is_array($value)) {
            // Convert the selections field to an array.
            $registry = new JRegistry;
            $registry->loadString($value);
            $value = $registry->toArray();
        }

        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration("
        window.addEvent('domready',function(){
            \$\$('div.osmap-menu-options select').addEvent('mouseover',function(event){OSMapMenusSortable.detach();})
            \$\$('div.osmap-menu-options select').addEvent('mouseout',function(event){OSMapMenusSortable.attach();})
            var OSMapMenusSortable = new Sortables(\$('ul_" . $this->inputId . "'),{
                clone:true,
                revert: true,
                preventDefault: true,
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
        $options = (array) $this->_getOptions();
        $return = '<ul id="ul_' . $this->inputId . '" class="ul_sortable">';

        // Create a regular list.
        $i = 0;

        //Lets show the enabled menus first
        $this->currentItems = array_keys($value);
        // Sort the menu options
        uasort($options, array($this, 'myCompare'));

        foreach ($options as $option) {
            $prioritiesName = preg_replace('/(jform\[[^\]]+)(\].*)/', '$1_priority$2', $this->name);
            $changefreqName = preg_replace('/(jform\[[^\]]+)(\].*)/', '$1_changefreq$2', $this->name);
            $selected = (isset($value[$option->value]) ? ' checked="checked"' : '');
            $i++;
            $return .= '<li id="menu_' . $i . '">';
            $return .= '<input type="' . $type . '" id="' . $this->id . '_' . $i . '" name="' . $this->name . '" value="' . $option->value . '"' . $attributes . $selected . ' />';
            $return .= '<label for="' . $this->id . '_' . $i . '" class="menu_label">' . $option->text . '</label>';
            $return .= '<div class="osmap-menu-options" id="menu_options_' . $i . '">';
            $return .= '<label class="control-label">' . JText::_('OSMAP_PRIORITY') . '</label>';
            $return .= '<div class="controls">' . JHTML::_('osmap.priorities', $prioritiesName, ($selected ? $value[$option->value]['priority'] : '0.5'), $i) . '</div>';
            $return .= '<label class="control-label">' . JText::_('OSMAP_CHANGE_FREQUENCY') . '</label>';
            $return .= '<div class="controls">' . JHTML::_('osmap.changefrequency', $changefreqName, ($selected ? $value[$option->value]['changefreq'] : 'weekly'), $i) . '</div>';
            $return .= '</div>';
            $return .= '</li>';
        }

        $return .= "</ul>";

        return $return;
    }

    public function myCompare($a, $b) {
        $indexA = array_search($a->value, $this->currentItems);
        $indexB = array_search($b->value, $this->currentItems);

        if ($indexA === $indexB && $indexA !== false) {
            return 0;
        }

        if ($indexA === false && $indexA === $indexB) {
            return ($a->value < $b->value) ? -1 : 1;
        }

        if ($indexA === false) {
            return 1;
        }

        if ($indexB === false) {
            return -1;
        }

        return ($indexA < $indexB) ? -1 : 1;
    }

}
