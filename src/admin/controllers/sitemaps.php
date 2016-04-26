<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved..
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

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controlleradmin');


/**
 * @package     OSMap
 * @subpackage  com_osmap
 * @since       2.0
 */
class OSMapControllerSitemaps extends JControllerAdmin
{

    protected $text_prefix = 'COM_OSMAP_SITEMAPS';

    /**
     * Constructor
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->registerTask('unpublish', 'publish');
        $this->registerTask('trash', 'publish');
        $this->registerTask('unfeatured', 'featured');
        $this->registerTask('debug_osmap_disable', 'enable');
        $this->registerTask('debug_osmap_enable', 'enable');
    }


    /**
     * Method to toggle the default sitemap.
     *
     * @return      void
     * @since       2.0
     */
    public function setDefault()
    {
        // Check for request forgeries
        JRequest::checkToken() or die('Invalid Token');

        // Get items to publish from the request.
        $cid = JRequest::getVar('cid', 0, '', 'array');
        $id  = @$cid[0];

        if (!$id) {
            JError::raiseWarning(500, JText::_('Select an item to set as default'));
        } else {
            // Get the model.
            $model = $this->getModel();

            // Publish the items.
            if (!$model->setDefault($id)) {
                JError::raiseWarning(500, $model->getError());
            }
        }

        $this->setRedirect('index.php?option=com_osmap&view=sitemaps');
    }

    /**
     * Proxy for getModel.
     *
     * @param    string    $name    The name of the model.
     * @param    string    $prefix    The prefix for the PHP class name.
     *
     * @return    JModel
     * @since    2.0
     */
    public function getModel($name = 'Sitemap', $prefix = 'OSMapModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Enables or disables Attribs items, also redirects back to correct view
     */
    public function enable()
    {
        // Check for request forgeries
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

        // Get items to publish from the request.
        $sitemapId = JFactory::getApplication()->input->get('cid', array(), 'array');
        $data      = array('enable' => 1, 'disable' => 0);
        $task      = $this->getTask();
        $taskTemp  = explode('_', $task);
        $value     = JArrayHelper::getValue($data, end($taskTemp), 0, 'int');

        if (empty($sitemapId)) {
            JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
        } else {
            $model = $this->getModel();

            $attrs = (end($taskTemp) == 'disable')
                    ? substr($task, 0, strpos($task, '_disable'))
                    : substr($task, 0, strpos($task, '_enable'));
            // Make sure the item ids are integers
            JArrayHelper::toInteger($sitemapId);

            // Publish the item.
            try {
                $model->enabled($sitemapId[0], $attrs, $value);

                if ($value == 1) {
                    $ntext = $this->text_prefix . '_N_ITEMS_ENABLED';
                } elseif ($value == 0) {
                    $ntext = $this->text_prefix . '_N_ITEMS_DISABLED';
                }

                $this->setMessage(JText::plural($ntext, count($sitemapId)));
            } catch (Exception $e) {
                $this->setMessage($e->getMessage(), 'error');
            }
        }

        $this->setRedirect(JRoute::_('index.php?option=' . $this->option
                                     . '&view=' . $this->view_list . '', false));
    }
}
