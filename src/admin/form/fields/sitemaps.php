<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

jimport('joomla.form.field');

/**
 * Supports a modal sitemap picker.
 *
 * @package             OSMap
 * @subpackage          com_osmap
 * @since               2.0
 */
class JFormFieldSitemaps extends JFormField
{
    /**
     * The field type.
     *
     * @var    string
     */
    protected $type = 'Sitemaps';

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

        // Get the name of the linked chart
        if ($this->value) {
            $query = $db->getQuery(true)
                ->select('name')
                ->from('#__osmap_sitemaps')
                ->where('id = ' . (int)$this->value);
            $name = $db->setQuery($query)->loadResult();

            if ($error = $db->getErrorMsg()) {
                JError::raiseWarning(500, $error);
            }
        } else {
            $name = '';
        }

        if (empty($name)) {
            $name = JText::_('COM_OSMAP_SELECT_AN_SITEMAP');
        }

        $doc->addScriptDeclaration(
            "function jSelectSitemap_" . $this->id . "(id, name, object) {
                   $('" . $this->id . "_id').value = id;
                   $('" . $this->id . "_name').value = name;
                   SqueezeBox.close();
              }"
        );

        $link = 'index.php?option=com_osmap&amp;view=sitemaps&amp;layout=modal&amp;tmpl=component&amp;function=jSelectSitemap_' . $this->id;

        JHtml::_('behavior.modal', 'a.modal');

        $html = '<span class="input-append">' . "\n";
        $html .= '<input class="input-medium" type="text" id="' . $this->id . '_name" value="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" disabled="disabled" />';
        $html .= '<a class="modal btn" title="' . JText::_('COM_OSMAP_CHANGE_SITEMAP') . '"  href="' . $link . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> ' . JText::_('COM_OSMAP_CHANGE_SITEMAP_BUTTON') . '</a>' . "\n";
        $html .= '</span>' . "\n";
        $html .= '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . (int) $this->value . '" />';

        return $html;
    }
}
