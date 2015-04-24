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

jimport('joomla.form.field');

/**
 * Supports a modal sitemap picker.
 *
 * @package             OSMap
 * @subpackage          com_osmap
 * @since               2.0
 */
class JFormFieldModal_Sitemaps extends JFormField
{

    /**
     * The field type.
     *
     * @var    string
     */
    protected $type = 'Modal_Sitemaps';

    /**
     * Method to get a list of options for a sitemaps list input.
     *
     * @return    array        An array of JHtml options.
     */
    protected function getInput()
    {
        // Initialise variables.
        $db  = JFactory::getDBO();
        $doc = JFactory::getDocument();

        // Load the modal behavior.
        JHtml::_('behavior.modal', 'a.modal');

        // Get the title of the linked chart
        if ($this->value) {
            $db->setQuery(
                    'SELECT title' .
                    ' FROM #__osmap_sitemap' .
                    ' WHERE id = ' . (int) $this->value
            );
            $title = $db->loadResult();

            if ($error = $db->getErrorMsg()) {
                JError::raiseWarning(500, $error);
            }
        } else {
            $title = '';
        }

        if (empty($title)) {
            $title = JText::_('COM_OSMAP_SELECT_AN_SITEMAP');
        }

        $doc->addScriptDeclaration(
                  "function jSelectSitemap_" . $this->id . "(id, title, object) {
                       $('" . $this->id . "_id').value = id;
                       $('" . $this->id . "_name').value = title;
                       SqueezeBox.close();
                  }"
        );

        $link = 'index.php?option=com_osmap&amp;view=sitemaps&amp;layout=modal&amp;tmpl=component&amp;function=jSelectSitemap_' . $this->id;

        JHTML::_('behavior.modal', 'a.modal');
        $html = '<span class="input-append">';
        $html .= "\n" . '<input class="input-medium" type="text" id="' . $this->id . '_name" value="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '" disabled="disabled" />';
        if(version_compare(JVERSION,'3.0.0','ge'))
            $html .= '<a class="modal btn" title="' . JText::_('COM_OSMAP_CHANGE_SITEMAP') . '"  href="' . $link . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> ' . JText::_('COM_OSMAP_CHANGE_SITEMAP_BUTTON') . '</a>' . "\n";
        else
            $html .= '<div class="button2-left"><div class="blank"><a class="modal btn" title="' . JText::_('COM_OSMAP_CHANGE_SITEMAP') . '"  href="' . $link . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> ' . JText::_('COM_OSMAP_CHANGE_SITEMAP_BUTTON') . '</a></div></div>' . "\n";
        $html .= '</span>';
        $html .= "\n" . '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . (int) $this->value . '" />';
        return $html;
    }

}
