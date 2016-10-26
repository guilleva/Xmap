<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\View\Admin;

use Alledia\OSMap;
use Alledia\Framework\Joomla\Extension;

defined('_JEXEC') or die();


class Base extends OSMap\View\Base
{
    /**
     * @var JObject
     */
    protected $state = null;

    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    /**
     * Admin display
     *
     * @param null $tpl
     *
     * @throws Exception
     * @return void
     */
    public function display($tpl = null)
    {
        $this->displayHeader();

        $hide    = OSMap\Factory::getApplication()->input->getBool('hidemainmenu', false);
        $sidebar = count(\JHtmlSidebar::getEntries()) + count(\JHtmlSidebar::getFilters());
        if (!$hide && $sidebar > 0) {
            $start = array(
                '<div id="j-sidebar-container" class="span2">',
                \JHtmlSidebar::render(),
                '</div>',
                '<div id="j-main-container" class="span10">'
            );

        } else {
            $start = array('<div id="j-main-container">');
        }

        echo join("\n", $start) . "\n";
        parent::display($tpl);
        echo "\n</div>";

        $this->displayFooter();
    }

    /**
     * Default admin screen title
     *
     * @param string $sub
     * @param string $icon
     *
     * @return void
     */
    protected function setTitle($sub = null, $icon = 'osmap')
    {
        $img = \JHtml::_('image', "com_osmap/icon-48-{$icon}.png", null, null, true, true);
        if ($img) {
            $doc = OSMap\Factory::getDocument();
            $doc->addStyleDeclaration(".icon-48-{$icon} { background-image: url({$img}); }");
        }

        $title = \JText::_('COM_OSMAP');
        if ($sub) {
            $title .= ': ' . \JText::_($sub);
        }

        \JToolbarHelper::title($title, $icon);
    }

    /**
     * Render the admin screen toolbar buttons
     *
     * @param bool $addDivider
     *
     * @return void
     */
    protected function setToolBar($addDivider = true)
    {
        $user = OSMap\Factory::getUser();
        if ($user->authorise('core.admin', 'com_osmap')) {
            if ($addDivider) {
                \JToolBarHelper::divider();
            }
            \JToolBarHelper::preferences('com_osmap');
        }

        // Prepare the plugins
        \JPluginHelper::importPlugin('osmap');

        $viewName = strtolower(str_replace('OSMapView', '', $this->getName()));
        $eventParams = array(
            $viewName
        );
        \JEventDispatcher::getInstance()->trigger('osmapOnAfterSetToolBar', $eventParams);
    }

    /**
     * Render a form fieldset with the ability to compact two fields
     * into a single line
     *
     * @param string $fieldSet
     * @param array  $sameLine
     * @param bool   $tabbed
     *
     * @return string
     */
    protected function renderFieldset($fieldSet, array $sameLine = array(), $tabbed = false)
    {
        $html = array();
        if (!empty($this->form) && $this->form instanceof \JForm) {
            $fieldSets = $this->form->getFieldsets();

            if (!empty($fieldSets[$fieldSet])) {
                $name  = $fieldSets[$fieldSet]->name;
                $label = $fieldSets[$fieldSet]->label;

                $html = array();

                if ($tabbed) {
                    $html[] = \JHtml::_('bootstrap.addTab', 'myTab', $name, \JText::_($label));
                }

                $html[] = '<div class="row-fluid">';
                $html[] = '<fieldset class="adminform">';

                foreach ($this->form->getFieldset($name) as $field) {
                    if (in_array($field->fieldname, $sameLine)) {
                        continue;
                    }

                    $fieldHtml = array(
                        '<div class="control-group">',
                        '<div class="control-label">',
                        $field->label,
                        '</div>',
                        '<div class="controls">',
                        $field->input
                    );
                    $html      = array_merge($html, $fieldHtml);

                    if (isset($sameLine[$field->fieldname])) {
                        $html[] = ' ' . $this->form->getField($sameLine[$field->fieldname])->input;
                    }

                    $html[] = '</div>';
                    $html[] = '</div>';
                }
                $html[] = '</fieldset>';
                $html[] = '</div>';
                if ($tabbed) {
                    $html[] = \JHtml::_('bootstrap.endTab');
                }
            }
        }

        return join("\n", $html);
    }

    /**
     * Display a header on admin pages
     *
     * @return void
     */
    protected function displayHeader()
    {
        // To be set in subclasses
    }

    /**
     * Display a standard footer on all admin pages
     *
     * @return void
     */
    protected function displayFooter()
    {
        $extension = new Extension\Licensed('OSMap', 'component');
        echo $extension->getFooterMarkup();
    }
}
