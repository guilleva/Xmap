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

defined('_JEXEC') or die();

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

JHtml::_('stylesheet', 'com_osmap/admin.min.css', array('relative' => true));
$input = JFactory::getApplication()->input;

$actionQuery = array(
    'option' => 'com_osmap',
    'view'   => 'sitemap',
    'layout' => 'edit',
    'id'     => (int)$this->item->id
);
?>
<script>
    ;(function($) {
        Joomla.submitbutton = function(task) {
            if (task === 'sitemap.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
                var ordering = $('#ul_menus').sortable('toArray').toString();
                $('#menus_ordering').val(ordering);

                Joomla.submitform(task, document.getElementById('adminForm'));
            }
        }
    })(jQuery);
</script>

<form action="<?php echo JRoute::_('index.php?' . http_build_query($actionQuery)); ?>"
      method="post"
      name="adminForm"
      id="adminForm"
      class="form-validate sitemap">

    <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="form-horizontal">
        <div class="row-fluid">
            <div class="span9">
                <?php echo $this->form->getField('menus')->renderField(array('hiddenLabel' => true)); ?>
            </div>

            <div class="span3">
                <?php echo $this->form->renderFieldset('params'); ?>
            </div>
        </div>
    </div>

    <input type="hidden" id="menus_ordering" name="jform[menus_ordering]" value=""/>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>"/>
    <?php echo JHtml::_('form.token'); ?>
</form>

