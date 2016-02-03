<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controlleradmin');


/**
 * @package     OSMap
 * @subpackage  com_osmap
 * @since       2.0
 */
class OSMapControllerDebug extends JControllerAdmin
{
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->registerTask('unpublished', 'published');
    }
    public function publish()
    {
        $ids = JRequest::getVar('cid', array(), '', 'array');
        $values = array('published' => 1, 'unpublished' => 0);
        $task = $this->getTask();
        $value = JArrayHelper::getValue($values, $task, 0, 'int');
        $model = $this->getModel();
        if (!$model->publish($ids, $value)) {
            JError::raiseWarning(500, $model->getError());
        }
        $this->setRedirect('index.php?option=com_osmap&view=debug');
    }
}
