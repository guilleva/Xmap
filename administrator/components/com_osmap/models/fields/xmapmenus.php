<?php
/**
 * @version          $Id$
 * @copyright        Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license          GNU General Public License version 2 or later; see LICENSE.txt
 * @author           Guillermo Vargas (guille@vargas.co.cr)
 */
defined('_JEXEC') or die;

jimport('joomla.html.html');
require_once JPATH_LIBRARIES . '/joomla/form/fields/list.php';

/**
 * Menus Form Field class for the Xmap Component
 *
 * @package      Xmap
 * @subpackage   com_xmap
 * @since        2.0
 */
class JFormFieldXmapmenus extends JFormFieldList
{

    /**
     * The field type.
     *
     * @var      string
     */
    public $type = 'Xmapmenus';

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

        $options = array_merge(
                       parent::getOptions(),
                       $options
        );
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
            \$\$('div.xmap-menu-options select').addEvent('mouseover',function(event){xmapMenusSortable.detach();})
            \$\$('div.xmap-menu-options select').addEvent('mouseout',function(event){xmapMenusSortable.attach();})
            var xmapMenusSortable = new Sortables(\$('ul_" . $this->inputId . "'),{
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
            $return .= '<div class="xmap-menu-options" id="menu_options_' . $i . '">';
            $return .= '<label class="control-label">' . JText::_('XMAP_PRIORITY') . '</label>';
            $return .= '<div class="controls">' . JHTML::_('xmap.priorities', $prioritiesName, ($selected ? $value[$option->value]['priority'] : '0.5'), $i) . '</div>';
            $return .= '<label class="control-label">' . JText::_('XMAP_CHANGE_FREQUENCY') . '</label>';
            $return .= '<div class="controls">' . JHTML::_('xmap.changefrequency', $changefreqName, ($selected ? $value[$option->value]['changefreq'] : 'weekly'), $i) . '</div>';
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
