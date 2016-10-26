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

jimport('joomla.application.component.controllerform');

abstract class Form extends \JControllerForm
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
}
