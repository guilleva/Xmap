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
// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

# For compatibility with older versions of Joola 2.5
if (!class_exists('JViewLegacy')){
    class JViewLegacy extends JView {

    }
}

/**
 * XML Sitemap View class for the OSMap component
 *
 * @package      OSMap
 * @subpackage   com_osmap
 * @since        2.0
 */
class OSMapViewXml extends JViewLegacy
{

    protected $state;
    protected $print;

    protected $_obLevel;

    function display($tpl = null)
    {
        // Initialise variables.
        $app = JFactory::getApplication();
        $this->user = JFactory::getUser();
        $isNewsSitemap = JRequest::getInt('news',0);
        $this->isImages = JRequest::getInt('images',0);

        $model = $this->getModel('Sitemap');
        $this->setModel($model);

        # Increase max execution time for XML sitemaps to make it work with very large sites
        @ini_set('max_execution_time', 300);

        $layout = $this->getLayout();

        $this->item = $this->get('Item');
        $this->state = $this->get('State');
        $this->canEdit = JFactory::getUser()->authorise('core.admin', 'com_osmap');

        // For now, news sitemaps are not editable
        $this->canEdit = $this->canEdit && !$isNewsSitemap;

        if ($layout == 'xsl') {
            return $this->displayXSL($layout);
        }

        // Get model data.
        $this->items = $this->get('Items');
        $this->sitemapItems = $this->get('SitemapItems');
        $this->extensions = $this->get('Extensions');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseWarning(500, implode("\n", $errors));
            return false;
        }

        // Add router helpers.
        $this->item->slug = $this->item->alias ? ($this->item->id . ':' . $this->item->alias) : $this->item->id;

        $this->item->rlink = JRoute::_('index.php?option=com_osmap&view=xml&id=' . $this->item->slug);

        // Create a shortcut to the paramemters.
        $params = &$this->state->params;
        $offset = $this->state->get('page.offset');

        if (!$this->item->params->get('access-view')) {
            if ($this->user->get('guest')) {
                // Redirect to login
                $uri = JFactory::getURI();
                $base = '64';
                $function = "base${base}_encode";
                $app->redirect(
                    'index.php?option=com_users&view=login&return=' . call_user_func($function, $uri),
                    JText::_('OSMAP_ERROR_LOGIN_TO_VIEW_SITEMAP')
                );
                return;
            } else {
                JError::raiseWarning(403, JText::_('OSMAP_ERROR_NOT_AUTH'));
                return;
            }
        }

        // Override the layout.
        if ($layout = $params->get('layout')) {
            $this->setLayout($layout);
        }

        // Load the class used to display the sitemap
        $this->loadTemplate('class');
        $this->displayer = new OSMapXmlDisplayer($params, $this->item);

        $this->displayer->setJView($this);

        $this->displayer->isNews = $isNewsSitemap;
        $this->displayer->isImages = $this->isImages;
        $this->displayer->canEdit = $this->canEdit;

        $doCompression = ($this->item->params->get('compress_xml') && !ini_get('zlib.output_compression') && ini_get('output_handler') != 'ob_gzhandler');
        $this->endAllBuffering();
        if ($doCompression) {
            ob_start();
        }

        parent::display($tpl);

        $model = $this->getModel();
        $model->hit($this->displayer->getCount());

        if ($doCompression) {
            $data = ob_get_contents();
            JResponse::setBody($data);
            @ob_end_clean();
            echo JResponse::toString(true);
        }
        $this->recreateBuffering();
        exit;
    }

    function displayXSL()
    {
        $this->setLayout('default');

        $this->endAllBuffering();
        parent::display('xsl');
        $this->recreateBuffering();
        exit;
    }

    private function endAllBuffering()
    {
        $this->_obLevel = ob_get_level();
        $level = FALSE;
        while (ob_get_level() > 0 && $level !== ob_get_level()) {
            @ob_end_clean();
            $level = ob_get_level();
        }
    }
    private function recreateBuffering()
    {
        while($this->_obLevel--) {
            ob_start();
        }
    }

}
