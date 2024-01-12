<?php
/**
 * @version          $Id$
 * @copyright        Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license          GNU General Public License version 2 or later; see LICENSE.txt
 * @author           Guillermo Vargas (guille@vargas.co.cr)
 */
defined('_JEXEC') or die;

jimport('joomla.form.field');
use Joomla\CMS\HTML\HTMLHelper;
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
        if (version_compare(JVERSION, '4.0', '>'))
        {
            HTMLHelper::_('bootstrap.renderModal', 'moderateModal');
        } else
        {
            JHTML::_('behavior.modal', 'a.modal');
        }

        // Get the title of the linked chart
        if ($this->value) {
            $db->setQuery(
                    'SELECT title' .
                    ' FROM #__xmap_sitemap' .
                    ' WHERE id = ' . (int) $this->value
            );
            $title = $db->loadResult();
            if (version_compare(JVERSION, '4.0', '<')){
                if ($error = $db->getErrorMsg()) {
                    JFactory::getApplication()->enqueueMessage(500, $error, 'warning');
                }
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
        if (version_compare(JVERSION, '4.0', '<')){
            JHTML::_('behavior.modal', 'a.modal');
        }
        $html = '<span class="input-append">';
        $html .= "\n" . '<input class="input-medium" type="text" id="' . $this->id . '_name" value="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '" disabled="disabled" />';
        if(version_compare(JVERSION,'3.0.0','ge'))
            $html .= '<a class="modal btn" title="' . JText::_('COM_XMAP_CHANGE_SITEMAP') . '"  href="' . $link . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> ' . JText::_('COM_XMAP_CHANGE_SITEMAP_BUTTON') . '</a>' . "\n";
        else
            $html .= '<div class="button2-left"><div class="blank"><a class="modal btn" title="' . JText::_('COM_XMAP_CHANGE_SITEMAP') . '"  href="' . $link . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> ' . JText::_('COM_XMAP_CHANGE_SITEMAP_BUTTON') . '</a></div></div>' . "\n";
        $html .= '</span>';
        $html .= "\n" . '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . (int) $this->value . '" />';
        return $html;
    }

}