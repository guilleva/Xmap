<?php
/**
 * @version             $Id$
 * @copyright		Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
 */
defined('JPATH_BASE') or die;

jimport('joomla.form.field');

/**
 * Supports a modal sitemap picker.
 *
 * @package             Xmap
 * @subpackage          com_xmap
 * @since               2.0
 */
class JFormFieldModal_Sitemaps extends JFormField
{

    /**
     * The field type.
     *
     * @var	string
     */
    protected $type = 'Modal_Sitemaps';

    /**
     * Method to get a list of options for a sitemaps list input.
     *
     * @return	array		An array of JHtml options.
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
                    ' FROM #__xmap_sitemap' .
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
            $title = JText::_('COM_XMAP_SELECT_AN_SITEMAP');
        }

        $doc->addScriptDeclaration(
                "function jSelectSitemap_" . $this->id . "(id, title, object) {
			$('" . $this->id . "_id').value = id;
			$('" . $this->id . "_name').value = title;
			SqueezeBox.close();
		}"
        );

        $link = 'index.php?option=com_xmap&amp;view=sitemaps&amp;layout=modal&amp;tmpl=component&amp;function=jSelectSitemap_' . $this->id;

        JHTML::_('behavior.modal', 'a.modal');
        $html = "\n" . '<div style="float: left;"><input style="background: #ffffff;" type="text" id="' . $this->id . '_name" value="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '" disabled="disabled" /></div>';
        $html .= '<div class="button2-left"><div class="blank"><a class="modal" title="' . JText::_('COM_XMAP_CHANGE_SITEMAP') . '"  href="' . $link . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">' . JText::_('COM_XMAP_CHANGE_SITEMAP_BUTTON') . '</a></div></div>' . "\n";
        $html .= "\n" . '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . (int) $this->value . '" />';
        return $html;
    }

}