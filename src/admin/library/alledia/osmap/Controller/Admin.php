<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Controller;

use Alledia\OSMap;

defined('_JEXEC') or die();

jimport('joomla.application.component.controlleradmin');

abstract class Admin extends \JControllerAdmin
{
    /**
     * Execute a task by triggering a method in the derived class. Triggers events before and after execute the task
     *
     * @param   string  $task  The task to perform. If no matching task is found, the '__default' task is executed, if defined.
     *
     * @return  mixed   The value returned by the called method, false in error case.
     *
     * @throws  Exception
     */
    public function execute($task)
    {
        $this->task = $task;

        $task = strtolower($task);

        // Prepare the plugins
        \JPluginHelper::importPlugin('osmap');

        $controllerName = strtolower(str_replace('OSMapController', '', get_class($this)));
        $eventParams = array(
            $controllerName,
            $task
        );
        $results = \JEventDispatcher::getInstance()->trigger('osmapOnBeforeExecuteTask', $eventParams);

        // Check if any of the plugins returned the exit signal
        if (is_array($results) && in_array('exit', $results, true)) {
            OSMap\Factory::getApplication()->enqueueMessage('COM_OSMAP_MSG_TASK_STOPPED_BY_PLUGIN', 'warning');
            return;
        }

        if (isset($this->taskMap[$task])) {
            $doTask = $this->taskMap[$task];
        } elseif (isset($this->taskMap['__default'])) {
            $doTask = $this->taskMap['__default'];
        } else {
            throw new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_TASK_NOT_FOUND', $task), 404);
        }

        // Record the actual task being fired
        $this->doTask = $doTask;

        $result = $this->$doTask();

        // Runs the event after the task was executed
        $eventParams[] = &$result;
        \JEventDispatcher::getInstance()->trigger('osmapOnAfterExecuteTask', $eventParams);

        return $result;
    }

    protected function checkToken()
    {
        \JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));
    }

    /**
     * Typical view method for MVC based architecture. The parent class
     * JControllerAdmin class doesn't support this method. But we need it to
     * allow cancel a task and get back the list of items.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     *
     * @return  JControllerLegacy  A JControllerLegacy object to support chaining.
     *
     * @since   12.2
     */
    public function display($cachable = false, $urlparams = array())
    {
        $document = OSMap\Factory::getDocument();
        $viewType = $document->getType();
        $viewName = $this->input->get('view', $this->default_view);
        $viewLayout = $this->input->get('layout', 'default', 'string');

        $view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));

        // Get/Create the model
        if ($model = $this->getModel($viewName)) {
            // Push the model into the view (as default)
            $view->setModel($model, true);
        }

        $view->document = $document;

        $conf = OSMap\Factory::getConfig();

        // Display the view
        if ($cachable && $viewType != 'feed' && $conf->get('caching') >= 1) {
            $option = $this->input->get('option');
            $cache = OSMap\Factory::getCache($option, 'view');

            if (is_array($urlparams)) {
                $app = OSMap\Factory::getApplication();

                if (!empty($app->registeredurlparams)) {
                    $registeredurlparams = $app->registeredurlparams;
                } else {
                    $registeredurlparams = new stdClass;
                }

                foreach ($urlparams as $key => $value) {
                    // Add your safe url parameters with variable type as value {@see JFilterInput::clean()}.
                    $registeredurlparams->$key = $value;
                }

                $app->registeredurlparams = $registeredurlparams;
            }

            $cache->get($view, 'display');
        } else {
            $view->display();
        }

        return $this;
    }
}
