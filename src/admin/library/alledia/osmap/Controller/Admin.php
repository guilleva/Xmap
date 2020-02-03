<?php
/**
 * @package   OSMap
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2020 Joomlashack.com. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSMap.
 *
 * OSMap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSMap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSMap.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Alledia\OSMap\Controller;

use Alledia\OSMap;
use Exception;
use JPluginHelper;
use JText;

defined('_JEXEC') or die();

abstract class Admin extends \JControllerAdmin
{
    /**
     * Execute a task by triggering a method in the derived class. Triggers events before and after execute the task
     *
     * @param string $task The task to perform. If no matching task is found, the '__default' task is executed, if
     *                     defined.
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
        JPluginHelper::importPlugin('osmap');

        $controllerName = strtolower(str_replace('OSMapController', '', get_class($this)));
        $eventParams    = array($controllerName, $task);
        $results        = \JEventDispatcher::getInstance()->trigger('osmapOnBeforeExecuteTask', $eventParams);

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

    public function checkToken($method = 'post', $redirect = true)
    {
        if (is_callable('parent::checkToken')) {
            return parent::checkToken($method, $redirect);
        }

        \JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

        return true;
    }
}
