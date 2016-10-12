<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap;

defined('_JEXEC') or die();

jimport('joomla.html.html');

require_once JPATH_LIBRARIES . '/joomla/form/fields/list.php';

JHtml::addIncludePath(OSMAP_ADMIN_PATH . '/helpers/html');

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
     * @var string
     */
    public $type = 'osmapmenus';

    /**
     * Method to get a list of options for a list input.
     *
     * @return   array        An array of JHTML options.
     */
    protected function _getOptions()
    {
        $db = JFactory::getDbo();

        $currentMenus = array();

        // Get the list of menus from the database
        $query = $db->getQuery(true)
            ->select('id AS value')
            ->select('title AS text')
            ->from('#__menu_types AS menus')
            ->order('menus.title');
        $db->setQuery($query);

        $menus = $db->loadObjectList('value');

        // Get the options
        $options = array();

        // Add the current sitemap menus in the defined order to the list
        foreach ($currentMenus as $menuId) {
            if (!empty($menus[$menuId])) {
                $options[] = $menus[$menuId];
            }
        }

        // Add the rest of the menus to the list (if any)
        foreach ($menus as $menuId => $menu) {
            if (!in_array($menuId, $currentMenus)) {
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
        $disabled   = $this->element['disabled'] == 'true' ? true : false;
        $readonly   = $this->element['readonly'] == 'true' ? true : false;
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

        if ($this->element['multiple']) {
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

        $this->inputId = 'menus';

        // Depends on jQuery UI
        JHtml::_('jquery.ui', array('core', 'sortable'));
        JHtml::_('script', 'jui/sortablelist.js', false, true);
        JHtml::_('stylesheet', 'jui/sortablelist.css', false, true, false);

        $doc->addScriptDeclaration("
            ;(function ($){
                $(document).ready(function (){
                    $('#ul_" . $this->inputId . "').sortable({
                        'appendTo': document.body
                    });

                    // Toggle checkbox clicking on the line
                    $('#ul_" . $this->inputId . " li').on('click', function(event) {
                        if ($(event.srcElement).hasClass('osmap-menu-item')
                            || $(event.srcElement).hasClass('control-label')
                            || $(event.srcElement).hasClass('osmap-menu-options')) {

                            $(this).children('input').click();
                        }
                    });
                });
            })(jQuery);
        ");

        if ($disabled || $readonly) {
            $attributes .= 'disabled="disabled"';
        }
        $options = (array) $this->_getOptions();

        $textSelected         = JText::_('COM_OSMAP_SELECTED_LABEL');
        $textTitle            = JText::_('COM_OSMAP_TITLE_LABEL');
        $textChangePriority   = JText::_('COM_OSMAP_PRIORITY_LABEL');
        $textChangeChangeFreq = JText::_('COM_OSMAP_CHANGE_FREQUENCY_LABEL');

        $return = <<<HTML
            <div class="osmap-table">
                <div class="osmap-list-header">
                    <div class="osmap-cell osmap-col-selected">{$textSelected}</div>
                    <div class="osmap-cell osmap-col-title">{$textTitle}</div>
                    <div class="osmap-cell osmap-col-priority">{$textChangePriority}</div>
                    <div class="osmap-cell osmap-col-changefreq">{$textChangeChangeFreq}</div>
                </div>
HTML;

        $return .= '<ul id="ul_' . $this->inputId . '" class="ul_sortable">';

        // Create a regular list.
        $i = 0;

        //Lets show the enabled menus first
        $this->currentItems = array_keys($value);
        // Sort the menu options
        uasort($options, array($this, 'myCompare'));

        foreach ($options as $option) {
            $prioritiesName        = preg_replace('/(jform\[[^\]]+)(\].*)/', '$1_priority$2', $this->name);
            $changefreqName        = preg_replace('/(jform\[[^\]]+)(\].*)/', '$1_changefreq$2', $this->name);
            $selected              = (isset($value[$option->value]) ? ' checked="checked"' : '');
            $changePriorityField   = JHTML::_('osmap.priorities', $prioritiesName, ($selected ? $value[$option->value]['priority'] : '0.5'), $i);
            $changeChangeFreqField = JHTML::_('osmap.changefrequency', $changefreqName, ($selected ? $value[$option->value]['changefreq'] : 'weekly'), $i);

            $i++;

            $return .= <<<HTML
                <li id="menu_{$option->value}" class="osmap-menu-item">
                    <div class="osmap-cell osmap-col-selected" data-title="{$textSelected}">
                        <input type="{$type}" id="{$this->id}_{$i}" name="{$this->name}" value="{$option->value}" {$attributes} {$selected} />
                    </div>
                    <div class="osmap-cell osmap-col-title" data-title="{$textTitle}">
                        <label for="{$this->id}_{$i}" class="menu_label">{$option->text}</label>
                    </div>

                    <div class="osmap-cell osmap-col-priority osmap-menu-options" data-title="{$textChangePriority}">
                        <div class="controls">{$changePriorityField}</div>
                    </div>

                    <div class="osmap-cell osmap-col-changefreq osmap-menu-options" data-title="{$textChangeChangeFreq}">
                        <div class="controls">{$changeChangeFreqField}</div>
                    </div>
                </li>
HTML;
        }

        $return .= "</ul></div>";

        return $return;
    }

    public function myCompare($a, $b)
    {
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
