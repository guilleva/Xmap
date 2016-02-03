<?php
defined('_JEXEC') or die;

class JHtmlDebug
{
    public static function publish($i, $value = 0)
    {
        $states = array(0=> array('disabled.png','debug.published','Toggle to publish'),
        1=> array('tick.png', 'debug.unpublished', 'Toggle to unpublish'), );
        $state = JArrayHelper::getValue($states, (int) $value, $states[1]);
        $html = JHtml::_('image', 'admin/'.$state[0], JText::_($state[2]), null, true);
        $html = '<a href="#" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" title="'.
                JText::_($state[3]).'">'. $html.'</a>';
         return $html;
    }
}
